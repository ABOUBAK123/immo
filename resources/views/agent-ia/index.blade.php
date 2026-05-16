@extends('layouts.app')

@section('title', 'Agent IA')
@section('page-title', 'Agent IA')

@push('styles')
<style>
    .ia-wrapper {
        display: flex; gap: 0; height: calc(100vh - var(--topbar-h) - 48px);
        background: #fff; border-radius: var(--card-radius);
        border: 1px solid #FED7AA; overflow: hidden;
    }

    /* ── Panneau gauche : liste conversations ── */
    .ia-sidebar {
        width: 280px; flex-shrink: 0;
        border-right: 1px solid #FED7AA;
        display: flex; flex-direction: column;
        background: var(--primary-lt);
    }
    .ia-sidebar-head {
        padding: 16px; border-bottom: 1px solid #FED7AA;
        display: flex; align-items: center; justify-content: space-between;
    }
    .ia-sidebar-head h6 { margin: 0; font-size: .82rem; font-weight: 700; color: var(--text-main); }
    .ia-conv-list { overflow-y: auto; flex: 1; padding: 8px 0; }
    .ia-conv-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 16px; cursor: pointer; transition: background .15s;
        text-decoration: none; color: var(--text-main);
        border-left: 3px solid transparent;
    }
    .ia-conv-item:hover { background: rgba(249,115,22,.08); color: var(--text-main); }
    .ia-conv-item.active { background: rgba(249,115,22,.12); border-left-color: var(--primary); }
    .ia-conv-item .ia-conv-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: var(--primary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; font-weight: 700; flex-shrink: 0;
    }
    .ia-conv-item .ia-conv-info { flex: 1; min-width: 0; }
    .ia-conv-item .ia-conv-name { font-size: .8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ia-conv-item .ia-conv-preview { font-size: .72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* ── Panneau droit : chat ── */
    .ia-chat {
        flex: 1; display: flex; flex-direction: column; min-width: 0;
    }
    .ia-chat-head {
        padding: 14px 20px; border-bottom: 1px solid #FED7AA;
        display: flex; align-items: center; gap: 12px; background: #fff;
    }
    .ia-chat-head .ia-head-avatar {
        width: 38px; height: 38px; border-radius: 50%;
        background: linear-gradient(135deg,#7C3AED,#4F46E5);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.1rem; flex-shrink: 0;
    }
    .ia-chat-head h6 { margin: 0; font-size: .88rem; font-weight: 700; }
    .ia-chat-head small { color: var(--text-muted); font-size: .72rem; }

    /* ── Messages ── */
    .ia-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 14px; }
    .ia-msg { display: flex; gap: 10px; align-items: flex-start; max-width: 78%; }
    .ia-msg.user { margin-left: auto; flex-direction: row-reverse; }
    .ia-msg-avatar {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 700;
    }
    .ia-msg.assistant .ia-msg-avatar { background: linear-gradient(135deg,#7C3AED,#4F46E5); color: #fff; }
    .ia-msg.user .ia-msg-avatar { background: var(--primary); color: #fff; }
    .ia-msg-bubble {
        padding: 10px 14px; border-radius: 14px; font-size: .83rem; line-height: 1.55;
        word-break: break-word; position: relative;
    }
    .ia-msg.assistant .ia-msg-bubble {
        background: #F3F4F6; color: var(--text-main);
        border-bottom-left-radius: 4px;
    }
    .ia-msg.user .ia-msg-bubble {
        background: var(--primary); color: #fff;
        border-bottom-right-radius: 4px;
    }
    .ia-msg-time { font-size: .65rem; color: var(--text-muted); margin-top: 3px; display: block; }
    .ia-msg.user .ia-msg-time { text-align: right; }

    /* ── Barre d'envoi ── */
    .ia-input-bar {
        padding: 14px 16px; border-top: 1px solid #FED7AA;
        display: flex; gap: 10px; align-items: flex-end; background: #fff;
    }
    .ia-input-bar textarea {
        flex: 1; resize: none; border: 1px solid #FED7AA; border-radius: 10px;
        padding: 10px 14px; font-size: .83rem; font-family: inherit;
        outline: none; max-height: 120px; line-height: 1.5;
        transition: border-color .15s;
    }
    .ia-input-bar textarea:focus { border-color: var(--primary); }
    .ia-send-btn {
        width: 40px; height: 40px; border-radius: 10px;
        background: var(--primary); color: #fff; border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; flex-shrink: 0; transition: background .15s;
    }
    .ia-send-btn:hover { background: var(--primary-dk); }
    .ia-send-btn:disabled { background: #D1D5DB; cursor: not-allowed; }

    /* ── Barre actions message IA ── */
    .ia-msg-actions {
        display: none; gap: 6px; margin-top: 6px; flex-wrap: wrap;
    }
    .ia-msg:hover .ia-msg-actions { display: flex; }
    .ia-msg-action-btn {
        font-size: .7rem; padding: 3px 8px; border-radius: 6px; border: 1px solid #E5E7EB;
        background: #fff; color: var(--text-muted); cursor: pointer; transition: all .15s;
        display: flex; align-items: center; gap: 4px;
    }
    .ia-msg-action-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-lt); }

    /* ── État vide ── */
    .ia-empty {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        color: var(--text-muted); gap: 12px; padding: 40px;
    }
    .ia-empty i { font-size: 3.5rem; opacity: .3; }
    .ia-empty p { font-size: .85rem; text-align: center; max-width: 300px; margin: 0; }

    /* ── Modale envoi canal ── */
    .canal-card {
        border: 2px solid #E5E7EB; border-radius: 10px; padding: 14px;
        cursor: pointer; transition: all .15s; text-align: center;
    }
    .canal-card:hover, .canal-card.selected { border-color: var(--primary); background: var(--primary-lt); }
    .canal-card i { font-size: 1.5rem; }

    /* ── Loader typing ── */
    .typing-indicator { display: flex; gap: 4px; align-items: center; padding: 4px 2px; }
    .typing-dot {
        width: 7px; height: 7px; border-radius: 50%; background: #9CA3AF;
        animation: typing-bounce 1.2s infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: .2s; }
    .typing-dot:nth-child(3) { animation-delay: .4s; }
    @keyframes typing-bounce { 0%,60%,100% { transform: translateY(0) } 30% { transform: translateY(-6px) } }

    /* ── Alerte config ── */
    .ia-config-banner {
        margin: 16px; padding: 12px 16px; border-radius: 10px;
        background: #FEF3C7; border: 1px solid #FDE68A; color: #92400E;
        font-size: .8rem; display: flex; align-items: center; gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="ia-wrapper">

    {{-- ── Sidebar conversations ─────────────────────────────────────────────── --}}
    <div class="ia-sidebar">
        <div class="ia-sidebar-head">
            <h6><i class="bi bi-robot me-1"></i> Conversations</h6>
            <button class="btn-primary-immo btn btn-sm" style="font-size:.75rem;padding:4px 10px"
                    data-bs-toggle="modal" data-bs-target="#modalNouvelle">
                <i class="bi bi-plus"></i> Nouveau
            </button>
        </div>

        @if(!$iaConfigured)
        <div class="ia-config-banner">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Clé API IA non configurée. <a href="{{ route('admin.parametres', ['tab'=>'ia']) }}" class="fw-semibold" style="color:#92400E">Config. APIs →</a></span>
        </div>
        @endif

        <div class="ia-conv-list">
            @forelse($conversations as $conv)
            @php $lastMsg = $conv->messages->first(); @endphp
            <a href="{{ route('agent-ia.index', ['conv' => $conv->id]) }}"
               class="ia-conv-item {{ $conversationActive && $conversationActive->id == $conv->id ? 'active' : '' }}">
                <div class="ia-conv-avatar">
                    {{ $conv->locataire ? strtoupper(substr($conv->locataire->name,0,1)) : 'IA' }}
                </div>
                <div class="ia-conv-info">
                    <div class="ia-conv-name">{{ $conv->titre }}</div>
                    <div class="ia-conv-preview">
                        {{ $lastMsg ? Str::limit($lastMsg->contenu, 38) : 'Nouvelle conversation' }}
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-4" style="font-size:.78rem;color:var(--text-muted)">
                Aucune conversation.<br>Cliquez sur <strong>+ Nouveau</strong>.
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── Zone de chat ─────────────────────────────────────────────────────── --}}
    <div class="ia-chat">
        @if($conversationActive)

        {{-- En-tête conversation --}}
        <div class="ia-chat-head">
            <div class="ia-head-avatar"><i class="bi bi-robot"></i></div>
            <div style="flex:1">
                <h6>{{ $conversationActive->titre }}</h6>
                <small>
                    @if($conversationActive->locataire)
                        Locataire : {{ $conversationActive->locataire->name }}
                        @if($conversationActive->locataire->email) — {{ $conversationActive->locataire->email }}@endif
                    @else
                        Assistant général ImmoGest
                    @endif
                </small>
            </div>
            {{-- Bouton envoyer via canal --}}
            @if($conversationActive->locataire)
            <button class="btn btn-sm" style="border:1px solid #FED7AA;font-size:.75rem;gap:5px;display:flex;align-items:center"
                    data-bs-toggle="modal" data-bs-target="#modalEnvoi"
                    data-locataire="{{ $conversationActive->locataire_id }}"
                    data-nom="{{ $conversationActive->locataire->name }}">
                <i class="bi bi-send"></i> Envoyer via canal
            </button>
            @endif
        </div>

        {{-- Messages --}}
        <div class="ia-messages" id="chatMessages">
            @foreach($messages->where('role','!=','system') as $msg)
            <div class="ia-msg {{ $msg->role }}" id="msg-{{ $msg->id }}">
                <div class="ia-msg-avatar">
                    @if($msg->role === 'assistant')<i class="bi bi-robot"></i>
                    @else{{ strtoupper(substr(auth()->user()->name,0,1)) }}@endif
                </div>
                <div>
                    <div class="ia-msg-bubble">{{ $msg->contenu }}</div>
                    <span class="ia-msg-time">{{ $msg->created_at->format('H:i') }}</span>
                    @if($msg->role === 'assistant')
                    <div class="ia-msg-actions">
                        <button class="ia-msg-action-btn" onclick="copyMsg(this)" data-text="{{ htmlspecialchars($msg->contenu) }}">
                            <i class="bi bi-clipboard"></i> Copier
                        </button>
                        @if($conversationActive->locataire)
                        <button class="ia-msg-action-btn" onclick="prepareEnvoi('{{ htmlspecialchars($msg->contenu, ENT_QUOTES) }}')">
                            <i class="bi bi-send"></i> Envoyer
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            <div id="typingIndicator" style="display:none" class="ia-msg assistant">
                <div class="ia-msg-avatar"><i class="bi bi-robot"></i></div>
                <div class="ia-msg-bubble">
                    <div class="typing-indicator">
                        <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barre d'envoi --}}
        <div class="ia-input-bar">
            <textarea id="userInput" rows="1" placeholder="Posez une question ou demandez de rédiger un message…"
                      onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
            <button class="ia-send-btn" id="sendBtn" onclick="sendMessage()">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>

        @else
        {{-- État vide --}}
        <div class="ia-empty">
            <i class="bi bi-robot"></i>
            <strong style="font-size:.9rem;color:var(--text-main)">Agent IA ImmoGest</strong>
            <p>Démarrez une conversation pour rédiger des messages, gérer des relances ou obtenir de l'aide sur votre gestion locative.</p>
            <button class="btn-primary-immo btn" data-bs-toggle="modal" data-bs-target="#modalNouvelle">
                <i class="bi bi-plus me-1"></i> Nouvelle conversation
            </button>
        </div>
        @endif
    </div>
</div>

{{-- ── Modale nouvelle conversation ──────────────────────────────────────────── --}}
<div class="modal fade" id="modalNouvelle" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('agent-ia.nouvelle') }}">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #FED7AA">
                    <h6 class="modal-title"><i class="bi bi-robot me-2" style="color:var(--primary)"></i>Nouvelle conversation</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem;font-weight:600">Locataire concerné (optionnel)</label>
                        <select name="locataire_id" class="form-select form-select-sm">
                            <option value="">— Assistant général —</option>
                            @foreach($locataires as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" style="font-size:.72rem">Sélectionner un locataire injecte son contexte (bail, retards…) dans l'IA.</div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #FED7AA">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary-immo">
                        <i class="bi bi-chat-dots me-1"></i>Démarrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modale envoi via canal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalEnvoi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="border-bottom:1px solid #FED7AA">
                <h6 class="modal-title"><i class="bi bi-send me-2" style="color:var(--primary)"></i>Envoyer le message</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600">Canal d'envoi</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="canal-card" onclick="selectCanal('email',this)">
                                <i class="bi bi-envelope-fill" style="color:#2563EB"></i>
                                <div style="font-size:.75rem;font-weight:600;margin-top:4px">Email</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="canal-card" onclick="selectCanal('sms',this)">
                                <i class="bi bi-phone" style="color:#D97706"></i>
                                <div style="font-size:.75rem;font-weight:600;margin-top:4px">SMS</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="canal-card" onclick="selectCanal('whatsapp',this)">
                                <i class="bi bi-whatsapp" style="color:#059669"></i>
                                <div style="font-size:.75rem;font-weight:600;margin-top:4px">WhatsApp</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600">Sujet (email)</label>
                    <input type="text" id="envoiSujet" class="form-control form-control-sm" placeholder="Objet du message">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600">Message</label>
                    <textarea id="envoiMessage" class="form-control form-control-sm" rows="5" style="font-size:.82rem"></textarea>
                </div>
                <div id="envoiResult" class="d-none"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #FED7AA">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-sm btn-primary-immo" onclick="doEnvoi()">
                    <i class="bi bi-send-fill me-1"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CONV_ID = {{ $conversationActive ? $conversationActive->id : 'null' }};
const LOCATAIRE_ID = {{ $conversationActive && $conversationActive->locataire ? $conversationActive->locataire_id : 'null' }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

let selectedCanal = '';
let envoiLocataireId = LOCATAIRE_ID;

// ── Auto-scroll ──
function scrollBottom() {
    const el = document.getElementById('chatMessages');
    if (el) el.scrollTop = el.scrollHeight;
}
scrollBottom();

// ── Auto-resize textarea ──
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ── Enter = envoyer (Shift+Enter = saut de ligne) ──
function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

// ── Envoyer message à l'IA ──
async function sendMessage() {
    const input = document.getElementById('userInput');
    const text = input.value.trim();
    if (!text || !CONV_ID) return;

    const btn = document.getElementById('sendBtn');
    btn.disabled = true;
    input.value = '';
    input.style.height = 'auto';

    // Afficher le message utilisateur
    appendMsg('user', text, new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}));
    showTyping(true);

    try {
        const res = await fetch('{{ route("agent-ia.chat") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ conversation_id: CONV_ID, message: text }),
        });
        const data = await res.json();
        showTyping(false);

        if (data.ok) {
            appendMsg('assistant', data.reponse, data.created_at, data.msg_id);
        } else {
            appendMsg('assistant', '❌ Erreur : ' + (data.message || 'inconnue'), '');
        }
    } catch (e) {
        showTyping(false);
        appendMsg('assistant', '❌ Erreur réseau. Vérifiez votre connexion.', '');
    }
    btn.disabled = false;
}

