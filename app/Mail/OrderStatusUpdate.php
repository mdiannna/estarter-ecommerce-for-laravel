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
    public $hasError;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OrderStatus $orderStatus, Order $order)
    {
        $this->hasError = false; 
        $this->orderStatus = $orderStatus;
        $this->mailContent = $this->parseOrderStatusTemplate($orderStatus, $order);

        if(!isset($this->mailContent)) {
            $this->hasError = true;    
        }
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

    /**
     * Filter parameter string - delete spaces and &nbsp;
     *
     * @return string
     */
    public function filterParametersString($substrParameter) {
        $parameter = preg_replace('/\s+/', '', $substrParameter);
        $parameter = str_replace('&nbsp;', '', $parameter);
        return $parameter;
    }

    /**
     * Parse Order Status Tenokate and get parameters data.
     *
     * @return string or null
     */
    public function parseOrderStatusTemplate($orderStatus, $order) {
        $orderMailContent = $orderStatus->notificationTemplate->content;
        
        $posStart = strpos($orderMailContent, "{{");
        $posEnd = strpos($orderMailContent, "}}");

        while($posStart !== false && $posEnd !== false ) {

            $substrParameter = substr($orderMailContent, $posStart+2, $posEnd-$posStart-2 );
            
            $parameter = $this->filterParametersString($substrParameter);
            
            // Look for , to see how many parameters are
            $posSeparator = strpos($parameter, ",", 0);

            // If there are two parameters, parse them both
            if($posSeparator !== false) {
                $parameter1 = substr($parameter, 0, $posSeparator );             
                $parameter2 = substr($parameter, $posSeparator+1, strlen($parameter) );             

                $parameter1Value =  ${"order"}->{$parameter1};
                if(isset($parameter1Value) && isset($parameter1) && isset($parameter2)) {
                    $parameter2Value =  $parameter1Value->{$parameter2};    
                    $finalParameterValue = $parameter2Value;         
                }
                else{
                    return null;
                }
                  
            }
            // If there is only one parameter, parse just one
            else {
                $finalParameterValue =  ${"order"}->{$parameter};
                if($parameter == "orderStatus") {
                  $finalParameterValue = $orderStatus->name;
                }
            }
            

            if(isset($finalParameterValue)) {
                $orderMailContent = str_replace("{{" . $substrParameter . "}}", $finalParameterValue, $orderMailContent);    
            }
            else {
                // Show error notification
                 \Alert::error(trans('common.parameter') . " " . $parameter . " " . trans('common.is_wrong'))->flash();  
                 return null;  

            }

            // Look for more {{ and }}
            $posStart = strpos($orderMailContent, "{{");
            $posEnd = strpos($orderMailContent, "}}");
        }

        return $orderMailContent;
    }
}
