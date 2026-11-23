<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiVoiceSession;
use Illuminate\Http\Request;

class AdminAiController extends Controller
{
    /**
     * Display a listing of all AI conversations and voice usage analytics.
     */
    public function index(Request $request)
    {
        $channel = $request->input('channel');
        $rating = $request->input('rating');
        $search = $request->input('search');

        $query = AiConversation::with(['workspace', 'user', 'messages'])
            ->latest();

        if ($channel) {
            $query->where('channel', $channel);
        }

        if ($rating !== null && $rating !== '') {
            $query->where('rating', (int) $rating);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('session_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $conversations = $query->paginate(20)->withQueryString();

        // High-level analytics
        $totalConversations = AiConversation::count();
        $totalVoiceSeconds = AiVoiceSession::sum('duration_seconds');
        $totalVoiceMinutes = round($totalVoiceSeconds / 60, 1);
        $totalMessages = AiMessage::count();
        $helpfulCount = AiConversation::where('rating', 1)->count();
        $unhelpfulCount = AiConversation::where('rating', -1)->count();
        $ratedCount = $helpfulCount + $unhelpfulCount;
        $satisfactionRate = $ratedCount > 0 ? round(($helpfulCount / $ratedCount) * 100, 1) : 100.0;

        return view('admin.support.ai-conversations', compact(
            'conversations',
            'totalConversations',
            'totalVoiceMinutes',
            'totalMessages',
            'satisfactionRate',
            'helpfulCount',
            'unhelpfulCount'
        ));
    }

    /**
     * Display detailed conversation transcript & voice session logs.
     */
    public function show($id)
    {
        $conversation = AiConversation::with(['workspace', 'user', 'messages', 'voiceSessions', 'escalatedTicket'])
            ->findOrFail($id);

        return view('admin.support.ai-conversation-show', compact('conversation'));
    }
}
