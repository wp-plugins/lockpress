jQuery(document).ready(function(){
	jQuery('.closelink').click(function(){
		jQuery(this).parent().slideUp('slow',function(){});
	});
});