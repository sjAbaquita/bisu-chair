<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Subject/Index', [
            'subjects' => Subject::when(request('search'), fn($q) => $q->where('code', 'like', '%'.request('search').'%')->orWhere('title', 'like', '%'.request('search').'%'))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Subject/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'title' => ['required', 'string', 'max:255'],
            'units' => ['required', 'numeric', 'between:0.25,10'],
            'lecture_hours' => ['required', 'integer', 'min:0'],
            'laboratory_hours' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['units'] = number_format((float) $data['units'], 2, '.', '');

        Subject::create($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        return Inertia::render('Admin/Subject/Edit', [
            'subject' => $subject,
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code,'.$subject->id],
            'title' => ['required', 'string', 'max:255'],
            'units' => ['required', 'numeric', 'between:0.25,10'],
            'lecture_hours' => ['required', 'integer', 'min:0'],
            'laboratory_hours' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['units'] = number_format((float) $data['units'], 2, '.', '');

        $subject->update($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted.');
    }
}