<?php
/* Newsletter2Go integration for Layered Popups */
class ulp_newsletter2go_class {
	var $default_popup_options = array(
		"newsletter2go_enable" => "off",
		"newsletter2go_auth_key" => "",
		"newsletter2go_username" => "",
		"newsletter2go_password" => "",
		"newsletter2go_access_token" => "",
		"newsletter2go_refresh_token" => "",
		"newsletter2go_list" => "",
		"newsletter2go_list_id" => "",
		"newsletter2go_fields" => array()
	);
	function __construct() {
		if (is_admin()) {
			add_action('ulp_popup_options_integration_show', array(&$this, 'popup_options_show'));
			add_filter('ulp_popup_options_check', array(&$this, 'popup_options_check'), 10, 1);
			add_filter('ulp_popup_options_populate', array(&$this, 'popup_options_populate'), 10, 1);
			add_action('wp_ajax_ulp-newsletter2go-lists', array(&$this, "show_lists"));
			add_action('wp_ajax_ulp-newsletter2go-fields', array(&$this, "show_fields"));
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
				<h3>'.__('Newsletter2Go Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Enable Newsletter2Go', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_newsletter2go_enable" name="ulp_newsletter2go_enable" '.($popup_options['newsletter2go_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to Newsletter2Go', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to Newsletter2Go.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('Auth Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_newsletter2go_auth_key" name="ulp_newsletter2go_auth_key" value="'.esc_html($popup_options['newsletter2go_auth_key']).'" class="widefat">
							<br /><em>'.__('Enter your Newsletter2Go Auth Key. You can get it <a href="https://ui.newsletter2go.com/api-client" target="_blank">here</a>.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('Username', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_newsletter2go_username" name="ulp_newsletter2go_username" value="'.esc_html($popup_options['newsletter2go_username']).'" class="widefat">
							<br /><em>'.__('Enter your Newsletter2Go Username. You can get it <a href="https://ui.newsletter2go.com/api-client" target="_blank">here</a>.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('Password', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_newsletter2go_password" name="ulp_newsletter2go_password" value="'.esc_html($popup_options['newsletter2go_password']).'" class="widefat">
							<br /><em>'.__('Enter your Newsletter2Go Password.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('List ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp-newsletter2go-list" name="ulp_newsletter2go_list" value="'.esc_html($popup_options['newsletter2go_list']).'" class="ulp-input-options ulp-input" readonly="readonly" onfocus="ulp_newsletter2go_lists_focus(this);" onblur="ulp_input_options_blur(this);" />
							<input type="hidden" id="ulp-newsletter2go-list-id" name="ulp_newsletter2go_list_id" value="'.esc_html($popup_options['newsletter2go_list_id']).'" />
							<div id="ulp-newsletter2go-list-items" class="ulp-options-list">
								<div class="ulp-options-list-data"></div>
								<div class="ulp-options-list-spinner"></div>
							</div>
							<br /><em>'.__('Enter your List ID.', 'ulp').'</em>
							<script>
								function ulp_newsletter2go_lists_focus(object) {
									ulp_input_options_focus(object, {"action": "ulp-newsletter2go-lists", "ulp_auth_key": jQuery("#ulp_newsletter2go_auth_key").val(), "ulp_username": jQuery("#ulp_newsletter2go_username").val(), "ulp_password": jQuery("#ulp_newsletter2go_password").val()});
								}
							</script>
						</td>
					</tr>
					<tr>
						<th>'.__('Fields', 'ulp').':</th>
						<td style="vertical-align: middle;">
							<div class="ulp-newsletter2go-fields-html">';
		if (!empty($popup_options['newsletter2go_auth_key']) && !empty($popup_options['newsletter2go_list_id'])) {
			$fields = $this->get_fields_html($popup_options['newsletter2go_auth_key'], $popup_options['newsletter2go_list_id'], $popup_options['newsletter2go_fields']);
			echo $fields;
		}
		echo '
							</div>
							<a id="ulp_newsletter2go_fields_button" class="ulp_button button-secondary" onclick="return ulp_newsletter2go_loadfields();">'.__('Load Fields', 'ulp').'</a>
							<img class="ulp-loading" id="ulp-newsletter2go-fields-loading" src="'.plugins_url('/images/loading.gif', dirname(__FILE__)).'">
							<br /><em>'.__('Click the button to (re)load fields list. Ignore if you do not need specify fields values.', 'ulp').'</em>
							<script>
								function ulp_newsletter2go_loadfields() {
									jQuery("#ulp-newsletter2go-fields-loading").fadeIn(350);
									jQuery(".ulp-newsletter2go-fields-html").slideUp(350);
									var data = {action: "ulp-newsletter2go-fields", ulp_key: jQuery("#ulp_newsletter2go_auth_key").val(), ulp_list: jQuery("#ulp-newsletter2go-list-id").val()};
									jQuery.post("'.admin_url('admin-ajax.php').'", data, function(return_data) {
										jQuery("#ulp-newsletter2go-fields-loading").fadeOut(350);
										try {
											var data = jQuery.parseJSON(return_data);
											var status = data.status;
											if (status == "OK") {
												jQuery(".ulp-newsletter2go-fields-html").html(data.html);
												jQuery(".ulp-newsletter2go-fields-html").slideDown(350);
											} else {
												jQuery(".ulp-newsletter2go-fields-html").html("<div class=\'ulp-newsletter2go-grouping\' style=\'margin-bottom: 10px;\'><strong>'.__('Internal error! Can not connect to Newsletter2Go server.', 'ulp').'</strong></div>");
												jQuery(".ulp-newsletter2go-fields-html").slideDown(350);
											}
										} catch(error) {
											jQuery(".ulp-newsletter2go-fields-html").html("<div class=\'ulp-newsletter2go-grouping\' style=\'margin-bottom: 10px;\'><strong>'.__('Internal error! Can not connect to Newsletter2Go server.', 'ulp').'</strong></div>");
											jQuery(".ulp-newsletter2go-fields-html").slideDown(350);
										}
									});
									return false;
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
		if (isset($ulp->postdata["ulp_newsletter2go_enable"])) $popup_options['newsletter2go_enable'] = "on";
		else $popup_options['newsletter2go_enable'] = "off";
		if ($popup_options['newsletter2go_enable'] == 'on') {
			if (empty($popup_options['newsletter2go_auth_key']) || strpos($popup_options['newsletter2go_auth_key'], '-') === false) $errors[] = __('Invalid Newsletter2Go Auth Key.', 'ulp');
			if (empty($popup_options['newsletter2go_list_id'])) $errors[] = __('Invalid Newsletter2Go List ID.', 'ulp');
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
		if (isset($ulp->postdata["ulp_newsletter2go_double"])) $popup_options['newsletter2go_double'] = "on";
		else $popup_options['newsletter2go_double'] = "off";
		if (isset($ulp->postdata["ulp_newsletter2go_enable"])) $popup_options['newsletter2go_enable'] = "on";
		else $popup_options['newsletter2go_enable'] = "off";
		
		$groups = array();
		foreach($ulp->postdata as $key => $value) {
			if (substr($key, 0, strlen('ulp_newsletter2go_group_')) == 'ulp_newsletter2go_group_') {
				$groups[] = substr($key, strlen('ulp_newsletter2go_group_'));
			}
		}
		$popup_options['newsletter2go_groups'] = implode(':', $groups);

		$fields = array();
		foreach($ulp->postdata as $key => $value) {
			if (substr($key, 0, strlen('ulp_newsletter2go_field_')) == 'ulp_newsletter2go_field_') {
				$field = substr($key, strlen('ulp_newsletter2go_field_'));
				$fields[$field] = stripslashes(trim($value));
			}
		}
		$popup_options['newsletter2go_fields'] = serialize($fields);
		
		return array_merge($_popup_options, $popup_options);
	}
	function subscribe($_popup_options, $_subscriber) {
		if (empty($_subscriber['{subscription-email}'])) return;
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		if ($popup_options['newsletter2go_enable'] == 'on') {
			$result = $this->connect($popup_options['newsletter2go_auth_key'], 'lists/'.urlencode($popup_options['newsletter2go_list_id']).'/members/'.md5(strtolower($_subscriber['{subscription-email}'])));
			$merge_fields = array();
			$interests = array();
			$status = '';
			if (array_key_exists('status', $result)) $status = $result['status'];
			if (array_key_exists('status', $result) && $result['status'] == 'pending') {
				$this->connect($popup_options['newsletter2go_auth_key'], 'lists/'.urlencode($popup_options['newsletter2go_list_id']).'/members/'.md5(strtolower($_subscriber['{subscription-email}'])), array(), 'DELETE');
			} else {
				if (array_key_exists('merge_fields', $result)) $merge_fields = $result['merge_fields'];
				if (array_key_exists('interests', $result)) $interests = $result['interests'];
			}
			
			$fields = array();
			if (!empty($popup_options['newsletter2go_fields'])) $fields = unserialize($popup_options['newsletter2go_fields']);
			if (!empty($fields) && is_array($fields)) {
				foreach ($fields as $key => $value) {
					if (!empty($value)) {
						$merge_fields[$key] = strtr($value, $_subscriber);
					}
				}
			}
			
			$interests_marked = explode(':', $popup_options['newsletter2go_groups']);
			if (!empty($interests_marked) && is_array($interests_marked)) {
				foreach ($interests_marked as $interest_marked) {
					if (!empty($interest_marked) && strpos($interest_marked, '-') !== false) {
						$key = null;
						list($tmp, $key) = explode("-", $interest_marked, 2);
						if (!empty($key)) $interests[$key] = true;
					}
				}
			}
			
			$data = array(
				'ip_signup' => $_SERVER['REMOTE_ADDR'],
				'email_address' => $_subscriber['{subscription-email}'],
				'status' => $popup_options['newsletter2go_double'] == 'on' ? (!empty($status) ? 'subscribed' : 'pending') : 'subscribed',
				'status_if_new' => $popup_options['newsletter2go_double'] == 'on' ? 'pending' : 'subscribed'
			);
			if (!empty($merge_fields)) {
				$data['merge_fields'] = $merge_fields;
			}
			if (!empty($interests)) {
				$data['interests'] = $interests;
			}
			$result = $this->connect($popup_options['newsletter2go_auth_key'], 'lists/'.urlencode($popup_options['newsletter2go_list_id']).'/members/'.md5(strtolower($_subscriber['{subscription-email}'])), $data, 'PUT');
		}
	}
	function show_lists() {
		global $wpdb;
		if (current_user_can('manage_options')) {
			$lists = array();
			if (!isset($_POST['ulp_auth_key']) || empty($_POST['ulp_auth_key']) || !isset($_POST['ulp_username']) || empty($_POST['ulp_username']) || !isset($_POST['ulp_password']) || empty($_POST['ulp_password'])) {
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '<div style="text-align: center; margin: 20px 0px;">'.__('Invalid API Credentials!', 'ulp').'</div>';
				echo json_encode($return_object);
				exit;
			}
			$key = trim(stripslashes($_POST['ulp_auth_key']));
			$username = trim(stripslashes($_POST['ulp_username']));
			$password = trim(stripslashes($_POST['ulp_password']));
			
			$data = array(
				'username' => $username,
				'password' => $password,
				'grant_type' => 'https://nl2go.com/jwt'
			);
			print_r($data);
			$result = $this->connect($key, '/oauth/v2/token', $data);
print_r($result);
exit;
			
			if (is_array($result) && array_key_exists('total_items', $result)) {
				if (intval($result['total_items']) > 0) {
					foreach ($result['lists'] as $list) {
						if (is_array($list)) {
							if (array_key_exists('id', $list) && array_key_exists('name', $list)) {
								$lists[$list['id']] = $list['name'];
							}
						}
					}
				} else {
					$return_object = array();
					$return_object['status'] = 'OK';
					$return_object['html'] = '<div style="text-align: center; margin: 20px 0px;">'.__('No Lists found!', 'ulp').'</div>';
					echo json_encode($return_object);
					exit;
				}
			} else {
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '<div style="text-align: center; margin: 20px 0px;">'.__('Invalid Auth Key!', 'ulp').'</div>';
				echo json_encode($return_object);
				exit;
			}
			$list_html = '';
			if (!empty($lists)) {
				foreach ($lists as $id => $name) {
					$list_html .= '<a href="#" data-id="'.esc_html($id).'" data-title="'.esc_html($id).(!empty($name) ? ' | '.esc_html($name) : '').'" onclick="return ulp_input_options_selected(this);">'.esc_html($id).(!empty($name) ? ' | '.esc_html($name) : '').'</a>';
				}
			} else $list_html .= '<div style="text-align: center; margin: 20px 0px;">'.__('No data found!', 'ulp').'</div>';
			$return_object = array();
			$return_object['status'] = 'OK';
			$return_object['html'] = $list_html;
			$return_object['items'] = sizeof($lists);
			echo json_encode($return_object);
		}
		exit;
	}
	function show_fields() {
		global $wpdb;
		if (current_user_can('manage_options')) {
			if (!isset($_POST['ulp_key']) || !isset($_POST['ulp_list']) || empty($_POST['ulp_key']) || empty($_POST['ulp_list'])) {
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '<div class="ulp-newsletter2go-grouping" style="margin-bottom: 10px;"><strong>'.__('Invalid Auth Key or List ID.', 'ulp').'</strong></div>';
				echo json_encode($return_object);
				exit;
			}
			$key = trim(stripslashes($_POST['ulp_key']));
			$list = trim(stripslashes($_POST['ulp_list']));
			$return_object = array();
			$return_object['status'] = 'OK';
			$return_object['html'] = $this->get_fields_html($key, $list, $this->default_popup_options['newsletter2go_fields']);
			echo json_encode($return_object);
		}
		exit;
	}
	function get_fields_html($_key, $_list, $_fields) {
		$result = $this->connect($_key, 'lists/'.urlencode($_list).'/merge-fields?count=100');
		$fields = '';
		$values = unserialize($_fields);
		if (!is_array($values)) $values = array();
		if (!empty($result) && is_array($result)) {
			if (array_key_exists('status', $result)) {
				$fields = '<div class="ulp-newsletter2go-grouping" style="margin-bottom: 10px;"><strong>'.$result['title'].'</strong></div>';
			} else {
				if (array_key_exists('total_items', $result) && $result['total_items'] > 0) {
					$fields = '
			'.__('Please adjust the fields below. You can use the same shortcodes (<code>{subscription-email}</code>, <code>{subscription-name}</code>, etc.) to associate Newsletter2Go fields with the popup fields.', 'ulp').'
			<table style="min-width: 280px; width: 50%;">';
					foreach ($result['merge_fields'] as $field) {
						if (is_array($field)) {
							if (array_key_exists('tag', $field) && array_key_exists('name', $field)) {
								$fields .= '
				<tr>
					<td style="width: 100px;"><strong>'.esc_html($field['tag']).':</strong></td>
					<td>
						<input type="text" id="ulp_newsletter2go_field_'.esc_html($field['tag']).'" name="ulp_newsletter2go_field_'.esc_html($field['tag']).'" value="'.esc_html(array_key_exists($field['tag'], $values) ? $values[$field['tag']] : '').'" class="widefat"'.($field['tag'] == 'EMAIL' ? ' readonly="readonly"' : '').' />
						<br /><em>'.esc_html($field['name']).'</em>
					</td>
				</tr>';
							}
						}
					}
					$fields .= '
			</table>';
				} else {
					$fields = '<div class="ulp-newsletter2go-grouping" style="margin-bottom: 10px;"><strong>'.__('No fields found.', 'ulp').'</strong></div>';
				}
			}
		} else {
			$fields = '<div class="ulp-newsletter2go-grouping" style="margin-bottom: 10px;"><strong>'.__('Inavlid server response.', 'ulp').'</strong></div>';
		}
		return $fields;
	}
	function connect($_auth_key, $_path, $_data = array(), $_method = '') {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Basic '.base64_decode($_auth_key)
		);
		$result = array(
			'http_code' => 0,
			'data' => array()
		);
		try {
			$url = 'https://api.newsletter2go.com/'.ltrim($_path, '/');
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			if (!empty($_data)) {
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($_data));
			}
			if (!empty($_method)) {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_method);
			}
			curl_setopt($curl, CURLOPT_TIMEOUT, 20);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
			//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($curl);
			print_r($response);
			$result['http_code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			$result['data'] = json_decode($response, true);
		} catch (Exception $e) {
		}
		return $result;
	}
}
$ulp_newsletter2go = new ulp_newsletter2go_class();
?>