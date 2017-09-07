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
            
            // $parameter = preg_replace('/\s+/', '', $parameter);
            // $parameter = str_replace('&nbsp;', '', $parameter);



            

            

            // Look for , between {{ and }} to see how many parameters are
            $posSeparator = strpos($orderMailContent, ",", $posStart);


            // If there are two parameters, parse them both
            if($posSeparator !== false && $posSeparator < $posEnd) {
                try {
                    $parameter1 = substr($orderMailContent, $posStart+2, $posSeparator-$posStart-2 );             
                    $parameter2 = substr($orderMailContent, $posSeparator+2, $posEnd-$posSeparator-2 );             
                        

                    $parameter1Value =  ${"order"}->{$parameter1};
                    if(isset($parameter1Value) && isset($parameter1) && isset($parameter2)) {
                        $parameter2Value =  $parameter1Value->{$parameter2};    
                        $finalParameterValue = $parameter2Value;         
                    }
                    else {
                        \Alert::error(trans('common.parameter') . " " . $parameter . " " . trans('common.is_wrong'))->flash();    
                    }
                } catch(Exception $e) {
                    \Alert::error(trans('common.parameter') . " " . $parameter . " " . trans('common.is_wrong'))->flash();    
                }
            }
            // If there is only one parameter, parse just one
            else {
                $finalParameterValue =  ${"order"}->{$parameter};
                var_dump($finalParameterValue);
                if(!isset($finalParameterValue)) {
                    \Alert::error(trans('common.parameter') . " " . $parameter . " " . trans('common.is_wrong'))->flash();     
                }
            }

            if(isset($finalParameterValue)) {
                $orderMailContent = str_replace("{{" . $parameter . "}}", $finalParameterValue, $orderMailContent);    
            }
            else {
                // Show error notification
                $orderMailContent = str_replace("{{" . $parameter . "}}", "{" . $parameter . "}", $orderMailContent);       
                 \Alert::error(trans('common.parameter') . " " . $parameter . " " . trans('common.is_wrong'))->flash();    
            }

            // Look for more {{ and }}
            $posStart = strpos($orderMailContent, "{{");
            $posEnd = strpos($orderMailContent, "}}");
        }

        // dd($orderMailContenttent);
        return $orderMailContent;
    }
}
