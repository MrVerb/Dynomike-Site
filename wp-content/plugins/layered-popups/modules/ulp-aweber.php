<?php
/* AWeber integration for Layered Popups */
define('ULP_AWEBER_APPID', '0e193739');

class ulp_aweber_class {
	var $options = array(
		"aweber_consumer_key" => "",
		"aweber_consumer_secret" => "",
		"aweber_access_key" => "",
		"aweber_access_secret" => ""
	);
	var $default_popup_options = array(
		'aweber_enable' => "off",
		'aweber_listid' => ""
	);
	function __construct() {
		$this->get_options();
		if (is_admin()) {
			add_action('ulp_options_show', array(&$this, 'options_show'));
			add_action('wp_ajax_ulp_aweber_connect', array(&$this, "aweber_connect"));
			add_action('wp_ajax_ulp_aweber_disconnect', array(&$this, "aweber_disconnect"));
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
	function get_options() {
		foreach ($this->options as $key => $value) {
			$this->options[$key] = get_option('ulp_'.$key, $this->options[$key]);
		}
	}
	function update_options() {
		if (current_user_can('manage_options')) {
			foreach ($this->options as $key => $value) {
				update_option('ulp_'.$key, $value);
			}
		}
	}
	function populate_options() {
		foreach ($this->options as $key => $value) {
			if (isset($_POST['ulp_'.$key])) {
				$this->options[$key] = trim(stripslashes($_POST['ulp_'.$key]));
			}
		}
	}
	function options_show() {
		echo '
			<h3>'.__('AWeber Connection', 'ulp').'</h3>';
		$account = null;
		if ($this->options['aweber_access_secret']) {
			if (!class_exists('AWeberAPI')) {
				require_once(dirname(dirname(__FILE__)).'/aweber_api/aweber_api.php');
			}
			try {
				$aweber = new AWeberAPI($this->options['aweber_consumer_key'], $this->options['aweber_consumer_secret']);
				$account = $aweber->getAccount($this->options['aweber_access_key'], $this->options['aweber_access_secret']);
			} catch (AWeberException $e) {
				$account = null;
			}
		}
		if (!$account) {
			echo '
			<div id="ulp-aweber-connection">
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Authorization code', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_aweber_oauth_id" value="" class="widefat" placeholder="AWeber authorization code">
							<br /><em>Get your authorization code <a target="_blank" href="https://auth.aweber.com/1.0/oauth/authorize_app/'.ULP_AWEBER_APPID.'">'.__('here', 'ulp').'</a></em>.
						</td>
					</tr>
					<tr>
						<th></th>
						<td style="vertical-align: middle;">
							<input type="button" class="ulp_button button-secondary" value="'.__('Make Connection', 'ulp').'" onclick="return ulp_aweber_connect();" >
							<img id="ulp-aweber-loading" src="'.plugins_url('/images/loading.gif', dirname(__FILE__)).'">
						</td>
					</tr>
				</table>
			</div>';
		} else {
			echo '
			<div id="ulp-aweber-connection">
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Connected', 'ulp').':</th>
						<td>
							<input type="button" class="ulp_button button-secondary" value="'.__('Disconnect', 'ulp').'" onclick="return ulp_aweber_disconnect();" >
							<img id="ulp-aweber-loading" src="'.plugins_url('/images/loading.gif', dirname(__FILE__)).'">
							<br /><em>'.__('Click the button to disconnect.', 'ulp').'</em>
						</td>
					</tr>
				</table>
			</div>';
		}
		echo '
			<script>
				function ulp_aweber_connect() {
					jQuery("#ulp-aweber-loading").fadeIn(350);
					jQuery(".ulp-popup-form").find(".ulp-message").slideUp(350);
					var data = {action: "ulp_aweber_connect", ulp_aweber_oauth_id: jQuery("#ulp_aweber_oauth_id").val()};
					jQuery.post("'.admin_url('admin-ajax.php').'", data, function(return_data) {
						jQuery("#ulp-aweber-loading").fadeOut(350);
						try {
							//alert(return_data);
							var data = jQuery.parseJSON(return_data);
							var status = data.status;
							if (status == "OK") {
								jQuery("#ulp-aweber-connection").slideUp(350, function() {
									jQuery("#ulp-aweber-connection").html(data.html);
									jQuery("#ulp-aweber-connection").slideDown(350);
								});
							} else if (status == "ERROR") {
								jQuery(".ulp-popup-form").find(".ulp-message").html(data.message);
								jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
							} else {
								jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
								jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
							}
						} catch(error) {
							jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
							jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
						}
					});
					return false;
				}
				function ulp_aweber_disconnect() {
					jQuery("#ulp-aweber-loading").fadeIn(350);
					var data = {action: "ulp_aweber_disconnect"};
					jQuery.post("'.admin_url('admin-ajax.php').'", data, function(return_data) {
						jQuery("#ulp-aweber-loading").fadeOut(350);
						try {
							//alert(return_data);
							var data = jQuery.parseJSON(return_data);
							var status = data.status;
							if (status == "OK") {
								jQuery("#ulp-aweber-connection").slideUp(350, function() {
									jQuery("#ulp-aweber-connection").html(data.html);
									jQuery("#ulp-aweber-connection").slideDown(350);
								});
							} else if (status == "ERROR") {
								jQuery(".ulp-popup-form").find(".ulp-message").html(data.message);
								jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
							} else {
								jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
								jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
							}
						} catch(error) {
							jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
							jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
						}
					});
					return false;
				}
			</script>';
	}
	function aweber_connect() {
		global $wpdb;
		if (current_user_can('manage_options')) {
			if (!isset($_POST['ulp_aweber_oauth_id']) || empty($_POST['ulp_aweber_oauth_id'])) {
				$return_object = array();
				$return_object['status'] = 'ERROR';
				$return_object['message'] = __('Authorization Code not found.', 'ulp');
				echo json_encode($return_object);
				exit;
			}
			$code = trim(stripslashes($_POST['ulp_aweber_oauth_id']));
			if (!class_exists('AWeberAPI')) {
				require_once(dirname(dirname(__FILE__)).'/aweber_api/aweber_api.php');
			}
			$account = null;
			try {
				list($consumer_key, $consumer_secret, $access_key, $access_secret) = AWeberAPI::getDataFromAweberID($code);
			} catch (AWeberAPIException $exc) {
				list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
			} catch (AWeberOAuthDataMissing $exc) {
				list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
			} catch (AWeberException $exc) {
				list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
			}
			if (!$access_secret) {
				$return_object = array();
				$return_object['status'] = 'ERROR';
				$return_object['message'] = __('Invalid Authorization Code!', 'ulp');
				echo json_encode($return_object);
				exit;
			} else {
				try {
					$aweber = new AWeberAPI($consumer_key, $consumer_secret);
					$account = $aweber->getAccount($access_key, $access_secret);
				} catch (AWeberException $e) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = __('Can not access AWeber account!', 'ulp');
					echo json_encode($return_object);
					exit;
				}
			}
			update_option('ulp_aweber_consumer_key', $consumer_key);
			update_option('ulp_aweber_consumer_secret', $consumer_secret);
			update_option('ulp_aweber_access_key', $access_key);
			update_option('ulp_aweber_access_secret', $access_secret);
			$return_object = array();
			$return_object['status'] = 'OK';
			$return_object['html'] = '
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Connected', 'ulp').':</th>
						<td>
							<input type="button" class="ulp_button button-secondary" value="'.__('Disconnect', 'ulp').'" onclick="return ulp_aweber_disconnect();" >
							<img id="ulp-aweber-loading" src="'.plugins_url('/images/loading.gif', dirname(__FILE__)).'">
							<br /><em>'.__('Click the button to disconnect.', 'ulp').'</em>
						</td>
					</tr>
				</table>';
			echo json_encode($return_object);
			exit;
		}
		exit;
	}
	function aweber_disconnect() {
		global $wpdb;
		if (current_user_can('manage_options')) {
			update_option('ulp_aweber_consumer_key', "");
			update_option('ulp_aweber_consumer_secret', "");
			update_option('ulp_aweber_access_key', "");
			update_option('ulp_aweber_access_secret', "");
			$return_object = array();
			$return_object['status'] = 'OK';
			$return_object['html'] = '
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Authorization code', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_aweber_oauth_id" value="" class="widefat" placeholder="AWeber authorization code">
							<br />Get your authorization code <a target="_blank" href="https://auth.aweber.com/1.0/oauth/authorize_app/'.ULP_AWEBER_APPID.'">'.__('here', 'ulp').'</a>.
						</td>
					</tr>
					<tr>
						<th></th>
						<td style="vertical-align: middle;">
							<input type="button" class="ulp_button button-secondary" value="'.__('Make Connection', 'ulp').'" onclick="return ulp_aweber_connect();" >
							<img id="ulp-aweber-loading" src="'.plugins_url('/images/loading.gif', dirname(__FILE__)).'">
						</td>
					</tr>
				</table>';
			echo json_encode($return_object);
			exit;
		}
		exit;
	}
	function popup_options_show($_popup_options) {
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		echo '
				<h3>'.__('AWeber Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">';
		$account = null;
		if ($this->options['aweber_access_secret']) {
			if (!class_exists('AWeberAPI')) {
				require_once(dirname(dirname(__FILE__)).'/aweber_api/aweber_api.php');
			}
			try {
				$aweber = new AWeberAPI($this->options['aweber_consumer_key'], $this->options['aweber_consumer_secret']);
				$account = $aweber->getAccount($this->options['aweber_access_key'], $this->options['aweber_access_secret']);
			} catch (AWeberException $e) {
				$account = null;
			}
		}
		if (!$account) {
			echo '
					<tr>
						<th>'.__('Enable AWeber', 'ulp').':</th>
						<td>'.__('Please connect your AWeber account on <a target="_blank" href="admin.php?page=ulp-settings">Settings</a> page.', 'ulp').'</td>
					</tr>';
		} else {
			$lists = $account->lists;
            if (empty($lists)) {
				echo '
					<tr>
						<th>'.__('Enable AWeber', 'ulp').':</th>
						<td>'.__('This AWeber account does not currently have any lists.', 'ulp').'</td>
					</tr>';
			} else {
				echo '
					<tr>
						<th>'.__('Enable AWeber', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_aweber_enable" name="ulp_aweber_enable" '.($popup_options['aweber_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to AWeber', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to AWeber.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('List ID', 'ulp').':</th>
						<td>
							<select name="ulp_aweber_listid" class="ic_input_m">';
				foreach ($lists as $list) {
					echo '
								<option value="'.$list->id.'"'.($list->id == $popup_options['aweber_listid'] ? ' selected="selected"' : '').'>'.$list->name.'</option>';
				}
				echo '
							</select>
							<br /><em>'.__('Select your List ID.', 'ulp').'</em>
						</td>
					</tr>';
			}
		}
		echo '
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
		if (isset($ulp->postdata["ulp_aweber_enable"])) $popup_options['aweber_enable'] = "on";
		else $popup_options['aweber_enable'] = "off";
		if ($popup_options['aweber_enable'] == 'on') {
			if (empty($popup_options['aweber_listid'])) $errors[] = __('Invalid AWeber List ID.', 'ulp');
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
		if (isset($ulp->postdata["ulp_aweber_enable"])) $popup_options['aweber_enable'] = "on";
		else $popup_options['aweber_enable'] = "off";
		return array_merge($_popup_options, $popup_options);
	}
	function subscribe($_popup_options, $_subscriber) {
		if (empty($_subscriber['{subscription-email}'])) return;
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		if ($this->options['aweber_access_secret']) {
			if ($popup_options['aweber_enable'] == 'on') {
				$account = null;
				if (!class_exists('AWeberAPI')) {
					require_once(dirname(dirname(__FILE__)).'/aweber_api/aweber_api.php');
				}
				try {
					$aweber = new AWeberAPI($this->options['aweber_consumer_key'], $this->options['aweber_consumer_secret']);
					$account = $aweber->getAccount($this->options['aweber_access_key'], $this->options['aweber_access_secret']);
					$subscribers = $account->loadFromUrl('/accounts/' . $account->id . '/lists/' . $popup_options['aweber_listid'] . '/subscribers');
					$subscribers->create(array(
						'email' => $_subscriber['{subscription-email}'],
						'ip_address' => $_SERVER['REMOTE_ADDR'],
						'name' => $_subscriber['{subscription-name}'],
						'ad_tracking' => 'Layered Popups',
					));
				} catch (Exception $e) {
					$account = null;
				}
			}
		}
	}
}
$ulp_aweber = new ulp_aweber_class();
?>