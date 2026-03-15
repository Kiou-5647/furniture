<?php

namespace App\Mail;

use App\Models\Auth\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome - Set Your Password',
        );
    }

    public function content(): Content
    {
        $resetUrl = route('password.request', ['email' => $this->user->email]);

        return new Content(
            view: 'emails.employee_welcome',
            with: ['resetUrl' => $resetUrl],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
