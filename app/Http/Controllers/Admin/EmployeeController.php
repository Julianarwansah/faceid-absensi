<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(15);
        $departments = Department::active()->get();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $departments = Department::active()->get();
        return view('admin.employees.create', compact('departments'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|unique:employees,employee_id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'position' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            // Get employee role
            $employeeRole = Role::where('name', 'employee')->first();

            // Create user account
            $user = User::create([
                'name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $employeeRole->id,
            ]);

            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'position' => $request->position,
                'address' => $request->address,
                'join_date' => $request->join_date ?? now(),
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        $departments = Department::active()->get();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'employee_id' => 'required|unique:employees,employee_id,' . $id,
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id . '|unique:users,email,' . $employee->user_id,
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'position' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'password' => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            // Update user
            $userData = [
                'name' => $request->full_name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $employee->user->update($userData);

            // Update employee
            $employee->update([
                'employee_id' => $request->employee_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'position' => $request->position,
                'address' => $request->address,
                'join_date' => $request->join_date,
            ]);

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified employee.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        DB::beginTransaction();
        try {
            $user = $employee->user;
            $employee->delete();
            $user->delete();

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    }

    /**
     * Toggle employee status.
     */
    public function toggleStatus($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['is_active' => !$employee->is_active]);

        $status = $employee->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Employee {$status} successfully!");
    }
}
