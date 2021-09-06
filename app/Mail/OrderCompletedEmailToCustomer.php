<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;
use App\Order;
use PDF;

class OrderCompletedEmailToCustomer extends Mailable
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
		
		
		$order=$this->data;
		
		
		//PDF invoice attachment
		 $pdf = PDF::loadView('includes.invoice_template',array('order'=>$order));
		 $invoice_date = date('jS F Y', strtotime($order->invoice_date)); 
		$invoice_pdf_name='Invoice_RSI_Order_No # '.$oid.' Date_'.$invoice_date.'.pdf';
		///
		
		
        return $this->subject('Your '.$app_name.' order #['.$oid.'] has been successfully completed!')
					->markdown('emails.order_completed_email_to_customer')
					->with([
						'user'=>$user,
						'order'=>$order,
					])
					->attachData($pdf->output(), $invoice_pdf_name, [
                    	'mime' => 'application/pdf',
                	]);
    }
}
