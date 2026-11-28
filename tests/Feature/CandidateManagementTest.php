<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_photo_is_saved_as_a_cropped_file_and_candidate_waits_for_approval(): void
    {
        Storage::fake('public');
        [$admin, $election, $position] = $this->electionForInstitution('CANDIDATE-A');

        $this->actingAs($admin)
            ->post(route('admin.candidates.store', [$election, $position]), [
                'name' => 'Asha Juma',
                'reg_number' => 'MOCU/2026/101',
                'manifesto' => 'Uwazi na uwajibikaji.',
                'photo' => UploadedFile::fake()->image('asha.png', 800, 600),
            ])
            ->assertRedirect();

        $candidate = Candidate::query()->where('reg_number', 'MOCU/2026/101')->firstOrFail();
        $this->assertSame('pending', $candidate->status);
        $this->assertStringEndsWith('.jpg', $candidate->photo);
        $this->assertStringEndsWith('.jpg', $candidate->photo_thumbnail);
        Storage::disk('public')->assertExists($candidate->photo);
        Storage::disk('public')->assertExists($candidate->photo_thumbnail);
        $profileSize = getimagesize(Storage::disk('public')->path($candidate->photo));
        $thumbnailSize = getimagesize(Storage::disk('public')->path($candidate->photo_thumbnail));
        $this->assertSame([512, 512], [$profileSize[0], $profileSize[1]]);
        $this->assertSame([160, 160], [$thumbnailSize[0], $thumbnailSize[1]]);
    }

    public function test_admin_can_remove_existing_candidate_photos_and_files(): void
    {
        Storage::fake('public');
        [$admin, $election, $position] = $this->electionForInstitution('CANDIDATE-C');
        $photo = 'candidates/profile.jpg';
        $thumbnail = 'candidates/thumbnails/profile.jpg';
        Storage::disk('public')->put($photo, 'profile image');
        Storage::disk('public')->put($thumbnail, 'thumbnail image');
        $candidate = Candidate::create([
            'position_id' => $position->id,
            'name' => 'Asha Juma',
            'reg_number' => 'MOCU/2026/102',
            'photo' => $photo,
            'photo_thumbnail' => $thumbnail,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.candidates.update', [$election, $position, $candidate]), [
                'name' => $candidate->name,
                'reg_number' => $candidate->reg_number,
                'remove_photo' => true,
            ])
            ->assertRedirect();

        $this->assertNull($candidate->fresh()->photo);
        $this->assertNull($candidate->fresh()->photo_thumbnail);
        Storage::disk('public')->assertMissing([$photo, $thumbnail]);
    }

    public function test_candidate_cannot_be_assigned_to_a_department_from_another_institution(): void
    {
        [$admin, $election, $position] = $this->electionForInstitution('CANDIDATE-A');
        $otherInstitution = Institution::create(['name' => 'Chuo B', 'code' => 'CANDIDATE-B']);
        $faculty = Faculty::create(['institution_id' => $otherInstitution->id, 'name' => 'Sayansi', 'code' => 'SCI']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'TEHAMA', 'code' => 'ICT']);

        $this->actingAs($admin)
            ->post(route('admin.candidates.store', [$election, $position]), [
                'name' => 'Juma Paulo',
                'reg_number' => 'MOCU/2026/102',
                'department_id' => $department->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('candidates', ['reg_number' => 'MOCU/2026/102']);
    }

    /**
     * @return array{User, Election, Position}
     */
    private function electionForInstitution(string $code): array
    {
        $institution = Institution::create(['name' => 'Chuo cha Jaribio', 'code' => $code]);
        $admin = User::factory()->create(['is_admin' => true, 'institution_id' => $institution->id]);
        $election = Election::create([
            'title' => 'Uchaguzi wa majaribio',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'status' => 'draft',
            'institution_id' => $institution->id,
        ]);
        $position = Position::create(['election_id' => $election->id, 'title' => 'Rais']);

        return [$admin, $election, $position];
    }
}
