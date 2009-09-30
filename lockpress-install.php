<?php

function lockpressActivation(){
	global $wpdb;	
	$wpdb->query("
		CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lockpress_data` (
		`userId` bigint(20) unsigned NOT NULL default '0',
		`groupId` bigint(20) unsigned NOT NULL default '0',
		PRIMARY KEY  (`userId`,`groupId`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	");
	$wpdb->query("
		CREATE TABLE `".$wpdb->prefix."lockpress_payments` (
		`id` varchar(50) NOT NULL default '0' COMMENT 'transaction id',
		`userId` bigint(20) unsigned NOT NULL default '0',
		`groupId` bigint(20) unsigned NOT NULL default '0',
		`dt` datetime default NULL,
		`amount` float unsigned default NULL,
		`currency` enum('USD','EUR','GBP','YEN','CAD') default 'USD',
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	");
	
	function defaultOption($opt){
		if(strlen(get_option($opt))<1){
			include 'lockpress-vars.php';
			update_option($opt,$$opt);
		}
	}
		
	if(strlen(get_option('lockpressOnDeactivation'))<1){
		update_option('lockpressOnDeactivation','clean');
	}
	defaultOption('lockpressTmplClosedGuest');
	defaultOption('lockpressTmplClosedUser');
	defaultOption('lockpressTmplClosedGuestR0');
	defaultOption('lockpressTmplClosedUserR0');
	defaultOption('lockpressTmplCancelPage');
	defaultOption('lockpressTmplSuccessPageGuest');
	defaultOption('lockpressTmplSuccessPageUser');
	defaultOption('lockpressTmplSuccessEmail');
	defaultOption('lockpressTmplSuccessEmailThema');
	//
	defaultOption('lockpressMsg_NotVerified');
	defaultOption('lockpressMsg_NotCorrected');
	defaultOption('lockpressMsg_RepeatTxn');
	defaultOption('lockpressMsg_NotFoundPost');
	defaultOption('lockpressMsg_NotSell');
	defaultOption('lockpressMsg_NotAmount');
	//
	defaultOption('lockpressMsg_NotPaypalEmail');
	defaultOption('lockpressMsg_NotSecureKey');
	/**/
	if(strlen(get_option('lockpressAdminEmail'))<1){
		$row=$wpdb->get_row("SELECT `user_email` FROM `".$wpdb->users."` WHERE `ID`='1'",ARRAY_A);
		$lockpressAdminEmail=(count($row)>0)?$row['user_email']:'';						
		update_option('lockpressAdminEmail',$lockpressAdminEmail);
	}
	/**/
	if(strlen(get_option('lockpressCurrency'))<1){
		update_option('lockpressCurrency','USD');
	}
	/**/
	update_option('lockpressShowLink','1');	
	lockpressCreateLink();
	/**/
}

function lockpressDeactivation(){
	if(get_option('lockpressOnDeactivation')=='clean'){

		global $wpdb;
		$wpdb->query("
			DROP TABLE IF EXISTS `".$wpdb->prefix."lockpress_data`
		");
		$wpdb->query("
			DROP TABLE IF EXISTS `".$wpdb->prefix."lockpress_payments`
		");

		delete_option('lockpressTmplClosedGuest');
		delete_option('lockpressTmplClosedUser');
		delete_option('lockpressTmplClosedGuestR0');
		delete_option('lockpressTmplClosedUserR0');
		delete_option('lockpressTmplCancelPage');
		delete_option('lockpressTmplSuccessPageGuest');
		delete_option('lockpressTmplSuccessPageUser');
		delete_option('lockpressTmplSuccessEmail');
		delete_option('lockpressTmplSuccessEmailThema');

		delete_option('lockpressMsg_NotVerified');
		delete_option('lockpressMsg_NotCorrected');
		delete_option('lockpressMsg_RepeatTxn');
		delete_option('lockpressMsg_NotFoundPost');
		delete_option('lockpressMsg_NotSell');
		delete_option('lockpressMsg_NotAmount');
		//
		delete_option('lockpressMsg_NotPaypalEmail');
		delete_option('lockpressMsg_NotSecureKey');

		delete_option('lockpressPaypalEmail');
		delete_option('lockpressAdminEmail');
		delete_option('lockpressCurrency');
		delete_option('lockpressShowLink');
		delete_option('lockpressOnDeactivation');
	}	
}
/**/
?>