<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderStatus;
use App\Models\Order;

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
    public function __construct(OrderStatus $orderStatus, Order $order)
    {
        $this->orderStatus = $orderStatus;
        $this->mailContent = $this->parseOrderStatusTemplate($orderStatus, $order);
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

    public function parseOrderStatusTemplate($orderStatus, $order) {
        $orderMailContent = $orderStatus->notificationTemplate->content;
        
        $posStart = strpos($orderMailContent, "{{");
        $posEnd = strpos($orderMailContent, "}}");

        while($posStart !== false && $posEnd !== false ) {

            $parameter = substr($orderMailContent, $posStart+2, $posEnd-$posStart-2 );            
            
            $posSeparator = strpos($orderMailContent, ",", $posStart);
            var_dump($posSeparator);

            var_dump($parameter);

            // If there are two parameters, parse them both
            if($posSeparator !== false && $posSeparator < $posEnd) {
                try {
                    $parameter1 = substr($orderMailContent, $posStart+2, $posSeparator-$posStart-2 );             
                    $parameter2 = substr($orderMailContent, $posSeparator+2, $posEnd-$posSeparator-2 );             
                    
                    $parameter1Value =  ${"order"}->{$parameter1};
                    if(isset($parameter1Value)) {
                        $parameter2Value =  $parameter1Value->{$parameter2};    
                        $finalParameterValue = $parameter2Value;         
                    }
                    else {
                        \Alert::error("Error at parameter" . $parameter)->flash();    
                    }

                    
                } catch(Exception $e) {
                    \Alert::error("Error")->flash();
                }
                
            }
            // If there is only one parameter
            else {
                $finalParameterValue =  ${"order"}->{$parameter};
                // dd($finalParameterValue);
            }


            $orderMailContent = str_replace("{{" . $parameter . "}}", $finalParameterValue, $orderMailContent);

            
            
            // $orderStatusName = ${"orderStatus"}->{$parameter};
            // $parameterValue =  ${"orderStatus"}->{$parameter};




            $posStart = strpos($orderMailContent, "{{");
            $posEnd = strpos($orderMailContent, "}}");
            $posSeparator = strpos($orderMailContent, ",", $posStart);
        }

        // dd(${"orderStatus"}->{$parameter});
        // dd($orderStatusName);
        // dd($orderStatus->{"name"});

        // dd(${"orderMailContent"});
        // dd($orderMailContent);
        // dd($orderMailContent);
        return $orderMailContent;


    }
}
