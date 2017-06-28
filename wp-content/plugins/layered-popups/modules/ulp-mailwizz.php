<?php
/* MailWizz integration for Layered Popups */
class ulp_mailwizz_class {
	var $default_popup_options = array(
		'mailwizz_enable' => 'off',
		'mailwizz_api_url' => '',
		'mailwizz_public_key' => '',
		'mailwizz_private_key' => '',
		'mailwizz_list_id' => ''
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
				<h3>'.__('MailWizz Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Enable MailWizz', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_mailwizz_enable" name="ulp_mailwizz_enable" '.($popup_options['mailwizz_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to MailWizz', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to MailWizz.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API URL', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_mailwizz_api_url" name="ulp_mailwizz_api_url" value="'.esc_html($popup_options['mailwizz_api_url']).'" class="widefat" onchange="ulp_mailwizz_handler();">
							<br /><em>'.__('Enter your MailWizz API URL. If the MailWizz powered website does not use clean urls, make sure your API URL has the index.php part of url included.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API Public Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_mailwizz_public_key" name="ulp_mailwizz_public_key" value="'.esc_html($popup_options['mailwizz_public_key']).'" class="widefat" onchange="ulp_mailwizz_handler();">
							<br /><em>'.__('Enter your MailWizz API Public Key. You can generate it in MailWizz customer area.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('API Private Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_mailwizz_private_key" name="ulp_mailwizz_private_key" value="'.esc_html($popup_options['mailwizz_private_key']).'" class="widefat" onchange="ulp_mailwizz_handler();">
							<br /><em>'.__('Enter your MailWizz API Private Key. You can generate it in MailWizz customer area.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('List ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_mailwizz_list_id" name="ulp_mailwizz_list_id" value="'.esc_html($popup_options['mailwizz_list_id']).'" class="widefat">
							<br /><em>'.__('Enter your List ID. You can get List ID from', 'ulp').' <a href="'.admin_url('admin.php').'?action=ulp-mailwizz-lists&api_url='.base64_encode($popup_options['mailwizz_api_url']).'&public_key='.base64_encode($popup_options['mailwizz_public_key']).'&private_key='.base64_encode($popup_options['mailwizz_private_key']).'" class="thickbox" id="ulp_mailwizz_lists" title="'.__('Available Lists', 'ulp').'">'.__('this table', 'ulp').'</a>.</em>
							<script>
								function ulp_mailwizz_handler() {
									jQuery("#ulp_mailwizz_lists").attr("href", "'.admin_url('admin.php').'?action=ulp-mailwizz-lists&api_url="+ulp_encode64(jQuery("#ulp_mailwizz_api_url").val())+"&public_key="+ulp_encode64(jQuery("#ulp_mailwizz_public_key").val())+"&private_key="+ulp_encode64(jQuery("#ulp_mailwizz_private_key").val()));
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
		if (isset($ulp->postdata["ulp_mailwizz_enable"])) $popup_options['mailwizz_enable'] = "on";
		else $popup_options['mailwizz_enable'] = "off";
		if ($popup_options['mailwizz_enable'] == 'on') {
			if (strlen($popup_options['mailwizz_api_url']) == 0 || !preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $popup_options['mailwizz_api_url'])) $errors[] = __('Invalid MailWizz API URL.', 'ulp');
			if (empty($popup_options['mailwizz_public_key'])) $errors[] = __('Invalid MailWizz API Public Key.', 'ulp');
			if (empty($popup_options['mailwizz_private_key'])) $errors[] = __('Invalid MailWizz API Private Key.', 'ulp');
			if (empty($popup_options['mailwizz_list_id'])) $errors[] = __('Invalid MailWizz List ID.', 'ulp');
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
		if (isset($ulp->postdata["ulp_mailwizz_enable"])) $popup_options['mailwizz_enable'] = "on";
		else $popup_options['mailwizz_enable'] = "off";
		return array_merge($_popup_options, $popup_options);
	}
	function admin_request_handler() {
		global $wpdb;
		if (!empty($_GET['action'])) {
			switch($_GET['action']) {
				case 'ulp-mailwizz-lists':
					if (isset($_GET["api_url"]) && isset($_GET["public_key"]) && isset($_GET["private_key"])) {
						$api_url = base64_decode($_GET["api_url"]);
						$public_key = base64_decode($_GET["public_key"]);
						$private_key = base64_decode($_GET["private_key"]);
						$lists = $this->get_lists($api_url, $public_key, $private_key);
						if (!empty($lists)) {
							echo '
<html>
<head>
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
	<title>'.__('MailWizz Lists', 'ulp').'</title>
</head>
<body>
	<table style="width: 100%;">
		<tr>
			<td style="width: 170px; font-weight: bold;">'.__('List ID', 'ulp').'</td>
			<td style="font-weight: bold;">'.__('List Name', 'ulp').'</td>
		</tr>';
							foreach ($lists as $key => $value) {
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
		if ($popup_options['mailwizz_enable'] == 'on') {
			$url = rtrim($popup_options['mailwizz_api_url'], '/').'/lists/'.$popup_options['mailwizz_list_id'].'/subscribers/search-by-email?EMAIL='.urlencode($_subscriber['{subscription-email}']);
			$timestamp = time();
			$headers = array(
				'X-MW-PUBLIC-KEY' => $popup_options['mailwizz_public_key'],
				'X-MW-REMOTE-ADDR' => $_SERVER['REMOTE_ADDR'],
				'X-MW-TIMESTAMP' => $timestamp
			);
			ksort($headers, SORT_STRING);
			$signature_string = 'GET '.$url.'&'.http_build_query($headers, '', '&');
			$signature = hash_hmac('sha1', $signature_string, $popup_options['mailwizz_private_key'], false);
			$headers['X-MW-SIGNATURE'] = $signature;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_USERAGENT, 'MailWizzApi Client version 1.0');
			curl_setopt($ch, CURLOPT_AUTOREFERER , true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$curl_headers = array();
			foreach($headers as $name => $value) {
				$curl_headers[] = $name.': '.$value;
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
			$response = curl_exec($ch);
			if (curl_error($ch)) return;
			curl_close($ch);
			$result = json_decode($response, true);
			if(!$result) return;
			if ($result['status'] != 'success') {
				$url = rtrim($popup_options['mailwizz_api_url'], '/').'/lists/'.$popup_options['mailwizz_list_id'].'/subscribers';
				$timestamp = time();
				$post_data = array(
					'EMAIL' => $_subscriber['{subscription-email}'], 
					'FNAME' => $_subscriber['{subscription-name}'], 
					'LNAME' => ''
				);
				$headers = array(
					'X-MW-PUBLIC-KEY' => $popup_options['mailwizz_public_key'],
					'X-MW-REMOTE-ADDR' => $_SERVER['REMOTE_ADDR'],
					'X-MW-TIMESTAMP' => $timestamp
				);
				$signature_data = array_merge($headers, $post_data);
				ksort($signature_data, SORT_STRING);
				$signature_string = 'POST '.$url.'?'.http_build_query($signature_data, '', '&');
				$signature = hash_hmac('sha1', $signature_string, $popup_options['mailwizz_private_key'], false);
				$headers['X-MW-SIGNATURE'] = $signature;
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_USERAGENT, 'MailWizzApi Client version 1.0');
				curl_setopt($ch, CURLOPT_AUTOREFERER , true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				$curl_headers = array();
				foreach($headers as $name => $value) {
					$curl_headers[] = $name.': '.$value;
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
				curl_setopt($ch, CURLOPT_POST, 3);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data, '', '&'));
				$response = curl_exec($ch);
				curl_close($ch);
			} else {
				$url = rtrim($popup_options['mailwizz_api_url'], '/').'/lists/'.$popup_options['mailwizz_list_id'].'/subscribers/'.$result['data']['subscriber_uid'];
				$timestamp = time();
				$post_data = array(
					'EMAIL' => $_subscriber['{subscription-email}'], 
					'FNAME' => $_subscriber['{subscription-name}'], 
					'LNAME' => ''
				);
				$headers = array(
					'X-MW-PUBLIC-KEY' => $popup_options['mailwizz_public_key'],
					'X-MW-REMOTE-ADDR' => $_SERVER['REMOTE_ADDR'],
					'X-MW-TIMESTAMP' => $timestamp
				);
				$signature_data = array_merge($headers, $post_data);
				ksort($signature_data, SORT_STRING);
				$signature_string = 'PUT '.$url.'?'.http_build_query($signature_data, '', '&');
				$signature = hash_hmac('sha1', $signature_string, $popup_options['mailwizz_private_key'], false);
				$headers['X-MW-SIGNATURE'] = $signature;
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_USERAGENT, 'MailWizzApi Client version 1.0');
				curl_setopt($ch, CURLOPT_AUTOREFERER , true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				$curl_headers = array();
				$headers['X-HTTP-Method-Override'] = 'PUT';
				foreach($headers as $name => $value) {
					$curl_headers[] = $name.': '.$value;
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data, '', '&'));
				$response = curl_exec($ch);
				curl_close($ch);
			}
		}
	}
	function get_lists($api_url, $public_key, $private_key) {
		$lists = array();
		$url = rtrim($api_url, '/').'/lists?page=1&per_page=9999';
		$timestamp = time();
		$headers = array(
			'X-MW-PUBLIC-KEY' => $public_key,
			'X-MW-REMOTE-ADDR' => $_SERVER['REMOTE_ADDR'],
			'X-MW-TIMESTAMP' => $timestamp
		);
		ksort($headers, SORT_STRING);
		$signature_string = 'GET '.$url.'&'.http_build_query($headers, '', '&');
		$signature = hash_hmac('sha1', $signature_string, $private_key, false);
		$headers['X-MW-SIGNATURE'] = $signature;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MailWizzApi Client version 1.0');
		curl_setopt($ch, CURLOPT_AUTOREFERER , true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$curl_headers = array();
		foreach($headers as $name => $value) {
			$curl_headers[] = $name.': '.$value;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		$response = curl_exec($ch);
					
		if (curl_error($ch)) return array();
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode != '200') return array();
		curl_close($ch);
					
		$result = json_decode($response, true);
		if(!$result) return array();
		if ($result['status'] != 'success') return array();
		if ($result['data']['count'] > 0)
		$lists = array();
		foreach ($result['data']['records'] as $key => $value) {
			$lists[$value['general']['list_uid']] = $value['general']['name'];
		}
		return $lists;
	}
}
$ulp_mailwizz = new ulp_mailwizz_class();
?>