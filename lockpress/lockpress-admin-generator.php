<?php
function lockpressAdminGenerator(){
	global $wpdb;
	if(strlen(get_option('lockpressPaypalEmail'))<3){
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br/></div>
			<h2>LockPress / lock combination</h2>
			<p><?php echo get_option('lockpressMsg_NotPaypalEmail');?></p>
		</div>
		<?php
		return false;
	}
	?>
	<script type="text/javascript">
	
	var recur_text = '';
	var group_text = '';
	
	jQuery(document).ready(function(){
		jQuery('#buildtag').click(function(){
			price=jQuery('#price').val();
			recurring=jQuery('#recurring').val();
			period=jQuery('#period').val();
			group=jQuery('#group').val();
			if (recurring > 0) recur_text = ' recurring="'+recurring+period+'"';
			if (group > 0) group_text = ' group="'+group+'"';
						
			jQuery('#locktag').val('<!--sell price="'+price+'"'+recur_text+group_text+'-->');
		});
	});
	</script>
	<style>
	label{
		width:70px;
		display:block;
		float:left;
	}
	#price,#recurring,#group{
		width:40px;
	}
	</style>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>LockPress / lock combination</h2>
		Here you generate the code which you have to paste into the post/page you want locked. When you put your lock code in the post/page, any text before the code will be visible for "free". Only the text after the code will be "sold" and visitor will see it only after the purchase. Add lock code at the beginning of post/page content to hide all content. If you want to have a visible "teaser" text before your locked content, add it before the lock code.
		<p/>
		<label>Price</label><input id="price" value="1" /> <?php echo get_option('lockpressCurrency');?>
		<br/><label>Recurring</label><input id="recurring" value="0" /> 
		<select id="period">
		<option value="D">Days</option>
		<option value="W">Weeks</option>
		<option value="M">Monthes</option>
		<option value="Y">Years</option>
		</select> If you want the user to be charged each day or month - put the period and choose the billing cycle. To charge user each 2 weeks put 2 and select weeks
		<br/><label>Group #</label><input id="group" value="0" /> You can put any number, 1-... here. Purchasing one item from the group will give user access to the rest of group items for free
		<p/><input type="button" value="build the tag" class="button" id="buildtag" title="build the tag for inserting to post" />
		<p/><textarea id="locktag" rows="3" cols="100"><!--sell price="1"--></textarea>
	</div>
	<?php
}
?>