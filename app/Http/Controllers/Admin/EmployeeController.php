<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::with('user');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $employees = $query->latest()->paginate(10)->withQueryString();
        $search = $request->input('search', '');
        $filterStatus = $request->input('status', '');

        return view('admin.employees.index', compact('employees', 'search', 'filterStatus'));
    }

    public function create(): View
    {
        return view('admin.employees.create');
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $employee = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_EMPLOYEE,
            ]);

            return Employee::create([
                'user_id' => $user->id,
                'employee_number' => $data['employee_number'],
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
                'department' => $data['department'] ?? null,
                'join_date' => $data['join_date'] ?? null,
                'status' => $data['status'],
            ]);
        });

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee): View
    {
        $employee->load('user');

        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user');

        return view('admin.employees.edit', compact('employee'));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $employee) {
            $employee->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            if (! empty($data['password'])) {
                $employee->user->update([
                    'password' => $data['password'],
                ]);
            }

            $employee->update([
                'employee_number' => $data['employee_number'],
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
                'department' => $data['department'] ?? null,
                'join_date' => $data['join_date'] ?? null,
                'status' => $data['status'],
            ]);
        });

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function toggleStatus(Employee $employee): RedirectResponse
    {
        $employee->status = $employee->isActive() ? Employee::STATUS_INACTIVE : Employee::STATUS_ACTIVE;
        $employee->save();

        $message = $employee->isActive()
            ? 'Karyawan berhasil diaktifkan.'
            : 'Karyawan berhasil dinonaktifkan.';

        return back()->with('success', $message);
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->user->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan beserta akunnya telah dihapus.');
    }
}