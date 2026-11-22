<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\ClassOffering;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClassOfferingController extends Controller
{
    public function index()
    {
        $academicPeriods = AcademicPeriod::where('status', '!=', 'archived')->get(['id', 'academic_year', 'term', 'start_date', 'end_date', 'status']);
        $departments = Department::where('is_active', true)->with('college')->get(['id', 'name', 'code', 'college_id']);
        $subjects = Subject::where('is_active', true)->get(['id', 'code', 'title', 'units']);
        $faculty = Faculty::where('status', 'active')->get(['id', 'employee_no', 'first_name', 'last_name']);

        $classOfferings = ClassOffering::when(request('academic_period_id'), fn($q) => $q->where('academic_period_id', request('academic_period_id')))
            ->when(request('department_id'), fn($q) => $q->where('department_id', request('department_id')))
            ->when(request('subject_id'), fn($q) => $q->where('subject_id', request('subject_id')))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('search'), fn($q) => $q->whereHas('subject', fn($q) => $q->where('code', 'like', '%'.request('search').'%')->orWhere('title', 'like', '%'.request('search').'%')))
            ->with(['academicPeriod', 'department.college', 'subject', 'section', 'faculty'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/ClassOffering/Index', [
            'classOfferings' => $classOfferings,
            'academicPeriods' => $academicPeriods,
            'departments' => $departments,
            'subjects' => $subjects,
            'faculty' => $faculty,
            'filters' => [
                'academic_period_id' => request('academic_period_id'),
                'department_id' => request('department_id'),
                'subject_id' => request('subject_id'),
                'status' => request('status'),
                'search' => request('search'),
            ],
        ]);
    }

    public function create()
    {
        $academicPeriods = AcademicPeriod::where('status', '!=', 'archived')->get(['id', 'academic_year', 'term', 'start_date', 'end_date', 'status']);
        $departments = Department::where('is_active', true)->with('college')->get(['id', 'name', 'code', 'college_id']);
        $subjects = Subject::where('is_active', true)->get(['id', 'code', 'title', 'units', 'lecture_hours', 'laboratory_hours']);
        $faculty = Faculty::where('status', 'active')->get(['id', 'employee_no', 'first_name', 'middle_name', 'last_name', 'email']);

        return Inertia::render('Admin/ClassOffering/Create', [
            'academicPeriods' => $academicPeriods,
            'departments' => $departments,
            'subjects' => $subjects,
            'faculty' => $faculty,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_period_id' => ['required', 'exists:academic_periods,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'delivery_mode' => ['required', 'in:face_to_face,online,hybrid'],
            'class_size' => ['nullable', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'faculty' => ['required', 'array', 'min:1'],
            'faculty.*.faculty_id' => ['required', 'exists:faculty,id'],
            'faculty.*.load_hours' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.load_units' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.role' => ['nullable', 'string', 'max:50'],
        ]);

        $classOffering = DB::transaction(function () use ($data, $request) {
            $data['created_by'] = $request->user()->id;
            $data['status'] = 'draft';

            $classOffering = ClassOffering::create($data);

            foreach ($data['faculty'] as $facultyData) {
                $classOffering->faculty()->attach($facultyData['faculty_id'], [
                    'load_hours' => $facultyData['load_hours'] ?? null,
                    'load_units' => $facultyData['load_units'] ?? null,
                    'role' => $facultyData['role'] ?? null,
                ]);
            }

            return $classOffering;
        });

        return redirect()->route('admin.class_offerings.index')
            ->with('success', 'Class offering created.');
    }

    public function show(ClassOffering $classOffering)
    {
        $classOffering->load('academicPeriod', 'department.college', 'subject', 'section', 'faculty', 'classMeetings');

        return Inertia::render('Admin/ClassOffering/Show', [
            'classOffering' => $classOffering,
        ]);
    }

    public function edit(ClassOffering $classOffering)
    {
        $academicPeriods = AcademicPeriod::where('status', '!=', 'archived')->get(['id', 'academic_year', 'term', 'start_date', 'end_date', 'status']);
        $departments = Department::where('is_active', true)->with('college')->get(['id', 'name', 'code', 'college_id']);
        $subjects = Subject::where('is_active', true)->get(['id', 'code', 'title', 'units', 'lecture_hours', 'laboratory_hours']);
        $faculty = Faculty::where('status', 'active')->get(['id', 'employee_no', 'first_name', 'middle_name', 'last_name', 'email']);

        return Inertia::render('Admin/ClassOffering/Edit', [
            'classOffering' => $classOffering->load('faculty'),
            'academicPeriods' => $academicPeriods,
            'departments' => $departments,
            'subjects' => $subjects,
            'faculty' => $faculty,
        ]);
    }

    public function update(Request $request, ClassOffering $classOffering)
    {
        $data = $request->validate([
            'academic_period_id' => ['required', 'exists:academic_periods,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'delivery_mode' => ['required', 'in:face_to_face,online,hybrid'],
            'class_size' => ['nullable', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'faculty' => ['required', 'array', 'min:1'],
            'faculty.*.faculty_id' => ['required', 'exists:faculty,id'],
            'faculty.*.load_hours' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.load_units' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.role' => ['nullable', 'string', 'max:50'],
        ]);

        $classOffering->update($data);

        // Sync faculty relationships
        $facultyData = [];
        foreach ($data['faculty'] as $faculty) {
            $facultyData[$faculty['faculty_id']] = [
                'load_hours' => $faculty['load_hours'] ?? null,
                'load_units' => $faculty['load_units'] ?? null,
                'role' => $faculty['role'] ?? null,
            ];
        }
        $classOffering->faculty()->sync($facultyData);

        return redirect()->route('admin.class_offerings.index')
            ->with('success', 'Class offering updated.');
    }

    public function destroy(ClassOffering $classOffering)
    {
        // Only allow deletion of draft or returned class offerings
        if (!$classOffering->isDraft() && !$classOffering->isReturned()) {
            return redirect()->route('admin.class_offerings.index')
                ->with('error', 'Only draft or returned class offerings can be deleted.');
        }

        $classOffering->delete();

        return redirect()->route('admin.class_offerings.index')
            ->with('success', 'Class offering deleted.');
    }
}