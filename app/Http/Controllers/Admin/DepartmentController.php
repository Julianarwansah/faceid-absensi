<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string',
        ]);

        Department::create($request->all());

        return back()->with('success', 'Department created successfully!');
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code,' . $id,
            'description' => 'nullable|string',
        ]);

        $department->update($request->all());

        return back()->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department.
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete department with employees!');
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully!');
    }

    /**
     * Toggle department status.
     */
    public function toggleStatus($id)
    {
        $department = Department::findOrFail($id);
        $department->update(['is_active' => !$department->is_active]);

        $status = $department->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Department {$status} successfully!");
    }
}
