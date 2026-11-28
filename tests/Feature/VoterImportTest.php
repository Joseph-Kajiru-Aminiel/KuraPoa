<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\Department;
use App\Models\Election;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\Voter;
use App\Services\VoterImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VoterImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_skips_duplicates_and_returns_unique_plain_codes_once(): void
    {
        $election = Election::create([
            'title' => 'Uchaguzi wa majaribio',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addDay(),
            'status' => 'draft',
        ]);
        $file = UploadedFile::fake()->createWithContent(
            'wapiga-kura.csv',
            "reg_number,name,phone\nMOCU/2026/001,Asha Mussa,0712345678\nMOCU/2026/001,Asha Mussa,0712345678\n,Haijakamilika,\n",
        );

        $result = app(VoterImportService::class)->import($election, $file);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, $result['duplicates']);
        $this->assertSame(1, $result['invalid']);
        $this->assertCount(1, $result['codes']);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $result['codes'][0]['access_code']);
        $this->assertSame(1, Voter::count());
        $this->assertTrue(Hash::check($result['codes'][0]['access_code'], Voter::firstOrFail()->access_code));
        $this->assertNotSame($result['codes'][0]['access_code'], Voter::firstOrFail()->access_code);
        $this->assertNotNull(Voter::firstOrFail()->access_code_fingerprint);
    }

    public function test_import_rejects_csv_without_required_headers(): void
    {
        $election = Election::create([
            'title' => 'Uchaguzi wa majaribio',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addDay(),
            'status' => 'draft',
        ]);
        $file = UploadedFile::fake()->createWithContent('wapiga-kura.csv', "namba,jina\n001,Asha\n");

        $this->expectException(ValidationException::class);
        app(VoterImportService::class)->import($election, $file);
    }

    public function test_voters_cannot_be_imported_after_an_election_opens(): void
    {
        $election = Election::create([
            'title' => 'Uchaguzi unaoendelea',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'open',
        ]);
        $file = UploadedFile::fake()->createWithContent(
            'wapiga-kura.csv',
            "reg_number,name,phone\nMOCU/2026/001,Asha Mussa,0712345678\n",
        );

        try {
            app(VoterImportService::class)->import($election, $file);
            $this->fail('Voter registration must remain stable after the election opens.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('csv', $exception->errors());
        }

        $this->assertDatabaseCount('voters', 0);
    }

    public function test_csv_import_maps_academic_codes_within_the_elections_institution(): void
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => 'IMPORT-1']);
        $election = Election::create([
            'title' => 'Uchaguzi wa majaribio',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addDay(),
            'status' => 'draft',
            'institution_id' => $institution->id,
        ]);
        $faculty = Faculty::create(['institution_id' => $institution->id, 'name' => 'Sayansi', 'code' => 'SCI']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'TEHAMA', 'code' => 'ICT']);
        $course = Course::create(['department_id' => $department->id, 'name' => 'Teknolojia', 'code' => 'IT', 'duration_years' => 3]);
        $academicYear = AcademicYear::create([
            'institution_id' => $institution->id,
            'name' => '2026/2027',
            'starts_on' => '2026-09-01',
            'ends_on' => '2027-08-31',
        ]);
        $classGroup = ClassGroup::create([
            'course_id' => $course->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Mwaka wa Kwanza',
            'year_of_study' => 1,
        ]);
        $file = UploadedFile::fake()->createWithContent(
            'wapiga-kura.csv',
            "reg_number,name,department_code,course_code,class_name\nMOCU/2026/001,Asha,ICT,IT,Mwaka wa Kwanza\n",
        );

        $result = app(VoterImportService::class)->import($election, $file);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('voters', [
            'reg_number' => 'MOCU/2026/001',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'class_group_id' => $classGroup->id,
        ]);
    }
}
