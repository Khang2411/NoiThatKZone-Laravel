<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Models\User;

class SendOrderMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $order;
    protected $localOrderMail;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $order, $localOrderMail)
    {
        $this->userId = $userId;
        $this->order = $order;
        $this->localOrderMail = $localOrderMail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = $this->localOrderMail ? null : User::find($this->userId)->email;
        Mail::to($email ? $email : $this->localOrderMail)->send(new OrderConfirmed($this->order));
    }
}
