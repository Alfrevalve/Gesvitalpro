<?php

namespace App\Http\Controllers;

use App\Models\SurgeryRequest;
use App\Models\SurgeryMaterialDelivery;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    public function index()
    {
        $deliveries = SurgeryRequest::with(['surgery', 'items', 'preparation'])
            ->whereHas('preparation')
            ->latest()
            ->paginate(10);

        return view('dispatch.index', compact('deliveries'));
    }

    public function kanban()
    {
        $pendingDeliveries = SurgeryRequest::whereHas('preparation')
            ->where('dispatch_status', 'pending')
            ->get();

        $inTransitDeliveries = SurgeryRequest::whereHas('preparation')
            ->where('dispatch_status', 'in_transit')
            ->get();

        $deliveredRequests = SurgeryRequest::whereHas('preparation')
            ->where('dispatch_status', 'delivered')
            ->get();

        return view('dispatch.kanban', compact('pendingDeliveries', 'inTransitDeliveries', 'deliveredRequests'));
    }

    public function report()
    {
        $stats = [
            'total_deliveries' => SurgeryRequest::whereHas('preparation')->count(),
            'pending_deliveries' => SurgeryRequest::whereHas('preparation')
                ->where('dispatch_status', 'pending')
                ->count(),
            'in_transit_deliveries' => SurgeryRequest::whereHas('preparation')
                ->where('dispatch_status', 'in_transit')
                ->count(),
            'delivered_requests' => SurgeryRequest::whereHas('preparation')
                ->where('dispatch_status', 'delivered')
                ->count(),
        ];

        return view('dispatch.report', compact('stats'));
    }

    public function show(SurgeryRequest $surgeryRequest)
    {
        $surgeryRequest->load(['surgery', 'items', 'preparation', 'delivery']);
        return view('dispatch.show', compact('surgeryRequest'));
    }

    public function confirmDelivery(Request $request, SurgeryRequest $surgeryRequest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'recipient_name' => 'required|string|max:255',
            'recipient_signature' => 'required|string',
            'delivery_photo' => 'nullable|image|max:2048',
        ]);

        // Guardar foto de entrega si se proporcionó
        $photoPath = null;
        if ($request->hasFile('delivery_photo')) {
            $photoPath = $request->file('delivery_photo')->store('delivery-photos', 'public');
        }

        // Crear registro de entrega
        SurgeryMaterialDelivery::create([
            'surgery_request_id' => $surgeryRequest->id,
            'delivered_by' => auth()->id(),
            'recipient_name' => $validated['recipient_name'],
            'recipient_signature' => $validated['recipient_signature'],
            'delivery_photo' => $photoPath,
            'notes' => $validated['notes'],
            'delivery_date' => now(),
        ]);

        // Actualizar estado de la solicitud
        $surgeryRequest->update([
            'dispatch_status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return redirect()->route('dispatch.show', $surgeryRequest)
            ->with('success', 'Entrega confirmada correctamente.');
    }
}
