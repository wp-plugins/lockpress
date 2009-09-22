<?php
function lockpressAdminMessages(){
	$msg='';
	function save($opt){
		if(isset($_POST['set'.$opt])){
			update_option($opt,stripslashes($_POST[$opt]));
			$msg=lockpressActionMsg('Template saved.');
		}elseif(isset($_POST['set'.$opt.'Default'])){
			include 'lockpress-vars.php';
			update_option($opt,$$opt);
			$msg=lockpressActionMsg('Default template applied.');
		}
		return $msg;
	}
	function show($opt,$caption){
	?>
		<form action="" method="post">
		<h3><?php echo $caption;?></h3>
		<textarea name="<?php echo $opt;?>"
		rows="2" cols="100"><?php echo htmlspecialchars(get_option($opt));?></textarea>
		<p>
		<input type="submit" name="set<?php echo $opt;?>" value="save template" class="button" />
		<input type="submit" name="set<?php echo $opt;?>Default" value="apply default value" class="button" />
		</p>
		</form>
		<p/>
	<?php
	}
	$msg.=save('lockpressMsg_NotVerified')
	.save('lockpressMsg_NotCorrected')
	.save('lockpressMsg_RepeatTxn')
	.save('lockpressMsg_NotFoundPost')
	.save('lockpressMsg_NotSell')
	.save('lockpressMsg_NotAmount');
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>WP-LockPress - Messages</h2>
		<?php 
		echo $msg
		,show('lockpressMsg_NotVerified','Not verified by PayPal')
		,show('lockpressMsg_NotCorrected','Payment data is incorrect')
		,show('lockpressMsg_RepeatTxn','Repeated transaction')
		,show('lockpressMsg_NotFoundPost','Item not found')
		,show('lockpressMsg_NotSell','Item is not for sale')
		,show('lockpressMsg_NotAmount','Amount error');		
		?>	
	</div>
	<?php
}
?>