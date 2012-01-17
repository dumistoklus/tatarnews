$(document).ready(function(){
	$( ".datepicker" ).datepicker({dateFormat:'yy-mm-dd'});
	$( "#tabs" ).tabs();
	var template_id = $('#aboutme').html();
	if (template_id !== undefined ) {
		$( "#tabs" ).tabs({select:1});
	}
});
