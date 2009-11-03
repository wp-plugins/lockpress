<?php
/*
Plugin Name: LockPress
Plugin URI: http://twoenough.com/products/lockpress
Description: Monetize your visitors - get money from people reading your posts
Author: twoenough.com
Version: 1.3.2
Author URI: http://twoenough.com
*/
if(!class_exists('lockpress')){
define('lockpressTablePrefix','lockpress_');

class lockpress{
	var $sellTag,$visible,$content,$siteUrl,$adminEmail,$userEmail,$paymentMsg,$signinlink
	,$price,$currency,$recurring,$period,$group,$buylink,$login,$password,$newUser,$success;
	
	/* Construct */
	function lockpress(){
		$this->siteUrl=get_option('siteurl');
		$this->currency=get_option('lockpressCurrency');
		$this->adminEmail=get_option('lockpressAdminEmail');
	}
	function printPeriod(){
		if($this->recurring>1){
			$periods=array('D'=>'days','W'=>'weeks','M'=>'monthes','Y'=>'years');
			return (isset($periods[$this->period]))?$periods[$this->period]:'';
		}elseif($this->recurring>0){
			$periods=array('D'=>'day','W'=>'week','M'=>'month','Y'=>'year');
			return (isset($periods[$this->period]))?$periods[$this->period]:'';
		}else{
			return '';
		}
	}
	
	/* getting <!--sell--> tag */
	function getArgs($content){
		if(eregi("<!--sell([^\-\>]*)-->",$content,$eregArr)){
			require_once 'lockpress-payment.php';
			$this->content=$content;
			$this->sellTag=$eregArr[0];
			$this->price=(eregi("price=\"([0-9,\.]+)\"",$eregArr[0],$eregArr2))?$eregArr2[1]:0;
			$this->price = preg_replace("/,/", ".", $this->price);
			
			if(eregi("recurring=\"([0-9]+)(D|W|M|Y)\"",$eregArr[0],$eregArr2)){
				$this->recurring=$eregArr2[1];
				$this->period=$eregArr2[2];
				$this->buylink=lockpressSubscribeLink($this->price,$this->recurring,$this->period);
			}else{
				$this->recurring=$this->period=0;
				$this->periodStr='';
				$this->buylink=lockpressBuyLink($this->price);
			}
			$this->group=(eregi("group=\"([0-9]+)\"",$eregArr[0],$eregArr2))?$eregArr2[1]:0;
			$this->visible=substr($content,0,strpos($content,'<!--sell'));
			$this->signinlink='<a href="'.$this->siteUrl.'/wp-login.php?redirect_to='
				.$this->siteUrl.$_SERVER['REQUEST_URI'].'" class="signinlink">\1</a>';		
			return true;
		}else{
			return false;
		}
	}
	/* parsing */
	function replaceTags($content){
		global $current_user;
		$replace=array('<!--price-->'=>$this->price
			,'<!--currency-->'=>$this->currency
			,'<!--recurring-->'=>$this->recurring.' '.$this->printPeriod()
			,'<!--buylink-->'=>$this->buylink
			,'<!--username-->'=>$current_user->display_name
			,'<!--login-->'=>$this->login
			,'<!--password-->'=>$this->password	
			,'<!--adminEmail-->'=>$this->adminEmail				
		);
		$content=strtr($content,$replace);
		$content=eregi_replace("<!--signinlink ([^>]+)-->",$this->signinlink,$content);
		return $content;
	}
	function setMsg($opt){
		$this->paymentMsg=$this->replaceTags(get_option($opt));
	}
	/* Check access */
	function isSecureKey(){
	
		return strlen(get_option('lockpress_secureKey')) == 32;
		//return md5($this->siteUrl)==get_option('lockpress_secureKey');
		
	}
	function isGroupOpen(){
		global $wpdb,$current_user;
		$query=$wpdb->prepare("SELECT `userId` 
			FROM `".$wpdb->prefix."lockpress_data` 
			WHERE `userId`='%d' and `groupId`='%d'
			LIMIT 0,1
			"
			,$current_user->ID
			,$this->group
		);
		return (count($wpdb->get_row($query,ARRAY_A))>0);
	}
	function closePage($content){
		if($this->isSecureKey()){
			return $this->visible.'<div class="lockpress">'.$this->replaceTags($content).'</div>';
		}else{
			return str_replace($this->sellTag,'<div class="lockpress">'.get_option('lockpressMsg_NotSecureKey').'</div>',$content);
		}
	}
	function openPage($content){
		$postdata='log='.urlencode($this->login).'&pwd='.urlencode($this->password)
				.'&loginform=&rememberme=on&wp-submit=Enter&redirect_to=&testcookie=1'; 
		// $jq="<script>jQuery.post('".$this->siteUrl."/wp-login.php','".$postdata."');</script>";
		return str_replace($this->sellTag,'<div class="lockpress">'.$this->replaceTags($content).'</div>',$this->content);
	}
	/* Create user */
	function createUser($email,$displayName){
		global $wpdb,$current_user;
		/*	get email		*/
		$user_email=$wpdb->escape($this->userEmail=trim($email));
		if(count($row=$wpdb->get_row("SELECT `ID` FROM `".$wpdb->users."` WHERE `user_login`='".$user_email."'",ARRAY_A))>0){
			$userId=$row['ID'];
			$this->newUser=false;
		}else{
			/*	create a new user	*/					
			$this->login=apply_filters('pre_user_login',sanitize_user($this->userEmail,true));
			$user_login=$wpdb->escape($this->login);
			$this->password=wp_generate_password(12);
			$user_pass=wp_hash_password($this->password);
			$user_nicename=apply_filters('pre_user_nicename',sanitize_title($user_login));			
			$user_url=apply_filters('pre_user_url','');
			$user_email=apply_filters('pre_user_email',$user_email);
			$display_name=apply_filters('pre_user_display_name',$displayName);
			$user_registered=gmdate('Y-m-d H:i:s');
			//
			$data=compact('user_pass','user_email','user_url','user_nicename','display_name','user_registered');
			$data=stripslashes_deep($data);
			$wpdb->insert($wpdb->users,$data+compact('user_login'));
			$userId=(int)$wpdb->insert_id;
			// set a password
			$role=apply_filters('pre_user_role',get_option('default_role'));
			$user=new WP_User($userId);
			$user->set_role($role);
			/**/
			$this->newUser=true;
		}
		// authorize user
		if($userId!=$current_user->ID){
			set_current_user($userId);
			wp_set_auth_cookie($userId,true);
		}
		return $userId;
	}
	/* save the payment */
	function savePayment($id,$uid,$amount){
		global $wpdb;
		$wpdb->query("REPLACE INTO `".$wpdb->prefix."lockpress_payments`
		(`id`,`userId`,`groupId`,`dt`,`amount`,`currency`)
		VALUES
		('".$wpdb->escape($id)."','".$uid."','".$this->group."',NOW(),'".round($amount,2)."','".$this->currency."')
		");
		$wpdb->query("REPLACE INTO `".$wpdb->prefix."lockpress_data`
		(`userId`,`groupId`)
		VALUES
		('".$uid."','".$this->group."')
		");
	}
}
/////	class end  /////

function lockpressCreateLink(){
	global $wpdb;
	$link='http://twoenough.com/products/lockpress/';
	if(($link_id=intval(get_option('lockpressShowLink')))
		&& !count($row=$wpdb->get_row("SELECT `link_id` FROM `".$wpdb->links."` 
			WHERE `link_url`='".$link."' AND `link_visible`='Y' AND `link_id`='".$link_id."' LIMIT 1",ARRAY_A))
	){
		$wpdb->query("INSERT INTO `".$wpdb->links."` (`link_url`,`link_name`,`link_visible`) 
			VALUES('".$link."','Posts are locked with LockPress','Y')");
		$link_id=$wpdb->insert_id;
		if(($cat=intval(get_option('default_link_category')))
			&& count($row=$wpdb->get_row("SELECT `term_taxonomy_id` FROM `".$wpdb->term_taxonomy."` 
			WHERE `term_id`='".$cat."' AND `taxonomy`='link_category' LIMIT 1",ARRAY_A))
		){
			$wpdb->query("INSERT INTO `".$wpdb->term_relationships."` (`object_id`,`term_taxonomy_id`) 
				VALUES('".$link_id."','".$row['term_taxonomy_id']."')");
		}
		update_option('lockpressShowLink',$link_id);
		do_action('add_link',$link_id);		
	}
}

/* content filters */
function lockpressContent($content=''){
	global $wpdb,$current_user,$post,$lockpress;
	//
	if($lockpress->isSecureKey()){
		if(strlen(get_option('lockpressPaypalEmail'))>3){
			$isSell=$lockpress->getArgs($content);
			if(isset($_POST['txn_type'])){ // IPN PayPal
				if($lockpress->success){
					$opt=($lockpress->newUser)?'Guest':'User';
					$content=$lockpress->openPage(get_option('lockpressTmplSuccessPage'.$opt));
				}else{
					$content=$lockpress->closePage($lockpress->paymentMsg);
				}
			}else{
				if($isSell){			
					if(is_user_logged_in() && $lockpress->isGroupOpen()){
						$content=str_replace($lockpress->sellTag,'',$content);				
					}else{
						$opt='lockpressTmplClosed';
						$opt.=(is_user_logged_in())?'User':'Guest';
						$opt.=($lockpress->recurring>0)?'':'R0';
						$content=$lockpress->closePage(get_option($opt));
					}
				}else{}
			}
		}elseif($lockpress->getArgs($content)){// No paypal email in settings	
			$content=str_replace($lockpress->sellTag,'<div class="lockpress">'.get_option('lockpressMsg_NotPaypalEmail').'</div>',$content);
		}else{}
	}elseif($lockpress->getArgs($content)){
		$content=str_replace($lockpress->sellTag,'<div class="lockpress">'.get_option('lockpressMsg_NotSecureKey').'</div>',$content);
	}else{}
	return $content;
}

///
function lockpressMain(){
	global $lockpress;
	if(isset($_POST['txn_type'])){ // IPN PayPal
		/* модуль оплаты */
		require_once 'lockpress-payment.php';
		if(lockpressPayment()){
			$opt=($lockpress->newUser)?'Guest':'User';
			$content=$lockpress->openPage(get_option('lockpressTmplSuccessPage'.$opt));
		}else{
			$content=$lockpress->closePage($lockpress->paymentMsg);
		}
	}
}
add_action('init','lockpressMain',1000);
///
$lockpress=new lockpress;
lockpressCreateLink();

if(is_admin()){
	/* activate/deactivate */
	include_once 'lockpress-install.php';
	register_activation_hook(__FILE__,'lockpressActivation');
	register_deactivation_hook(__FILE__,'lockpressDeactivation');
	/* admin */
	include_once 'lockpress-admin.php';
	/**/
}else{
	/* css & js */
	wp_enqueue_style('lockpress','/'.PLUGINDIR.'/lockpress/lockpress.css');
	wp_enqueue_script('lockpress','/'.PLUGINDIR.'/lockpress/lockpress.js',array('jquery'));
	add_filter('the_content','lockpressContent',1000);
}
}
?>