<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\Department;
use App\Models\Election;
use App\Models\Voter;
use App\Services\AuditLogger;
use App\Services\VoterImportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VoterController extends Controller
{
    public function index(Request $request): View
    {
        $voters = Voter::query()
            ->with(['election.institution', 'department.faculty', 'course', 'classGroup'])
            ->when($request->filled('election_id'), fn (Builder $query) => $query->where('election_id', $request->integer('election_id')))
            ->when($request->filled('faculty_id'), fn (Builder $query) => $query->whereHas('department.faculty', fn ($q) => $q->whereKey($request->integer('faculty_id'))))
            ->when($request->filled('department_id'), fn (Builder $query) => $query->where('department_id', $request->integer('department_id')))
            ->when($request->filled('course_id'), fn (Builder $query) => $query->where('course_id', $request->integer('course_id')))
            ->when($request->filled('class_group_id'), fn (Builder $query) => $query->where('class_group_id', $request->integer('class_group_id')))
            ->when($request->filled('verified'), fn (Builder $query) => $request->boolean('verified') ? $query->whereNotNull('verified_at') : $query->whereNull('verified_at'))
            ->when($request->filled('active'), fn (Builder $query) => $query->where('is_active', $request->boolean('active')))
            ->when($request->filled('voted'), fn (Builder $query) => $query->where('has_voted', $request->boolean('voted')))
            ->when($request->filled('q'), fn (Builder $query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%')->orWhere('reg_number', 'like', '%'.$request->string('q').'%')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $institutionId = $request->user()->is_super_admin
            ? $request->integer('institution_id') ?: null
            : $request->user()->institution_id;

        return view('admin.voters.index', [
            'voters' => $voters,
            'elections' => Election::query()->orderByDesc('starts_at')->get(),
            'departments' => Department::query()
                ->with('faculty')
                ->when($institutionId, fn ($query) => $query->whereHas('faculty', fn ($faculty) => $faculty->where('institution_id', $institutionId)))
                ->when(! $request->user()->is_super_admin && ! $institutionId, fn ($query) => $query->whereRaw('1 = 0'))
                ->orderBy('name')
                ->get(),
            'courses' => Course::query()
                ->with('department')
                ->when($institutionId, fn ($query) => $query->whereHas('department.faculty', fn ($faculty) => $faculty->where('institution_id', $institutionId)))
                ->when(! $request->user()->is_super_admin && ! $institutionId, fn ($query) => $query->whereRaw('1 = 0'))
                ->orderBy('name')
                ->get(),
            'classGroups' => ClassGroup::query()
                ->with(['course', 'academicYear'])
                ->when($institutionId, fn ($query) => $query->whereHas('course.department.faculty', fn ($faculty) => $faculty->where('institution_id', $institutionId)))
                ->when(! $request->user()->is_super_admin && ! $institutionId, fn ($query) => $query->whereRaw('1 = 0'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, AuditLogger $auditLogger): StreamedResponse
    {
        $data = $request->validate([
            'election_id' => ['required', 'integer'],
            'reg_number' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);
        $election = Election::query()->findOrFail($data['election_id']);
        abort_unless($election->status === 'draft', 403);
        $this->assertRegistrationPattern($election, $data['reg_number']);

        $code = $this->newCode();
        $voter = $election->voters()->create([
            ...$data,
            'access_code' => Hash::make($code),
            'access_code_fingerprint' => hash_hmac('sha256', $code, (string) config('app.key')),
        ]);
        $auditLogger->record('voter.created', ['election_id' => $election->id, 'voter_id' => $voter->id]);

        return $this->codeDownload([[$voter->reg_number, $voter->name, (string) $voter->phone, $code]], 'msimbo-mpya.csv');
    }

    public function update(Request $request, Voter $voter, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($voter->election->status === 'draft', 403);
        $data = $request->validate([
            'reg_number' => ['required', 'string', 'max:100', 'unique:voters,reg_number,'.$voter->id.',id,election_id,'.$voter->election_id],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'integer'],
            'course_id' => ['nullable', 'integer'],
            'class_group_id' => ['nullable', 'integer'],
        ]);
        $this->assertRegistrationPattern($voter->election, $data['reg_number']);
        $this->assertAcademicStructure($voter->election, $data);

        $voter->update($data);
        $auditLogger->record('voter.updated', ['election_id' => $voter->election_id, 'voter_id' => $voter->id]);

        return back()->with('success', __('Taarifa za mpiga kura zimehifadhiwa.'));
    }

    public function verify(Request $request, Voter $voter, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($voter->election->status === 'draft', 403);
        $verified = $request->boolean('verified');
        $voter->update([
            'verified_at' => $verified ? now() : null,
            'verified_by' => $verified ? $request->user()->id : null,
        ]);
        $auditLogger->record($verified ? 'voter.verified' : 'voter.unverified', ['election_id' => $voter->election_id, 'voter_id' => $voter->id]);

        return back()->with('success', $verified ? __('Mpiga kura amethibitishwa.') : __('Uthibitisho wa mpiga kura umeondolewa.'));
    }

    public function active(Request $request, Voter $voter, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($voter->election->status === 'draft', 403);
        $active = $request->boolean('active');
        $voter->update(['is_active' => $active]);
        $auditLogger->record($active ? 'voter.activated' : 'voter.deactivated', ['election_id' => $voter->election_id, 'voter_id' => $voter->id]);

        return back()->with('success', $active ? __('Akaunti ya mpiga kura imewezeshwa.') : __('Akaunti ya mpiga kura imezimwa.'));
    }

    public function bulk(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'voter_ids' => ['required', 'array', 'min:1'],
            'voter_ids.*' => ['required', 'integer'],
            'action' => ['required', 'in:verify,unverify,activate,deactivate'],
        ]);
        $voters = Voter::query()->whereIn('id', $data['voter_ids'])->with('election')->get();
        abort_unless($voters->count() === count(array_unique($data['voter_ids'])), 404);
        abort_if($voters->contains(fn (Voter $voter) => $voter->election->status !== 'draft'), 403);
        $changes = match ($data['action']) {
            'verify' => ['verified_at' => now(), 'verified_by' => $request->user()->id],
            'unverify' => ['verified_at' => null, 'verified_by' => null],
            'activate' => ['is_active' => true],
            'deactivate' => ['is_active' => false],
        };
        Voter::query()->whereIn('id', $voters->modelKeys())->update($changes);
        $auditLogger->record('voters.bulk_updated', ['action' => $data['action'], 'count' => $voters->count()]);

        return back()->with('success', __('Taarifa za wapiga kura zimesasishwa.'));
    }

    public function regenerate(Voter $voter, AuditLogger $auditLogger): StreamedResponse
    {
        abort_unless($voter->election->status === 'draft' && ! $voter->has_voted, 403);
        $code = $this->newCode();
        $voter->update([
            'access_code' => Hash::make($code),
            'access_code_fingerprint' => hash_hmac('sha256', $code, (string) config('app.key')),
        ]);
        $auditLogger->record('voter.access_code_regenerated', ['election_id' => $voter->election_id, 'voter_id' => $voter->id]);

        return $this->codeDownload([[$voter->reg_number, $voter->name, (string) $voter->phone, $code]], 'msimbo-mpya.csv');
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['reg_number', 'name', 'phone', 'department_code', 'course_code', 'class_name']);
            fclose($output);
        }, 'kiolezo-cha-wapiga-kura.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(
        Request $request,
        Election $election,
        VoterImportService $importService,
        AuditLogger $auditLogger,
    ): StreamedResponse|RedirectResponse {
        abort_unless($election->status === 'draft', 403);
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120']]);

        $result = $importService->import($election, $request->file('csv'));
        $request->session()->flash('import_summary', [
            'imported' => $result['imported'],
            'duplicates' => $result['duplicates'],
            'invalid' => $result['invalid'],
        ]);
        $request->session()->flash('import_errors', $result['errors']);
        $auditLogger->record('voters.imported', [
            'election_id' => $election->id,
            'imported' => $result['imported'],
            'duplicates' => $result['duplicates'],
            'invalid' => $result['invalid'],
        ]);
        $auditLogger->record('voters.access_codes_downloaded', [
            'election_id' => $election->id,
            'count' => count($result['codes']),
        ]);

        if ($result['codes'] === []) {
            return redirect()->route('admin.elections.show', $election)
                ->with('success', 'Uingizaji umekamilika. Hakukuwa na msimbo mpya wa kupakua.')
                ->with('import_summary', [
                    'imported' => $result['imported'],
                    'duplicates' => $result['duplicates'],
                    'invalid' => $result['invalid'],
                ]);
        }

        $filename = 'misimbo-kura-'.$election->id.'.csv';

        return response()->streamDownload(function () use ($result): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['reg_number', 'name', 'phone', 'access_code'], ',', '"', '');

            foreach ($result['codes'] as $row) {
                fputcsv($output, array_map($this->safeCsvValue(...), array_values($row)), ',', '"', '');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'X-Kurapoa-Import-Count' => (string) $result['imported'],
            'X-Kurapoa-Duplicate-Count' => (string) $result['duplicates'],
            'X-Kurapoa-Invalid-Count' => (string) $result['invalid'],
        ]);
    }

    public function downloadImportErrors(Request $request): StreamedResponse
    {
        $errors = $request->session()->get('import_errors', []);
        abort_if($errors === [], 404);

        return response()->streamDownload(function () use ($errors): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['row', 'reg_number', 'reason']);
            foreach ($errors as $error) {
                fputcsv($output, array_map($this->safeCsvValue(...), [
                    (string) $error['row'],
                    (string) $error['reg_number'],
                    (string) $error['reason'],
                ]));
            }
            fclose($output);
        }, 'makosa-ya-wapiga-kura.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    private function assertAcademicStructure(Election $election, array $data): void
    {
        $department = isset($data['department_id'])
            ? Department::query()->whereKey($data['department_id'])
                ->whereHas('faculty', fn ($query) => $query->where('institution_id', $election->institution_id))
                ->first()
            : null;
        abort_if(isset($data['department_id']) && ! $department, 404);

        $course = isset($data['course_id'])
            ? Course::query()->whereKey($data['course_id'])
                ->whereHas('department.faculty', fn ($query) => $query->where('institution_id', $election->institution_id))
                ->first()
            : null;
        abort_if(isset($data['course_id']) && (! $course || ($department && $course->department_id !== $department->id)), 404);

        $classGroup = isset($data['class_group_id'])
            ? ClassGroup::query()->whereKey($data['class_group_id'])
                ->whereHas('course.department.faculty', fn ($query) => $query->where('institution_id', $election->institution_id))
                ->first()
            : null;
        abort_if(isset($data['class_group_id']) && (! $classGroup || ($course && $classGroup->course_id !== $course->id)), 404);
    }

    private function safeCsvValue(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }

    private function newCode(): string
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            if (! Voter::query()->where('access_code_fingerprint', hash_hmac('sha256', $code, (string) config('app.key')))->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Unable to allocate a unique voter access code.');
    }

    private function assertRegistrationPattern(Election $election, string $registrationNumber): void
    {
        $pattern = $election->institution?->reg_number_pattern;

        if ($pattern && preg_match($pattern, $registrationNumber) !== 1) {
            throw ValidationException::withMessages([
                'reg_number' => __('Namba ya usajili haifuati muundo wa taasisi.'),
            ]);
        }
    }

    private function codeDownload(array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['reg_number', 'name', 'phone', 'access_code']);
            foreach ($rows as $row) {
                fputcsv($output, array_map($this->safeCsvValue(...), $row));
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
