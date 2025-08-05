<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmergencyContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $contacts = $request->user()->emergencyContacts()
            ->orderBy('is_primary', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_primary' => 'boolean'
        ]);

        // Si se marca como primario, desmarcar otros contactos primarios
        if (isset($validated['is_primary']) && $validated['is_primary']) {
            $request->user()->emergencyContacts()
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $contact = $request->user()->emergencyContacts()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contacto de emergencia creado exitosamente',
            'data' => $contact
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, EmergencyContact $emergencyContact): JsonResponse
    {
        // Verificar que el contacto pertenece al usuario autenticado
        if ($emergencyContact->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $emergencyContact
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmergencyContact $emergencyContact): JsonResponse
    {
        // Verificar que el contacto pertenece al usuario autenticado
        if ($emergencyContact->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'relationship' => 'string|max:255',
            'phone' => 'string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_primary' => 'boolean'
        ]);

        // Si se marca como primario, desmarcar otros contactos primarios
        if (isset($validated['is_primary']) && $validated['is_primary']) {
            $request->user()->emergencyContacts()
                ->where('id', '!=', $emergencyContact->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $emergencyContact->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contacto de emergencia actualizado exitosamente',
            'data' => $emergencyContact
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, EmergencyContact $emergencyContact): JsonResponse
    {
        // Verificar que el contacto pertenece al usuario autenticado
        if ($emergencyContact->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $emergencyContact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contacto de emergencia eliminado exitosamente'
        ]);
    }
}
