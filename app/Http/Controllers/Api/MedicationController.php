<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $medications = $request->user()->medications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $medications
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'reminder_times' => 'nullable|array',
            'reminder_times.*' => 'string',
            'frequency' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $medication = $request->user()->medications()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Medicamento creado exitosamente',
            'data' => $medication
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Medication $medication): JsonResponse
    {
        // Verificar que el medicamento pertenece al usuario autenticado
        if ($medication->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $medication
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Medication $medication): JsonResponse
    {
        // Verificar que el medicamento pertenece al usuario autenticado
        if ($medication->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }


        $validated = $request->validate([
            'name' => 'string|max:255',
            'dosage' => 'string|max:255',
            'reminder_times' => 'nullable|array',
            'reminder_times.*' => 'string',
            'frequency' => 'string|max:255',
            'start_date' => 'date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $medication->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Medicamento actualizado exitosamente',
            'data' => $medication
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Medication $medication): JsonResponse
    {
        // Verificar que el medicamento pertenece al usuario autenticado
        if ($medication->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $medication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medicamento eliminado exitosamente'
        ]);
    }
}
