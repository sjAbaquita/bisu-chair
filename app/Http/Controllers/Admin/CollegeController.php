<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CollegeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/College/Index', [
            'colleges' => College::when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%'))
                ->with('campus')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/College/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:colleges,code'],
            'is_active' => ['required', 'boolean'],
        ]);

        College::create($data);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College created.');
    }

    public function edit(College $college)
    {
        return Inertia::render('Admin/College/Edit', [
            'college' => $college->load('campus'),
        ]);
    }

    public function update(Request $request, College $college)
    {
        $data = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:colleges,code,'.$college->id],
            'is_active' => ['required', 'boolean'],
        ]);

        $college->update($data);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College updated.');
    }

    public function destroy(College $college)
    {
        $college->delete();

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College deleted.');
    }
}