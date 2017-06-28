<?php
/* Ontraport (Office Auto Pilot) integration for Layered Popups */
class ulp_ontraport_class {
	var $default_popup_options = array(
		'ontraport_enable' => 'off',
		'ontraport_app_id' => '',
		'ontraport_api_key' => '',
		'ontraport_tag_id' => '',
		'ontraport_sequence_id' => ''
	);
	function __construct() {
		if (is_admin()) {
			add_action('admin_init', array(&$this, 'admin_request_handler'));
			add_action('ulp_popup_options_integration_show', array(&$this, 'popup_options_show'));
			add_filter('ulp_popup_options_check', array(&$this, 'popup_options_check'), 10, 1);
			add_filter('ulp_popup_options_populate', array(&$this, 'popup_options_populate'), 10, 1);
			add_filter('ulp_popup_options_tabs', array(&$this, 'popup_options_tabs'), 10, 1);
		}
		add_action('ulp_subscribe', array(&$this, 'subscribe'), 10, 2);
	}
	function popup_options_tabs($_tabs) {
		if (!array_key_exists("integration", $_tabs)) $_tabs["integration"] = __('Integration', 'ulp');
		return $_tabs;
	}
	function popup_options_show($_popup_options) {
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		echo '
				<h3>'.__('Ontraport (Office Auto Pilot) Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Enable Ontraport', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_ontraport_enable" name="ulp_ontraport_enable" '.($popup_options['ontraport_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to Ontraport (Office Auto Pilot)', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to Ontraport (Office Auto Pilot).', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('App ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_ontraport_app_id" name="ulp_ontraport_app_id" value="'.esc_html($popup_options['ontraport_app_id']).'" class="widefat" onchange="ulp_ontraport_handler();">
							<br /><em>'.__('Enter your Ontraport App ID. <a href="https://officeautopilot.zendesk.com/entries/22308086-Contacts-API#auth" target="_blank">How to find it?</a>', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_ontraport_api_key" name="ulp_ontraport_api_key" value="'.esc_html($popup_options['ontraport_api_key']).'" class="widefat" onchange="ulp_ontraport_handler();">
							<br /><em>'.__('Enter your Ontraport (Office Auto Pilot) API Key. <a href="https://officeautopilot.zendesk.com/entries/22308086-Contacts-API#auth" target="_blank">How to find it?</a>', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('Tag ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_ontraport_tag_id" name="ulp_ontraport_tag_id" value="'.esc_html($popup_options['ontraport_tag_id']).'" class="widefat">
							<br /><em>'.__('Enter comma-separated list of Tag IDs. You can get Tag IDs from', 'ulp').' <a href="'.admin_url('admin.php').'?action=ulp-ontraport-tags&app='.base64_encode($popup_options['ontraport_app_id']).'&key='.base64_encode($popup_options['ontraport_api_key']).'" class="thickbox" id="ulp_ontraport_tags" title="'.__('Available Tags', 'ulp').'">'.__('this table', 'ulp').'</a>.</em>
						</td>
					</tr>
					<tr>
						<th>'.__('Sequence ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_ontraport_sequence_id" name="ulp_ontraport_sequence_id" value="'.esc_html($popup_options['ontraport_sequence_id']).'" class="widefat">
							<br /><em>'.__('Enter comma-separated list of Sequence IDs. You can get Sequence IDs from', 'ulp').' <a href="'.admin_url('admin.php').'?action=ulp-ontraport-sequences&app='.base64_encode($popup_options['ontraport_app_id']).'&key='.base64_encode($popup_options['ontraport_api_key']).'" class="thickbox" id="ulp_ontraport_sequences" title="'.__('Available Sequences', 'ulp').'">'.__('this table', 'ulp').'</a>.</em>
							<script>
								function ulp_ontraport_handler() {
									jQuery("#ulp_ontraport_tags").attr("href", "'.admin_url('admin.php').'?action=ulp-ontraport-tags&app="+ulp_encode64(jQuery("#ulp_ontraport_app_id").val())+"&key="+ulp_encode64(jQuery("#ulp_ontraport_api_key").val()));
									jQuery("#ulp_ontraport_sequences").attr("href", "'.admin_url('admin.php').'?action=ulp-ontraport-sequences&app="+ulp_encode64(jQuery("#ulp_ontraport_app_id").val())+"&key="+ulp_encode64(jQuery("#ulp_ontraport_api_key").val()));
								}
							</script>
						</td>
					</tr>
				</table>';
	}
	function popup_options_check($_errors) {
		global $ulp;
		$errors = array();
		$popup_options = array();
		foreach ($this->default_popup_options as $key => $value) {
			if (isset($ulp->postdata['ulp_'.$key])) {
				$popup_options[$key] = stripslashes(trim($ulp->postdata['ulp_'.$key]));
			}
		}
		if (isset($ulp->postdata["ulp_ontraport_enable"])) $popup_options['ontraport_enable'] = "on";
		else $popup_options['ontraport_enable'] = "off";
		if ($popup_options['ontraport_enable'] == 'on') {
			if (empty($popup_options['ontraport_app_id'])) $errors[] = __('Invalid Ontraport (Office Auto Pilot) App ID', 'ulp');
			if (empty($popup_options['ontraport_api_key'])) $errors[] = __('Invalid Ontraport (Office Auto Pilot) API key', 'ulp');
		}
		return array_merge($_errors, $errors);
	}
	function popup_options_populate($_popup_options) {
		global $ulp;
		$popup_options = array();
		foreach ($this->default_popup_options as $key => $value) {
			if (isset($ulp->postdata['ulp_'.$key])) {
				$popup_options[$key] = stripslashes(trim($ulp->postdata['ulp_'.$key]));
			}
		}
		if (isset($ulp->postdata["ulp_ontraport_enable"])) $popup_options['ontraport_enable'] = "on";
		else $popup_options['ontraport_enable'] = "off";
		return array_merge($_popup_options, $popup_options);
	}
	function admin_request_handler() {
		global $wpdb;
		if (!empty($_GET['action'])) {
			switch($_GET['action']) {
				case 'ulp-ontraport-tags':
					if (isset($_GET["app"]) && isset($_GET["key"])) {
						$app = base64_decode($_GET["app"]);
						$key = base64_decode($_GET["key"]);
						
						$postargs = 'appid='.$app.'&key='.$key.'&reqType=pull_tag';
						$request = "http://api.moon-ray.com/cdata.php";
						$session = curl_init($request);
						curl_setopt($session, CURLOPT_POST, true);
						curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
						curl_setopt($session, CURLOPT_HEADER, false);
						curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
						$response = curl_exec($session);
						curl_close($session);
						$p = xml_parser_create();
						xml_parse_into_struct($p, $response, $values, $index);
						xml_parser_free($p);
						$tags = array();
						if (isset($index['TAG'])) {
							foreach ($index['TAG'] as $idx) {
								$tags[$values[$idx]['attributes']['ID']] = $values[$idx]['value'];
							}
						}

						if (!empty($tags)) {
							ksort($tags);
							echo '
<html>
<head>
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
	<title>'.__('Ontraport (Office Auto Pilot) Tags', 'ulp').'</title>
</head>
<body>
	<table style="width: 100%;">
		<tr>
			<td style="width: 170px; font-weight: bold;">'.__('Tag ID', 'ulp').'</td>
			<td style="font-weight: bold;">'.__('Tag Name', 'ulp').'</td>
		</tr>';
							foreach ($tags as $key => $value) {
								echo '
		<tr>
			<td>'.esc_html($key).'</td>
			<td>'.esc_html(esc_html($value)).'</td>
		</tr>';
							}
							echo '
	</table>						
</body>
</html>';
						} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					die();
					break;
				case 'ulp-ontraport-sequences':
					if (isset($_GET["app"]) && isset($_GET["key"])) {
						$app = base64_decode($_GET["app"]);
						$key = base64_decode($_GET["key"]);
						
						$postargs = 'appid='.$app.'&key='.$key.'&reqType=fetch_sequences';
						$request = "http://api.moon-ray.com/cdata.php";
						$session = curl_init($request);
						curl_setopt($session, CURLOPT_POST, true);
						curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
						curl_setopt($session, CURLOPT_HEADER, false);
						curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
						$response = curl_exec($session);
						curl_close($session);
						$p = xml_parser_create();
						xml_parse_into_struct($p, $response, $values, $index);
						xml_parser_free($p);
						$sequences = array();
						if (isset($index['SEQUENCE'])) {
							foreach ($index['SEQUENCE'] as $idx) {
								$sequences[$values[$idx]['attributes']['ID']] = $values[$idx]['value'];
							}
						}

						if (!empty($sequences)) {
							ksort($sequences);
							echo '
<html>
<head>
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
	<title>'.__('Ontraport (Office Auto Pilot) Sequences', 'ulp').'</title>
</head>
<body>
	<table style="width: 100%;">
		<tr>
			<td style="width: 170px; font-weight: bold;">'.__('Sequence ID', 'ulp').'</td>
			<td style="font-weight: bold;">'.__('Sequence Name', 'ulp').'</td>
		</tr>';
							foreach ($sequences as $key => $value) {
								echo '
		<tr>
			<td>'.esc_html($key).'</td>
			<td>'.esc_html(esc_html($value)).'</td>
		</tr>';
							}
							echo '
	</table>						
</body>
</html>';
						} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					} else echo '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
					die();
					break;
				default:
					break;
			}
		}
	}
	function subscribe($_popup_options, $_subscriber) {
		if (empty($_subscriber['{subscription-email}'])) return;
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		if ($popup_options['ontraport_enable'] == 'on') {
			$tags = str_replace(',', ';', preg_replace('/[^a-zA-Z0-9,;]/', '', $popup_options['ontraport_tag_id']));
			$sequences = str_replace(',', ';', preg_replace('/[^a-zA-Z0-9,;]/', '', $popup_options['ontraport_sequence_id']));
			$data = '
<contact>
<Group_Tag name="Contact Information">
<field name="First Name">'.$_subscriber['{subscription-name}'].'</field>
<field name="E-Mail">'.$_subscriber['{subscription-email}'].'</field>
<field name="Home Phone">'.$_subscriber['{subscription-phone}'].'</field>
</Group_Tag>
<Group_Tag name="Sequences and Tags">
<field name="Contact Tags" type="numeric">'.$tags.'</field>
<field name="Sequences" type="numeric">'.$sequences.'</field>
</Group_Tag>
</contact>';

			$data = urlencode(urlencode($data));

			$reqType= "add";
			$postargs = "appid=".$popup_options['ontraport_app_id']."&key=".$popup_options['ontraport_api_key']."&reqType=add&data=".$data;
			$request = "http://api.moon-ray.com/cdata.php";

			$session = curl_init($request);
			curl_setopt ($session, CURLOPT_POST, true);
			curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
			curl_setopt($session, CURLOPT_HEADER, false);
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($session);
			curl_close($session);
		}
	}
}
$ulp_ontraport = new ulp_ontraport_class();
?>