function appendMsg(role, text, time, msgId) {
    const container = document.getElementById('chatMessages');
    const isUser = role === 'user';

    const actionsHtml = (!isUser && LOCATAIRE_ID) ? `
        <div class="ia-msg-actions">
            <button class="ia-msg-action-btn" onclick="copyMsg(this)" data-text="${escHtml(text)}">
                <i class="bi bi-clipboard"></i> Copier
            </button>
            <button class="ia-msg-action-btn" onclick="prepareEnvoi(\`${escHtml(text)}\`)">
                <i class="bi bi-send"></i> Envoyer
            </button>
        </div>` : '';

    const html = `
        <div class="ia-msg ${role}" ${msgId ? 'id="msg-'+msgId+'"' : ''}>
            <div class="ia-msg-avatar">${isUser ? '{{ strtoupper(substr(auth()->user()->name,0,1)) }}' : '<i class="bi bi-robot"></i>'}</div>
            <div>
                <div class="ia-msg-bubble">${escHtml(text)}</div>
                <span class="ia-msg-time">${time}</span>
                ${actionsHtml}
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    scrollBottom();
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

function showTyping(show) {
    document.getElementById('typingIndicator').style.display = show ? 'flex' : 'none';
    if (show) scrollBottom();
}

// ── Copier dans le presse-papiers ──
function copyMsg(btn) {
    const text = btn.getAttribute('data-text');
    navigator.clipboard.writeText(text).then(() => {
        btn.innerHTML = '<i class="bi bi-check"></i> Copié !';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i> Copier', 2000);
    });
}

// ── Préparer envoi depuis un message ──
function prepareEnvoi(text) {
    document.getElementById('envoiMessage').value = text;
    new bootstrap.Modal(document.getElementById('modalEnvoi')).show();
}

// ── Modale envoi : canal ──
function selectCanal(canal, el) {
    selectedCanal = canal;
    document.querySelectorAll('.canal-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    // Email : afficher champ sujet
    document.querySelector('[for="envoiSujet"]').closest('.mb-3').style.display = canal === 'email' ? '' : 'none';
}
// Cacher sujet par défaut
document.addEventListener('DOMContentLoaded', () => {
    const s = document.querySelector('[for="envoiSujet"]');
    if (s) s.closest('.mb-3').style.display = 'none';
});

// Pré-remplir locataire depuis bouton header
document.getElementById('modalEnvoi')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    if (btn) {
        envoiLocataireId = btn.dataset.locataire || LOCATAIRE_ID;
    }
});

// ── Envoyer via canal ──
async function doEnvoi() {
    if (!selectedCanal) { alert('Sélectionnez un canal d\'envoi.'); return; }
    const msg = document.getElementById('envoiMessage').value.trim();
    if (!msg) { alert('Le message est vide.'); return; }

    const result = document.getElementById('envoiResult');
    result.className = 'd-none';

    try {
        const res = await fetch('{{ route("agent-ia.envoi") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                canal: selectedCanal,
                locataire_id: envoiLocataireId,
                sujet: document.getElementById('envoiSujet').value || 'Message de votre propriétaire',
                message: msg,
            }),
        });
        const data = await res.json();
        result.className = data.ok ? 'alert alert-success py-2 px-3 mt-2' : 'alert alert-danger py-2 px-3 mt-2';
        result.style.fontSize = '.8rem';
        result.textContent = data.message;
    } catch (e) {
        result.className = 'alert alert-danger py-2 px-3 mt-2';
        result.style.fontSize = '.8rem';
        result.textContent = 'Erreur réseau.';
    }
}
</script>
@endpush
