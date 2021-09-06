<?php


	
	function sms_send_on_new_order_place_to_customer($mobile,$oid){
		
		$order=Order::find($oid);
		
		$visiting_charge=350;
		
		$order_items=OrderItem::where('oid',$order->oid)->orderby('created_at','DESC')->get();
		
		$product_data_arr=array();
		foreach($order_items as $order_item){
			$product_data_arr[]=$order_item->title;
		}
		 
		
		$product_data=implode(" , ",$product_data_arr);
		
		
		//English 
		$msg1="Dear Customer,
Thanks for your Complaint No ".$oid." has successfully placed   of ".$product_data.". Order pass code is #".$order->order_pass_code." to share with technician";
		
		$msg1.=" Payable Amount is Rs. ".$order->grand_total." Our Engineer Coming Soon at your Home within 24hours & Visiting Charges @".$visiting_charge."rs/ compulsory or GST Billing 18% charges Of Amount & Billing By 
whatsapp or Email For Sercurity Reason. ";
		
		$msg1.=" Note:-
1.Collect The Receive Bill By The Engineer.
2.Don't Help To Private service.
3.Payment Mode-Paytm/UPI 
Paytm No-8802343357(Only For PayTm),UPI ID-RepairServicesIndia@UPI(only for UPI)
Call-01128335539,9811303092.
If You need help. ";

 		$msg1.=config("app.name");
		
		$msg1.=" Download The App -http://lrsi/App";	
		
		//End English	




	//Hindi 
	 	$msg2="&#2346;&#2381;&#2352;&#2367;&#2351;&#2375; &#2327;&#2381;&#2352;&#2361;&#2366;&#2325;, 
&#2310;&#2346;&#2360;&#2375; &#2309;&#2344;&#2369;&#2352;&#2379;&#2343; &#2361;&#2376;,&#2325;&#2368; 
&#2361;&#2350;&#2366;&#2352;&#2368; &#2325;&#2306;&#2346;&#2344;&#2368; &#2342;&#2381;&#2357;&#2366;&#2352;&#2366; &#2342;&#2367;&#2319; &#2327;&#2319; &#2325;&#2366;&#2350; &#2325;&#2366; &#2350;&#2370;&#2354;&#2381;&#2351;  &#8377; ".$order->grand_total." &#2325;&#2371;&#2346;&#2351;&#2366; &#2361;&#2350;&#2366;&#2352;&#2375; &#2325;&#2306;&#2346;&#2344;&#2368; &#2342;&#2381;&#2357;&#2366;&#2352;&#2366; &#2342;&#2367;&#2319; &#2327;&#2319; :-  
1.Paytm no/Whatsapp No- 8802343357 
2.UPI ID- RepairServicesIndia@UPI 
&#2346;&#2352; &#2361;&#2368; Payment &#2325;&#2352;&#2375; | &#2311;&#2360;&#2360;&#2375; &#2310;&#2346;&#2325;&#2366; &#2324;&#2352; &#2361;&#2350;&#2366;&#2352;&#2368; &#2325;&#2306;&#2346;&#2344;&#2368; &#2325;&#2366; &#2346;&#2375;&#2350;&#2375;&#2306;&#2335; / &#2348;&#2367;&#2354; &#2352;&#2367;&#2325;&#2377;&#2352;&#2381;&#2337; &#2348;&#2344;&#2366; &#2352;&#2361;&#2375;&#2327;&#2366; | &#2332;&#2367;&#2360;&#2360;&#2375; &#2310;&#2346;&#2325;&#2379; &#2310;&#2327;&#2375; &#2310;&#2344;&#2375; &#2357;&#2366;&#2354;&#2375; &#2360;&#2350;&#2351; &#2350;&#2375; &#2325;&#2379;&#2312; &#2346;&#2352;&#2375;&#2358;&#2366;&#2344;&#2368; &#2344;&#2361;&#2368;&#2306; &#2361;&#2379;&#2327;&#2368; |  
{&#2325;&#2371;&#2346;&#2351;&#2366; &#2325;&#2376;&#2358; &#2342;&#2375;&#2344;&#2375; &#2325;&#2368; &#2311;&#2330;&#2381;&#2331;&#2366; &#2346;&#2381;&#2352;&#2325;&#2335; &#2344; &#2325;&#2352;&#2375; &#2324;&#2352; &#2348;&#2367;&#2354; &#2325;&#2306;&#2346;&#2344;&#2368; &#2325;&#2379; Whatsapp &#2325;&#2352;&#2375;}
Thank You - ";
 $msg2.=config("app.name");

 
 
		
 		
		//End Hindi	 


		
		 $this->sms_send($mobile,$msg1,"EN");	
		 $this->sms_send($mobile,$msg2,"HN");	
		
		
	}
	
	