<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Candidate;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\Voter;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstitutionStructureController extends Controller
{
    private const RESOURCES = [
        'faculties' => Faculty::class,
        'departments' => Department::class,
        'courses' => Course::class,
        'academic-years' => AcademicYear::class,
        'class-groups' => ClassGroup::class,
    ];

    public function index(Request $request): View
    {
        $institution = $this->institution($request);
        $records = [];

        foreach (self::RESOURCES as $resource => $model) {
            $records[$resource] = $this->query($resource, $institution)
                ->with($this->relations($resource))
                ->orderBy('name')
                ->get();
        }

        return view('admin.institution.structure', [
            'institution' => $institution,
            'records' => $records,
            'faculties' => $institution->faculties()->orderBy('name')->get(),
            'departments' => Department::query()->whereHas('faculty', fn ($query) => $query->where('institution_id', $institution->id))->orderBy('name')->get(),
            'courses' => Course::query()->whereHas('department.faculty', fn ($query) => $query->where('institution_id', $institution->id))->orderBy('name')->get(),
            'years' => $institution->academicYears()->orderBy('starts_on')->get(),
        ]);
    }

    public function store(Request $request, string $resource, AuditLogger $auditLogger): RedirectResponse
    {
        $institution = $this->institution($request);
        $class = $this->model($resource);
        $data = $this->validated($request, $resource, $institution);

        DB::transaction(function () use ($class, $resource, $data, $institution, $auditLogger): void {
            if ($resource === 'academic-years' && ($data['is_current'] ?? false)) {
                $institution->academicYears()->lockForUpdate()->update(['is_current' => false]);
            }

            $class::query()->create($data);
            $auditLogger->record('institution_structure.created', [
                'institution_id' => $institution->id,
                'resource' => $resource,
            ]);
        });

        return back()->with('success', __('Taarifa zimeongezwa.'));
    }

    public function update(Request $request, string $resource, int $record, AuditLogger $auditLogger): RedirectResponse
    {
        $institution = $this->institution($request);
        $class = $this->model($resource);
        $model = $this->query($resource, $institution)->findOrFail($record);
        $data = $this->validated($request, $resource, $institution);

        DB::transaction(function () use ($class, $model, $resource, $data, $institution, $auditLogger): void {
            if ($resource === 'academic-years' && ($data['is_current'] ?? false)) {
                $institution->academicYears()->lockForUpdate()->where('id', '<>', $model->id)->update(['is_current' => false]);
            }

            $class::query()->lockForUpdate()->findOrFail($model->id)->update($data);
            $auditLogger->record('institution_structure.updated', [
                'institution_id' => $institution->id,
                'resource' => $resource,
                'record_id' => $model->id,
            ]);
        });

        return back()->with('success', __('Taarifa zimehifadhiwa.'));
    }

    public function destroy(Request $request, string $resource, int $record, AuditLogger $auditLogger): RedirectResponse
    {
        $institution = $this->institution($request);
        $model = $this->query($resource, $institution)->findOrFail($record);

        if ($this->hasDependents($resource, $model->id)) {
            throw ValidationException::withMessages([
                'record' => __('Rekodi hii haiwezi kufutwa kwa sababu taarifa nyingine zinaitegemea.'),
            ]);
        }

        $model->delete();
        $auditLogger->record('institution_structure.deleted', [
            'institution_id' => $institution->id,
            'resource' => $resource,
            'record_id' => $model->id,
        ]);

        return back()->with('success', __('Taarifa zimefutwa.'));
    }

    private function institution(Request $request): Institution
    {
        $user = $request->user();
        $institutionId = $user->is_super_admin
            ? $request->integer('institution_id') ?: $user->institution_id
            : $user->institution_id;

        abort_if(! $institutionId, 404);

        return Institution::query()->findOrFail($institutionId);
    }

    private function model(string $resource): string
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);

        return self::RESOURCES[$resource];
    }

    private function query(string $resource, Institution $institution): Builder
    {
        $class = $this->model($resource);
        $query = $class::query();

        return match ($resource) {
            'faculties', 'academic-years' => $query->where('institution_id', $institution->id),
            'departments' => $query->whereHas('faculty', fn ($q) => $q->where('institution_id', $institution->id)),
            'courses' => $query->whereHas('department.faculty', fn ($q) => $q->where('institution_id', $institution->id)),
            'class-groups' => $query->whereHas('course.department.faculty', fn ($q) => $q->where('institution_id', $institution->id)),
        };
    }

    private function validated(Request $request, string $resource, Institution $institution): array
    {
        $rules = match ($resource) {
            'faculties' => ['name' => ['required', 'string', 'max:255'], 'code' => ['required', 'string', 'max:100']],
            'departments' => ['name' => ['required', 'string', 'max:255'], 'code' => ['required', 'string', 'max:100'], 'faculty_id' => ['required', 'integer']],
            'courses' => [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:100'],
                'department_id' => ['required', 'integer'],
                'duration_years' => ['required', 'integer', 'min:1', 'max:10'],
            ],
            'academic-years' => [
                'name' => ['required', 'string', 'max:255'],
                'starts_on' => ['required', 'date'],
                'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
                'is_current' => ['sometimes', 'boolean'],
            ],
            'class-groups' => [
                'name' => ['required', 'string', 'max:255'],
                'course_id' => ['required', 'integer'],
                'academic_year_id' => ['required', 'integer'],
                'year_of_study' => ['required', 'integer', 'min:1', 'max:10'],
            ],
        };
        $data = $request->validate($rules);

        if ($resource === 'faculties' || $resource === 'academic-years') {
            $data['institution_id'] = $institution->id;
        }

        if ($resource === 'departments') {
            abort_unless($institution->faculties()->whereKey($data['faculty_id'])->exists(), 404);
        }

        if ($resource === 'courses') {
            abort_unless(Department::query()->whereKey($data['department_id'])
                ->whereHas('faculty', fn ($q) => $q->where('institution_id', $institution->id))->exists(), 404);
        }

        if ($resource === 'class-groups') {
            abort_unless(Course::query()->whereKey($data['course_id'])
                ->whereHas('department.faculty', fn ($q) => $q->where('institution_id', $institution->id))->exists(), 404);
            abort_unless($institution->academicYears()->whereKey($data['academic_year_id'])->exists(), 404);
        }

        return $data;
    }

    private function relations(string $resource): array
    {
        return match ($resource) {
            'departments' => ['faculty'],
            'courses' => ['department'],
            'class-groups' => ['course', 'academicYear'],
            default => [],
        };
    }

    private function hasDependents(string $resource, int $id): bool
    {
        return match ($resource) {
            'faculties' => Department::query()->where('faculty_id', $id)->exists(),
            'departments' => Course::query()->where('department_id', $id)->exists()
                || Voter::query()->where('department_id', $id)->exists()
                || Candidate::query()->where('department_id', $id)->exists(),
            'courses' => ClassGroup::query()->where('course_id', $id)->exists()
                || Voter::query()->where('course_id', $id)->exists(),
            'academic-years' => ClassGroup::query()->where('academic_year_id', $id)->exists(),
            'class-groups' => Voter::query()->where('class_group_id', $id)->exists(),
            default => true,
        };
    }
}
