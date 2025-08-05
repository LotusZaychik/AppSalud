<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->appointments();
        
        // Si se solicitan solo las próximas citas
        if ($request->boolean('upcoming')) {
            $query->where('appointment_date', '>=', now()->toDateString())
                  ->where('status', '!=', 'cancelled');
        }
        
        // Aplicar límite si se especifica
        if ($request->has('limit')) {
            $query->limit($request->integer('limit'));
        }
        
        $appointments = $query
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Log temporal para debugging
            \Log::info('AppointmentController store llamado', [
                'user_id' => $request->user()->id,
                'data' => $request->all(),
                'headers' => $request->headers->all()
            ]);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'doctor_name' => 'required|string|max:255',
                'specialty' => 'nullable|string|max:255',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|date_format:H:i',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'in:scheduled,completed,cancelled',
                'type' => 'nullable|string|max:50',
                'priority' => 'nullable|string|max:20',
                'reminder' => 'nullable|string|max:20',
                'enable_reminders' => 'nullable|boolean',
                'reminder_intervals' => 'nullable|array',
                'reminder_intervals.*' => 'integer'
            ]);

            $appointment = $request->user()->appointments()->create($validated);

            \Log::info('Appointment creado exitosamente', ['appointment' => $appointment]);

            return response()->json([
                'success' => true,
                'message' => 'Cita médica creada exitosamente',
                'data' => $appointment
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Error de validación en appointment', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error creando appointment', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        // Verificar que la cita pertenece al usuario autenticado
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $appointment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        // Verificar que la cita pertenece al usuario autenticado
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'doctor_name' => 'string|max:255',
            'specialty' => 'string|max:255',
            'appointment_date' => 'date',
            'appointment_time' => 'date_format:H:i',
            'location' => 'string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:scheduled,completed,cancelled',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|string|max:20',
            'reminder' => 'nullable|string|max:20',
            'enable_reminders' => 'nullable|boolean',
            'reminder_intervals' => 'nullable|array',
            'reminder_intervals.*' => 'integer'
        ]);

        $appointment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cita médica actualizada exitosamente',
            'data' => $appointment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        // Verificar que la cita pertenece al usuario autenticado
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cita médica eliminada exitosamente'
        ]);
    }

    /**
     * Marcar una cita como completada/asistida
     */
    public function markAsCompleted(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            // Verificar que la cita pertenece al usuario autenticado
            if ($appointment->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            // Marcar como completada
            $appointment->update([
                'status' => 'completed'
            ]);

            \Log::info('Cita marcada como completada', [
                'appointment_id' => $appointment->id,
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cita marcada como completada exitosamente',
                'data' => $appointment->fresh()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error marcando cita como completada', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar una cita como no asistida/cancelada
     */
    public function markAsCancelled(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            // Verificar que la cita pertenece al usuario autenticado
            if ($appointment->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            // Marcar como cancelada
            $appointment->update([
                'status' => 'cancelled'
            ]);

            \Log::info('Cita marcada como cancelada', [
                'appointment_id' => $appointment->id,
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cita marcada como cancelada',
                'data' => $appointment->fresh()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error marcando cita como cancelada', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
