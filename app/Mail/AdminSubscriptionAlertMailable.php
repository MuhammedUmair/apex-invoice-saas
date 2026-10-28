<?php

namespace App\Mail;

use App\Models\SubscriptionAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminSubscriptionAlertMailable extends Mailable
{
    use Queueable, SerializesModels;

    public SubscriptionAlert $alert;

    public function __construct(SubscriptionAlert $alert)
    {
        $this->alert = $alert;
    }

    public function envelope(): Envelope
    {
        $prefix = match ($this->alert->type) {
            'signup' => '🚀 [New Signup]',
            'upgrade' => '⭐ [Plan Upgrade]',
            'upgrade_request' => '💳 [Upgrade Request]',
            'downgrade' => '⚠️ [Plan Downgrade]',
            'trial_expired' => '⏳ [Trial Expired]',
            'renewal' => '🔄 [Subscription Renewed]',
            default => '🔔 [Subscription Alert]',
        };

        return new Envelope(
            subject: "{$prefix} {$this->alert->title} — Invoicy Super Admin Alert",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-subscription-alert',
            with: [
                'alert' => $this->alert,
                'metadata' => $this->alert->metadata ?? [],
                'workspace' => $this->alert->workspace,
                'user' => $this->alert->user,
                'adminUrl' => route('admin.subscriptions.index'),
            ],
        );
    }
}
