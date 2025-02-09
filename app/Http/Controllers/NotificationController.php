<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    /**
     * Enviar una notificación.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        // Validar datos de entrada
        $request->validate([
            'users' => 'required|array',
            'message' => 'required|string',
        ]);

        try {
            // Lógica para enviar notificaciones
            Notification::send($request->users, new \App\Notifications\YourNotificationClass([
                'message' => $request->message,
            ]));

            return response()->json(['message' => 'Notificación enviada con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar la notificación.'], 500);
        }
    }

    /**
     * Enviar una notificación de prueba.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testNotification()
    {
        $user = Auth::user(); // Obtener el usuario autenticado

        try {
            // Enviar notificación
            Notification::send($user, new \App\Notifications\YourNotificationClass([
                'title' => 'Notificación de Prueba',
                'message' => 'Este es un mensaje de prueba para la notificación.',
            ]));

            session()->flash('notification', 'Notificación de prueba enviada con éxito.');

            return response()->json(['message' => 'Notificación de prueba enviada con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar la notificación de prueba.'], 500);
        }
    }
}
