<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;

    public function __construct($subject, $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.campaign')
                    ->with(['body' => $this->body]);
    }
}
