<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /** Liste les notifications de l'utilisateur connecté */
    public function index(Request $request)
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'non_lues'      => $notifications->where('lu', false)->count(),
        ]);
    }

    /** Nombre de notifications non lues */
    public function count(Request $request)
    {
        $count = AppNotification::where('user_id', $request->user()->id)
            ->where('lu', false)
            ->count();

        return response()->json(['non_lues' => $count]);
    }

    /** Marquer une notification comme lue */
    public function lire(Request $request, $id)
    {
        $notif = AppNotification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notif->update(['lu' => true, 'lu_at' => now()]);

        return response()->json(['message' => 'Notification lue']);
    }

    /** Marquer toutes comme lues */
    public function lireTout(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->where('lu', false)
            ->update(['lu' => true, 'lu_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues']);
    }
}
