<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/AcademicPeriod/Index', [
            'periods' => AcademicPeriod::when(request('search'), fn($q) => $q->where('academic_year', 'like', '%'.request('search').'%'))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/AcademicPeriod/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'string', 'in:draft,open,locked,archived'],
            'is_current' => ['required', 'boolean'],
        ]);

        // If setting as current, unset any other current periods
        if ($data['is_current']) {
            AcademicPeriod::where('is_current', true)->update(['is_current' => false]);
        }

        AcademicPeriod::create($data);

        return redirect()->route('admin.academic_periods.index')
            ->with('success', 'Academic period created.');
    }

    public function edit(AcademicPeriod $academicPeriod)
    {
        return Inertia::render('Admin/AcademicPeriod/Edit', [
            'period' => $academicPeriod,
        ]);
    }

    public function update(Request $request, AcademicPeriod $academicPeriod)
    {
        $data = $request->validate([
            'academic_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'string', 'in:draft,open,locked,archived'],
            'is_current' => ['required', 'boolean'],
        ]);

        // If setting as current, unset any other current periods
        if ($data['is_current']) {
            AcademicPeriod::where('is_current', true)
                ->where('id', '!=', $academicPeriod->id)
                ->update(['is_current' => false]);
        }

        $academicPeriod->update($data);

        return redirect()->route('admin.academic_periods.index')
            ->with('success', 'Academic period updated.');
    }

    public function destroy(AcademicPeriod $academicPeriod)
    {
        $academicPeriod->delete();

        return redirect()->route('admin.academic_periods.index')
            ->with('success', 'Academic period deleted.');
    }
}