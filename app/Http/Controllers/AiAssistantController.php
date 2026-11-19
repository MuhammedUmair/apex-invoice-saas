<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\AiAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    protected AiAgentService $aiService;

    public function __construct(AiAgentService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Process AI Agent text and voice inquiries with native multi-language intelligence (Master Prompt Phases 1-12).
     */
    public function chat(Request $request)
    {
        $requestedLang = $request->input('lang');
        $query = (string) $request->input('message', '');

        $user = Auth::guard('web')->user() ?? Auth::guard('superadmin')->user();

        // Enforce Super Admin Tenant Visibility control
        $tenantAiConfig = \App\Models\CmsContent::getVal('feature_tenant_ai_agent', null);
        if ($tenantAiConfig === null) {
            $tenantAiConfig = \App\Models\CmsContent::getVal('ai_agent_enabled', '1');
        }
        $isAiActive = ($tenantAiConfig === '1' || $tenantAiConfig === true || $tenantAiConfig === 1);
        if (!$isAiActive && $user && !$user->isSuperAdmin()) {
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

        $response = $this->aiService->handleUserMessage($query, $requestedLang, $workspace, $user);

        return response()->json($response);
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
