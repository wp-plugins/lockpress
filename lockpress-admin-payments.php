<?php
function lockpressAdminPayments(){
	global $wpdb;
	$query=$wpdb->prepare("SELECT sum(`amount`) as 'money',`currency`
		FROM `".$wpdb->prefix."lockpress_payments`
		GROUP BY `currency`
		HAVING `money`>0
		"
	);
	$money='';
	if(count($list=$wpdb->get_results($query,ARRAY_A))>0){
		$mns=array();
		foreach($list as $item){
			$mns[]=$item['money'].' '.$item['currency'];
		}
		$money=implode(',',$mns);
	}else{
		$money='0';
	}
    ?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>LockPress payments</h2>
		<p>Total: <?php echo $money;?></p><br>
		<h3>Payments</h3>
	<?php
	$start=(isset($_GET['start']))?intval($_GET['start']):0;
	$limit=20;
	$query=$wpdb->prepare("SELECT `u`.`ID`,`u`.`display_name`,`u`.`user_login`
		,`p`.`groupId`,`p`.`dt`,`p`.`amount`,`p`.`currency`
		FROM `".$wpdb->prefix."lockpress_payments` p
		LEFT JOIN `".$wpdb->users."` u on `p`.`userId`=`u`.`ID`
		ORDER BY `p`.`dt` DESC
		LIMIT ".$start.",".$limit."
	");
	if(count($list=$wpdb->get_results($query,ARRAY_A))>0){
		?>
		<table class="widefat fixed">
		<thead><tr>
		<th>Date</th><th>Amount</th>
		<th>User</th><th>Group #</th>
		</tr></thead><tbody>
		<?php
		$siteUrl=get_option('siteurl');
		
		foreach($list as $item){
			echo '<tr>
			<td>'.$item['dt'].'</td>
			<td>'.$item['amount'].' '.$item['currency'].'</td>
			<td><a href="'.$siteUrl.'/wp-admin/user-edit.php?user_id='.$item['ID'].'">'.wp_specialchars($item['display_name']).'</a></td>
			<td>'.$item['groupId'].'</td>		
			</tr>';
		}
		$nextLink=(strlen($_SERVER['QUERY_STRING'])>0)
			?'?'.$_SERVER['QUERY_STRING'].'&start='.($start+$limit)
			:'?start='.($start+$limit);
		?>
		</tbody></table>
		<p><a href="<?php echo $nextLink;?>" class="button">Next</a></p>
		<?php
	}else{
		echo '<p/>No purchases yet.';
	}	
	?>		
	</div>
	<?php	
}
?>