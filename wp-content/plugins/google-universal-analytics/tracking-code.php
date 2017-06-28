<?php

$web_property_id = 	get_option( 'web_property_id' );
$track_links 	 =	get_option('track_links');
$enable_display  =	get_option('enable_display');
$anonymize_ip 	 =	get_option('anonymize_ip');
$enhancedlink_u  =  get_option('enhancedlink_u');

/*if(get_option('set_domain')=='on'){
	$homeurl		 =	get_option('set_domain_domain');
}*/

$homeurl	=	@get_option('set_domain_domain');

$find = array( 'http://', 'https://', 'www.' );
$replace = '';
$homeurl = @str_replace( $find, $replace, $homeurl );

global $current_user;
      get_currentuserinfo();
?>

<!-- Google Universal Analytics for WordPress v2.4.2 -->

<script>

	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', '<?php echo $web_property_id; ?>', 'auto');<?php echo "\n\r" ?>
<?php if ( is_user_logged_in() ): ?>	ga('set', '&uid', '<?php echo $current_user->ID; ?>');<?php endif; ?><?php echo "\n\r" ?>
<?php if($anonymize_ip=='on'): ?>	ga('set', 'anonymizeIp', true);<?php endif; ?><?php echo "\n\r" ?>
<?php if($enhancedlink_u=='on'): ?>	ga('require', 'linkid', 'linkid.js');<?php endif; ?><?php echo "\n\r" ?>
<?php if($enable_display=='on'): ?>	ga('require', 'displayfeatures');<?php endif; ?><?php echo "\n\r" ?>
	ga('set', 'forceSSL', true);<?php echo "\n\r" ?>
	ga('send', 'pageview');

</script>
<!-- Google Universal Analytics for WordPress v2.4.2 - https://wordpress.org/plugins/google-universal-analytics -->

<?php if($track_links=='on'): ?>

<script type="text/javascript">

	jQuery(document).ready(function(e) {
    jQuery('a').click(function(e) {
		var $this = jQuery(this);
      	var href = $this.prop('href').split('?')[0];
		var ext = href.split('.').pop();
		if ('xls,xlsx,doc,docx,ppt,pot,pptx,pdf,pub,txt,zip,rar,tar,7z,gz,exe,wma,mov,avi,wmv,wav,mp3,midi,csv,tsv,jar,psd,pdn,ai,pez,wwf,torrent,cbr'.split(',').indexOf(ext) !== -1) {		
        ga('send', 'event', 'Download', ext, href);
      }
	  if (href.toLowerCase().indexOf('mailto:') === 0) {
        ga('send', 'event', 'Mailto', href.substr(7));
      }
	  if (href.toLowerCase().indexOf('tel:') === 0) {
        ga('send', 'event', 'Phone number', href.substr(4));
      }
      if ((this.protocol === 'http:' || this.protocol === 'https:') && this.hostname.indexOf(document.location.hostname) === -1) {
        ga('send', 'event', 'Outbound', this.hostname, this.pathname);
      }
	});
});

</script>

<?php endif; 