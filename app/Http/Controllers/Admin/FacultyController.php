<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FacultyController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Faculty/Index', [
            'faculty' => Faculty::when(request('search'), fn($q) => $q->where('last_name', 'like', '%'.request('search').'%'))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Faculty/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_no' => ['nullable', 'string', 'max:50', 'unique:faculty,employee_no'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'highest_degree_earned' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
        ]);

        Faculty::create($data);

        return redirect()->route('admin.faculty.index')
            ->with('success', 'Faculty member created.');
    }

    public function edit(Faculty $faculty)
    {
        return Inertia::render('Admin/Faculty/Edit', [
            'faculty' => $faculty,
        ]);
    }

    public function update(Request $request, Faculty $faculty)
    {
        $data = $request->validate([
            'employee_no' => ['nullable', 'string', 'max:50', 'unique:faculty,employee_no,'.$faculty->id],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'highest_degree_earned' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
        ]);

        $faculty->update($data);

        return redirect()->route('admin.faculty.index')
            ->with('success', 'Faculty member updated.');
    }

    public function destroy(Faculty $faculty)
    {
        $faculty->delete();

        return redirect()->route('admin.faculty.index')
            ->with('success', 'Faculty member deleted.');
    }
}