<?php
/* MailChimp integration for Layered Popups */
class ulp_mailchimp_class {
	var $default_popup_options = array(
		"mailchimp_enable" => "off",
		"mailchimp_api_key" => "",
		"mailchimp_list" => "",
		"mailchimp_list_id" => "",
		"mailchimp_groups" => "",
		"mailchimp_fields" => "",
		"mailchimp_double" => "off"
	);
	function __construct() {
		$this->default_popup_options['mailchimp_fields'] = serialize(array('EMAIL' => '{subscription-email}', 'FNAME' => '{subscription-name}', 'NAME' => '{subscription-name}'));
		if (is_admin()) {
			add_action('ulp_popup_options_integration_show', array(&$this, 'popup_options_show'));
			add_filter('ulp_popup_options_check', array(&$this, 'popup_options_check'), 10, 1);
			add_filter('ulp_popup_options_populate', array(&$this, 'popup_options_populate'), 10, 1);
			add_action('wp_ajax_ulp-mailchimp-lists', array(&$this, "show_lists"));
			add_action('wp_ajax_ulp-mailchimp-groups', array(&$this, "show_groups"));
			add_action('wp_ajax_ulp-mailchimp-fields', array(&$this, "show_fields"));
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
				<h3>'.__('MailChimp Parameters', 'ulp').'</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>'.__('Enable MailChimp', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_mailchimp_enable" name="ulp_mailchimp_enable" '.($popup_options['mailchimp_enable'] == "on" ? 'checked="checked"' : '').'"> '.__('Submit contact details to MailChimp', 'ulp').'
							<br /><em>'.__('Please tick checkbox if you want to submit contact details to MailChimp.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('MailChimp API Key', 'ulp').':</th>
						<td>
							<input type="text" id="ulp_mailchimp_api_key" name="ulp_mailchimp_api_key" value="'.esc_html($popup_options['mailchimp_api_key']).'" class="widefat">
							<br /><em>'.__('Enter your MailChimp API Key. You can get it <a href="https://admin.mailchimp.com/account/api-key-popup" target="_blank">here</a>.', 'ulp').'</em>
						</td>
					</tr>
					<tr>
						<th>'.__('List ID', 'ulp').':</th>
						<td>
							<input type="text" id="ulp-mailchimp-list" name="ulp_mailchimp_list" value="'.esc_html($popup_options['mailchimp_list']).'" class="ulp-input-options ulp-input" readonly="readonly" onfocus="ulp_mailchimp_lists_focus(this);" onblur="ulp_input_options_blur(this);" />
							<input type="hidden" id="ulp-mailchimp-list-id" name="ulp_mailchimp_list_id" value="'.esc_html($popup_options['mailchimp_list_id']).'" />
							<div id="ulp-mailchimp-list-items" class="ulp-options-list">
								<div class="ulp-options-list-data"></div>
								<div class="ulp-options-list-spinner"></div>
							</div>
							<br /><em>'.__('Enter your List ID.', 'ulp').'</em>
							<script>
								function ulp_mailchimp_lists_focus(object) {
									ulp_input_options_focus(object, {"action": "ulp-mailchimp-lists", "ulp_api_key": jQuery("#ulp_mailchimp_api_key").val()});
								}
							</script>
						</td>
					</tr>
					<tr>
						<th>'.__('Fields', 'ulp').':</th>
						<td style="vertical-align: middle;">
							<div class="ulp-mailchimp-fields-html">';
		if (!empty($popup_options['mailchimp_api_key']) && !empty($popup_options['mailchimp_list_id'])) {
			$fields = $this->get_fields_html($popup_options['mailchimp_api_key'], $popup_options['mailchimp_list_id'], $popup_options['mailchimp_fields']);
			echo $fields;
		}
		echo '
							</div>
							<a id="ulp_mailchimp_fields_button" class="ulp_button button-secondary" onclick="return ulp_mailchimp_loadfields();">'.__('Load Fields', 'ulp').'</a>
							<img class="ulp-loading" id="ulp-mailchimp-fields-loading" src="'.plugins_url('/images/loading.gif', dirname(__FILE__)).'">
							<br /><em>'.__('Click the button to (re)load fields list. Ignore if you do not need specify fields values.', 'ulp').'</em>
							<script>
								function ulp_mailchimp_loadfields() {
									jQuery("#ulp-mailchimp-fields-loading").fadeIn(350);
									jQuery(".ulp-mailchimp-fields-html").slideUp(350);
									var data = {action: "ulp-mailchimp-fields", ulp_key: jQuery("#ulp_mailchimp_api_key").val(), ulp_list: jQuery("#ulp-mailchimp-list-id").val()};
									jQuery.post("'.admin_url('admin-ajax.php').'", data, function(return_data) {
										jQuery("#ulp-mailchimp-fields-loading").fadeOut(350);
										try {
											var data = jQuery.parseJSON(return_data);
											var status = data.status;
											if (status == "OK") {
												jQuery(".ulp-mailchimp-fields-html").html(data.html);
												jQuery(".ulp-mailchimp-fields-html").slideDown(350);
											} else {
												jQuery(".ulp-mailchimp-fields-html").html("<div class=\'ulp-mailchimp-grouping\' style=\'margin-bottom: 10px;\'><strong>'.__('Internal error! Can not connect to MailChimp server.', 'ulp').'</strong></div>");
												jQuery(".ulp-mailchimp-fields-html").slideDown(350);
											}
										} catch(error) {
											jQuery(".ulp-mailchimp-fields-html").html("<div class=\'ulp-mailchimp-grouping\' style=\'margin-bottom: 10px;\'><strong>'.__('Internal error! Can not connect to MailChimp server.', 'ulp').'</strong></div>");
											jQuery(".ulp-mailchimp-fields-html").slideDown(350);
										}
									});
									return false;
								}
							</script>
						</td>
					</tr>
					<tr>
						<th>'.__('Groups', 'ulp').':</th>
						<td style="vertical-align: middle;">
							<div class="ulp-mailchimp-groups-html">';
		if (!empty($popup_options['mailchimp_api_key']) && !empty($popup_options['mailchimp_list_id'])) {
			$groups = $this->get_groups_html($popup_options['mailchimp_api_key'], $popup_options['mailchimp_list_id'], $popup_options['mailchimp_groups']);
			echo $groups;
		}
		echo '
							</div>
							<a id="ulp_mailchimp_groups_button" class="ulp_button button-secondary" onclick="return ulp_mailchimp_loadgroups();">'.__('Load Groups', 'ulp').'</a>
							<img class="ulp-loading" id="ulp-mailchimp-groups-loading" src="'.plugins_url('/images/loading.gif', dirname(__FILE__)).'">
							<br /><em>'.__('Click the button to (re)load groups of the list. Ignore if you do not use groups.', 'ulp').'</em>
							<script>
								function ulp_mailchimp_loadgroups() {
									jQuery("#ulp-mailchimp-groups-loading").fadeIn(350);
									jQuery(".ulp-mailchimp-groups-html").slideUp(350);
									var data = {action: "ulp-mailchimp-groups", ulp_key: jQuery("#ulp_mailchimp_api_key").val(), ulp_list: jQuery("#ulp-mailchimp-list-id").val()};
									jQuery.post("'.admin_url('admin-ajax.php').'", data, function(return_data) {
										jQuery("#ulp-mailchimp-groups-loading").fadeOut(350);
										try {
											var data = jQuery.parseJSON(return_data);
											var status = data.status;
											if (status == "OK") {
												jQuery(".ulp-mailchimp-groups-html").html(data.html);
												jQuery(".ulp-mailchimp-groups-html").slideDown(350);
											} else {
												jQuery(".ulp-mailchimp-groups-html").html("<div class=\'ulp-mailchimp-grouping\' style=\'margin-bottom: 10px;\'><strong>'.__('Internal error! Can not connect to MailChimp server.', 'ulp').'</strong></div>");
												jQuery(".ulp-mailchimp-groups-html").slideDown(350);
											}
										} catch(error) {
											jQuery(".ulp-mailchimp-groups-html").html("<div class=\'ulp-mailchimp-grouping\' style=\'margin-bottom: 10px;\'><strong>'.__('Internal error! Can not connect to MailChimp server.', 'ulp').'</strong></div>");
											jQuery(".ulp-mailchimp-groups-html").slideDown(350);
										}
									});
									return false;
								}
							</script>
						</td>
					</tr>
					<tr>
						<th>'.__('Double opt-in', 'ulp').':</th>
						<td>
							<input type="checkbox" id="ulp_mailchimp_double" name="ulp_mailchimp_double" '.($popup_options['mailchimp_double'] == "on" ? 'checked="checked"' : '').'"> '.__('Ask users to confirm their subscription', 'ulp').'
							<br /><em>'.__('Control whether a double opt-in confirmation message is sent.', 'ulp').'</em>
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
		if (isset($ulp->postdata["ulp_mailchimp_enable"])) $popup_options['mailchimp_enable'] = "on";
		else $popup_options['mailchimp_enable'] = "off";
		if ($popup_options['mailchimp_enable'] == 'on') {
			if (empty($popup_options['mailchimp_api_key']) || strpos($popup_options['mailchimp_api_key'], '-') === false) $errors[] = __('Invalid MailChimp API Key.', 'ulp');
			if (empty($popup_options['mailchimp_list_id'])) $errors[] = __('Invalid MailChimp List ID.', 'ulp');
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
		if (isset($ulp->postdata["ulp_mailchimp_double"])) $popup_options['mailchimp_double'] = "on";
		else $popup_options['mailchimp_double'] = "off";
		if (isset($ulp->postdata["ulp_mailchimp_enable"])) $popup_options['mailchimp_enable'] = "on";
		else $popup_options['mailchimp_enable'] = "off";
		
		$groups = array();
		foreach($ulp->postdata as $key => $value) {
			if (substr($key, 0, strlen('ulp_mailchimp_group_')) == 'ulp_mailchimp_group_') {
				$groups[] = substr($key, strlen('ulp_mailchimp_group_'));
			}
		}
		$popup_options['mailchimp_groups'] = implode(':', $groups);

		$fields = array();
		foreach($ulp->postdata as $key => $value) {
			if (substr($key, 0, strlen('ulp_mailchimp_field_')) == 'ulp_mailchimp_field_') {
				$field = substr($key, strlen('ulp_mailchimp_field_'));
				$fields[$field] = stripslashes(trim($value));
			}
		}
		$popup_options['mailchimp_fields'] = serialize($fields);
		
		return array_merge($_popup_options, $popup_options);
	}
	function subscribe($_popup_options, $_subscriber) {
		if (empty($_subscriber['{subscription-email}'])) return;
		$popup_options = array_merge($this->default_popup_options, $_popup_options);
		if ($popup_options['mailchimp_enable'] == 'on') {
			$result = $this->connect($popup_options['mailchimp_api_key'], 'lists/'.urlencode($popup_options['mailchimp_list_id']).'/members/'.md5(strtolower($_subscriber['{subscription-email}'])));
			$merge_fields = array();
			$interests = array();
			$status = '';
			if (array_key_exists('status', $result)) $status = $result['status'];
			if (array_key_exists('status', $result) && $result['status'] == 'pending') {
				$this->connect($popup_options['mailchimp_api_key'], 'lists/'.urlencode($popup_options['mailchimp_list_id']).'/members/'.md5(strtolower($_subscriber['{subscription-email}'])), array(), 'DELETE');
			} else {
				if (array_key_exists('merge_fields', $result)) $merge_fields = $result['merge_fields'];
				if (array_key_exists('interests', $result)) $interests = $result['interests'];
			}
			
			$fields = array();
			if (!empty($popup_options['mailchimp_fields'])) $fields = unserialize($popup_options['mailchimp_fields']);
			if (!empty($fields) && is_array($fields)) {
				foreach ($fields as $key => $value) {
					if (!empty($value)) {
						$merge_fields[$key] = strtr($value, $_subscriber);
					}
				}
			}
			
			$interests_marked = explode(':', $popup_options['mailchimp_groups']);
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
				'status' => $popup_options['mailchimp_double'] == 'on' ? (!empty($status) ? 'subscribed' : 'pending') : 'subscribed',
				'status_if_new' => $popup_options['mailchimp_double'] == 'on' ? 'pending' : 'subscribed'
			);
			if (!empty($merge_fields)) {
				$data['merge_fields'] = $merge_fields;
			}
			if (!empty($interests)) {
				$data['interests'] = $interests;
			}
			$result = $this->connect($popup_options['mailchimp_api_key'], 'lists/'.urlencode($popup_options['mailchimp_list_id']).'/members/'.md5(strtolower($_subscriber['{subscription-email}'])), $data, 'PUT');
		}
	}
	function show_lists() {
		global $wpdb;
		if (current_user_can('manage_options')) {
			$lists = array();
			if (!isset($_POST['ulp_api_key']) || empty($_POST['ulp_api_key'])) {
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '<div style="text-align: center; margin: 20px 0px;">'.__('Invalid API Key!', 'ulp').'</div>';
				echo json_encode($return_object);
				exit;
			}
			$key = trim(stripslashes($_POST['ulp_api_key']));
			
			$result = $this->connect($key, 'lists?count=100');
			
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
				$return_object['html'] = '<div style="text-align: center; margin: 20px 0px;">'.__('Invalid API Key!', 'ulp').'</div>';
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
	function show_groups() {
		global $wpdb;
		if (current_user_can('manage_options')) {
			if (!isset($_POST['ulp_key']) || !isset($_POST['ulp_list']) || empty($_POST['ulp_key']) || empty($_POST['ulp_list'])) {
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.__('Invalid API Key or List ID.', 'ulp').'</strong></div>';
				echo json_encode($return_object);
				exit;
			}
			$key = trim(stripslashes($_POST['ulp_key']));
			$list = trim(stripslashes($_POST['ulp_list']));
			$return_object = array();
			$return_object['status'] = 'OK';
			$return_object['html'] = $this->get_groups_html($key, $list, '');
			echo json_encode($return_object);
		}
		exit;
	}
	function get_groups_html($_key, $_list, $_groups) {
		$result = $this->connect($_key, 'lists/'.urlencode($_list).'/interest-categories?count=100');
		$groups = '';
		$groups_marked = explode(':', $_groups);
		if (!empty($result) && is_array($result)) {
			if (array_key_exists('status', $result)) {
				$groups = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.$result['title'].'</strong></div>';
			} else {
				if (array_key_exists('total_items', $result) && $result['total_items'] > 0) {
					foreach ($result['categories'] as $category) {
						$result2 = $this->connect($_key, 'lists/'.urlencode($_list).'/interest-categories/'.$category['id'].'/interests?count=100');
						if (!empty($result2) && is_array($result2) && array_key_exists('total_items', $result2) && $result2['total_items'] > 0) {
							$groups .= '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.$category['title'].'</strong>';
							foreach ($result2['interests'] as $interest) {
								$groups .= '<div class="ulp-mailchimp-group" style="margin: 1px 0 1px 10px;"><input type="checkbox" name="ulp_mailchimp_group_'.$category['id'].'-'.$interest['id'].'"'.(in_array($category['id'].'-'.$interest['id'], $groups_marked) ? ' checked="checked"' : '').' /> '.$interest['name'].'</div>';
							}
							$groups .= '</div>';
						}
					}
				} else {
					$groups = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.__('No groups found.', 'ulp').'</strong></div>';
				}
			}
		} else {
			$groups = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.__('Inavlid server response.', 'ulp').'</strong></div>';
		}
		return $groups;
	}
	function show_fields() {
		global $wpdb;
		if (current_user_can('manage_options')) {
			if (!isset($_POST['ulp_key']) || !isset($_POST['ulp_list']) || empty($_POST['ulp_key']) || empty($_POST['ulp_list'])) {
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.__('Invalid API Key or List ID.', 'ulp').'</strong></div>';
				echo json_encode($return_object);
				exit;
			}
			$key = trim(stripslashes($_POST['ulp_key']));
			$list = trim(stripslashes($_POST['ulp_list']));
			$return_object = array();
			$return_object['status'] = 'OK';
			$return_object['html'] = $this->get_fields_html($key, $list, $this->default_popup_options['mailchimp_fields']);
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
				$fields = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.$result['title'].'</strong></div>';
			} else {
				if (array_key_exists('total_items', $result) && $result['total_items'] > 0) {
					$fields = '
			'.__('Please adjust the fields below. You can use the same shortcodes (<code>{subscription-email}</code>, <code>{subscription-name}</code>, etc.) to associate MailChimp fields with the popup fields.', 'ulp').'
			<table style="min-width: 280px; width: 50%;">';
					foreach ($result['merge_fields'] as $field) {
						if (is_array($field)) {
							if (array_key_exists('tag', $field) && array_key_exists('name', $field)) {
								$fields .= '
				<tr>
					<td style="width: 100px;"><strong>'.esc_html($field['tag']).':</strong></td>
					<td>
						<input type="text" id="ulp_mailchimp_field_'.esc_html($field['tag']).'" name="ulp_mailchimp_field_'.esc_html($field['tag']).'" value="'.esc_html(array_key_exists($field['tag'], $values) ? $values[$field['tag']] : '').'" class="widefat"'.($field['tag'] == 'EMAIL' ? ' readonly="readonly"' : '').' />
						<br /><em>'.esc_html($field['name']).'</em>
					</td>
				</tr>';
							}
						}
					}
					$fields .= '
			</table>';
				} else {
					$fields = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.__('No fields found.', 'ulp').'</strong></div>';
				}
			}
		} else {
			$fields = '<div class="ulp-mailchimp-grouping" style="margin-bottom: 10px;"><strong>'.__('Inavlid server response.', 'ulp').'</strong></div>';
		}
		return $fields;
	}
	function connect($_api_key, $_path, $_data = array(), $_method = '') {
		$dc = "us1";
		if (strstr($_api_key, "-")) {
			list($key, $dc) = explode("-", $_api_key, 2);
			if (!$dc) $dc = "us1";
		}
		$headers = array(
			'Content-Type: application/json;charset=UTF-8',
			'Accept: application/json'
		);
		try {
			$url = 'https://'.$dc.'.api.mailchimp.com/3.0/'.ltrim($_path, '/');
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, 'layered-popups:'.$_api_key);
			if (!empty($_data)) {
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($_data));
			}
			if (!empty($_method)) {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_method);
			}
			curl_setopt($curl, CURLOPT_TIMEOUT, 120);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
			//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($curl);
			curl_close($curl);
			$result = json_decode($response, true);
		} catch (Exception $e) {
			$result = false;
		}
		return $result;
	}
}
$ulp_mailchimp = new ulp_mailchimp_class();
?>