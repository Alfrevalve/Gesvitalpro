<?php

namespace App\Http\Controllers;

use App\Models\SurgeryRequest;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index()
    {
        $requests = SurgeryRequest::with(['surgery', 'items', 'preparation'])
            ->latest()
            ->paginate(10);

        return view('storage.index', compact('requests'));
    }

    public function kanban()
    {
        $pendingRequests = SurgeryRequest::with(['surgery', 'items', 'preparation'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $inProgressRequests = SurgeryRequest::with(['surgery', 'items', 'preparation'])
            ->where('status', 'in_progress')
            ->latest()
            ->get();

        $completedRequests = SurgeryRequest::with(['surgery', 'items', 'preparation'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        return view('storage.kanban', compact('pendingRequests', 'inProgressRequests', 'completedRequests'));
    }

    public function show(SurgeryRequest $surgeryRequest)
    {
        $surgeryRequest->load(['surgery', 'items', 'preparation']);
        return view('storage.show', compact('surgeryRequest'));
    }

    public function updateStatus(Request $request, SurgeryRequest $surgeryRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $surgeryRequest->update([
            'status' => $validated['status']
        ]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function report()
    {
        $totalRequests = SurgeryRequest::count();
        $pendingCount = SurgeryRequest::where('status', 'pending')->count();
        $inProgressCount = SurgeryRequest::where('status', 'in_progress')->count();
        $completedCount = SurgeryRequest::where('status', 'completed')->count();

        return view('storage.report', compact(
            'totalRequests',
            'pendingCount',
            'inProgressCount',
            'completedCount'
        ));
    }
}
