<?php

namespace App\Mail\Admin\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailUpdate extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail.subject.update_user'))
            ->view('admin.mail.user.update', [
                'user' => $this->data['user'],
                'data' => $this->data['update_data'],
                'new_password' => $this->data['new_password'],
            ]);
    }
}
