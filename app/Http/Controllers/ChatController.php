<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin|superviseur');
    }

    // ── Page principale chat ─────────────────────────────────────────────
    public function index()
    {
        $userId = auth()->id();

        $conversations = Conversation::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with(['participants', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get()
            ->map(function (Conversation $c) use ($userId) {
                $last = $c->messages->first();
                return [
                    'id'          => $c->id,
                    'nom'         => $c->nomPour($userId),
                    'type'        => $c->type,
                    'dernier_msg' => $last?->contenu,
                    'dernier_at'  => $last?->created_at?->diffForHumans(),
                    'non_lus'     => $c->nonLusPour($userId),
                    'participants'=> $c->participants->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
                ];
            });

        // Utilisateurs disponibles pour démarrer une conversation
        $users = User::role(['super_admin', 'superviseur'])
            ->where('id', '!=', $userId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('chat.index', compact('conversations', 'users'));
    }

    // ── Récupérer messages d'une conversation (AJAX polling) ─────────────
    public function messages(Request $request, Conversation $conversation)
    {
        $userId = auth()->id();

        if (!$conversation->participants->contains('id', $userId)) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $since = $request->input('since'); // timestamp ISO pour ne récupérer que les nouveaux

        $query = $conversation->messages()->with('sender:id,name')
            ->orderBy('created_at');

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        // Filtrage PHP : exclure les messages supprimés pour cet utilisateur
        $messages = $query->limit(100)->get()
            ->filter(fn($m) => !in_array($userId, $m->supprimes_pour ?? []))
            ->values()
            ->map(fn($m) => [
            'id'          => $m->id,
            'contenu'     => $m->supprime_pour_tous ? 'Message supprimé' : $m->contenu,
            'type'        => $m->supprime_pour_tous ? 'text' : ($m->type ?? 'text'),
            'fichier_url' => (!$m->supprime_pour_tous && $m->fichier) ? asset('storage/' . $m->fichier) : null,
            'duree'       => $m->supprime_pour_tous ? null : $m->duree,
            'supprime'    => (bool) $m->supprime_pour_tous,
            'sender_id'   => $m->sender_id,
            'sender_name' => $m->sender->name,
            'mine'        => $m->sender_id === $userId,
            'created_at'  => $m->created_at->format('H:i'),
            'date'        => $m->created_at->format('d/m/Y'),
            'timestamp'   => $m->created_at->toISOString(),
        ]);

        // Marquer comme lu
        $conversation->participants()->updateExistingPivot($userId, ['last_read_at' => now()]);

        return response()->json([
            'messages'  => $messages,
            'conv_nom'  => $conversation->nomPour($userId),
            'conv_type' => $conversation->type,
            'participants' => $conversation->participants->map(fn($p) => $p->name)->implode(', '),
        ]);
    }

    // ── Envoyer un message ───────────────────────────────────────────────
    public function envoyer(Request $request, Conversation $conversation)
    {
        $userId = auth()->id();

        if (!$conversation->participants->contains('id', $userId)) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $request->validate(['contenu' => 'required|string|max:2000']);

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $userId,
            'contenu'         => $request->contenu,
        ]);

        $conversation->touch(); // update updated_at pour tri

        return response()->json([
            'id'          => $msg->id,
            'contenu'     => $msg->contenu,
            'type'        => 'text',
            'fichier_url' => null,
            'duree'       => null,
            'sender_id'   => $msg->sender_id,
            'sender_name' => auth()->user()->name,
            'mine'        => true,
            'created_at'  => $msg->created_at->format('H:i'),
            'date'        => $msg->created_at->format('d/m/Y'),
            'timestamp'   => $msg->created_at->toISOString(),
        ]);
    }

    // ── Envoyer un message vocal ─────────────────────────────────────────
    public function envoyerVocal(Request $request, Conversation $conversation)
    {
        $userId = auth()->id();

        if (!$conversation->participants->contains('id', $userId)) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $request->validate([
            'audio' => 'required|file|mimetypes:audio/webm,audio/ogg,audio/wav,audio/mpeg,video/webm,application/ogg|max:10240',
            'duree' => 'nullable|integer|min:1|max:300',
        ]);

        $file     = $request->file('audio');
        $filename = 'vocal_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('storage/chat/audio'), $filename);
        $path = 'chat/audio/' . $filename;

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $userId,
            'contenu'         => '🎤 Message vocal',
            'type'            => 'audio',
            'fichier'         => $path,
            'duree'           => $request->input('duree'),
        ]);

        $conversation->touch();

        return response()->json([
            'id'          => $msg->id,
            'contenu'     => $msg->contenu,
            'type'        => 'audio',
            'fichier_url' => asset('storage/' . $path),
            'duree'       => $msg->duree,
            'sender_id'   => $msg->sender_id,
            'sender_name' => auth()->user()->name,
            'mine'        => true,
            'created_at'  => $msg->created_at->format('H:i'),
            'date'        => $msg->created_at->format('d/m/Y'),
            'timestamp'   => $msg->created_at->toISOString(),
        ]);
    }

    // ── Créer une conversation directe ───────────────────────────────────
    public function creerDirect(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $userId   = auth()->id();
        $otherId  = (int) $request->user_id;

        if ($userId === $otherId) {
            return response()->json(['error' => 'Impossible de discuter avec soi-même'], 422);
        }

        // Chercher si une conversation directe existe déjà entre ces deux users
        $existing = Conversation::where('type', 'direct')
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->whereHas('participants', fn($q) => $q->where('user_id', $otherId))
            ->first();

        if ($existing) {
            return response()->json(['conversation_id' => $existing->id]);
        }

        DB::transaction(function () use (&$conv, $userId, $otherId) {
            $conv = Conversation::create(['type' => 'direct', 'created_by' => $userId]);
            $conv->participants()->attach([$userId, $otherId]);
        });

        return response()->json(['conversation_id' => $conv->id], 201);
    }

    // ── Créer un groupe ──────────────────────────────────────────────────
    public function creerGroupe(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:100',
            'user_ids'  => 'required|array|min:1',
            'user_ids.*'=> 'exists:users,id',
        ]);

        $userId = auth()->id();
        $members = array_unique(array_merge([$userId], $request->user_ids));

        DB::transaction(function () use (&$conv, $userId, $request, $members) {
            $conv = Conversation::create([
                'nom'        => $request->nom,
                'type'       => 'groupe',
                'created_by' => $userId,
            ]);
            $conv->participants()->attach($members);
        });

        return response()->json(['conversation_id' => $conv->id], 201);
    }

    // ── Supprimer des messages (pour moi / pour tous) ────────────────────
    public function supprimerMessages(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'mode'   => 'required|in:tous,moi',
        ]);

        // Charger les messages accessibles par cet utilisateur
        $messages = ChatMessage::whereIn('id', $request->ids)
            ->whereHas('conversation', fn($q) =>
                $q->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            )
            ->get();

        foreach ($messages as $msg) {
            if ($request->mode === 'tous') {
                // Seulement si c'est son message ET créé il y a moins de 20 min
                if ($msg->sender_id === $userId && now()->diffInMinutes($msg->created_at) <= 20) {
                    $msg->update(['supprime_pour_tous' => true]);
                }
            } else {
                // Supprimer pour moi : ajouter mon ID dans supprimes_pour
                $suppPour = $msg->supprimes_pour ?? [];
                if (!in_array($userId, $suppPour)) {
                    $suppPour[] = $userId;
                    $msg->update(['supprimes_pour' => $suppPour]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    // ── Nombre total de messages non lus (pour badge sidebar) ────────────
    public function nonLus()
    {
        $userId = auth()->id();
        $count  = 0;

        Conversation::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with(['participants', 'messages'])
            ->get()
            ->each(function ($c) use ($userId, &$count) {
                $count += $c->nonLusPour($userId);
            });

        return response()->json(['non_lus' => $count]);
    }
}
