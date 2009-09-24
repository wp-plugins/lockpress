<?php
/* Buy now */
function lockpressBuyLink($price){
	global $post;
	$form='<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display:inline;margin:0;">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="'.get_option('lockpressPaypalEmail').'" />	
	<input type="hidden" name="item_name" value="Access for '.get_option('siteurl').'" />	
	<input type="hidden" name="item_number" value="'.$post->ID.'" /> 
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="rm" value="2" />

	<input type="hidden" name="amount" value="'.round($price,2).'" /> 
	<input type="hidden" name="currency_code" value="'.get_option('lockpressCurrency').'" />
	
	<input type="hidden" name="return" value="'.get_permalink($post->ID).'" />
	<input type="hidden" name="cancel_return" value="'.get_permalink($post->ID).'" />
	<input type="hidden" name="notify_url" value="'.get_permalink($post->ID).'" />
	
	<input type="image" name="submit" border="0" src="https://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif" 
		alt="PayPal - The safer, easier way to pay online" /> 
	</form>
	';
	return $form;
}

/* Subscribe now */
function lockpressSubscribeLink($a3,$p3,$t3){
	global $post;
	$form='<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display:inline;margin:0;">
	<input type="hidden" name="cmd" value="_xclick-subscriptions" />
	<input type="hidden" name="business" value="'.get_option('lockpressPaypalEmail').'" />	
	<input type="hidden" name="item_name" value="Access for '.get_option('siteurl').'" />	
	<input type="hidden" name="item_number" value="'.$post->ID.'" /> 
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="rm" value="2" />
	<input type="hidden" name="src" value="1">
	
	<input type="hidden" name="return" value="'.get_permalink($post->ID).'" />
	<input type="hidden" name="cancel_return" value="'.get_permalink($post->ID).'" />
	<input type="hidden" name="notify_url" value="'.get_permalink($post->ID).'" />
	
	<input type="hidden" name="currency_code" value="'.get_option('lockpressCurrency').'" />
	<input type="hidden" name="a3" value="'.round($a3,2).'">
	<input type="hidden" name="p3" value="'.intval($p3).'">
	<input type="hidden" name="t3" value="'.htmlspecialchars($t3).'">
	
	<input type="image" name="submit" border="0" src="https://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif" alt="PayPal - The safer, easier way to pay online">
	</form>
	';
	return $form;
}

/* payment check (IPN) */
function lockpressPayment(){
	global $lockpress;
	$lockpress->success=false;
	/*	let's ask for transaction verification	*/
	$postdata="";
	foreach($_POST as $key=>$value) $postdata.=$key."=".urlencode($value)."&";
	$postdata.='cmd=_notify-validate'; 
	$ch=curl_init('https://www.paypal.com/cgi-bin/webscr');
	curl_setopt($ch,CURLOPT_HEADER,0); 
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0); 
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,1);
	$response=curl_exec($ch);
	curl_close($ch);
	if($response!='VERIFIED'){
		$lockpress->setMsg('lockpressMsg_NotVerified');
		return false;
	}
	if($_POST['business']!=get_option('lockpressPaypalEmail')){
		$lockpress->setMsg('lockpressMsg_NotCorrected');$lockpress->paymentMsg.=' '.$_POST['business'].'!='.get_option('lockpressPaypalEmail');
		return false;
	}
	global $wpdb;
	if(count($wpdb->get_row("SELECT `id` FROM `".$wpdb->prefix."lockpress_payments` WHERE `id`='".$wpdb->escape($_POST['txn_id'])."'"))>0){
		$lockpress->setMsg('lockpressMsg_RepeatTxn');
		return false;
	}
	if($_POST['txn_type']=='web_accept' || $_POST['txn_type']=='subscr_payment'){ 
		if($_POST['payment_status']!='Completed' && $_POST['pending_reason']!='intl'){
			$lockpress->setMsg('lockpressMsg_NotCorrected');$lockpress->paymentMsg.=' payment_status='.$_POST['payment_status'].' pending_reason='.$_POST['pending_reason'];
			return false;
		}
		if(count($row=$wpdb->get_row("SELECT `post_content` FROM `".$wpdb->posts."` WHERE `ID`='".intval($_POST['item_number'])."'"))){
			if($lockpress->getArgs($row->post_content)){			
				if($lockpress->price==$_POST['mc_gross'] && $lockpress->currency==$_POST['mc_currency']){
					$uId=$lockpress->createUser($_POST['payer_email'],$_POST['first_name'].' '.$_POST['last_name']);							

					$lockpress->savePayment($_POST['txn_id'],$uId,$_POST['mc_gross']);

					if($lockpress->newUser){
						$thema=$lockpress->replaceTags(get_option('lockpressTmplSuccessEmailThema'));
						$msg=$lockpress->replaceTags(get_option('lockpressTmplSuccessEmail'));
						mail($lockpress->userEmail,$thema,$msg);
					}
					/**/
					$lockpress->success=true;
					return true;
				}else{
					mail($lockpress->adminEmail,'Paypal IPN error',"Payment amount mismatch\r\nTransaction ID: ".$_POST['txn_id']);
					$lockpress->setMsg('lockpressMsg_NotAmount');
					return false;
				}
			}else{
				$lockpress->setMsg('lockpressMsg_NotSell');
				return false;
			}
		}else{
			$lockpress->setMsg('lockpressMsg_NotFoundPost');
			return false;	
		}
	}elseif($_POST['txn_type']=='subscr_eot'){
		if(count($row=$wpdb->get_row("SELECT `ID` FROM `".$wpdb->users."` WHERE `login`='".$wpdb->escape($_POST['payer_email'])."'"))){
			$wpdb->query("
				DELETE FROM `".$wpdb->prefix."lockpress_data` WHERE `userId`='".$row['ID']."' and `groupId`='".intval($_POST['item_number'])."'
			");
		}
	}
}
/**/
?>