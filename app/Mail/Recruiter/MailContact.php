<?php

namespace App\Mail\Recruiter;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailContact extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $recruiter;
    protected $store;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $recruiter, $store)
    {
        $this->data = $data;
        $this->recruiter = $recruiter;
        $this->store = $store;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail.subject.contact'))->view('recruiter.mail.contact', [
            'data' => $this->data,
            'recruiter' => $this->recruiter,
            'store' => $this->store,
        ]);
    }
}
