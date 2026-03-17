<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PendingReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    private $suggestedCategory;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($suggestedCategory)
    {
        $this->suggestedCategory = $suggestedCategory;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Thank you')
            ->view('email-template.pending-review')
            ->with(['suggestedCategory' => $this->suggestedCategory]);
    }
}
