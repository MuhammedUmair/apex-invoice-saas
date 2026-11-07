<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuperAdminOtpMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public User $user;

    public function __construct(string $otp, User $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoicy Control Panel - Your Verification Code: {$this->otp}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.superadmin-otp',
            with: [
                'otp' => $this->otp,
                'user' => $this->user,
            ],
        );
    }
}
