<?php

function lockpressActionMsg($str){
	return '<div style="padding:10px;" class="updated">'.htmlspecialchars($str).'</div>';
}

function lockpressMainPage(){
	$msg='';
	if(isset($_POST['save'])){
		update_option('lockpressPaypalEmail',$_POST['lockpressPaypalEmail']);
		update_option('lockpressAdminEmail',$_POST['lockpressAdminEmail']);
		update_option('lockpressCurrency',$_POST['lockpressCurrency']);
		update_option('lockpressOnDeactivation',($_POST['lockpressOnDeactivation']=='on')?'clean':'');
		if($_POST['lockpressShowLink']=='on'){
			update_option('lockpressShowLink',1);
			lockpressCreateLink();
		}else{
			if($link_id=intval(get_option('lockpressShowLink'))) wp_delete_link($link_id);
			update_option('lockpressShowLink',0);	
		}
		$msg=lockpressActionMsg('Options saved.');
	}
	///
	$currencies=array('USD','EUR','GBP','YEN','CAD');
	$currency=get_option('lockpressCurrency');
	$currencyOptions='';
	foreach($currencies as $key=>$val){
		$currencyOptions.=($val==$currency)
			?'<option value="'.$val.'" selected>'.$val.'</option>'
			:'<option value="'.$val.'">'.$val.'</option>';
	}
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>LockPress / settings</h2>
		<?php echo $msg;?>
		<form action="" method="post">
		<p/>Your PayPal e-mail (you will get payments to this account)
		<br/><input type="text" name="lockpressPaypalEmail" value="<?php echo htmlspecialchars(get_option('lockpressPaypalEmail'));?>" size="50" />
		<p/>Notifications e-mail
		<br/><input type="text" name="lockpressAdminEmail" value="<?php echo htmlspecialchars(get_option('lockpressAdminEmail'));?>" size="50" />
		<p/>Currency
		<br/><select name="lockpressCurrency" style="width:200px;"><?php echo $currencyOptions;?></select>
		<p/>
		<p/>
		<input type="checkbox" id="lockpressShowLink" name="lockpressShowLink" <?php echo (intval(get_option('lockpressShowLink')))?'checked':'';?> />
		<label for="lockpressShowLink">Link to LockPress site from blogroll</label>
		</p>
		<input type="checkbox" id="lockpressOnDeactivation" name="lockpressOnDeactivation" <?php echo (get_option('lockpressOnDeactivation')=='clean')?'checked':'';?> />
		<label for="lockpressOnDeactivation">Clean all settings and data on uninstall</label>
		<p/><input type="submit" name="save" value="save options" class="button" />
		</form>
	</div>
	<?php
}
function lockpressMainPageNoKey(){

/*

Hello there!

The activation protection in this plugin is not intended to limit you in using it in any way - we just try to keep a list of 
people who use our plugin. So please - take one minute of your time activating the plugin on our site instead taking same minute
trying to figure out how to avoid activation :)


*/


	$msg='';
	if(isset($_GET['lockpressSecureKey'])){
		update_option('lockpress_secureKey',$_GET['lockpressSecureKey']);
		?>
		<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>LockPress / settings</h2>
		<p/>
		Settings saved. Reloading...
		<script type="text/javascript">document.location='?page=lockpress/lockpress-admin.php';</script>
		<noscript><a href="?page=lockpress/lockpress-admin.php">click here to continue...</a></noscript>
		</p>
		</div>
		<?php
	}else{	
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>LockPress / settings</h2>
		<h3>Your copy is not registered</h3>
		<form action="" method="get">
			<input type="hidden" name="page" value="lockpress/lockpress-admin.php"/>
			<p/>Please put the license key and click save (you can use the license from another LockPress install)
			<br/><input type="text" name="lockpressSecureKey" size="50" />
			</p>
			<input type="submit" value="save" class="button" />
		</form>
		<br/>
		<p>If you haven't got your license yet, please visit our <a href='http://twoenough.com/products/lockpress/activate?domain=<?php echo urlencode(htmlspecialchars(get_option('siteurl')));?>'>activation page</a>.
		</p>
		</form>
	</div>
	<?php
	}
}

if($_GET['page']=='lockpress-admin-payments.php') include 'lockpress-admin-payments.php';
if($_GET['page']=='lockpress-admin-generator.php') include 'lockpress-admin-generator.php';
if($_GET['page']=='lockpress-admin-templates.php') include 'lockpress-admin-templates.php';
if($_GET['page']=='lockpress-admin-messages.php') include 'lockpress-admin-messages.php';

function lockpress_add_pages(){
    if(strlen(get_option('lockpress_secureKey')) == 32){
		add_menu_page('LockPress Main','LockPress',8, __FILE__,'lockpressMainPage');
	    add_submenu_page(__FILE__,'LockPress settings','Settings',8,__FILE__,'lockpressMainPage');	
	    add_submenu_page(__FILE__,'LockPress payments','Payments',8,'lockpress-admin-payments.php','lockpressAdminPayments');	
	    add_submenu_page(__FILE__,'LockPress lock generator','Lock generator',8,'lockpress-admin-generator.php','lockpressAdminGenerator');	
	    add_submenu_page(__FILE__,'LockPress Templates','Templates',8,'lockpress-admin-templates.php','lockpressAdminTemplates');	
		add_submenu_page(__FILE__,'LockPress Messages','Messages',8,'lockpress-admin-messages.php','lockpressAdminMessages');	
	}else{
		add_menu_page('LockPress Main','LockPress',8, __FILE__,'lockpressMainPageNoKey');
	}
}
/// actions
add_action('admin_menu', 'lockpress_add_pages');
?>