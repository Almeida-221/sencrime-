@extends('layouts.app')

@section('title', 'Messagerie')
@section('page-title', 'Messagerie Interne')
@section('breadcrumb')
    <li class="breadcrumb-item active">Chat</li>
@endsection

@push('styles')
<style>
    .chat-container {
        height: calc(100vh - 140px);
        display: flex;
        gap: 0;
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    }

    /* ── Colonne gauche : liste conversations ── */
    .chat-sidebar {
        width: 300px;
        flex-shrink: 0;
        border-right: 1px solid #f0f0f0;
        display: flex;
        flex-direction: column;
        background: #fafafa;
    }
    .chat-sidebar-header {
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }
    .chat-sidebar-header h6 { margin: 0; font-weight: 800; color: #1a3a5c; font-size: 0.95rem; }

    .chat-search {
        padding: 10px 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    .chat-search input {
        width: 100%;
        padding: 7px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        font-size: 0.82rem;
        outline: none;
        background: #f4f6f8;
    }
    .chat-search input:focus { border-color: #1a3a5c; background: #fff; }

    .conv-list { flex: 1; overflow-y: auto; }
    .conv-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f5f5f5;
        transition: background 0.15s;
    }
    .conv-item:hover, .conv-item.active { background: rgba(26,58,92,0.06); }
    .conv-item.active { border-left: 3px solid #1a3a5c; }
    .conv-avatar {
        width: 42px; height: 42px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 1rem; color: #fff;
        flex-shrink: 0;
    }
    .conv-info { flex: 1; min-width: 0; }
    .conv-name { font-weight: 700; font-size: 0.85rem; color: #1a3a5c; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .conv-last { font-size: 0.75rem; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .conv-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
    .conv-time { font-size: 0.68rem; color: #bbb; }
    .conv-badge { background: #e63946; color: #fff; font-size: 0.65rem; font-weight: 800;
                  border-radius: 10px; padding: 2px 6px; min-width: 18px; text-align: center; }

    /* ── Zone messages ── */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .chat-header {
        padding: 14px 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        flex-shrink: 0;
    }
    .chat-header-avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a3a5c, #2d6a9f);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800;
    }
    .chat-header-info h6 { margin: 0; font-weight: 800; font-size: 0.9rem; color: #1a3a5c; }
    .chat-header-info small { color: #9ca3af; font-size: 0.75rem; }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        background: #f8f9fb;
    }
    .chat-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #bbb;
    }

    /* Messages */
    .msg-day { text-align: center; margin: 12px 0 8px; }
    .msg-day span {
        background: #e5e7eb; color: #6b7280;
        font-size: 0.72rem; border-radius: 10px; padding: 3px 10px;
    }
    .msg-row { display: flex; align-items: flex-end; gap: 8px; margin-bottom: 2px; }
    .msg-row.mine { flex-direction: row-reverse; }

    .msg-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: 700; color: #6b7280;
        flex-shrink: 0;
    }
    .msg-bubble {
        max-width: 65%;
        padding: 9px 13px;
        border-radius: 16px;
        font-size: 0.875rem;
        line-height: 1.4;
        word-break: break-word;
    }
    .msg-row:not(.mine) .msg-bubble {
        background: #fff;
        color: #1f2937;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }
    .msg-row.mine .msg-bubble {
        background: linear-gradient(135deg, #1a3a5c, #2d6a9f);
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-sender { font-size: 0.68rem; color: #9ca3af; margin-bottom: 2px; font-weight: 600; }
    .msg-time   { font-size: 0.65rem; color: #bbb; margin-top: 3px; text-align: right; }
    .msg-row.mine .msg-time { color: rgba(255,255,255,0.6); }

    /* Zone saisie */
    .chat-input-area {
        padding: 12px 16px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        background: #fff;
        flex-shrink: 0;
    }
    .chat-input-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .chat-input-area textarea {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 10px 16px;
        font-size: 0.9rem;
        resize: none;
        outline: none;
        font-family: inherit;
        max-height: 100px;
        line-height: 1.4;
        background: #f8f9fb;
        transition: border-color 0.2s;
    }
    .chat-input-area textarea:focus { border-color: #1a3a5c; background: #fff; }
    .btn-send {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a3a5c, #2d6a9f);
        color: #fff;
        border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: transform 0.15s;
    }
    .btn-send:hover { transform: scale(1.08); }
    .btn-send:disabled { background: #e5e7eb; cursor: not-allowed; transform: none; }

    /* Bouton micro */
    .btn-mic {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: #f4f6f8;
        color: #6b7280;
        border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .btn-mic:hover { background: #fee2e2; color: #e63946; border-color: #e63946; }
    .btn-mic.recording {
        background: #e63946;
        color: #fff;
        border-color: #e63946;
        animation: mic-pulse 1s ease-in-out infinite;
    }
    @keyframes mic-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(230,57,70,0.4); }
        50%       { box-shadow: 0 0 0 8px rgba(230,57,70,0); }
    }

    /* Barre d'enregistrement */
    .recording-bar {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-radius: 22px;
        font-size: 0.82rem;
        color: #e63946;
    }
    .recording-bar.show { display: flex; }
    .rec-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        background: #e63946;
        animation: rec-blink 1s step-end infinite;
    }
    @keyframes rec-blink { 0%,100%{opacity:1} 50%{opacity:0} }
    .rec-timer { font-weight: 700; min-width: 38px; }

    /* Prévisualisation audio avant envoi */
    .audio-preview {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 6px 12px;
        background: #f0f4ff;
        border: 1px solid #c7d2fe;
        border-radius: 22px;
    }
    .audio-preview.show { display: flex; }
    .audio-preview audio { height: 32px; flex: 1; min-width: 0; }
    .btn-cancel-audio {
        background: none; border: none; color: #e63946;
        cursor: pointer; font-size: 1rem; padding: 0 4px;
    }

    /* Bulles audio */
    .audio-bubble {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 200px;
    }
    .btn-play-audio {
        width: 34px; height: 34px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: transform 0.15s;
    }
    .btn-play-audio:hover { transform: scale(1.1); }
    .msg-row:not(.mine) .btn-play-audio { background: #1a3a5c; color: #fff; }
    .msg-row.mine       .btn-play-audio { background: rgba(255,255,255,0.25); color: #fff; }
    .audio-waveform {
        flex: 1;
        height: 4px;
        background: rgba(255,255,255,0.3);
        border-radius: 2px;
        overflow: hidden;
        cursor: pointer;
    }
    .msg-row:not(.mine) .audio-waveform { background: #e5e7eb; }
    .audio-progress {
        height: 100%;
        background: currentColor;
        border-radius: 2px;
        width: 0%;
        transition: width 0.1s linear;
    }
    .audio-duration { font-size: 0.72rem; opacity: 0.8; white-space: nowrap; }

    /* ── Mode sélection ── */
    .msg-row { transition: background 0.15s; }
    .msg-row.selectable { cursor: pointer; }
    .msg-row.selected { background: rgba(26,58,92,0.10); border-radius: 8px; }
    .msg-row.selected .msg-bubble {
        outline: 2px solid rgba(26,58,92,0.45);
        outline-offset: 1px;
    }
    .msg-check {
        width: 20px; height: 20px;
        border-radius: 50%;
        border: 2px solid #c0c0c0;
        background: #fff;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: 0.7rem;
        color: #fff;
        transition: all 0.15s;
        cursor: pointer;
        opacity: 0.4;
    }
    .msg-row:hover .msg-check { opacity: 1; }
    .msg-row.selectable .msg-check { opacity: 1; }
    .msg-row.selected .msg-check {
        background: #1a3a5c;
        border-color: #1a3a5c;
        opacity: 1;
    }

    /* Barre de sélection en haut de la zone messages */
    .selection-bar {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        background: #1a3a5c;
        color: #fff;
        flex-shrink: 0;
    }
    .selection-bar.show { display: flex; }

    /* Message supprimé */
    .msg-deleted {
        font-style: italic;
        color: #9ca3af !important;
        font-size: 0.82rem;
        display: flex; align-items: center; gap: 5px;
    }
    .msg-row.mine .msg-deleted { color: rgba(255,255,255,0.55) !important; }

    /* Placeholder état vide */
    .chat-placeholder {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #bbb;
        gap: 10px;
    }
    .chat-placeholder i { font-size: 4rem; opacity: 0.3; }

    @media (max-width: 768px) {
        .chat-sidebar { width: 100%; display: none; }
        .chat-sidebar.show { display: flex; }
    }
</style>
@endpush

@section('content')
<div class="chat-container">

    {{-- ── Sidebar conversations ── --}}
    <div class="chat-sidebar" id="chatSidebar">
        <div class="chat-sidebar-header">
            <h6><i class="fas fa-comments me-2"></i>Conversations</h6>
            <div class="d-flex gap-1">
                {{-- Nouveau message direct --}}
                <button class="btn btn-sm btn-light" title="Message direct"
                        data-bs-toggle="modal" data-bs-target="#modalDirect">
                    <i class="fas fa-user-plus" style="font-size:0.8rem;color:#1a3a5c;"></i>
                </button>
                {{-- Nouveau groupe --}}
                <button class="btn btn-sm btn-light" title="Créer un groupe" data-bs-toggle="modal" data-bs-target="#modalGroupe">
                    <i class="fas fa-users" style="font-size:0.8rem;color:#2d6a9f;"></i>
                </button>
            </div>
        </div>
        <div class="chat-search">
            <input type="text" id="searchConv" placeholder="Rechercher..." oninput="filtrerConversations(this.value)">
        </div>
        <div class="conv-list" id="convList">
            @forelse($conversations as $conv)
            <div class="conv-item {{ request('conv') == $conv['id'] ? 'active' : '' }}"
                 id="conv-{{ $conv['id'] }}"
                 onclick="ouvrirConversation({{ $conv['id'] }}, '{{ addslashes($conv['nom']) }}', '{{ $conv['type'] }}')">
                <div class="conv-avatar" style="background:{{ $conv['type']==='groupe' ? 'linear-gradient(135deg,#7b2d8b,#a855f7)' : 'linear-gradient(135deg,#1a3a5c,#2d6a9f)' }};">
                    @if($conv['type']==='groupe')
                        <i class="fas fa-users" style="font-size:1rem;"></i>
                    @else
                        {{ strtoupper(substr($conv['nom'], 0, 1)) }}
                    @endif
                </div>
                <div class="conv-info">
                    <div class="conv-name">{{ $conv['nom'] }}</div>
                    <div class="conv-last">{{ $conv['dernier_msg'] ? Str::limit($conv['dernier_msg'], 35) : 'Démarrer la conversation...' }}</div>
                </div>
                <div class="conv-meta">
                    <div class="conv-time">{{ $conv['dernier_at'] ?? '' }}</div>
                    @if($conv['non_lus'] > 0)
                    <div class="conv-badge" id="badge-{{ $conv['id'] }}">{{ $conv['non_lus'] }}</div>
                    @else
                    <div class="conv-badge" id="badge-{{ $conv['id'] }}" style="display:none;">0</div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4 small">
                <i class="fas fa-comment-slash d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>
                Aucune conversation.<br>Démarrez une nouvelle discussion !
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── Zone principale messages ── --}}
    <div class="chat-main" id="chatMain">
        <div class="chat-placeholder" id="chatPlaceholder">
            <i class="fas fa-comments"></i>
            <p class="text-muted">Sélectionnez une conversation ou démarrez-en une nouvelle</p>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalDirect">
                    <i class="fas fa-user-plus me-1"></i> Message direct
                </button>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGroupe">
                    <i class="fas fa-users me-1"></i> Créer un groupe
                </button>
            </div>
        </div>

        {{-- Header de conversation (masqué par défaut) --}}
        <div class="chat-header" id="chatHeader" style="display:none;">
            <div class="chat-header-avatar" id="headerAvatar">?</div>
            <div class="chat-header-info">
                <h6 id="headerNom">—</h6>
                <small id="headerParticipants"></small>
            </div>
        </div>

        {{-- Barre de sélection (mode suppression) --}}
        <div class="selection-bar" id="selectionBar">
            <button onclick="annulerSelection()" style="background:none;border:none;color:#fff;padding:0 4px;" title="Annuler">
                <i class="fas fa-times"></i>
            </button>
            <span id="selCount" style="font-size:0.88rem;font-weight:600;">0 sélectionné(s)</span>
            <div class="ms-auto d-flex gap-2">
                <button class="btn btn-sm btn-light" onclick="ouvrirModalSuppression()"
                        style="font-size:0.8rem;border-radius:20px;padding:3px 12px;">
                    <i class="fas fa-trash me-1"></i>Supprimer
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div class="chat-messages" id="chatMessages" style="display:none;"></div>

        {{-- Saisie --}}
        <div class="chat-input-area" id="chatInputArea" style="display:none;">

            {{-- Barre enregistrement en cours --}}
            <div class="recording-bar" id="recordingBar">
                <div class="rec-dot"></div>
                <span>Enregistrement…</span>
                <span class="rec-timer" id="recTimer">0:00</span>
                <button class="btn btn-sm btn-outline-danger ms-auto" onclick="arreterEnregistrement(true)" style="border-radius:20px;padding:2px 10px;font-size:0.78rem;">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
                <button class="btn btn-sm btn-danger" onclick="arreterEnregistrement(false)" style="border-radius:20px;padding:2px 10px;font-size:0.78rem;">
                    <i class="fas fa-stop me-1"></i>Arrêter
                </button>
            </div>

            {{-- Prévisualisation audio --}}
            <div class="audio-preview" id="audioPreview">
                <audio id="audioPreviewPlayer" controls></audio>
                <button class="btn-cancel-audio" onclick="annulerAudio()" title="Annuler">
                    <i class="fas fa-times-circle"></i>
                </button>
                <button class="btn btn-sm btn-primary" onclick="envoyerVocal()" style="border-radius:20px;padding:4px 14px;">
                    <i class="fas fa-paper-plane me-1"></i>Envoyer
                </button>
            </div>

            {{-- Zone texte normale --}}
            <div class="chat-input-row" id="textInputRow">
                <button class="btn-mic" id="btnMic" onclick="demarrerEnregistrement()" title="Message vocal">
                    <i class="fas fa-microphone" style="font-size:0.95rem;"></i>
                </button>
                <textarea id="msgInput" rows="1" placeholder="Écrire un message… (Entrée pour envoyer)"
                          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();envoyerMessage();}"
                          oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px';"></textarea>
                <button class="btn-send" id="btnSend" onclick="envoyerMessage()">
                    <i class="fas fa-paper-plane" style="font-size:0.9rem;"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal : Message direct ── --}}
<div class="modal fade" id="modalDirect" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#1a3a5c;"><i class="fas fa-user-plus me-2"></i>Nouveau message direct</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold small">Choisir un utilisateur</label>
                <select class="form-select" id="selectUser">
                    <option value="">-- Sélectionner --</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="creerDirect()">
                    <i class="fas fa-comment me-1"></i>Démarrer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal : Créer groupe ── --}}
<div class="modal fade" id="modalGroupe" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#1a3a5c;"><i class="fas fa-users me-2"></i>Créer un groupe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nom du groupe</label>
                    <input type="text" class="form-control" id="nomGroupe" placeholder="Ex: Équipe Dakar">
                </div>
                <div>
                    <label class="form-label fw-bold small">Membres</label>
                    <div style="max-height:200px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:8px;">
                        @foreach($users as $u)
                        <div class="form-check py-1">
                            <input class="form-check-input membre-check" type="checkbox" value="{{ $u->id }}" id="m{{ $u->id }}">
                            <label class="form-check-label" for="m{{ $u->id }}">{{ $u->name }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="creerGroupe()">
                    <i class="fas fa-users me-1"></i>Créer le groupe
                </button>
            </div>
        </div>
    </div>
</div>
{{-- ── Modal : Supprimer messages ── --}}
<div class="modal fade" id="modalSupprimer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0 pb-1">
                <h6 class="modal-title fw-bold" style="color:#1a3a5c;">
                    <i class="fas fa-trash me-2"></i>Supprimer le(s) message(s)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger btn-sm" onclick="confirmerSuppression('moi')" style="border-radius:8px;">
                        <i class="fas fa-user me-2"></i>Supprimer pour moi
                    </button>
                    <button class="btn btn-danger btn-sm" id="btnSupprimerTous"
                            onclick="confirmerSuppression('tous')" style="border-radius:8px;">
                        <i class="fas fa-users me-2"></i>Supprimer pour tout le monde
                    </button>
                </div>
                <small class="text-muted d-block mt-2" id="supprimerTousInfo" style="font-size:0.75rem;"></small>
            </div>
            <div class="modal-footer border-0 pt-1">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>
{{-- Trigger caché pour ouvrir le modal via JS --}}
<button id="triggerModalSupprimer" data-bs-toggle="modal" data-bs-target="#modalSupprimer" style="display:none;"></button>

@endsection

@push('scripts')
<script>
const CSRF       = '{{ csrf_token() }}';
const BASE_CHAT  = '{{ url("/chat") }}';
const MY_ID      = {{ auth()->id() }};
const MY_NAME    = '{{ auth()->user()->name }}';

let convActive     = null;
let convNom        = '';
let pollInterval   = null;
let lastTimestamp  = null;

// ── Mode sélection ───────────────────────────────────────────────────────
let selectionMode     = false;
let selectedIds       = new Set();      // Set<number>
let selectedMsgData   = {};             // id -> {mine, ts}
const BASE_SUPPRIMER  = '{{ url("/chat/supprimer-messages") }}';

const COLORS = ['#1a3a5c','#e63946','#2d6a4f','#7b2d8b','#0077b6','#f4a261'];
function couleurPour(str) {
    let h = 0;
    for (let c of str) h = c.charCodeAt(0) + ((h << 5) - h);
    return COLORS[Math.abs(h) % COLORS.length];
}
function initiales(nom) {
    return nom.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

// ── Ouvrir une conversation ───────────────────────────────────────────────
function ouvrirConversation(id, nom, type, participants) {
    convActive    = id;
    convNom       = nom;
    lastTimestamp = null;

    // Mettre à jour UI sidebar
    document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
    const convEl = document.getElementById('conv-' + id);
    if (convEl) { convEl.classList.add('active'); }

    // Afficher zone messages
    document.getElementById('chatPlaceholder').style.display  = 'none';
    document.getElementById('chatHeader').style.display        = '';
    document.getElementById('chatMessages').style.display      = '';
    document.getElementById('chatInputArea').style.display     = '';

    // Header
    const avatar = document.getElementById('headerAvatar');
    if (type === 'groupe') {
        avatar.innerHTML = '<i class="fas fa-users"></i>';
        avatar.style.background = 'linear-gradient(135deg,#7b2d8b,#a855f7)';
    } else {
        avatar.textContent = initiales(nom);
        avatar.style.background = couleurPour(nom);
    }
    document.getElementById('headerNom').textContent = nom;

    // Vider et charger
    document.getElementById('chatMessages').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    // Arrêter ancien polling
    if (pollInterval) clearInterval(pollInterval);

    // Charger les messages
    chargerMessages(true);
    pollInterval = setInterval(() => chargerMessages(false), 3000);
}

// ── Charger / rafraîchir les messages ────────────────────────────────────
async function chargerMessages(initial) {
    if (!convActive) return;
    let url = `${BASE_CHAT}/${convActive}/messages`;
    if (!initial && lastTimestamp) url += `?since=${encodeURIComponent(lastTimestamp)}`;

    try {
        const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!r.ok) return;
        const data = await r.json();

        if (initial) {
            const zone = document.getElementById('chatMessages');
            zone.innerHTML = '';
            document.getElementById('headerParticipants').textContent = data.participants || '';
        }

        if (data.messages && data.messages.length > 0) {
            afficherMessages(data.messages, initial);
            lastTimestamp = data.messages[data.messages.length - 1].timestamp;
        } else if (initial) {
            document.getElementById('chatMessages').innerHTML =
                '<div class="text-center text-muted py-5 small"><i class="fas fa-comment-slash d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>Aucun message. Envoyez le premier !</div>';
        }

        // Supprimer badge de la conversation
        const badge = document.getElementById('badge-' + convActive);
        if (badge) badge.style.display = 'none';

    } catch(e) {}
}

// ── Afficher les messages dans le DOM ────────────────────────────────────
function afficherMessages(msgs, scroll) {
    const zone = document.getElementById('chatMessages');

    // Si zone contient le placeholder "aucun message", vider
    if (zone.querySelector('.fa-comment-slash')) zone.innerHTML = '';

    let lastDate = null;

    msgs.forEach(m => {
        // Séparateur de date
        if (m.date !== lastDate) {
            lastDate = m.date;
            const sep = document.createElement('div');
            sep.className = 'msg-day';
            sep.innerHTML = `<span>${m.date}</span>`;
            zone.appendChild(sep);
        }

        const row = document.createElement('div');
        row.className = `msg-row ${m.mine ? 'mine' : ''}`;
        row.id = `msg-${m.id}`;
        row.dataset.msgId = m.id;
        row.dataset.mine  = m.mine;
        row.dataset.ts    = m.timestamp;
        row.dataset.supprime = m.supprime || false;

        // Contenu de la bulle
        let contenuHTML;
        if (m.supprime) {
            contenuHTML = `<span class="msg-deleted"><i class="fas fa-ban"></i>Message supprimé</span>`;
        } else if (m.type === 'audio' && m.fichier_url) {
            contenuHTML = `<div class="audio-bubble">
                 <button class="btn-play-audio" onclick="toggleAudio(this, '${m.fichier_url}')">
                     <i class="fas fa-play" style="font-size:0.75rem;"></i>
                 </button>
                 <div style="flex:1;min-width:0;">
                     <div class="audio-waveform" onclick="seekAudio(this, event)">
                         <div class="audio-progress"></div>
                     </div>
                 </div>
                 <span class="audio-duration">${m.duree ? formatDuree(m.duree) : '🎤'}</span>
               </div>`;
        } else {
            contenuHTML = escHtml(m.contenu);
        }

        const checkHtml = `<div class="msg-check" id="chk-${m.id}"></div>`;

        if (!m.mine) {
            row.innerHTML = `
                ${checkHtml}
                <div class="msg-avatar" style="background:${couleurPour(m.sender_name)};color:#fff;">
                    ${initiales(m.sender_name)}
                </div>
                <div>
                    <div class="msg-sender">${m.sender_name}</div>
                    <div class="msg-bubble">${contenuHTML}</div>
                    <div class="msg-time">${m.created_at}</div>
                </div>`;
        } else {
            row.innerHTML = `
                <div>
                    <div class="msg-bubble">${contenuHTML}</div>
                    <div class="msg-time">${m.created_at}</div>
                </div>
                ${checkHtml}`;
        }

        // Clic sur le cercle check → entrer sélection ou toggle
        const chkEl = row.querySelector('#chk-' + m.id);
        if (chkEl) {
            chkEl.addEventListener('click', e => {
                e.stopPropagation();
                if (!m.supprime) {
                    if (!selectionMode) entrerSelection(m.id);
                    else toggleMsgSelection(m.id, m);
                }
            });
        }

        // Clic droit → entrer en mode sélection
        row.addEventListener('contextmenu', e => {
            e.preventDefault();
            if (!m.supprime) entrerSelection(m.id);
        });
        // Clic normal → toggle sélection si mode actif
        row.addEventListener('click', () => {
            if (selectionMode && !m.supprime) toggleMsgSelection(m.id, m);
        });
        // Long press (mobile)
        let longPressTimer;
        row.addEventListener('touchstart', () => {
            longPressTimer = setTimeout(() => {
                if (!m.supprime) entrerSelection(m.id);
            }, 500);
        });
        row.addEventListener('touchend',   () => clearTimeout(longPressTimer));
        row.addEventListener('touchmove',  () => clearTimeout(longPressTimer));

        zone.appendChild(row);
    });

    if (scroll) zone.scrollTop = zone.scrollHeight;
}

function escHtml(t) {
    return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}

// ── Envoyer un message ───────────────────────────────────────────────────
async function envoyerMessage() {
    if (!convActive) return;
    const input = document.getElementById('msgInput');
    const contenu = input.value.trim();
    if (!contenu) return;

    input.value = '';
    input.style.height = 'auto';
    document.getElementById('btnSend').disabled = true;

    try {
        const r = await fetch(`${BASE_CHAT}/${convActive}/envoyer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ contenu }),
        });
        if (r.ok) {
            const msg = await r.json();
            const zone = document.getElementById('chatMessages');
            if (zone.querySelector('.fa-comment-slash')) zone.innerHTML = '';
            afficherMessages([msg], true);
            lastTimestamp = msg.timestamp;
            // Mettre à jour dernier message dans sidebar
            const convEl = document.getElementById('conv-' + convActive);
            if (convEl) {
                const lastEl = convEl.querySelector('.conv-last');
                if (lastEl) lastEl.textContent = contenu.slice(0, 35);
            }
        }
    } catch(e) {}

    document.getElementById('btnSend').disabled = false;
    document.getElementById('msgInput').focus();
}

// ── Créer conversation directe ───────────────────────────────────────────
async function creerDirect() {
    const userId = document.getElementById('selectUser').value;
    if (!userId) { alert('Veuillez choisir un utilisateur.'); return; }

    try {
        const r = await fetch(`${BASE_CHAT}/creer-direct`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ user_id: userId }),
        });
        const data = await r.json();
        document.getElementById('modalDirect').querySelector('[data-bs-dismiss="modal"]')?.click();
        window.location.href = `${BASE_CHAT}?conv=${data.conversation_id}`;
    } catch(e) { alert('Erreur lors de la création.'); }
}

// ── Créer groupe ─────────────────────────────────────────────────────────
async function creerGroupe() {
    const nom     = document.getElementById('nomGroupe').value.trim();
    const checked = [...document.querySelectorAll('.membre-check:checked')].map(c => parseInt(c.value));
    if (!nom)          { alert('Donnez un nom au groupe.'); return; }
    if (!checked.length) { alert('Choisissez au moins un membre.'); return; }

    try {
        const r = await fetch(`${BASE_CHAT}/creer-groupe`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ nom, user_ids: checked }),
        });
        const data = await r.json();
        document.getElementById('modalGroupe').querySelector('[data-bs-dismiss="modal"]')?.click();
        window.location.href = `${BASE_CHAT}?conv=${data.conversation_id}`;
    } catch(e) { alert('Erreur lors de la création.'); }
}

// ═══════════════════════════════════════════════════════════════════════════
// MESSAGES VOCAUX
// ═══════════════════════════════════════════════════════════════════════════
let mediaRecorder  = null;
let audioChunks    = [];
let recordingBlob  = null;
let recTimerID     = null;
let recSeconds     = 0;

async function demarrerEnregistrement() {
    if (!navigator.mediaDevices?.getUserMedia) {
        alert('Votre navigateur ne supporte pas l\'enregistrement audio.');
        return;
    }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const mimeType = MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 'audio/ogg';
        mediaRecorder = new MediaRecorder(stream, { mimeType });
        audioChunks   = [];
        recSeconds    = 0;

        mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
        mediaRecorder.onstop = () => {
            recordingBlob = new Blob(audioChunks, { type: mimeType });
            const url = URL.createObjectURL(recordingBlob);
            document.getElementById('audioPreviewPlayer').src = url;

            // Afficher prévisualisation
            document.getElementById('recordingBar').classList.remove('show');
            document.getElementById('textInputRow').style.display = 'none';
            document.getElementById('audioPreview').classList.add('show');

            clearInterval(recTimerID);
            stream.getTracks().forEach(t => t.stop());
        };

        mediaRecorder.start(200);

        // UI : afficher barre enregistrement
        document.getElementById('textInputRow').style.display = 'none';
        document.getElementById('recordingBar').classList.add('show');
        document.getElementById('btnMic').classList.add('recording');

        // Timer
        recTimerID = setInterval(() => {
            recSeconds++;
            const m = Math.floor(recSeconds / 60);
            const s = recSeconds % 60;
            document.getElementById('recTimer').textContent = `${m}:${s.toString().padStart(2,'0')}`;
            if (recSeconds >= 300) arreterEnregistrement(false); // max 5 min
        }, 1000);

    } catch(e) {
        alert('Impossible d\'accéder au microphone. Vérifiez les permissions.');
    }
}

function arreterEnregistrement(annuler) {
    clearInterval(recTimerID);
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        if (annuler) {
            mediaRecorder.ondataavailable = null;
            mediaRecorder.onstop = () => {};
            mediaRecorder.stream.getTracks().forEach(t => t.stop());
            mediaRecorder.stop();
            reinitialiserInputAudio();
        } else {
            mediaRecorder.stop(); // onstop fera le reste
        }
    }
    document.getElementById('btnMic')?.classList.remove('recording');
}

function annulerAudio() {
    recordingBlob = null;
    reinitialiserInputAudio();
}

function reinitialiserInputAudio() {
    document.getElementById('recordingBar').classList.remove('show');
    document.getElementById('audioPreview').classList.remove('show');
    document.getElementById('textInputRow').style.display = '';
    document.getElementById('recTimer').textContent = '0:00';
    recSeconds = 0;
}

async function envoyerVocal() {
    if (!recordingBlob || !convActive) return;

    const btn = document.querySelector('#audioPreview .btn-primary');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Envoi...'; }

    const formData = new FormData();
    const ext = recordingBlob.type.includes('webm') ? 'webm' : 'ogg';
    formData.append('audio', recordingBlob, `vocal_${Date.now()}.${ext}`);
    formData.append('duree', recSeconds);
    formData.append('_token', CSRF);

    try {
        const r = await fetch(`${BASE_CHAT}/${convActive}/envoyer-vocal`, {
            method: 'POST',
            body: formData,
        });
        if (r.ok) {
            const msg = await r.json();
            const zone = document.getElementById('chatMessages');
            if (zone.querySelector('.fa-comment-slash')) zone.innerHTML = '';
            afficherMessages([msg], true);
            lastTimestamp = msg.timestamp;
        }
    } catch(e) { alert('Erreur lors de l\'envoi.'); }

    recordingBlob = null;
    reinitialiserInputAudio();
}

// ── Lecture audio dans les bulles ────────────────────────────────────────
let currentAudio = null;
let currentBtn   = null;

function toggleAudio(btn, url) {
    if (currentAudio && currentBtn !== btn) {
        currentAudio.pause();
        currentBtn.innerHTML = '<i class="fas fa-play" style="font-size:0.75rem;"></i>';
        updateProgress(currentBtn, 0);
    }

    if (!btn._audio) {
        btn._audio = new Audio(url);
        btn._audio.addEventListener('timeupdate', () => {
            const pct = btn._audio.duration ? (btn._audio.currentTime / btn._audio.duration) * 100 : 0;
            updateProgress(btn, pct);
        });
        btn._audio.addEventListener('ended', () => {
            btn.innerHTML = '<i class="fas fa-play" style="font-size:0.75rem;"></i>';
            updateProgress(btn, 0);
            currentAudio = null; currentBtn = null;
        });
    }

    if (btn._audio.paused) {
        btn._audio.play();
        btn.innerHTML = '<i class="fas fa-pause" style="font-size:0.75rem;"></i>';
        currentAudio = btn._audio;
        currentBtn   = btn;
    } else {
        btn._audio.pause();
        btn.innerHTML = '<i class="fas fa-play" style="font-size:0.75rem;"></i>';
    }
}

function updateProgress(btn, pct) {
    const waveform = btn.closest('.audio-bubble')?.querySelector('.audio-progress');
    if (waveform) waveform.style.width = pct + '%';
}

function seekAudio(waveformEl, event) {
    const btn = waveformEl.closest('.audio-bubble')?.querySelector('.btn-play-audio');
    if (!btn?._audio) return;
    const rect = waveformEl.getBoundingClientRect();
    const pct  = (event.clientX - rect.left) / rect.width;
    btn._audio.currentTime = btn._audio.duration * pct;
}

function formatDuree(s) {
    const m = Math.floor(s / 60);
    return `${m}:${(s % 60).toString().padStart(2,'0')}`;
}

// ═══════════════════════════════════════════════════════════════════════════
// MODE SÉLECTION & SUPPRESSION
// ═══════════════════════════════════════════════════════════════════════════

function entrerSelection(msgId) {
    selectionMode = true;
    document.getElementById('selectionBar').classList.add('show');
    document.querySelectorAll('.msg-row[data-msg-id]').forEach(el => el.classList.add('selectable'));
    toggleMsgSelection(msgId, { mine: document.getElementById('msg-' + msgId)?.dataset.mine === 'true',
                                  ts:   document.getElementById('msg-' + msgId)?.dataset.ts });
}

function annulerSelection() {
    selectionMode = false;
    selectedIds.clear();
    selectedMsgData = {};
    document.getElementById('selectionBar').classList.remove('show');
    document.querySelectorAll('.msg-row.selected').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.msg-check').forEach(el => { el.innerHTML = ''; });
    document.querySelectorAll('.msg-row.selectable').forEach(el => el.classList.remove('selectable'));
}

function toggleMsgSelection(msgId, msgInfo) {
    const id  = Number(msgId);
    const row = document.getElementById('msg-' + id);
    const chk = document.getElementById('chk-' + id);
    if (!row) return;

    if (selectedIds.has(id)) {
        selectedIds.delete(id);
        delete selectedMsgData[id];
        row.classList.remove('selected');
        if (chk) chk.innerHTML = '';
    } else {
        selectedIds.add(id);
        selectedMsgData[id] = msgInfo || { mine: row.dataset.mine === 'true', ts: row.dataset.ts };
        row.classList.add('selected');
        if (chk) chk.innerHTML = '<i class="fas fa-check" style="font-size:0.65rem;"></i>';
    }

    const cnt = selectedIds.size;
    document.getElementById('selCount').textContent = `${cnt} sélectionné(s)`;
    if (cnt === 0) annulerSelection();
}

function ouvrirModalSuppression() {
    if (selectedIds.size === 0) return;

    const now = Date.now();
    let canTous   = true;
    let infoText  = '';

    for (const [id, info] of Object.entries(selectedMsgData)) {
        if (!info || info.mine === false || info.mine === 'false') {
            canTous = false;
            infoText = 'Certains messages sélectionnés ne vous appartiennent pas.';
            break;
        }
        const diffMin = (now - new Date(info.ts).getTime()) / 60000;
        if (diffMin > 20) {
            canTous = false;
            infoText = 'Certains messages ont plus de 20 minutes (suppression pour tous impossible).';
            break;
        }
    }

    const btnTous = document.getElementById('btnSupprimerTous');
    btnTous.disabled = !canTous;
    document.getElementById('supprimerTousInfo').textContent = infoText;

    document.getElementById('triggerModalSupprimer').click();
}

async function confirmerSuppression(mode) {
    // Fermer le modal
    document.getElementById('modalSupprimer').querySelector('[data-bs-dismiss="modal"]').click();

    const ids = [...selectedIds];

    try {
        const r = await fetch(BASE_SUPPRIMER, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ids, mode }),
        });
        if (!r.ok) return;

        ids.forEach(id => {
            const row = document.getElementById('msg-' + id);
            if (!row) return;
            if (mode === 'moi') {
                // Retirer du DOM pour cet utilisateur
                row.remove();
            } else {
                // Remplacer le contenu par "Message supprimé"
                const bubble = row.querySelector('.msg-bubble');
                if (bubble) {
                    bubble.innerHTML = `<span class="msg-deleted"><i class="fas fa-ban"></i>Message supprimé</span>`;
                }
                row.dataset.supprime = 'true';
            }
        });
    } catch(e) {}

    annulerSelection();
}

// ── Filtre recherche conversations ───────────────────────────────────────
function filtrerConversations(q) {
    document.querySelectorAll('.conv-item').forEach(el => {
        const nom = el.querySelector('.conv-name')?.textContent?.toLowerCase() || '';
        el.style.display = nom.includes(q.toLowerCase()) ? '' : 'none';
    });
}

// ── Auto-ouvrir si ?conv=X ───────────────────────────────────────────────
const urlParams = new URLSearchParams(window.location.search);
const convParam = urlParams.get('conv');
if (convParam) {
    const el = document.getElementById('conv-' + convParam);
    if (el) el.click();
}
</script>
@endpush
