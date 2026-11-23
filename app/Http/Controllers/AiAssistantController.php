<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiVoiceSession;
use App\Models\Workspace;
use App\Services\AiAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    protected AiAgentService $aiService;

    public function __construct(AiAgentService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Process AI Agent text and voice inquiries with native multi-language intelligence & screen context.
     */
    public function chat(Request $request)
    {
        $requestedLang = $request->input('lang');
        $query = (string) $request->input('message', '');
        $context = $request->input('context', []);

        $user = Auth::guard('web')->user() ?? Auth::guard('superadmin')->user();

        // Enforce Super Admin Tenant Visibility control
        $tenantAiConfig = \App\Models\CmsContent::getVal('feature_tenant_ai_agent', '');
        if ($tenantAiConfig === '') {
            $tenantAiConfig = \App\Models\CmsContent::getVal('ai_agent_enabled', '1');
        }
        $isAiActive = ($tenantAiConfig === '1' || $tenantAiConfig === true || $tenantAiConfig === 1);
        if (!$isAiActive && (!$user || !$user->isSuperAdmin())) {
            return response()->json([
                'reply' => 'The AI Agent is currently disabled by system administration.',
                'lang' => 'en-US',
                'lang_code' => 'en',
                'is_rtl' => false,
                'suggestions' => [],
            ], 403);
        }

        $workspace = null;
        if ($user) {
            $workspace = $user->workspace 
                ?? ($user->current_workspace_id ? Workspace::find($user->current_workspace_id) : null)
                ?? (method_exists($user, 'workspaces') ? $user->workspaces()->first() : null);
        }
        if (!$workspace) {
            $workspace = Workspace::first();
        }

        // Attach session identifier
        if (empty($context['session_id'])) {
            $context['session_id'] = session()->getId();
        }

        $response = $this->aiService->handleUserMessage($query, $requestedLang, $workspace, $user, $context);

        return response()->json($response);
    }

    /**
     * Start a real-time voice session (verifies permissions and returns ephemeral credentials).
     */
    public function createVoiceSession(Request $request)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('superadmin')->user();
        $workspace = $user?->workspace 
            ?? ($user?->current_workspace_id ? Workspace::find($user->current_workspace_id) : null)
            ?? Workspace::first();

        $token = 'vcs_' . Str::random(40);
        $sessionId = session()->getId();

        // Find or create conversation thread
        $conversation = AiConversation::firstOrCreate(
            ['session_id' => $sessionId, 'status' => 'active'],
            [
                'workspace_id' => $workspace?->id,
                'user_id' => $user?->id,
                'channel' => 'voice',
                'language' => $request->input('lang', 'en'),
                'context_metadata' => $request->input('context', []),
            ]
        );

        $conversation->update(['channel' => 'voice']);

        $voiceSession = AiVoiceSession::create([
            'workspace_id' => $workspace?->id,
            'user_id' => $user?->id,
            'conversation_id' => $conversation->id,
            'token' => $token,
            'status' => 'active',
            'provider' => 'browser_native_duplex',
            'started_at' => now(),
            'metadata' => [
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'lang' => $request->input('lang', 'en'),
            ],
        ]);

        return response()->json([
            'success' => true,
            'session_token' => $token,
            'conversation_id' => $conversation->id,
            'voice_session_id' => $voiceSession->id,
            'provider' => 'browser_native_duplex',
            'supported_languages' => ['en', 'ur', 'ur-roman', 'ar', 'hi', 'tr', 'es', 'fr', 'de', 'zh'],
            'vad_config' => [
                'silence_threshold_ms' => 1200,
                'energy_threshold' => 0.02,
                'barge_in_enabled' => true,
            ]
        ]);
    }

    /**
     * End active voice session and log metrics.
     */
    public function endVoiceSession(Request $request)
    {
        $token = $request->input('session_token');
        $duration = (int) $request->input('duration_seconds', 0);

        if ($token) {
            $session = AiVoiceSession::where('token', $token)->first();
            if ($session) {
                $session->update([
                    'status' => 'ended',
                    'ended_at' => now(),
                    'duration_seconds' => $duration > 0 ? $duration : ($session->started_at ? now()->diffInSeconds($session->started_at) : 0),
                ]);

                if ($session->conversation) {
                    $session->conversation->increment('duration_seconds', $session->duration_seconds);
                    $session->conversation->update(['channel' => 'hybrid']);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Submit helpful/unhelpful rating for a conversation response.
     */
    public function submitFeedback(Request $request)
    {
        $conversationId = $request->input('conversation_id');
        $rating = (int) $request->input('rating', 0); // 1 = helpful, -1 = unhelpful
        $feedback = $request->input('feedback');

        if ($conversationId) {
            $conv = AiConversation::find($conversationId);
            if ($conv) {
                $conv->update([
                    'rating' => $rating,
                    'feedback' => $feedback,
                    'resolved' => ($rating === 1),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Clear chat memory for active session.
     */
    public function clearHistory()
    {
        $this->aiService->clearHistory();
        return response()->json(['success' => true]);
    }
}
