<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Technician;
use App\TechnicianCredit;

class TechnicianWalletRechargeEmailToTechnician extends Mailable
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
		$tid=$this->data['tid'];
 		$recharge_amount=$this->data['recharge_amount'];
		$total_balance=$this->data['total_balance'];
		$payment_method=$this->data['payment_method'];
		
		
		$technician=Technician::find($tid);
 		
		$app_name=config('app.name');
		$app_url=config('app.url');
		
        return $this->subject('Wallet Recharged with Rs. '.$recharge_amount." on ".$app_name)
					->markdown('emails.TechnicianWalletRechargeEmailToTechnician')
					->with([
						'technician'=>$technician,
						'recharge_amount'=>$recharge_amount,
						'total_balance'=>$total_balance,
						'payment_method'=>$payment_method
 					]);
    }
}
