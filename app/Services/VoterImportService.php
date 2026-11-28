<?php

namespace App\Services;

use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\Department;
use App\Models\Election;
use App\Models\Voter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetReaderException;
use RuntimeException;

class VoterImportService
{
    /**
     * Import valid rows, skipping duplicates and returning codes only in memory.
     *
     * @return array{imported: int, duplicates: int, invalid: int, codes: array<int, array{reg_number: string, name: string, phone: string, access_code: string}>, errors: array<int, array{row: int, reg_number: string, reason: string}>}
     */
    public function import(Election $election, UploadedFile $file): array
    {
        $isSpreadsheet = strtolower($file->getClientOriginalExtension()) === 'xlsx';
        $handle = $isSpreadsheet ? null : fopen($file->getRealPath(), 'rb');
        $spreadsheetRows = [];

        try {
            if ($isSpreadsheet) {
                if (! class_exists(\ZipArchive::class)) {
                    throw ValidationException::withMessages(['csv' => __('Kuwasha PHP ZIP kunahitajika kusoma faili za XLSX.')]);
                }

                try {
                    $spreadsheetRows = IOFactory::load($file->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
                } catch (SpreadsheetReaderException $exception) {
                    throw ValidationException::withMessages(['csv' => __('Faili la XLSX halikuweza kusomwa.')]);
                }

                $headers = array_shift($spreadsheetRows) ?: false;
            } else {
                if ($handle === false) {
                    throw ValidationException::withMessages(['csv' => __('Faili halikuweza kusomwa.')]);
                }

                $headers = fgetcsv($handle);
            }

            if ($headers === false) {
                throw ValidationException::withMessages(['csv' => __('Faili la CSV halina vichwa vya safu.')]);
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);
            $columns = array_flip($headers);

            foreach (['reg_number', 'name'] as $requiredColumn) {
                if (! array_key_exists($requiredColumn, $columns)) {
                    throw ValidationException::withMessages([
                        'csv' => __('CSV lazima iwe na safu reg_number na name. Safu za phone na muundo wa masomo ni za hiari.'),
                    ]);
                }
            }

            $result = ['imported' => 0, 'duplicates' => 0, 'invalid' => 0, 'codes' => [], 'errors' => []];
            $seenRegistrationNumbers = [];
            $seenFingerprints = [];

            DB::transaction(function () use ($handle, $spreadsheetRows, $isSpreadsheet, $columns, $election, &$result, &$seenRegistrationNumbers, &$seenFingerprints): void {
                $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);

                if ($lockedElection->status !== 'draft') {
                    throw ValidationException::withMessages(['csv' => 'Wapiga kura wanaweza kuongezwa kabla ya uchaguzi kufunguliwa pekee.']);
                }

                $rowNumber = 1;
                $rows = $isSpreadsheet ? $spreadsheetRows : null;
                while ($isSpreadsheet ? $rowNumber - 1 < count($rows) : ($row = fgetcsv($handle)) !== false) {
                    if ($isSpreadsheet) {
                        $row = $rows[$rowNumber - 1];
                    }
                    $rowNumber++;

                    if ($row === [null] || $row === []) {
                        continue;
                    }

                    $registrationNumber = trim((string) ($row[$columns['reg_number']] ?? ''));
                    $name = trim((string) ($row[$columns['name']] ?? ''));
                    $phone = trim((string) ($row[$columns['phone'] ?? -1] ?? ''));

                    if ($registrationNumber === '' || $name === '' || mb_strlen($registrationNumber) > 100 || mb_strlen($name) > 255 || mb_strlen($phone) > 50) {
                        $result['invalid']++;
                        $result['errors'][] = ['row' => $rowNumber, 'reg_number' => $registrationNumber, 'reason' => __('Taarifa za lazima hazipo au zimezidi urefu unaoruhusiwa.')];

                        continue;
                    }

                    $academicIds = $this->academicIds($lockedElection, $row, $columns);
                    if ($academicIds === null) {
                        $result['invalid']++;
                        $result['errors'][] = ['row' => $rowNumber, 'reg_number' => $registrationNumber, 'reason' => __('Muundo wa idara, kozi au darasa haupatikani katika taasisi hii.')];

                        continue;
                    }

                    $duplicateKey = mb_strtolower($registrationNumber);

                    if (isset($seenRegistrationNumbers[$duplicateKey]) || $lockedElection->voters()->where('reg_number', $registrationNumber)->exists()) {
                        $result['duplicates']++;
                        $result['errors'][] = ['row' => $rowNumber, 'reg_number' => $registrationNumber, 'reason' => __('Namba ya usajili imeshatumika.')];

                        continue;
                    }

                    $seenRegistrationNumbers[$duplicateKey] = true;
                    [$accessCode, $fingerprint] = $this->uniqueAccessCode($seenFingerprints);

                    $lockedElection->voters()->create([
                        'reg_number' => $registrationNumber,
                        'name' => $name,
                        'phone' => $phone !== '' ? $phone : null,
                        ...$academicIds,
                        'access_code' => Hash::make($accessCode),
                        'access_code_fingerprint' => $fingerprint,
                    ]);

                    $result['codes'][] = [
                        'reg_number' => $registrationNumber,
                        'name' => $name,
                        'phone' => $phone,
                        'access_code' => $accessCode,
                    ];
                    $result['imported']++;
                }

            });

            return $result;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int>  $columns
     * @return array{department_id: int|null, course_id: int|null, class_group_id: int|null}|null
     */
    private function academicIds(Election $election, array $row, array $columns): ?array
    {
        $departmentCode = trim((string) ($row[$columns['department_code'] ?? -1] ?? ''));
        $courseCode = trim((string) ($row[$columns['course_code'] ?? -1] ?? ''));
        $className = trim((string) ($row[$columns['class_name'] ?? -1] ?? ''));
        $institutionId = $election->institution_id;
        $department = null;
        $course = null;
        $classGroup = null;

        if ($departmentCode !== '') {
            $department = Department::query()
                ->where('code', $departmentCode)
                ->whereHas('faculty', fn ($query) => $query->where('institution_id', $institutionId))
                ->first();
            if (! $department) {
                return null;
            }
        }

        if ($courseCode !== '') {
            $course = Course::query()
                ->where('code', $courseCode)
                ->when($department, fn ($query) => $query->where('department_id', $department->id))
                ->whereHas('department.faculty', fn ($query) => $query->where('institution_id', $institutionId))
                ->first();
            if (! $course || ($department && $course->department_id !== $department->id)) {
                return null;
            }
            $department ??= $course->department;
        }

        if ($className !== '') {
            $classGroup = ClassGroup::query()
                ->where('name', $className)
                ->when($course, fn ($query) => $query->where('course_id', $course->id))
                ->whereHas('course.department.faculty', fn ($query) => $query->where('institution_id', $institutionId))
                ->first();
            if (! $classGroup || ($course && $classGroup->course_id !== $course->id)) {
                return null;
            }
            $course ??= $classGroup->course;
            $department ??= $course->department;
        }

        return [
            'department_id' => $department?->id,
            'course_id' => $course?->id,
            'class_group_id' => $classGroup?->id,
        ];
    }

    /**
     * @param  array<string, bool>  $seenFingerprints
     * @return array{string, string}
     */
    private function uniqueAccessCode(array &$seenFingerprints): array
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $fingerprint = $this->fingerprint($code);

            if (isset($seenFingerprints[$fingerprint])) {
                continue;
            }

            $exists = Voter::query()
                ->where('access_code_fingerprint', $fingerprint)
                ->exists();

            if (! $exists) {
                $seenFingerprints[$fingerprint] = true;

                return [$code, $fingerprint];
            }
        }

        throw new RuntimeException('Unable to allocate a unique voter access code.');
    }

    private function fingerprint(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }
}
