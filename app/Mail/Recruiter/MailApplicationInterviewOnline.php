<?php

namespace App\Mail\Recruiter;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailApplicationInterviewOnline extends Mailable
{
    use Queueable, SerializesModels;

    protected $application;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($application)
    {
        $this->application = $application;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('recruiter.mail.interview_online', ['data' => $this->application])
            ->subject($this->application->jobPosting->name . ' ' .trans('common.subject_mail_interview_online'));
    }
}
