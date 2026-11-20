<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\College;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Room/Index', [
            'rooms' => Room::when(request('search'), fn($q) => $q->where('code', 'like', '%'.request('search').'%'))
                ->with(['building', 'college'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        $buildings = Building::with('campus')->where('is_active', true)->get(['id', 'name', 'code', 'campus_id']);
        $colleges = College::where('is_active', true)->get(['id', 'name', 'code']);
        return Inertia::render('Admin/Room/Create', [
            'buildings' => $buildings,
            'colleges' => $colleges,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'code' => ['required', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'room_type' => ['required', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'status' => ['required', 'string', 'in:available,unavailable,under_maintenance,retired'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure unique room code per building
        $existingRoom = Room::where('building_id', $data['building_id'])
            ->where('code', $data['code'])
            ->first();

        if ($existingRoom) {
            return redirect()->route('admin.rooms.index')
                ->with('error', 'Room code already exists in this building.');
        }

        Room::create($data);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room created.');
    }

    public function edit(Room $room)
    {
        $buildings = Building::with('campus')->where('is_active', true)->get(['id', 'name', 'code', 'campus_id']);
        $colleges = College::where('is_active', true)->get(['id', 'name', 'code']);
        return Inertia::render('Admin/Room/Edit', [
            'room' => $room->load('building', 'college'),
            'buildings' => $buildings,
            'colleges' => $colleges,
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'code' => ['required', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'room_type' => ['required', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'status' => ['required', 'string', 'in:available,unavailable,under_maintenance,retired'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure unique room code per building (excluding current room)
        $existingRoom = Room::where('building_id', $data['building_id'])
            ->where('code', $data['code'])
            ->where('id', '!=', $room->id)
            ->first();

        if ($existingRoom) {
            return redirect()->route('admin.rooms.index')
                ->with('error', 'Room code already exists in this building.');
        }

        $room->update($data);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room updated.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room deleted.');
    }
}