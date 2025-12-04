<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionController extends Controller
{
    /**
     * Register face for employee.
     */
    public function register(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'face_descriptor' => 'required|array',
            'photo' => 'required|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        // Save face photo
        $photoData = $request->photo;
        $photoData = str_replace('data:image/png;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $photoName = 'face_' . $employee->id . '_' . time() . '.png';
        Storage::disk('public')->put('faces/' . $photoName, base64_decode($photoData));

        // Update employee with face descriptor and photo
        $employee->update([
            'face_descriptor' => $request->face_descriptor,
            'photo' => 'faces/' . $photoName,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Face registered successfully!',
        ]);
    }

    /**
     * Verify face against stored descriptor.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'face_descriptor' => 'required|array',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        if (!$employee->hasFaceRegistered()) {
            return response()->json([
                'success' => false,
                'message' => 'No face registered for this employee.',
            ], 400);
        }

        // Face matching is done on client-side using face-api.js
        // This endpoint just validates that the employee has a registered face
        return response()->json([
            'success' => true,
            'stored_descriptor' => $employee->face_descriptor,
        ]);
    }

    /**
     * Get employee face descriptor.
     */
    public function getDescriptor($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        if (!$employee->hasFaceRegistered()) {
            return response()->json([
                'success' => false,
                'message' => 'No face registered for this employee.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'descriptor' => $employee->face_descriptor,
        ]);
    }
}
