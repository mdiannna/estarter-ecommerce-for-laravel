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
    public $errorMessage = "";

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
     * Get error message after parsing notification template
     *
     * @return string
     */
    public function getTemplateParsingErrorMessage($wrongParameters) {
        $errorMessage = "";

        if(count($wrongParameters) > 0) {
             $i = 0;

            if(count($wrongParameters) == 1){
                // $errorMessage = "Message was not sent: "  . "parameter ";
                $errorMessage = trans("mail.mail_not_sent") . ": " . trans("common.parameter") . " ";
            } else{
                $errorMessage = trans("mail.mail_not_sent") . ": " . trans("common.parameters") . " ";
            }

            foreach ($wrongParameters as $parameter) {
                $errorMessage = $errorMessage . " {{" . $parameter . "}} " ;
                if($i < count($wrongParameters)-1) {
                   $errorMessage = $errorMessage  . ",";
                }
                $i++;
            }

            if(count($wrongParameters) == 1){
                $errorMessage = $errorMessage . trans("common.is_incorrect");
            }
            else {
                $errorMessage = $errorMessage . trans("common.are_incorrect");
            }

        }
        return $errorMessage;
    }


    /**
     * Get values from parameters 
     *
     * @return string or null
     */ 
    public function getValuesFromParameters($orderStatus, $order, $parameterString) {
        // Look for , to see how many parameters are
        $posSeparator = strpos($parameterString, ",", 0);

        // If there are two parameters, parse them both
        if($posSeparator !== false) {
            $parameter1 = substr($parameterString, 0, $posSeparator );             
            $parameter2 = substr($parameterString, $posSeparator+1, strlen($parameterString) );             

            $parameter1Value =  ${"order"}->{$parameter1};
            if(isset($parameter1Value) && isset($parameter1) && isset($parameter2)) {
                $parameter2Value =  $parameter1Value->{$parameter2};    
                $finalParameterValue = $parameter2Value;         
            }
            else{
                return null;
            }
        }
        // If there is only one parameterString, parse just one
        else {
            $finalParameterValue =  ${"order"}->{$parameterString};
            if($parameterString == "orderStatus") {
              $finalParameterValue = $orderStatus->name;
            }
        }
        return $finalParameterValue;
     }
   

    /**
     * Parse Order Status Tenokate and get parameters data.
     *
     * @return string or null
     */
    public function parseOrderStatusTemplate($orderStatus, $order) {
        $orderMailContent = $orderStatus->notificationTemplate->content;
        $wrongParameters = array();
        
        $posStart = strpos($orderMailContent, "{{");
        $posEnd = strpos($orderMailContent, "}}");

        while($posStart !== false && $posEnd !== false ) {

            $substrParameter = substr($orderMailContent, $posStart+2, $posEnd-$posStart-2 );
            
            $parameter = $this->filterParametersString($substrParameter);            
            $finalParameterValue = $this->getValuesFromParameters($orderStatus, $order, $parameter);

            if(isset($finalParameterValue)) {
                $orderMailContent = str_replace("{{" . $substrParameter . "}}", $finalParameterValue, $orderMailContent);    
            }
            else {
                $orderMailContent = str_replace("{{" . $substrParameter . "}}", "", $orderMailContent);
                array_push($wrongParameters, $parameter);
            }

            // Look for more {{ and }}
            $posStart = strpos($orderMailContent, "{{");
            $posEnd = strpos($orderMailContent, "}}");
        }

        // Build template parsing error message from $wrongParameters array
        $this->errorMessage = $this->getTemplateParsingErrorMessage($wrongParameters);
                 
        if($this->errorMessage != "") {
            return null;
        }
        return $orderMailContent;
    }


}
