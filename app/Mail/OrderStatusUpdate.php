<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderStatus;

class OrderStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The OrderStatus instance.
     *
     * @var OrderStatus
     */
    public $orderStatus;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    { 
        return $this->view('emails.notifications.order_status_update');
    }
}
