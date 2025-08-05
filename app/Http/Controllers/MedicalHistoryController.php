<?php

namespace App\Http\Controllers;

use App\Models\MedicalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $history = MedicalHistory::where('user_id', Auth::id())
            ->orderBy('event_date', 'desc')
            ->get();

        return response()->json($history);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'metadata' => 'nullable|array'
        ]);

        $history = MedicalHistory::create([
            'user_id' => Auth::id(),
            'event_type' => $request->event_type,
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'metadata' => $request->metadata
        ]);

        return response()->json($history, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $history = MedicalHistory::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($history);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $history = MedicalHistory::where('user_id', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'event_type' => 'sometimes|string',
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'event_date' => 'sometimes|date',
            'metadata' => 'nullable|array'
        ]);

        $history->update($request->only([
            'event_type', 'title', 'description', 'event_date', 'metadata'
        ]));

        return response()->json($history);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $history = MedicalHistory::where('user_id', Auth::id())
            ->findOrFail($id);

        $history->delete();

        return response()->json(['message' => 'Historial eliminado correctamente']);
    }

    /**
     * Get recent medical history events for dashboard
     */
    public function recent()
    {
        $history = MedicalHistory::where('user_id', Auth::id())
            ->orderBy('event_date', 'desc')
            ->limit(5)
            ->get();

        return response()->json($history);
    }
}
