function cd_crt_show_error() {
    jQuery('#crt-chart-container .cd-crt-loader')
        .html('<p>Something terrible happened. Reload this page to try again.</p>')
        .show();
}

function cd_crt_fetch_data(args) {
    var a = typeof(args) != 'undefined' ? args : {};
    a.action = 'cd_crt_fetch_data';
    a.crt_nonce = crawlrate_data.nonce;
    var rv = false;
    jQuery.post(
        ajaxurl, a,
        function(resp) {
            if('-1' == resp) {
                cd_crt_show_error();
                return;
            }
            jQuery('.cd-crt-loader').hide();
            rv = jQuery.parseJSON(resp);
            cd_crt_build_charts(rv);
        }
    );
    return rv;
}

function cd_crt_build_charts(data) {
    var range = data.dates;
    delete data.dates;
    jQuery.each(data, function(key, val) {
        cd_crt_build_single('crt-' + key, val, range);
    });
}

function cd_crt_build_single(location, data, range) {
    new Ico.LineGraph(
        location,
        [data],
        {
            colors: ['#1111CC'],
            labels: {values: range, angle: 90},
            grid: true,
            units: ' Crawls',
            status_bar: true,
            height: 390,
            width: 780,
            curve_amount: 0,
        }
    );
}

jQuery(document).ready(function(){
	jQuery('a#crt-reload-graph').click(function(e){
        data = {};
        jQuery('.cd-crt-tab-container').css('left', '99999px').first().css('left', '0');
		if( start_date = jQuery('input#cd-crt-start-date').val() )
			data.start_date = start_date;
		if( end_date = jQuery('input#cd-crt-end-date').val() )
			data.end_date = end_date;
		jQuery('.cd-crt-tab').each(function() { jQuery(this).html('') });
        jQuery('.cd-crt-loader').show();
		cd_crt_fetch_data(data);
		e.preventDefault();
	});
    jQuery('#crt-chart-container .nav-tab').click(function(e) {
        var id = jQuery(this).attr('rel');
        jQuery('#crt-chart-container .nav-tab').removeClass('nav-tab-active');
        jQuery(this).addClass('nav-tab-active');
        jQuery('.cd-crt-tab').css('left', '99999px');
        jQuery('#' + id).css('left', '10px');
        e.preventDefault();
        return false;
    });
	jQuery('input#cd-crt-start-date').datepicker({dateFormat: 'yy-mm-dd'});
	jQuery('input#cd-crt-end-date').datepicker({dateFormat: 'yy-mm-dd'});
    cd_crt_fetch_data();
});
