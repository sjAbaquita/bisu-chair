<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampusController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Campus/Index', [
            'campuses' => Campus::when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%'))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Campus/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:campuses,code'],
            'address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        Campus::create($data);

        return redirect()->route('admin.campuses.index')
            ->with('success', 'Campus created.');
    }

    public function edit(Campus $campus)
    {
        return Inertia::render('Admin/Campus/Edit', [
            'campus' => $campus,
        ]);
    }

    public function update(Request $request, Campus $campus)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:campuses,code,'.$campus->id],
            'address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $campus->update($data);

        return redirect()->route('admin.campuses.index')
            ->with('success', 'Campus updated.');
    }

    public function destroy(Campus $campus)
    {
        $campus->delete();

        return redirect()->route('admin.campuses.index')
            ->with('success', 'Campus deleted.');
    }
}