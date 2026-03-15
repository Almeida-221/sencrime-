<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Retourne les notifications en JSON (pour le polling AJAX) */
    public function ajax(Request $request)
    {
        $notifications = AppNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'titre'      => $n->titre,
                'message'    => $n->message,
                'type'       => $n->type,
                'icone'      => $n->icone,
                'couleur'    => $n->couleur,
                'lien'       => $n->lien,
                'lu'         => $n->lu,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'non_lues'      => $notifications->where('lu', false)->count(),
        ]);
    }

    /** Marquer une comme lue */
    public function lire($id)
    {
        AppNotification::where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['lu' => true, 'lu_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /** Marquer toutes comme lues */
    public function lireTout()
    {
        AppNotification::where('user_id', auth()->id())
            ->where('lu', false)
            ->update(['lu' => true, 'lu_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
