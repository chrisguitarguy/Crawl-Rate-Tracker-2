function cd_crt_show_loader() {
    jQuery('#crt-chart-container').html('<div class="cd-crt-loader"><img src="' + crawlrate_data.loader + '" /></div>');
}

function cd_crt_show_error() {
    jQuery('#crt-chart-container').html('<div class="cd-crt-loader"><p>Something terrible happened. Reload this page to try again.</p></div>');
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
            rv = jQuery.parseJSON(resp);
            jQuery('#crt-chart-container').html('');
            cd_crt_build_chart(rv);
        }
    );
    return rv;
}

function cd_crt_build_chart(data) {
    new Ico.LineGraph(
        'crt-chart-container',
        [
            data.totals,
            data.google,
            data.bing,
            data.yahoo,
            data.msn
        ],
        {
            colors: ['#FF0000', '#1111CC', '#F76120', '#7B0099', '#009AD9'],
            x_padding_right: 60,
            labels: {values: data.dates, angle: 90},
            grid: true,
            units: ' Crawls',
            status_bar: true
        }
    );
}

jQuery(document).ready(function(){
	jQuery('a#crt-reload-graph').click(function(e){
        data = {};
		if( start_date = jQuery('input#cd-crt-start-date').val() )
			data.start_date = start_date;
		if( end_date = jQuery('input#cd-crt-end-date').val() )
			data.end_date = end_date;
		
        cd_crt_show_loader();
		cd_crt_fetch_data(data);
		e.preventDefault();
	});
	jQuery('input#cd-crt-start-date').datepicker({dateFormat: 'yy-mm-dd'});
	jQuery('input#cd-crt-end-date').datepicker({dateFormat: 'yy-mm-dd'});
    cd_crt_show_loader();
    cd_crt_fetch_data();
});
