<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;
use App\Order;
class NewOrderPlaceEmailToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
       $this->data=$data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		$oid=$this->data['oid'];
		$id=$this->data['id'];
		
		$order=Order::find($oid);
		$user=User::find($id);
		
		$app_name=config('app.name');
		$app_url=config('app.url');
		
        return $this->subject('Your '.$app_name.' order #['.$oid.'] has been successfully placed!')
					->markdown('emails.new_order_place_email_to_customer')
					->with([
						'user'=>$user,
						'order'=>$order,
					]);
    }
}
