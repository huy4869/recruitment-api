<?php

namespace App\Jobs\Recruiter;

use App\Mail\Recruiter\MailContact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class JobContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $recruiter;
    protected $store;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $recruiter)
    {
        $this->data = $data;
        $this->recruiter = $recruiter;
        $this->store = $recruiter->stores->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $recruiter = $this->recruiter;

        Mail::to($data['email'] ?? $recruiter->email)->send(new MailContact(
            $data,
            $recruiter,
            $this->store
        ));
    }
}
