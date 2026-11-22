<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassOffering;
use App\Models\ClassOfferingFaculty;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClassOfferingFacultyController extends Controller
{
    /**
     * Display a listing of faculty assignments for a class offering.
     */
    public function index(ClassOffering $classOffering)
    {
        $facultyAssignments = $classOffering->faculty()
            ->withPivot('load_hours', 'load_units', 'role')
            ->get()
            ->map(function ($faculty) use ($classOffering) {
                $pivot = $classOffering->faculty()->newPivotStatement()
                    ->where('class_offering_id', $classOffering->id)
                    ->where('faculty_id', $faculty->id)
                    ->first();

                return [
                    'id' => $pivot ? $pivot->id : null,
                    'class_offering_id' => $classOffering->id,
                    'faculty_id' => $faculty->id,
                    'faculty' => [
                        'id' => $faculty->id,
                        'employee_no' => $faculty->employee_no,
                        'first_name' => $faculty->first_name,
                        'middle_name' => $faculty->middle_name,
                        'last_name' => $faculty->last_name,
                        'suffix' => $faculty->suffix,
                        'email' => $faculty->email,
                    ],
                    'load_hours' => $pivot ? $pivot->load_hours : null,
                    'load_units' => $pivot ? $pivot->load_units : null,
                    'role' => $pivot ? $pivot->role : null,
                ];
            });

        return Inertia::render('Admin/ClassOfferingFaculty/Index', [
            'classOffering' => $classOffering->load('subject', 'department.college', 'faculty'),
            'facultyAssignments' => $facultyAssignments,
            'faculty' => Faculty::where('status', 'active')
                ->get(['id', 'employee_no', 'first_name', 'middle_name', 'last_name', 'email']),
        ]);
    }

    /**
     * Store a new faculty assignment for a class offering.
     */
    public function store(Request $request, ClassOffering $classOffering)
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculty,id'],
            'load_hours' => ['nullable', 'numeric', 'min:0'],
            'load_units' => ['nullable', 'numeric', 'min:0'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        // Check if faculty is already assigned to this class offering
        $existing = $classOffering->faculty()
            ->wherePivot('faculty_id', $validated['faculty_id'])
            ->where('faculty.id', $validated['faculty_id'])
            ->first();

        if ($existing) {
            return redirect()->route('admin.class_offerings.faculty.index', $classOffering)
                ->with('error', 'Faculty member is already assigned to this class offering.');
        }

        DB::transaction(function () use ($classOffering, $validated) {
            $classOffering->faculty()->attach($validated['faculty_id'], [
                'load_hours' => $validated['load_hours'] ?? null,
                'load_units' => $validated['load_units'] ?? null,
                'role' => $validated['role'] ?? null,
            ]);
        });

        return redirect()->route('admin.class_offerings.faculty.index', $classOffering)
            ->with('success', 'Faculty member assigned successfully.');
    }

    /**
     * Update the specified faculty assignment.
     */
    public function update(Request $request, ClassOffering $classOffering, ClassOfferingFaculty $facultyAssignment)
    {
        $validated = $request->validate([
            'load_hours' => ['nullable', 'numeric', 'min:0'],
            'load_units' => ['nullable', 'numeric', 'min:0'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        // Ensure the assignment belongs to this class offering
        if ($facultyAssignment->class_offering_id !== $classOffering->id) {
            return redirect()->back()
                ->with('error', 'Invalid faculty assignment.');
        }

        $facultyAssignment->update($validated);

        return redirect()->route('admin.class_offerings.faculty.index', $classOffering)
            ->with('success', 'Faculty assignment updated successfully.');
    }

    /**
     * Remove the specified faculty assignment from the class offering.
     */
    public function destroy(Request $request, ClassOffering $classOffering, ClassOfferingFaculty $facultyAssignment)
    {
        // Ensure the assignment belongs to this class offering
        if ($facultyAssignment->class_offering_id !== $classOffering->id) {
            return redirect()->back()
                ->with('error', 'Invalid faculty assignment.');
        }

        $facultyAssignment->delete();

        return redirect()->route('admin.class_offerings.faculty.index', $classOffering)
            ->with('success', 'Faculty member removed from class offering.');
    }

    /**
     * Bulk assign multiple faculty members to a class offering.
     */
    public function bulkStore(Request $request, ClassOffering $classOffering)
    {
        $validated = $request->validate([
            'faculty' => ['required', 'array', 'min:1'],
            'faculty.*.faculty_id' => ['required', 'exists:faculty,id'],
            'faculty.*.load_hours' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.load_units' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.role' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($classOffering, $validated) {
            foreach ($validated['faculty'] as $facultyData) {
                // Check if already assigned
                $existing = ClassOfferingFaculty::where('class_offering_id', $classOffering->id)
                    ->where('faculty_id', $facultyData['faculty_id'])
                    ->first();

                if (!$existing) {
                    $classOffering->faculty()->attach($facultyData['faculty_id'], [
                        'load_hours' => $facultyData['load_hours'] ?? null,
                        'load_units' => $facultyData['load_units'] ?? null,
                        'role' => $facultyData['role'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('admin.class_offerings.faculty.index', $classOffering)
            ->with('success', 'Faculty members assigned successfully.');
    }

    /**
     * Synchronize faculty assignments for a class offering.
     */
    public function sync(Request $request, ClassOffering $classOffering)
    {
        $validated = $request->validate([
            'faculty' => ['required', 'array'],
            'faculty.*.faculty_id' => ['required', 'exists:faculty,id'],
            'faculty.*.load_hours' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.load_units' => ['nullable', 'numeric', 'min:0'],
            'faculty.*.role' => ['nullable', 'string', 'max:50'],
            'faculty.*.pivot_id' => ['nullable', 'exists:class_offering_faculty,id'],
        ]);

        DB::transaction(function () use ($classOffering, $validated) {
            $syncData = [];

            foreach ($validated['faculty'] as $faculty) {
                $syncData[$faculty['faculty_id']] = [
                    'load_hours' => $faculty['load_hours'] ?? null,
                    'load_units' => $faculty['load_units'] ?? null,
                    'role' => $faculty['role'] ?? null,
                ];
            }

            // Sync will update existing and add new, but keep deleted ones removed
            $classOffering->faculty()->sync($syncData, false);
        });

        return redirect()->route('admin.class_offerings.faculty.index', $classOffering)
            ->with('success', 'Faculty assignments updated successfully.');
    }
}