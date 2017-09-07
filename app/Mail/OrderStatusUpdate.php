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
    public $mailContent;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
        $this->mailContent = $this->parseOrderStatusTemplate($orderStatus);
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

    public function parseOrderStatusTemplate($orderStatus) {
        $orderMailContent = $orderStatus->notificationTemplate->content;
        

        $posStart = strpos($orderMailContent, "{{");
        $posEnd = strpos($orderMailContent, "}}");

        while($posStart !== false && $posEnd !== false ) {

            $parameter = substr($orderMailContent, $posStart+2, $posEnd-$posStart-2 );

            
            $orderStatusName = ${"orderStatus"}->{$parameter};
            $parameterValue =  ${"orderStatus"}->{$parameter};

            $orderMailContent = str_replace("{{" . $parameter . "}}", $parameterValue, $orderMailContent);


            $posStart = strpos($orderMailContent, "{{");
            $posEnd = strpos($orderMailContent, "}}");
        }

        // dd(${"orderStatus"}->{$parameter});
        // dd($orderStatusName);
        // dd($orderStatus->{"name"});

        // dd(${"orderMailContent"});
        // dd($orderMailContent);
        return $orderMailContent;


    }
}
