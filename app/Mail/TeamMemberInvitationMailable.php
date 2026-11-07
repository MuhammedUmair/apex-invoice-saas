<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamMemberInvitationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Workspace $workspace;
    public User $inviter;
    public string $verificationUrl;
    public ?string $initialPassword;

    public function __construct(
        User $user,
        Workspace $workspace,
        User $inviter,
        string $verificationUrl,
        ?string $initialPassword = null
    ) {
        $this->user = $user;
        $this->workspace = $workspace;
        $this->inviter = $inviter;
        $this->verificationUrl = $verificationUrl;
        $this->initialPassword = $initialPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation to Join {$this->workspace->name} - Verify Your Email",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-member-invitation',
            with: [
                'user' => $this->user,
                'workspace' => $this->workspace,
                'inviter' => $this->inviter,
                'verificationUrl' => $this->verificationUrl,
                'initialPassword' => $this->initialPassword,
            ],
        );
    }
}
