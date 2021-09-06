<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Technician;
use App\Category;
class NewSellerRegisterWelcomeEmailToSeller extends Mailable
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
		$categories_name=$this->data['categories_name'];
		
		$technician=Technician::find($tid);
 		
		$app_name=config('app.name');
		$app_url=config('app.url');
		
        return $this->subject('Welcome to  '.$app_name.' !')
					->markdown('emails.new_seller_register_welcome_email_to_seller')
					->with([
						'technician'=>$technician,
						'categories_name'=>$categories_name,
					]);
    }
}
