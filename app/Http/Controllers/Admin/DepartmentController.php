<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Department/Index', [
            'departments' => Department::when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%'))
                ->with('college')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        $colleges = College::where('is_active', true)->get(['id', 'name', 'code']);
        return Inertia::render('Admin/Department/Create', [
            'colleges' => $colleges,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:departments,code'],
            'is_active' => ['required', 'boolean'],
        ]);

        Department::create($data);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        $colleges = College::where('is_active', true)->get(['id', 'name', 'code']);
        return Inertia::render('Admin/Department/Edit', [
            'department' => $department->load('college'),
            'colleges' => $colleges,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:departments,code,'.$department->id],
            'is_active' => ['required', 'boolean'],
        ]);

        $department->update($data);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted.');
    }
}