<?php

namespace App\Http\Controllers;

use App\Models\ConversationIA;
use App\Models\MessageIA;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AgentIAController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $conversations = ConversationIA::where('proprietaire_id', $user->id)
            ->with(['locataire', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->latest()
            ->paginate(20);

        $locataires = User::where('role', 'locataire')
            ->when($user->role === 'proprietaire', function ($q) use ($user) {
                $q->whereHas('locations.bien', fn($b) => $b->where('proprietaire_id', $user->id));
            })
            ->orderBy('name')
            ->get();

        $conversationActive = null;
        $messages = collect();

        if ($request->filled('conv')) {
            $conversationActive = ConversationIA::where('proprietaire_id', $user->id)
                ->with('locataire')
                ->findOrFail($request->conv);
            $messages = $conversationActive->messages()->orderBy('created_at')->get();
        }

        $iaConfigured = (bool) Parametre::get('ia_api_key');

        return view('agent-ia.index', compact(
            'conversations', 'locataires', 'conversationActive', 'messages', 'iaConfigured'
        ));
    }

    public function nouvelles(Request $request)
    {
        $request->validate(['locataire_id' => 'nullable|exists:users,id']);

        $locataire = $request->locataire_id ? User::find($request->locataire_id) : null;
        $titre = $locataire ? 'Conv. avec ' . $locataire->name : 'Nouvelle conversation';

        $conversation = ConversationIA::create([
            'proprietaire_id' => Auth::id(),
            'locataire_id'    => $request->locataire_id,
            'titre'           => $titre,
        ]);

        // Message système d'introduction
        MessageIA::create([
            'conversation_id' => $conversation->id,
            'role'            => 'system',
            'contenu'         => $this->buildSystemPrompt($locataire),
        ]);

        return redirect()->route('agent-ia.index', ['conv' => $conversation->id]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations_ia,id',
            'message'         => 'required|string|max:2000',
        ]);

        $conversation = ConversationIA::where('proprietaire_id', Auth::id())
            ->with('locataire')
            ->findOrFail($request->conversation_id);

        // Sauvegarder le message utilisateur
        MessageIA::create([
            'conversation_id' => $conversation->id,
            'role'            => 'user',
            'contenu'         => $request->message,
        ]);

        // Construire l'historique pour l'API IA
        $historyMessages = $conversation->messages()->orderBy('created_at')->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->contenu])
            ->toArray();

        $reponse = $this->callIA($historyMessages);

        // Sauvegarder la réponse de l'assistant
        $msgIA = MessageIA::create([
            'conversation_id' => $conversation->id,
            'role'            => 'assistant',
            'contenu'         => $reponse,
        ]);

        return response()->json([
            'ok'       => true,
            'reponse'  => $reponse,
            'msg_id'   => $msgIA->id,
            'created_at' => $msgIA->created_at->format('H:i'),
        ]);
    }

    public function envoi(Request $request)
    {
        $request->validate([
            'message'     => 'required|string',
            'canal'       => 'required|in:email,sms,whatsapp',
            'locataire_id'=> 'required|exists:users,id',
        ]);

        $locataire = User::findOrFail($request->locataire_id);

        // Déléguer au NotificationController
        $notifController = app(NotificationController::class);
        $fakeRequest = Request::create('/notifications/send', 'POST', [
            'canal'        => $request->canal,
            'locataire_ids'=> [$request->locataire_id],
            'sujet'        => $request->sujet ?? 'Message de votre propriétaire',
            'message'      => $request->message,
            'type'         => 'autre',
        ]);
        $fakeRequest->setUserResolver(fn() => Auth::user());

        try {
            $notifController->send($fakeRequest);
            return response()->json(['ok' => true, 'message' => 'Message envoyé via ' . strtoupper($request->canal) . ' à ' . $locataire->name]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Appel API IA ─────────────────────────────────────────────────────────
    private function callIA(array $messages): string
    {
        $provider = Parametre::get('ia_provider', 'anthropic');
        $apiKey   = Parametre::get('ia_api_key');
        $model    = Parametre::get('ia_model', $provider === 'openai' ? 'gpt-4o-mini' : 'claude-haiku-4-5-20251001');

        if (!$apiKey) {
            return "⚠️ Clé API IA non configurée. Rendez-vous dans Administration > Config. APIs pour ajouter votre clé.";
        }

        try {
            if ($provider === 'openai') {
                return $this->callOpenAI($apiKey, $model, $messages);
            } else {
                return $this->callAnthropic($apiKey, $model, $messages);
            }
        } catch (\Exception $e) {
            return "❌ Erreur API IA : " . $e->getMessage();
        }
    }

    private function callOpenAI(string $apiKey, string $model, array $messages): string
    {
        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => 1024,
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->json('error.message', 'Erreur OpenAI inconnue'));
        }

        return $response->json('choices.0.message.content', '');
    }

    private function callAnthropic(string $apiKey, string $model, array $messages): string
    {
        // Séparer le message système des autres messages
        $systemMsg = '';
        $chatMessages = [];
        foreach ($messages as $m) {
            if ($m['role'] === 'system') {
                $systemMsg = $m['content'];
            } else {
                $chatMessages[] = $m;
            }
        }

        $payload = [
            'model'      => $model,
            'max_tokens' => 1024,
            'messages'   => $chatMessages ?: [['role' => 'user', 'content' => 'Bonjour']],
        ];
        if ($systemMsg) {
            $payload['system'] = $systemMsg;
        }

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', $payload);

        if (!$response->successful()) {
            throw new \Exception($response->json('error.message', 'Erreur Anthropic inconnue'));
        }

        return $response->json('content.0.text', '');
    }

    // ─── Prompt système contextuel ────────────────────────────────────────────
    private function buildSystemPrompt(?User $locataire): string
    {
        $proprietaire = Auth::user();
        $today = now()->translatedFormat('d F Y');

        $ctx = "Tu es un assistant de gestion locative pour {$proprietaire->name}, propriétaire immobilier. ";
        $ctx .= "Aujourd'hui nous sommes le {$today}. ";
        $ctx .= "Tu aides à rédiger des messages professionnels et courtois pour les locataires, ";
        $ctx .= "gérer les relances de paiement, planifier des interventions, et répondre aux questions. ";
        $ctx .= "Réponds toujours en français, de façon concise et professionnelle. ";

        if ($locataire) {
            $location = $locataire->locations()->where('statut', 'actif')->with('bien')->first();
            $ctx .= "\n\nLocataire actuel : {$locataire->name} (email: {$locataire->email}";
            if ($locataire->phone) $ctx .= ", tél: {$locataire->phone}";
            $ctx .= "). ";

            if ($location) {
                $ctx .= "Il occupe le bien : {$location->bien->titre} depuis le " . $location->date_debut->format('d/m/Y') . ". ";
                $ctx .= "Loyer mensuel : " . number_format($location->loyer_mensuel, 2, ',', ' ') . " €. ";

                $retards = $location->paiements()
                    ->where('statut', 'en_attente')
                    ->where('date_echeance', '<', now())
                    ->count();
                if ($retards > 0) {
                    $ctx .= "⚠️ Ce locataire a {$retards} paiement(s) en retard. ";
                }
            }
        }

        return $ctx;
    }
}
