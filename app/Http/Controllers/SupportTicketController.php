<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of consumer support tickets for the authenticated workspace.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $workspace = $user->workspace;

        $query = SupportTicket::query();
        if ($workspace) {
            $query->where('workspace_id', $workspace->id);
        } else {
            $query->where('user_id', $user->id);
        }

        // Status filter
        $status = $request->query('status', 'all');
        if ($status !== 'all' && in_array($status, ['open', 'in_progress', 'waiting_on_customer', 'resolved', 'closed'])) {
            $query->where('status', $status);
        }

        $tickets = $query->with(['user', 'messages'])->latest()->paginate(15);

        // Stats
        $baseQuery = SupportTicket::query();
        if ($workspace) {
            $baseQuery->where('workspace_id', $workspace->id);
        } else {
            $baseQuery->where('user_id', $user->id);
        }

        $openCount = (clone $baseQuery)->whereIn('status', ['open', 'in_progress', 'waiting_on_customer'])->count();
        $resolvedCount = (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->count();
        $totalCount = (clone $baseQuery)->count();

        return view('support.index', compact('tickets', 'status', 'openCount', 'resolvedCount', 'totalCount'));
    }

    /**
     * Show form for submitting a new consumer support ticket.
     */
    public function create()
    {
        return view('support.create');
    }

    /**
     * Store newly created support ticket with guaranteed 24-hour turnaround SLA.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:billing,technical,feature_request,general',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,zip,doc,docx',
        ]);

        $user = Auth::user();
        $workspace = $user->workspace;

        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'workspace_id' => $workspace?->id,
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'sla_due_at' => now()->addHours(24), // 24-Hour Turnaround SLA Guarantee
        ]);

        $attachments = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('support_attachments', 'public');
            $attachments = [
                'name' => $request->file('attachment')->getClientOriginalName(),
                'path' => Storage::url($path),
                'size' => $request->file('attachment')->getSize(),
            ];
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin_reply' => false,
            'message' => $validated['message'],
            'attachments_json' => $attachments ? [$attachments] : null,
        ]);

        return redirect()->route('support.show', $ticket->id)
            ->with('success', "Support Ticket #{$ticket->ticket_number} successfully submitted! Our 24-Hour Turnaround SLA is active; an engineer will review and respond shortly.");
    }

    /**
     * Display a specific support ticket thread.
     */
    public function show(SupportTicket $ticket)
    {
        $user = Auth::user();
        if ($ticket->workspace_id !== $user->workspace_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to support ticket.');
        }

        $ticket->load(['user', 'assignee', 'messages.user']);

        return view('support.show', compact('ticket'));
    }

    /**
     * Submit a customer reply to the support ticket.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $user = Auth::user();
        if ($ticket->workspace_id !== $user->workspace_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:2',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,zip,doc,docx',
        ]);

        $attachments = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('support_attachments', 'public');
            $attachments = [
                'name' => $request->file('attachment')->getClientOriginalName(),
                'path' => Storage::url($path),
                'size' => $request->file('attachment')->getSize(),
            ];
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin_reply' => false,
            'message' => $validated['message'],
            'attachments_json' => $attachments ? [$attachments] : null,
        ]);

        // If ticket was resolved or waiting on customer, reopen to open status
        if (in_array($ticket->status, ['resolved', 'waiting_on_customer'])) {
            $ticket->update(['status' => 'open']);
        } else {
            $ticket->touch();
        }

        return redirect()->route('support.show', $ticket->id)
            ->with('success', 'Your reply has been posted to the support ticket.');
    }

    /**
     * Close the ticket from the consumer portal.
     */
    public function close(SupportTicket $ticket)
    {
        $user = Auth::user();
        if ($ticket->workspace_id !== $user->workspace_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $ticket->update(['status' => 'closed']);

        return redirect()->route('support.show', $ticket->id)
            ->with('success', 'Support ticket marked as closed.');
    }
}
