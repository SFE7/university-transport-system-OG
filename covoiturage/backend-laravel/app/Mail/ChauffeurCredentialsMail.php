<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChauffeurCredentialsMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $emailAddress;
    public string $plainPassword;
    public string $name;

    public function __construct(string $emailAddress, string $plainPassword, string $name)
    {
        $this->emailAddress = $emailAddress;
        $this->plainPassword = $plainPassword;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('Vos identifiants — Covoiturage Universitaire')
            ->view('emails.chauffeur_credentials')
            ->with([
                'email' => $this->emailAddress,
                'password' => $this->plainPassword,
                'name' => $this->name,
            ]);
    }
}
