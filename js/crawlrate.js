jQuery(document).ready(function(){
	//jQuery('.wrap h2').html(window.location.search);
	jQuery('a#crt-reload-graph').click(function(e){
		var data = {
			'action': 'cd_crt_build_new_graph',
			'url': window.location.search
		}
		if( start_date = jQuery('input#cd-crt-start-date').val() )
			data.start_date = start_date;
		if( end_date = jQuery('input#cd-crt-end-date').val() )
			data.end_date = end_date;
		
		jQuery(this).append(userSettings.ajaxurl);
		jQuery.post(
			ajaxurl,
			data,
			function(data){
				jQuery('div#crt-chart-container').html(data);
			}
		);
		e.preventDefault();
	});
	jQuery('input#cd-crt-start-date').datepicker({dateFormat: 'yy-mm-dd'});
	jQuery('input#cd-crt-end-date').datepicker({dateFormat: 'yy-mm-dd'});
});
