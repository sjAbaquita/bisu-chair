<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Campus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Building/Index', [
            'buildings' => Building::when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%'))
                ->with('campus')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        $campuses = Campus::where('is_active', true)->get(['id', 'name', 'code']);
        return Inertia::render('Admin/Building/Create', [
            'campuses' => $campuses,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:buildings,code'],
            'is_active' => ['required', 'boolean'],
        ]);

        Building::create($data);

        return redirect()->route('admin.buildings.index')
            ->with('success', 'Building created.');
    }

    public function edit(Building $building)
    {
        $campuses = Campus::where('is_active', true)->get(['id', 'name', 'code']);
        return Inertia::render('Admin/Building/Edit', [
            'building' => $building->load('campus'),
            'campuses' => $campuses,
        ]);
    }

    public function update(Request $request, Building $building)
    {
        $data = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:buildings,code,'.$building->id],
            'is_active' => ['required', 'boolean'],
        ]);

        $building->update($data);

        return redirect()->route('admin.buildings.index')
            ->with('success', 'Building updated.');
    }

    public function destroy(Building $building)
    {
        $building->delete();

        return redirect()->route('admin.buildings.index')
            ->with('success', 'Building deleted.');
    }
}