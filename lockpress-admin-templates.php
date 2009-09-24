<?php
function lockpressAdminTemplates(){
	$msg='';
	function save($opt){
		if(isset($_POST['set'.$opt])){
			update_option($opt,stripslashes($_POST[$opt]));
			$msg=lockpressActionMsg('Template saved.');
		}elseif(isset($_POST['set'.$opt.'Default'])){
			include 'lockpress-vars.php';
			update_option($opt,$$opt);
			$msg=lockpressActionMsg('Default template is set.');
		}
		return $msg;
	}
	function show($opt,$caption){
	?>
		<form action="" method="post">
		<h3><?php echo $caption;?></h3>
		<textarea name="<?php echo $opt;?>"
		rows="4" cols="100"><?php echo htmlspecialchars(get_option($opt));?></textarea>
		<p>
		<input type="submit" name="set<?php echo $opt;?>" value="save template" class="button" />
		<input type="submit" name="set<?php echo $opt;?>Default" value="apply default value" class="button" />
		</p>
		</form>
		<p/>
	<?php
	}
	$msg.=save('lockpressTmplClosedGuest')
	.save('lockpressTmplClosedUser')
	.save('lockpressTmplClosedGuestR0')
	.save('lockpressTmplClosedUserR0')
	.save('lockpressTmplCancelPage')
	.save('lockpressTmplSuccessPageGuest')
	.save('lockpressTmplSuccessPageUser')
	.save('lockpressTmplSuccessEmailThema')
	.save('lockpressTmplSuccessEmail');
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>LockPress - templates</h2>
		<?php 
		echo $msg
		,show('lockpressTmplClosedGuest','Closed item for guests (with recurring option)')
		,show('lockpressTmplClosedUser','Closed item for registered users (with recurring option)')
		,show('lockpressTmplClosedGuestR0','Closed item for guests')
		,show('lockpressTmplClosedUserR0','Closed item for registered users')
		,show('lockpressTmplCancelPage','Cancel page')
		,show('lockpressTmplSuccessPageGuest','Success page for new users')
		,show('lockpressTmplSuccessPageUser','Success page for registered users')
		,show('lockpressTmplSuccessEmailThema','Success email subject')	
		,show('lockpressTmplSuccessEmail','Success email text');
		?>		
	</div>
	<?php
}
?>