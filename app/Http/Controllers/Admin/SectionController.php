<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SectionController extends Controller
{
    public function index()
    {
        $academicPeriods = AcademicPeriod::where('status', '!=', 'archived')->get(['id', 'academic_year', 'term', 'start_date', 'end_date', 'status']);
        $departments = Department::where('is_active', true)->with('college')->get(['id', 'name', 'code', 'college_id']);

        $sections = Section::when(request('academic_period_id'), fn($q) => $q->where('academic_period_id', request('academic_period_id')))
            ->when(request('department_id'), fn($q) => $q->where('department_id', request('department_id')))
            ->when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%'))
            ->with(['department.college'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Section/Index', [
            'sections' => $sections,
            'academicPeriods' => $academicPeriods,
            'departments' => $departments,
            'filters' => [
                'academic_period_id' => request('academic_period_id'),
                'search' => request('search'),
            ],
        ]);
    }

    public function create()
    {
        $academicPeriods = AcademicPeriod::where('status', '!=', 'archived')->get(['id', 'academic_year', 'term', 'start_date', 'end_date', 'status']);
        $departments = Department::where('is_active', true)->with('college')->get(['id', 'name', 'code', 'college_id']);

        return Inertia::render('Admin/Section/Create', [
            'academicPeriods' => $academicPeriods,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'academic_period_id' => ['required', 'exists:academic_periods,id'],
            'program_name' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'expected_enrollment' => ['nullable', 'integer', 'min:0'],
        ]);

        Section::create($data);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created.');
    }

    public function edit(Section $section)
    {
        $academicPeriods = AcademicPeriod::where('status', '!=', 'archived')->get(['id', 'academic_year', 'term', 'start_date', 'end_date', 'status']);
        $departments = Department::where('is_active', true)->with('college')->get(['id', 'name', 'code', 'college_id']);

        return Inertia::render('Admin/Section/Edit', [
            'section' => $section->load('department.college'),
            'academicPeriods' => $academicPeriods,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'academic_period_id' => ['required', 'exists:academic_periods,id'],
            'program_name' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'expected_enrollment' => ['nullable', 'integer', 'min:0'],
        ]);

        $section->update($data);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated.');
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section deleted.');
    }
}