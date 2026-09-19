<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkScheduleRequest;
use App\Models\WorkSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = WorkSchedule::orderBy('day')->latest()->paginate(10);

        return view('admin.schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        return view('admin.schedules.create');
    }

    public function store(WorkScheduleRequest $request): RedirectResponse
    {
        WorkSchedule::create([
            'name' => $request->input('name'),
            'day' => $request->input('day'),
            'start_time' => $request->input('start_time').':00',
            'end_time' => $request->input('end_time').':00',
            'late_tolerance' => $request->input('late_tolerance'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function edit(WorkSchedule $schedule): View
    {
        return view('admin.schedules.edit', compact('schedule'));
    }

    public function update(WorkScheduleRequest $request, WorkSchedule $schedule): RedirectResponse
    {
        $schedule->update([
            'name' => $request->input('name'),
            'day' => $request->input('day'),
            'start_time' => $request->input('start_time').':00',
            'end_time' => $request->input('end_time').':00',
            'late_tolerance' => $request->input('late_tolerance'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function toggleStatus(WorkSchedule $schedule): RedirectResponse
    {
        $schedule->is_active = ! $schedule->is_active;
        $schedule->save();

        $message = $schedule->is_active
            ? 'Jadwal kerja berhasil diaktifkan.'
            : 'Jadwal kerja berhasil dinonaktifkan.';

        return back()->with('success', $message);
    }

    public function destroy(WorkSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Jadwal kerja berhasil dihapus.');
    }
}