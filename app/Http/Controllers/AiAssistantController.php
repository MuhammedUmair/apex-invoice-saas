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
        $workspace = null;
        if ($user) {
            $workspace = $user->workspace ?? ($user->current_workspace_id ? Workspace::find($user->current_workspace_id) : null);
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
