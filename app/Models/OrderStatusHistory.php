<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
use App\Mail\OrderStatusUpdate;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\User;

class OrderStatusHistory extends Model
{
    use CrudTrait;

    /*
	|--------------------------------------------------------------------------
	| GLOBAL VARIABLES
	|--------------------------------------------------------------------------
	*/

    protected $table = 'order_status_history';
    //protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = [
    	'order_id',
    	'status_id'
	];
    // protected $hidden = [];
    // protected $dates = [];

	/*
	|--------------------------------------------------------------------------
	| EVENTS
	|--------------------------------------------------------------------------
	*/
	public function sendStatusUpdateMail(Mail $mail, OrderStatus $orderStatus, Order $order, User $user) 
    {
        // $userEmail = 'estartertest@test.com';
        $userEmail = $user->email;
        try {
        	$orderStatusUpdate = new OrderStatusUpdate($orderStatus, $order);
        	if($orderStatusUpdate->hasError) {
	        	throw new Exception("Mail not sent");
        	}
        	$mail::to($userEmail)->send($orderStatusUpdate);    
            \Alert::success(trans('mail.mail_was_sent'))->flash();    
		

        } catch (Exception $e){
        	\Alert::error(trans("mail.mail_not_sent") . ". " . trans("notificationtemplate.error_in_template") )->flash();  
        	return 0;
        }
        return 1;
    }


    /*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/

    /*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function status()
	{
		return $this->hasOne('App\Models\OrderStatus', 'id', 'status_id');
	}

    /*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/

    /*
	|--------------------------------------------------------------------------
	| ACCESORS
	|--------------------------------------------------------------------------
	*/

    /*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('d-m-Y H:i:s');
    }
}
