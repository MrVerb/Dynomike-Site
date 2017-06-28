<?php
/* Advanced Targeting for Layered Popups */
define('ULP_TARGETS_POSTS_PER_PAGE', 9);

class ulp_class_targeting {
	var $default_target = array(
		'id' => 0,
		'popup' => '',
		'popup_mobile' => 'same',
		'options' => array(
			'mode' => 'every-time',
			'mode_period' => 5,
			'delay' => 0,
			'close_delay' => 0,
			'offset' => 600
		),
		'post_type' => 'sitewide',
		'taxonomies' => array(),
		'posts' => array(),
		'posts_all' => true
	);
	function __construct() {
		
	}
	
	static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix."ulp_targets";
		if($wpdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
			$sql = "CREATE TABLE ".$table_name." (
				id int(11) NOT NULL auto_increment,
				event varchar(31) collate latin1_general_cs NULL,
				popup varchar(255) collate latin1_general_cs NULL,
				popup_mobile varchar(255) collate latin1_general_cs NULL,
				options longtext collate utf8_unicode_ci NULL,
				post_type varchar(255) collate utf8_unicode_ci NULL,
				taxonomies longtext collate utf8_unicode_ci NULL,
				posts longtext collate utf8_unicode_ci NULL,
				posts_all int(11) NULL default '1',
				priority int(11) NULL default '50',
				language varchar(255) collate utf8_unicode_ci NULL,
				created int(11) NULL,
				active int(11) NULL default '0',
				deleted int(11) NULL default '0',
				UNIQUE KEY  id (id)
			);";
			$wpdb->query($sql);
		}
		$version = get_option('ulp_version');
		if ($version === false) {
			update_option('ulp_ext_advanced_targeting', 'on');
			update_option('ulp_ext_advanced_targeting_converted', 'on');
		}
	}

	static function deactivate() {
		global $wpdb;
		$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix."ulp_targets";
		$wpdb->query($sql);
	}
	
	function admin_page() {
		global $wpdb, $ulp;
		$post_types = get_post_types(array('public' => true), 'names');
		if (isset($_REQUEST['event']) && array_key_exists($_REQUEST['event'], $ulp->events)) $event = $_REQUEST['event'];
		else $event = 'onload';
		$language_filter = '';
		if (defined('ICL_LANGUAGE_CODE')) {
			if (ICL_LANGUAGE_CODE != 'all') $language_filter = " AND t1.language IN ('all', '".esc_sql(ICL_LANGUAGE_CODE)."')";
		}
		$rows = $wpdb->get_results("SELECT t1.*, t2.title as popup_title, t3.title as popup_mobile_title FROM ".$wpdb->prefix."ulp_targets t1 LEFT JOIN ".$wpdb->prefix."ulp_popups t2 ON t2.str_id = t1.popup LEFT JOIN ".$wpdb->prefix."ulp_popups t3 ON t3.str_id = t1.popup_mobile WHERE t1.deleted = '0' AND t1.active = '1' AND t1.event = '".esc_sql($event)."' AND t1.post_type IN ('sitewide', '".implode("','", $post_types)."')".$language_filter." ORDER BY t1.priority ASC", ARRAY_A);
		echo '
		<div class="ulp-targets-global-message"></div>
		<div class="wrap ulp">
			<h2>
				Layered Popups - Targeting
				<a class="add-new-h2" href="#" onclick="return ulp_targets_window_open(\''.$event.'\', 0);">'.__('Create New Target', 'ulp').'</a>
				<a class="add-new-h2" target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(0);">'.__('Help', 'ulp').'</a>
			</h2>
			<!--<a class="button" href="#" onclick="return ulp_targets_window_open();">'.__('Create New Target', 'ulp').'</a>-->
			<div class="ulp-targets-events">';
		foreach ($ulp->events as $key => $value) {
			echo '
				<a class="ulp-targets-event-item'.($key == $event ? ' ulp-targets-event-item-selected' : '').'" href="'.admin_url('admin.php').'?page=ulp-targeting&event='.$key.'" title="'.esc_html($value['description']).'"><i class="fa '.($key == $event ? 'fa-dot-circle-o' : 'fa-circle-o').'"></i> '.esc_html($value['label']).'</a>';
		}
		echo '
			</div>
			<div class="ulp-options ulp-targets-page">
				<h2>'.__('Active Targets', 'ulp').'</h2>
				<div class="ulp-targets-list" id="ulp-targets-list-active">
					<div class="ulp-targets-noitems-message" style="'.(sizeof($rows) > 0 ? ' display: none;' : ' display: block;').'">'.sprintf(__('Drop existing target here or %screate%s new one.', 'ulp'), '<a href="#" onclick="return ulp_targets_window_open(\''.$event.'\', 0);">', '</a>').'</div>';
		foreach($rows as $row) {
			$target = $this->parse_target($row);
			$filter_html = $this->get_list_item_html($target);
			echo $filter_html;
		}
		echo '
				</div>
			</div>';
		$rows = $wpdb->get_results("SELECT t1.*, t2.title as popup_title, t3.title as popup_mobile_title FROM ".$wpdb->prefix."ulp_targets t1 LEFT JOIN ".$wpdb->prefix."ulp_popups t2 ON t2.str_id = t1.popup LEFT JOIN ".$wpdb->prefix."ulp_popups t3 ON t3.str_id = t1.popup_mobile WHERE t1.deleted = '0' AND t1.active = '0' AND t1.event = '".esc_sql($event)."' AND t1.post_type IN ('sitewide', '".implode("','", $post_types)."')".$language_filter." ORDER BY t1.created DESC", ARRAY_A);
		echo '
			<div class="ulp-options ulp-targets-page">
				<h2>'.__('Passive Targets', 'ulp').'</h2>
				<div class="ulp-targets-list" id="ulp-targets-list-passive">
					<div class="ulp-targets-noitems-message" style="'.(sizeof($rows) > 0 ? ' display: none;' : ' display: block;').'">'.__('Drop existing target here to disable it.', 'ulp').'</div>';
		foreach($rows as $row) {
			$target = $this->parse_target($row);
			$filter_html = $this->get_list_item_html($target);
			echo $filter_html;
		}
		echo '
				</div>
			</div>
		</div>
		<div class="ulp-targets-overlay"></div>
		<div class="ulp-targets-window">
			<div class="ulp-targets-window-title">
				<a href="#" onclick="return ulp_targets_window_close();">×</a>
				<h2>'.sprintf(__('Edit Target (%s Popup)', 'ulp'), $ulp->events[$event]['label']).'</h2>
			</div>
			<div class="ulp-targets-window-content">
				<div id="ulp-targets-window-content">
				</div>
				<div class="ulp-targets-window-buttons">
					<a class="ulp-targets-button" id="ulp-targets-save" href="#" onclick="return ulp_targets_save();"><i class="fa fa-check"></i> '.__('Save Target', 'ulp').'</a>
				</div>
			</div>
		</div>
		<input type="hidden" id="ulp-targets-event" value="'.esc_html($event).'">
		<div class="ulp-targets-intro-overlay"></div>
		<a href="#" id="ulp-targets-intro-close" onclick="return ulp_targets_intro_step_close();">×</a>
		<div class="ulp-targets-intro" id="ulp-targets-intro-0">
			<div class="ulp-targets-intro-next"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(1);"><i class="fa fa-angle-right"></i></a></div>
			<div class="ulp-targets-intro-content ulp-targets-intro-start">
				<h3>Introducing Trageting</h3>
				<span>Starting version 6.10 we improved flexibility of settings related to how and where to display popups. We call it "Targeting". Spend a minute to understand how to use new feature.</span>
				<a class="ulp-targets-intro-begin" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(1);"><i class="fa  fa-angle-double-right"></i> Let\'s begin!</a>
			</div>
		</div>
		<div class="ulp-targets-intro" id="ulp-targets-intro-1">
			<div class="ulp-targets-intro-prev"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(0);"><i class="fa fa-angle-left"></i></a></div>
			<div class="ulp-targets-intro-next"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(2);"><i class="fa fa-angle-right"></i></a></div>
			<div class="ulp-targets-intro-content">
				<div class="ulp-targets-intro-step-number">1</div>
				<div class="ulp-targets-intro-step-description">
					<h3>Select behavior trigger</h3>
					<span>By other words you need choose what event will trigger the popup. It can be OnLoad, OnScroll, OnExit, OnInactivity or OnAdblockDetected events.</span>
					<img src="'.$ulp->plugins_url.'/images/intro-step-1.png" />
				</div>
			</div>
		</div>
		<div class="ulp-targets-intro" id="ulp-targets-intro-2">
			<div class="ulp-targets-intro-prev"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(1);"><i class="fa fa-angle-left"></i></a></div>
			<div class="ulp-targets-intro-next"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(3);"><i class="fa fa-angle-right"></i></a></div>
			<div class="ulp-targets-intro-content">
				<div class="ulp-targets-intro-step-number">2</div>
				<div class="ulp-targets-intro-step-description">
					<h3>Click button to create new target</h3>
					<img src="'.$ulp->plugins_url.'/images/intro-step-2.png" />
				</div>
			</div>
		</div>
		<div class="ulp-targets-intro" id="ulp-targets-intro-3">
			<div class="ulp-targets-intro-prev"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(2);"><i class="fa fa-angle-left"></i></a></div>
			<div class="ulp-targets-intro-next"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(4);"><i class="fa fa-angle-right"></i></a></div>
			<div class="ulp-targets-intro-content">
				<div class="ulp-targets-intro-step-number">3</div>
				<div class="ulp-targets-intro-step-description">
					<h3>Select popups</h3>
					<span>For better user experience you can choose popups for desktops/laptops and tablets/mobiles separately.</span>
					<img src="'.$ulp->plugins_url.'/images/intro-step-3.png" />
				</div>
			</div>
		</div>
		<div class="ulp-targets-intro" id="ulp-targets-intro-4">
			<div class="ulp-targets-intro-prev"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(3);"><i class="fa fa-angle-left"></i></a></div>
			<div class="ulp-targets-intro-next"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(5);"><i class="fa fa-angle-right"></i></a></div>
			<div class="ulp-targets-intro-content">
				<div class="ulp-targets-intro-step-number">4</div>
				<div class="ulp-targets-intro-step-description">
					<h3>Configure event (trigger) parameters</h3>
					<span>Each event (trigger) has its own parameters.</span>
					<img src="'.$ulp->plugins_url.'/images/intro-step-4.png" />
				</div>
			</div>
		</div>
		<div class="ulp-targets-intro" id="ulp-targets-intro-5">
			<div class="ulp-targets-intro-prev"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(4);"><i class="fa fa-angle-left"></i></a></div>
			<div class="ulp-targets-intro-next"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(6);"><i class="fa fa-angle-right"></i></a></div>
			<div class="ulp-targets-intro-content">
				<div class="ulp-targets-intro-step-number">5</div>
				<div class="ulp-targets-intro-step-description">
					<h3>Adjust filter</h3>
					<span>Choose the part of website WHERE you want to see the popup. It can be posts, pages, products and even any custom post types filtered by any available taxonomies.</span>
					<img src="'.$ulp->plugins_url.'/images/intro-step-5.png" />
				</div>
			</div>
		</div>
		<div class="ulp-targets-intro" id="ulp-targets-intro-6">
			<div class="ulp-targets-intro-prev"><a target="_blank" href="https://layeredpopups.com/documentation/#targeting" onclick="return ulp_targets_intro_step_open(5);"><i class="fa fa-angle-left"></i></a></div>
			<div class="ulp-targets-intro-content">
				<div class="ulp-targets-intro-step-number">6</div>
				<div class="ulp-targets-intro-step-description">
					<h3>Sort active targets</h3>
					<span>The upper target has higher priority. If certain page match to several targets simultaneously, then target with higher priority will be applied to this page.</span>
					<img src="'.$ulp->plugins_url.'/images/intro-step-6.png" />
				</div>
			</div>
		</div>
		<script>jQuery(document).ready(function(){ulp_tragets_ready();});</script>';
	}
	function admin_load() {
		global $wpdb, $ulp;
		$return_object = array();
		if (current_user_can('manage_options')) {
			if (isset($_REQUEST['ulp-id'])) $target = $this->fetch_target($_REQUEST['ulp-id']);
			else $target = $this->default_target;
			if (isset($_REQUEST['ulp-event']) && array_key_exists($_REQUEST['ulp-event'], $ulp->events)) $event = $_REQUEST['ulp-event'];
			else $event = 'onload';
			$html = '
			<input type="hidden" name="ulp_event" value="'.esc_html($event).'">'.($target['id'] > 0 ? '<input type="hidden" id="ulp-id" name="ulp_id" value="'.$target['id'].'">' : '').'
			<div class="ulp-targets-window-content-row">
				<h3>'.__('Step 1: Select popups', 'ulp').'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-48pct">
					<strong>'.__('For desktops:', 'ulp').'</strong><br />
					<select id="ulp-popup" name="ulp_popup">';
			$popups = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ulp_popups WHERE deleted = '0' ORDER BY title ASC", ARRAY_A);
			$checked = false;
			if (sizeof($popups) > 0) {
				$html .= '
								<option disabled="disabled">--------- '.__('Popups', 'ulp').' ---------</option>';
				foreach($popups as $popup) {
					if ($target['popup'] == $popup['str_id']) {
						$checked = true;
						$html .= '
								<option value="'.$popup['str_id'].'" selected="selected">'.esc_html($popup['title']).($popup['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					} else {
						$html .= '
								<option value="'.$popup['str_id'].'">'.esc_html($popup['title']).($popup['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					}
				}
			}
			$campaigns = $wpdb->get_results("SELECT t1.*, t2.popups FROM ".$wpdb->prefix."ulp_campaigns t1 JOIN (SELECT COUNT(*) AS popups, tt1.campaign_id FROM ".$wpdb->prefix."ulp_campaign_items tt1 JOIN ".$wpdb->prefix."ulp_popups tt2 ON tt2.id = tt1.popup_id WHERE tt1.deleted = '0' AND tt2.deleted = '0' GROUP BY tt1.campaign_id) t2 ON t2.campaign_id = t1.id WHERE t1.deleted = '0' AND t2.popups > 0 ORDER BY t1.title ASC", ARRAY_A);
			if (sizeof($campaigns) > 0) {
				$html .= '
								<option disabled="disabled">--------- '.__('A/B Campaigns', 'ulp').' ---------</option>';
				foreach($campaigns as $campaign) {
					if ($target['popup'] == $campaign['str_id']) {
						$checked = true;
						$html .= '
								<option value="'.$campaign['str_id'].'" selected="selected">'.esc_html($campaign['title']).($campaign['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					} else {
						$html .= '
									<option value="'.$campaign['str_id'].'">'.esc_html($campaign['title']).($campaign['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					}
				}
			}
			if (sizeof($popups) > 0 || sizeof($campaigns) > 0) {
				$html .= '
								<option disabled="disabled">------------------</option>';
			}
			$html .= '
								<option value=""'.(!$checked ? ' selected="selected"' : '').'>'.__('None (disabled)', 'ulp').'</option>
							</select>
				</div>
				<div class="ulp-targets-window-content-column ulp-targets-48pct">
							<strong>'.__('For mobile devices:', 'ulp').'</strong><br />
							<select id="ulp_popup_mobile" name="ulp_popup_mobile">';
			//$popups = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ulp_popups WHERE deleted = '0' ORDER BY title ASC", ARRAY_A);
			$checked = false;
			if (sizeof($popups) > 0) {
				$html .= '
								<option disabled="disabled">--------- '.__('Popups', 'ulp').' ---------</option>';
				foreach($popups as $popup) {
					if ($target['popup_mobile'] == $popup['str_id']) {
						$checked = true;
						$html .= '
								<option value="'.$popup['str_id'].'" selected="selected">'.esc_html($popup['title']).($popup['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					} else {
						$html .= '
								<option value="'.$popup['str_id'].'">'.esc_html($popup['title']).($popup['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					}
				}
			}
			//$campaigns = $wpdb->get_results("SELECT t1.*, t2.popups FROM ".$wpdb->prefix."ulp_campaigns t1 JOIN (SELECT COUNT(*) AS popups, tt1.campaign_id FROM ".$wpdb->prefix."ulp_campaign_items tt1 JOIN ".$wpdb->prefix."ulp_popups tt2 ON tt2.id = tt1.popup_id WHERE tt1.deleted = '0' AND tt2.deleted = '0' GROUP BY tt1.campaign_id) t2 ON t2.campaign_id = t1.id WHERE t1.deleted = '0' AND t2.popups > 0 ORDER BY t1.title ASC", ARRAY_A);
			if (sizeof($campaigns) > 0) {
				$html .= '
								<option disabled="disabled">--------- '.__('A/B Campaigns', 'ulp').' ---------</option>';
				foreach($campaigns as $campaign) {
					if ($target['popup_mobile'] == $campaign['str_id']) {
						$checked = true;
						$html .= '
								<option value="'.$campaign['str_id'].'" selected="selected">'.esc_html($campaign['title']).($campaign['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					} else {
						$html .= '
								<option value="'.$campaign['str_id'].'">'.esc_html($campaign['title']).($campaign['blocked'] == 1 ? ' '.__('[blocked]', 'ulp') : '').'</option>';
					}
				}
			}
			if (sizeof($popups) > 0 || sizeof($campaigns) > 0) {
				$html .= '
								<option disabled="disabled">------------------</option>';
			}
			if ($target['popup_mobile'] == 'same') {
				$checked = true;
				$html .= '
								<option value="same" selected="selected">'.__('Same as for desktops', 'ulp').'</option>';
			} else {
				$html .= '
								<option value="same">'.__('Same as for desktops', 'ulp').'</option>';
			}
			$html .= '
								<option value=""'.(!$checked ? ' selected="selected"' : '').'>'.__('None (disabled)', 'ulp').'</option>
							</select>
				</div>
			</div>
			<div class="ulp-targets-window-content-row">
				<h3>'.__('Step 2: How often?', 'ulp').'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">';
			foreach ($ulp->display_modes as $key => $value) {
				if ($key != 'none') {
					$value = str_replace('%X', '<input type="text" name="ulp_options_mode_period" id="ulp_options_mode_period" class="ulp-targets-input-number" value="'.$target['options']['mode_period'].'">', $value);
					$html .= '
					<div class="ulp-targets-mode-item"><input type="radio" name="ulp_options_mode" id="ulp_options_mode" value="'.$key.'"'.($target['options']['mode'] == $key ? ' checked="checked"' : '').'> '.$value.'</div>';
				}
			}
			$html .= '
				</div>
			</div>';
			switch ($event) {
				case 'onload':
					$html .= '
			<div class="ulp-targets-window-content-row">
				<h3>'.__('Step 3: Set start delay', 'ulp').'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">
					<input type="text" name="ulp_options_delay" value="'.esc_html($target['options']['delay']).'" class="ulp-targets-input-number" placeholder="0"> '.__('seconds', 'ulp').'
					<br /><em>'.__('Popup appears with this delay after page loaded. Set "0" for immediate start.', 'ulp').'</em>
				</div>
			</div>
			<div class="ulp-targets-window-content-row">
				<h3>'.__('Step 4: Set autoclose delay', 'ulp').'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">
					<input type="text" name="ulp_options_close_delay" value="'.esc_html($target['options']['close_delay']).'" class="ulp-targets-input-number" placeholder="0"> '.__('seconds', 'ulp').'
					<br /><em>'.__('Popup is automatically closed after this period of time. Set "0", if you do not need autoclosing.', 'ulp').'</em>
				</div>
			</div>';
					$step = 5;
					break;
				case 'onscroll':
					$onscroll_units = 'px';
					if (strpos($target['options']['offset'], '%') !== false) {
						$onscroll_units = '%';
						$target['options']['offset'] = intval($target['options']['offset']);
						if ($target['options']['offset'] > 100) $target['options']['offset'] = 100;
					}
					$html .= '
			<div class="ulp-targets-window-content-row">
				<h3>'.__('Step 3: Set scrolling offset', 'ulp').'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">
					<input type="text" id="ulp_options_offset" name="ulp_options_offset" value="'.esc_html($target['options']['offset']).'" class="ulp-targets-input-number" placeholder="600" style="vertical-align: top;">
					<select id="ulp_onscroll_units" name="ulp_onscroll_units" style="width: 80px; min-width: 80px; height: 30px; line-height: 30px;" onchange="ulp_onscroll_units_changed();">
						<option value=""'.($onscroll_units != '%' ? ' selected="selected"' : '').'>pixels</option>
						<option value="%"'.($onscroll_units == '%' ? ' selected="selected"' : '').'>%</option>
					</select>
					<br /><em>'.__('Popup appears when user scroll down to this number of pixels or percents.', 'ulp').'</em>
				</div>
				<script>
					var ulp_onscroll_offset = "";
					function ulp_onscroll_units_changed() {
						if (jQuery("#ulp_onscroll_units").val() == "%") {
							ulp_tmp = jQuery("#ulp_options_offset").val();
							if (ulp_onscroll_offset == "") ulp_onscroll_offset = ulp_tmp;
							if (ulp_onscroll_offset > 100) ulp_onscroll_offset = 100;
							jQuery("#ulp_options_offset").val(ulp_onscroll_offset);
							ulp_onscroll_offset = ulp_tmp;
						} else {
							ulp_tmp = jQuery("#ulp_onscroll_offset").val();
							if (ulp_onscroll_offset != "") jQuery("#ulp_options_offset").val(ulp_onscroll_offset);
							ulp_onscroll_offset = ulp_tmp;
						}
					}
				</script>
			</div>';
					$step = 4;
					break;
				case 'onidle':
					$html .= '
			<div class="ulp-targets-window-content-row">
				<h3>'.__('Step 3: Set period of inactivity', 'ulp').'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">
					<input type="text" name="ulp_options_delay" value="'.esc_html($target['options']['delay']).'" class="ulp-targets-input-number" placeholder="0"> '.__('seconds', 'ulp').'
					<br /><em>'.__('Popup appears after this period of inactivity.', 'ulp').'</em>
				</div>
			</div>';
					$step = 4;
					break;
				default:
					$step = 3;
					break;
			}
			$html .= '
			<div class="ulp-targets-window-content-row" id="ulp-targets-post-types">
				<h3>'.sprintf(__('Step %s: Where to display the popup?', 'ulp'), $step).'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">';
			$post_types = get_post_types(array('public' => true), 'objects');
			//$step = 5;
			$html .= '
					<input type="hidden" id="ulp-post-type" name="ulp_post_type" value="'.esc_html($target['post_type']).'">
					<a href="#" class="ulp-targets-post-type ulp-targets-input-item'.($target['post_type'] == 'sitewide' ? ' ulp-targets-input-item-selected' : '').'" id="ulp-targets-post-type-sitewide" onclick="return ulp_targets_post_type_selected(this, '.$step.');"><i class="fa '.($target['post_type'] == 'sitewide' ? 'fa-dot-circle-o' : 'fa-circle-o').'"></i> '.__('Sitewide', 'ulp').'</a>';
			foreach ($post_types as $key => $post_type) {
				if ($key != 'attachment') {
					$html .= '
					<a href="#" class="ulp-targets-post-type ulp-targets-input-item'.($target['post_type'] == $key ? ' ulp-targets-input-item-selected' : '').'" id="ulp-targets-post-type-'.esc_html($key).'" onclick="return ulp_targets_post_type_selected(this, '.$step.');"><i class="fa '.($target['post_type'] == $key ? 'fa-dot-circle-o' : 'fa-circle-o').'"></i> '.esc_html($post_type->label).'</a>';
				}
			}
			$html .= '
				</div>
			</div>';
			$taxonomies_html = $this->get_taxonomies_html(5, $target);
			$html .= '
			<div id="ulp-targets-window-content-taxonomies">'.$taxonomies_html.'</div>
			<div id="ulp-targets-window-content-loading"></div>
			<div id="ulp-targets-window-content-errors"></div>';
			
			
			$return_object = array(
				'status' => 'OK',
				'html' => $html
			);
		} else {
			$return_object = array(
				'status' => 'ERROR',
				'message' => __('You do not have enough priveleges to perform this action.', 'ulp')
			);
		}
		echo json_encode($return_object);
		exit;
	}
	function admin_get_taxonomies() {
		global $wpdb, $ulp;
		$return_object = array();
		if (current_user_can('manage_options')) {
			$target = array();
			$target = array_merge($this->default_target, $target);
			$post_types = get_post_types(array('public' => true));
			if (isset($_REQUEST['ulp-post-type']) && in_array($_REQUEST['ulp-post-type'], $post_types)) $target['post_type'] = $_REQUEST['ulp-post-type'];
			else $target['post_type'] = 'sitewide';
			if (isset($_REQUEST['ulp-step'])) $step = intval($_REQUEST['ulp-step']);
			else $step = 0;
			
			$html = $this->get_taxonomies_html($step, $target);
			
			$return_object = array(
				'status' => 'OK',
				'html' => $html
			);
		} else {
			$return_object = array(
				'status' => 'ERROR',
				'message' => __('You do not have enough priveleges to perform this action.', 'ulp')
			);
		}
		echo json_encode($return_object);
		exit;
	}
	function get_taxonomies_html($_step, $_target) {
		global $wpdb, $ulp;
		$html = '';
		$step = $_step + 1;
		$target = array_merge($this->default_target, $_target);
		if ($target['post_type'] == 'sitewide') return '';
		
		$taxonomies = get_object_taxonomies($target['post_type'], 'object');
		$next_step = $step;
		foreach ($taxonomies as $key => $taxonomy) {
			if (!$taxonomy->public) continue;
			if ($key == 'post_format') continue;
			$next_step++;
		}
		foreach ($taxonomies as $key => $taxonomy) {
			if (!$taxonomy->public) continue;
			if ($key == 'post_format') continue;
			$selected_terms = array();
			if (array_key_exists($key, $target['taxonomies']) && is_array($target['taxonomies'][$key])) $selected_terms = $target['taxonomies'][$key];
			$selected = false;
			$terms = get_terms($key, array('hide_empty' => false));
			if (!empty($selected_terms)) {
				foreach ($terms as $term) {
					if (in_array($term->slug, $selected_terms)) {
						$selected = true;
						break;
					}
				}
			}
			$html .= '
			<div class="ulp-targets-window-content-row ulp-targets-taxonomies'.(!$selected ? ' ulp-targets-disabled' : '').'">
				<h3>'.sprintf(__('Step %s: Select %s', 'ulp'), $step, $taxonomy->label).'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">
					<input type="hidden" class="ulp-taxonomy-'.$key.'" id="ulp-taxonomy-'.$key.'-all" name="ulp_taxonomy_'.$key.'_all" value="'.(!$selected ? 'on' : 'off').'">
					<a href="#" class="ulp-targets-taxonomy-'.$key.' ulp-targets-input-item'.(!$selected ? ' ulp-targets-input-item-selected' : '').'" id="ulp-targets-taxonomy-'.$key.'-all" onclick="return ulp_targets_taxonomy_selected(this, \''.$key.'\');"><i class="fa '.(!$selected ? ' fa-check-square-o' : 'fa-square-o').'"></i> '.$taxonomy->labels->all_items.'</a>';
			foreach ($terms as $term) {
				$html .= '
					<input type="hidden" class="ulp-taxonomy-'.$key.'" id="ulp-taxonomy-'.$key.'-'.$term->slug.'" name="ulp_taxonomy_'.$key.'_'.$term->slug.'" value="'.(in_array($term->slug, $selected_terms) ? 'on' : 'off').'">
					<a href="#" class="ulp-targets-taxonomy-'.$key.' ulp-targets-input-item'.(in_array($term->slug, $selected_terms) ? ' ulp-targets-input-item-selected' : '').'" id="ulp-targets-taxonomy-'.$key.'-'.$term->slug.'" onclick="return ulp_targets_taxonomy_selected(this, \''.$key.'\');"><i class="fa '.(in_array($term->slug, $selected_terms) ? 'fa-check-square-o' : 'fa-square-o').'"></i> '.esc_html($term->name).'</a>';
			}
			$html .= '
				</div>
			</div>';
			$step++;
		}
		$post_type = get_post_type_object($target['post_type']);
		$posts_data = $this->get_posts_html($target, 0);
		$html .= '
			<input type="hidden" id="ulp-targets-next-offset" value="'.$posts_data['next_offset'].'">
			<div class="ulp-targets-window-content-row ulp-targets-posts">
				<h3>'.sprintf(__('Step %s: Select %s', 'ulp'), $step, $post_type->labels->name).'</h3>
				<div class="ulp-targets-window-content-column ulp-targets-3pct"></div>
				<div class="ulp-targets-window-content-column ulp-targets-96pct">
					<input type="hidden" id="ulp-post-all" name="ulp_post_all" value="'.($target['posts_all'] ? 'on' : 'off').'">
					<a href="#" class="ulp-targets-input-item'.($target['posts_all'] ? ' ulp-targets-input-item-selected' : '').'" id="ulp-targets-post-all" onclick="return ulp_targets_post_all_selected();"><i class="fa '.($target['posts_all'] ? ' fa-check-square-o' : 'fa-square-o').'"></i> '.sprintf(__('All %s', 'ulp'), strtolower($post_type->labels->name)).'</a>
					<div id="ulp-targets-window-posts-container"><div id="ulp-targets-window-posts-container-overlay"'.($target['posts_all'] ? ' style="display: block;"' : ' style="display: none;"').'></div><div id="ulp-targets-window-content-posts">'.$posts_data['html'].'</div></div>
				</div>
			</div>';
		return $html;
	}
	function admin_get_posts() {
		global $wpdb, $ulp;
		$return_object = array();
		if (current_user_can('manage_options')) {
			if (isset($_REQUEST['ulp-offset'])) $offset = intval($_REQUEST['ulp-offset']);
			else $offset = 0;
			if ($offset < 0) {
				echo json_encode(array('status' => 'OK', 'html' => ''));
				exit;
			}
			if (isset($_REQUEST['ulp-id'])) $target = $this->fetch_target($_REQUEST['ulp-id']);
			else $target = $this->default_target;
			$post_types = get_post_types(array('public' => true));
			if (isset($_REQUEST['ulp-post-type']) && in_array($_REQUEST['ulp-post-type'], $post_types)) $target['post_type'] = $_REQUEST['ulp-post-type'];
			else $target['post_type'] = 'sitewide';
			
			$target['taxonomies'] = array();
			$taxonomies = get_object_taxonomies($target['post_type'], 'object');
			foreach ($taxonomies as $key => $taxonomy) {
				if (!$taxonomy->public) continue;
				if ($key == 'post_format') continue;
				$terms = get_terms($key, array('hide_empty' => false));
				foreach ($terms as $term) {
					if (isset($_REQUEST['ulp_taxonomy_'.$key.'_'.$term->slug]) && $_REQUEST['ulp_taxonomy_'.$key.'_'.$term->slug] == 'on') {
						$target['taxonomies'][$key][] = $term->slug;
					}
				}
			}
			
			$posts_data = $this->get_posts_html($target, $offset);
			
			$return_object = array(
				'status' => 'OK',
				'html' => $posts_data['html'],
				'next_offset' => $posts_data['next_offset']
			);
		} else {
			$return_object = array(
				'status' => 'ERROR',
				'message' => __('You do not have enough priveleges to perform this action.', 'ulp')
			);
		}
		echo json_encode($return_object);
		exit;
	}
	function get_posts_html($_target, $_offset = 0) {
		global $wpdb, $ulp;
		$html = '';
		$target = array_merge($this->default_target, $_target);
		if ($target['post_type'] == 'sitewide') return '';
		$args = array(
			'post_type' => $target['post_type'],
			'order' => 'DESC',
			'orderby' => 'date'
		);
		$taxonomies = get_object_taxonomies($target['post_type'], 'object');
		foreach ($taxonomies as $key => $taxonomy) {
			if (!$taxonomy->public) continue;
			if ($key == 'post_format') continue;
			$tax_query = array(
				'taxonomy' => $key,
				'field' => 'slug',
				'terms' => array()
			);
			$terms = get_terms($key, array('hide_empty' => false));
			foreach ($terms as $term) {
				if (in_array($term->slug, $target['taxonomies'][$key])) {
					$tax_query['terms'][] = $term->slug;
				}
			}
			if (sizeof($tax_query['terms']) > 0) $args['tax_query'][] = $tax_query;
		}
		$posts_found = false;
		$next_offset = -1;
		if ($_offset == 0) {
			if (sizeof($target['posts']) > 0) {
				$args['post__in'] = $target['posts'];
				$args['nopaging'] = true;
				$query = new WP_Query($args);
				foreach ($query->posts as $post) {
					$html .= '
			<div class="ulp-targets-posts-item">
				<input type="hidden" id="ulp-post-'.$post->ID.'" name="ulp_post_'.$post->ID.'" value="on">
				<a href="#" id="ulp-targets-post-'.$post->ID.'" onclick="return ulp_targets_post_selected('.$post->ID.');"><i class="fa fa-check-square-o"></i> '.(empty($post->post_title) ? __('No title', 'ulp') : esc_html($post->post_title)).' (ID: '.$post->ID.', Status: '.ucfirst($post->post_status).')</a>
			</div>';
				}
				if ($query->found_posts > 0) $posts_found = true;
				unset($args['post__in']);
			}
		}
		$args['nopaging'] = false;
		$args['posts_per_page'] = ULP_TARGETS_POSTS_PER_PAGE;
		$args['offset'] = $_offset;
		if (sizeof($target['posts']) > 0) {
			$args['post__not_in'] = $target['posts'];
		}
		$query = new WP_Query($args);
		//if ($_offset > 0) print_r($query);
		foreach ($query->posts as $post) {
			$html .= '
			<div class="ulp-targets-posts-item">
				<input type="hidden" id="ulp-post-'.$post->ID.'" name="ulp_post_'.$post->ID.'" value="off">
				<a href="#" id="ulp-targets-post-'.$post->ID.'" onclick="return ulp_targets_post_selected('.$post->ID.');"><i class="fa fa-square-o"></i> '.(empty($post->post_title) ? __('No title', 'ulp') : esc_html($post->post_title)).' (ID: '.$post->ID.', Status: '.ucfirst($post->post_status).')</a>
			</div>';
		}
		if ($query->found_posts > 0) $posts_found = true;
		if (!$posts_found) {
			if ($_offset == 0) {
				$html = '<div id="ulp-targets-window-noposts">'.__('Nothing found.', 'ulp').'</div>';
			} else {
				$html .= '';
			}
		} else {
			if ($query->query_vars['offset'] + $query->post_count < $query->found_posts) $next_offset = $query->query_vars['offset'] + $query->post_count;
		}
		return array('html' => $html, 'next_offset' => $next_offset);
	}
	function admin_save() {
		global $wpdb, $ulp;
		if (current_user_can('manage_options')) {
			if (isset($_REQUEST['ulp_id'])) $target = $this->fetch_target($_REQUEST['ulp_id']);
			else $target = $this->default_target;
			$event = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['ulp_event']);
			$target['popup'] = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['ulp_popup']);
			$target['popup_mobile'] = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['ulp_popup_mobile']);
			foreach ($target['options'] as $key => $value) {
				if (isset($_REQUEST['ulp_options_'.$key])) $target['options'][$key] = trim($_REQUEST['ulp_options_'.$key]);
				else unset($target['options'][$key]);
			}
			$target['post_type'] = $_REQUEST['ulp_post_type'];
			$target['taxonomies'] = array();
			$target['posts'] = array();
			if ($target['post_type'] != 'sitewide') {
				$taxonomies = get_object_taxonomies($target['post_type'], 'object');
				foreach ($taxonomies as $key => $taxonomy) {
					if (!$taxonomy->public) continue;
					if ($key == 'post_format') continue;
					if (isset($_REQUEST['ulp_taxonomy_'.$key.'_all']) && $_REQUEST['ulp_taxonomy_'.$key.'_all'] == 'on') {
						$target['taxonomies'][$key] = 'all';
					} else {
						$terms = get_terms($key, array('hide_empty' => false));
						foreach ($terms as $term) {
							if (isset($_REQUEST['ulp_taxonomy_'.$key.'_'.$term->slug]) && $_REQUEST['ulp_taxonomy_'.$key.'_'.$term->slug] == 'on') {
								$target['taxonomies'][$key][] = $term->slug;
							}
						}
						if (!isset($target['taxonomies'][$key]) || empty($target['taxonomies'][$key])) $target['taxonomies'][$key] = 'all';
					}
				}
				$target['posts_all'] = $_REQUEST['ulp_post_all'] == 'on' ? true : false;
				if (!$target['posts_all']) {
					foreach ($_REQUEST as $key => $value) {
						if (strpos($key, 'ulp_post_') !== false) {
							$post_id = substr($key, strlen('ulp_post_'));
							if ($post_id != 'all' && $value == 'on') $target['posts'][] = $post_id;
						}
					}
					if (empty($target['posts'])) $target['posts_all'] = true;
				}
			}
			$errors = array();
			switch($event) {
				case 'onload':
					if (strlen($target['options']['delay']) > 0 && $target['options']['delay'] != preg_replace('/[^0-9]/', '', $target['options']['delay'])) $errors[] = __('Invalid start delay value.', 'ulp');
					if (strlen($target['options']['close_delay']) > 0 && $target['options']['close_delay'] != preg_replace('/[^0-9]/', '', $target['options']['close_delay'])) $errors[] = __('Invalid autoclosing delay value.', 'ulp');
					if (strlen($target['options']['mode_period']) == 0 || $target['options']['mode_period'] != preg_replace('/[^0-9]/', '', $target['options']['mode_period']) || intval($target['options']['mode_period']) < 1) $errors[] = __('Invalid cookie period.', 'ulp');
					break;
				case 'onscroll':
					if (strlen($target['options']['offset']) > 0 && $target['options']['offset'] != preg_replace('/[^0-9]/', '', $target['options']['offset'])) $errors[] = __('Invalid scrolling offset value.', 'ulp');
					else {
						if ($_REQUEST["ulp_onscroll_units"] == '%') {
							if ($target['options']['offset'] > 100) $target['options']['offset'] = '100';
							$target['options']['offset'] .= '%';
						}
					}
					break;
				case 'onidle':
					if (strlen($target['options']['delay']) > 0 && $target['options']['delay'] != preg_replace('/[^0-9]/', '', $target['options']['delay'])) $errors[] = __('Invalid period of inactivity value.', 'ulp');
					break;
				default:
					break;
			}
			
			if (!empty($errors)) {
				$return_object = array(
					'status' => 'ERROR',
					'message' => implode('<br />', $errors)
				);
				echo json_encode($return_object);
				exit;
			}
			if ($target['id'] > 0) {
				$sql = "UPDATE ".$wpdb->prefix."ulp_targets SET
					popup = '".esc_sql($target['popup'])."',
					popup_mobile = '".esc_sql($target['popup_mobile'])."',
					options = '".esc_sql(serialize($target['options']))."',
					post_type = '".esc_sql($target['post_type'])."',
					taxonomies = '".esc_sql(serialize($target['taxonomies']))."',
					posts = '".esc_sql(serialize($target['posts']))."',
					posts_all = '".esc_sql(intval($target['posts_all']))."'
					WHERE id = '".$target['id']."'";
				$message = __('Target successfully updated.', 'ulp');
				$wpdb->query($sql);
				$action = 'update';
			} else {
				if (defined('ICL_LANGUAGE_CODE')) $language = ICL_LANGUAGE_CODE;
				else $language = 'all';
				$sql = "INSERT INTO ".$wpdb->prefix."ulp_targets (
					event, popup, popup_mobile, options, post_type, taxonomies, posts, posts_all, priority, language, created, active, deleted) VALUES (
					'".esc_sql($event)."',
					'".esc_sql($target['popup'])."',
					'".esc_sql($target['popup_mobile'])."',
					'".esc_sql(serialize($target['options']))."',
					'".esc_sql($target['post_type'])."',
					'".esc_sql(serialize($target['taxonomies']))."',
					'".esc_sql(serialize($target['posts']))."',
					'".esc_sql(intval($target['posts_all']))."',
					'50', 
					'".esc_sql($language)."',
					'".time()."', '0', '0')";
				$message = __('New target successfully created.', 'ulp');
				$wpdb->query($sql);
				$target['id'] = $wpdb->insert_id;
				$action = 'insert';
			}
			$target = $wpdb->get_row("SELECT t1.*, t2.title as popup_title, t3.title as popup_mobile_title FROM ".$wpdb->prefix."ulp_targets t1 LEFT JOIN ".$wpdb->prefix."ulp_popups t2 ON t2.str_id = t1.popup LEFT JOIN ".$wpdb->prefix."ulp_popups t3 ON t3.str_id = t1.popup_mobile WHERE t1.id = '".$target['id']."'", ARRAY_A);
			$target = $this->parse_target($target);
			$html = $this->get_list_item_html($target);
			
			$return_object = array(
				'status' => 'OK',
				'action' => $action,
				'id' => $target['id'],
				'message' => $message,
				'html' => $html
			);
			echo json_encode($return_object);
			exit;
		}
	}
	function admin_save_list() {
		global $wpdb, $ulp;
		if (current_user_can('manage_options')) {
			if (isset($_REQUEST['ulp_event']) && array_key_exists($_REQUEST['ulp_event'], $ulp->events)) $event = $_REQUEST['ulp_event'];
			else {
				$return_object = array(
					'status' => 'ERROR',
					'message' => __('No event found.', 'ulp')
				);
				echo json_encode($return_object);
				exit;
			}
			if (isset($_REQUEST['ulp_targets_deleted'])) {
				$deleted = array();
				$deleted_raw = explode(',', $_REQUEST['ulp_targets_deleted']);
				foreach ($deleted_raw as $value) {
					if ($value == intval($value)) $deleted[] = intval($value);
				}
				if (!empty($deleted)) {
					$wpdb->query("UPDATE ".$wpdb->prefix."ulp_targets SET deleted = '1' WHERE event = '".$event."' AND id IN ('".implode("','", $deleted)."')");
				}
			}
			$language_filter = '';
			if (defined('ICL_LANGUAGE_CODE')) {
				if (ICL_LANGUAGE_CODE != 'all') $language_filter = " AND language IN ('all', '".esc_sql(ICL_LANGUAGE_CODE)."')";
			}
			
			$wpdb->query("UPDATE ".$wpdb->prefix."ulp_targets SET active = '0' WHERE event = '".$event."' AND deleted = '0'".$language_filter);
			if (isset($_REQUEST['ulp_targets_active'])) {
				$active = array();
				$active_raw = explode(',', $_REQUEST['ulp_targets_active']);
				foreach ($active_raw as $value) {
					if ($value == intval($value)) $active[] = intval($value);
				}
				if (!empty($active)) {
					for ($i=0; $i<sizeof($active); $i++) {
						$wpdb->query("UPDATE ".$wpdb->prefix."ulp_targets SET active = '1', priority = '".$i."' WHERE event = '".$event."' AND id = '".$active[$i]."'");
					}
				}
			}
			$return_object = array(
				'status' => 'OK',
				'message' => __('Targets list successfully saved.', 'ulp')
			);
			echo json_encode($return_object);
			exit;
		}
	}
	function fetch_target($_id) {
		global $wpdb;
		$target_details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ulp_targets WHERE deleted = '0' AND id = '".intval($_id)."'", ARRAY_A);
		if ($target_details) {
			$target = $this->parse_target($target_details);
		} else $target = $this->default_target;
		return $target;
	}
	function parse_target($_target) {
		$target = array_merge($this->default_target, $_target);
		$unserialized = unserialize($_target['options']);
		if (is_array($unserialized)) $target['options'] = array_merge($this->default_target['options'], $unserialized);
		$unserialized = unserialize($_target['taxonomies']);
		if (is_array($unserialized)) $target['taxonomies'] = array_merge($this->default_target['taxonomies'], $unserialized);
		$unserialized = unserialize($_target['posts']);
		if (is_array($unserialized)) $target['posts'] = array_merge($this->default_target['posts'], $unserialized);
		unset($target['posts_all']);
		$target['posts_all'] = $_target['posts_all'] == 1 ? true : false;
		return $target;
	}
	function get_list_item_html($_target) {
		$filter_html = '';
		if ($_target['post_type'] == 'sitewide') {
			$filter_html .= '<span>'.__('Sitewide', 'ulp').'</span>';
		} else if (!$_target['posts_all']) {
			$args = array(
				'post_type' => $_target['post_type'],
				'order' => 'DESC',
				'orderby' => 'date',
				'post__in' => $_target['posts'],
				'nopaging' => true
			);
			$args['post__in'] = $_target['posts'];
			$args['nopaging'] = true;
			$query = new WP_Query($args);
			if ($query->found_posts > 0) {
				$post_type = get_post_type_object($_target['post_type']);
				$filter_html .= '<span><label>'.esc_html($post_type->labels->name).':</label> ';
				$posts = array();
				foreach ($query->posts as $post) {
					$posts[] = (empty($post->post_title) ? __('No title', 'ulp') : esc_html($post->post_title)).' (ID: '.$post->ID.', Status: '.ucfirst($post->post_status).')';
				}
				$filter_html .= implode(', ', $posts).'</span>';
			}
			
		} else {
			$taxonomies = get_object_taxonomies($_target['post_type'], 'object');
			$skip = false;
			foreach ($taxonomies as $key => $taxonomy) {
				if (!$taxonomy->public) continue;
				if ($key == 'post_format') continue;
				if (array_key_exists($key, $_target['taxonomies'])) {
					if ($_target['taxonomies'][$key] == 'all') $filter_html .= '<span><label>'.esc_html($taxonomy->label).':</label> All</span>';
					else if (is_array($_target['taxonomies'][$key])) {
						$terms = get_terms($key, array('hide_empty' => false));
						$selected = array();
						foreach ($terms as $term) {
							if (in_array($term->slug, $_target['taxonomies'][$key])) $selected[] = esc_html($term->name);
						}
						if (sizeof($selected) > 0) {
							$filter_html .= '<span><label>'.esc_html($taxonomy->label).':</label> '.implode(', ', $selected).'</span>';
						} else {
							$skip = true;
							break;
						}
					} else {
						$skip = true;
						break;
					}
				}
			}
			$post_type = get_post_type_object($_target['post_type']);
			$filter_html .= '<span><label>'.esc_html($post_type->labels->name).':</label> All';
			if ($skip) return '';
		}
		$item_html = '
				<div class="ulp-targets-list-item" id="ulp-targets-list-item-'.$_target['id'].'" data-id="'.$_target['id'].'">
					<div class="ulp-targets-list-item-content">
						<h4>'.(empty($_target['popup_title']) ? 'None (disabled)' : esc_html($_target['popup_title'])).' / '.(empty($_target['popup_mobile_title']) ? ($_target['popup_mobile'] == 'same' ? (empty($_target['popup']) ? 'None (disabled)' : esc_html($_target['popup_title'])) : 'None (disabled)') : esc_html($_target['popup_mobile_title'])).'</h4>
						'.$filter_html.'
						<div class="ulp-targets-list-item-buttons">
							<a href="#" onclick="return ulp_targets_window_open(\''.$_target['event'].'\', '.$_target['id'].');"><i class="fa fa-edit"></i> '.__('Edit', 'ulp').'</a>
							<a href="#" onclick="return ulp_targets_delete(\''.$_target['event'].'\', '.$_target['id'].');"><i class="fa fa-close"></i> '.__('Remove', 'ulp').'</a>
						</div>
					</div>
				</div>';
		return $item_html;
	}
	function convert_classic() {
		global $ulp, $wpdb, $sitepress;
		
		$converted = get_option('ulp_ext_advanced_targeting_converted', 'off');
		if ($converted == 'off') {
			update_option('ulp_ext_advanced_targeting_converted', 'on');
			if (!defined('ICL_LANGUAGE_CODE')) {
				$this->convert_event('onload');
				$this->convert_event('onscroll');
				$this->convert_event('onexit');
				$this->convert_event('onidle');
				$this->convert_event('onabd');
			} else {
				$current_language = ICL_LANGUAGE_CODE;
				$languages = icl_get_languages();
				foreach ($languages as $language) {
					$sitepress->switch_lang($language['code']);
					$this->convert_event('onload', $language['language_code']);
					$this->convert_event('onscroll', $language['language_code']);
					$this->convert_event('onexit', $language['language_code']);
					$this->convert_event('onidle', $language['language_code']);
					$this->convert_event('onabd', $language['language_code']);
				}
				$sitepress->switch_lang($current_language);
			}
		}
	}
	function convert_event($_event, $_language = 'all') {
		global $ulp, $wpdb;
		if (array_key_exists($_event, $ulp->events)) {
			$args = array(
				'post_type' => 'any',
				'nopaging' => true,
				'meta_query' => array(
					array(
						'key' => 'ulp_'.$_event.'_mode',
						'value' => array('every-time', 'once-session', 'once-period', 'once-only'),
						'compare' => 'IN'
					)
				)
			);
			$query = new WP_Query($args);
			$i = 0;
			foreach ($query->posts as $post) {
				$target = $this->default_target;
				$target['options']['mode'] = get_post_meta($post->ID, 'ulp_'.$_event.'_mode', true);
				$target['options']['mode_period'] = get_post_meta($post->ID, 'ulp_'.$_event.'_period', true);
				$target['popup'] = get_post_meta($post->ID, 'ulp_'.$_event.'_popup', true);
				if ($target['popup'] == 'default') {
					if ($_language == 'all') $target['popup'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup'], '', '');
					else $target['popup'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup'], '', $_language);
				}
				$target['popup_mobile'] = get_post_meta($post->ID, 'ulp_'.$_event.'_popup_mobile', true);
				if ($target['popup_mobile'] == 'default') {
					if ($_language == 'all') $target['popup_mobile'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup_mobile'], 'same', '');
					else $target['popup_mobile'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup_mobile'], 'same', $_language);					
				}
				switch ($_event) {
					case 'onload':
						$target['options']['delay'] = get_post_meta($post->ID, 'ulp_onload_delay', true);
						$target['options']['close_delay'] = get_post_meta($post->ID, 'ulp_onload_close_delay', true);
						break;
					case 'onscroll':
						$target['options']['offset'] = get_post_meta($post->ID, 'ulp_onscroll_offset', true);
						break;
					case 'onidle':
						$target['options']['delay'] = get_post_meta($post->ID, 'ulp_onidle_delay', true);
						break;
					default:
						break;
				}
				$target['post_type'] = $post->post_type;
				$target['posts_all'] = false;
				$target['posts'] = array($post->ID);
				$sql = "INSERT INTO ".$wpdb->prefix."ulp_targets (
					event, popup, popup_mobile, options, post_type, taxonomies, posts, posts_all, priority, language, created, active, deleted) VALUES (
					'".esc_sql($_event)."',
					'".esc_sql($target['popup'])."',
					'".esc_sql($target['popup_mobile'])."',
					'".esc_sql(serialize($target['options']))."',
					'".esc_sql($target['post_type'])."',
					'".esc_sql(serialize($target['taxonomies']))."',
					'".esc_sql(serialize($target['posts']))."',
					'".esc_sql(intval($target['posts_all']))."',
					'".$i."',
					'".esc_sql($_language)."',
					'".time()."', '1', '0')";
				$wpdb->query($sql);
				$i++;
			}
			if (in_array($ulp->options[$_event.'_mode'], array('every-time', 'once-session', 'once-period', 'once-only'))) {
				$target = $this->default_target;
				$target['options']['mode'] = $ulp->options[$_event.'_mode'];
				$target['options']['mode_period'] = $ulp->options[$_event.'_period'];
				if ($_language == 'all') $target['popup'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup'], '', '');
				else $target['popup'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup'], '', $_language);
				if ($_language == 'all') $target['popup_mobile'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup_mobile'], 'same', '');
				else $target['popup_mobile'] = $ulp->wpml_parse_popup_id($ulp->options[$_event.'_popup_mobile'], 'same', $_language);					
				switch ($_event) {
					case 'onload':
						$target['options']['delay'] = $ulp->options['onload_delay'];
						$target['options']['close_delay'] = $ulp->options['onload_close_delay'];
						break;
					case 'onscroll':
						$target['options']['offset'] = $ulp->options['onscroll_offset'];
						break;
					case 'onload':
						$target['options']['delay'] = $ulp->options['onidle_delay'];
						break;
					default:
						break;
				}
				$target['post_type'] = 'sitewide';
				$target['posts_all'] = true;
				$sql = "INSERT INTO ".$wpdb->prefix."ulp_targets (
					event, popup, popup_mobile, options, post_type, taxonomies, posts, posts_all, priority, language, created, active, deleted) VALUES (
					'".esc_sql($_event)."',
					'".esc_sql($target['popup'])."',
					'".esc_sql($target['popup_mobile'])."',
					'".esc_sql(serialize($target['options']))."',
					'".esc_sql($target['post_type'])."',
					'".esc_sql(serialize($target['taxonomies']))."',
					'".esc_sql(serialize($target['posts']))."',
					'".esc_sql(intval($target['posts_all']))."',
					'".$i."',
					'".esc_sql($_language)."',
					'".time()."', '1', '0')";
				$wpdb->query($sql);
			}
		}
	}
	function front_init($_post_id) {
		global $wpdb, $post, $current_user, $ulp;
		$post_types = array('sitewide');
		if ($_post_id > 0) {
			$post_type = get_post_type($_post_id);
			if ($post_type !== false) $post_types[] = $post_type;
		}
		$language_filter = '';
		if (defined('ICL_LANGUAGE_CODE')) {
			if (ICL_LANGUAGE_CODE != 'all') $language_filter = " AND language IN ('all', '".esc_sql(ICL_LANGUAGE_CODE)."')";
		}
		$targets = array();
		foreach ($ulp->events as $key => $value) {
			$target_details = array();
			$rows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ulp_targets WHERE deleted = '0' AND active = '1' AND event = '".esc_sql($key)."' AND post_type IN ('".implode("','", $post_types)."')".$language_filter." ORDER BY priority ASC", ARRAY_A);
			foreach ($rows as $row) {
				if ($row['post_type'] == 'sitewide') {
					$target_details = $row;
					break;
				} else if ($row['posts_all'] == 0) {
					$posts = unserialize($row['posts']);
					if (is_array($posts) && in_array($_post_id, $posts)) {
						$target_details = $row;
						break;
					}
				} else {
					$taxonomies = unserialize($row['taxonomies']);
					if (is_array($taxonomies)) {
						$match = true;
						foreach ($taxonomies as $slug => $terms) {
							if (is_array($terms)) {
								if (empty($terms)) continue;
								else {
									$post_term_objects = wp_get_object_terms($_post_id, $slug);
									if (is_array($post_term_objects)) {
										$post_terms = array();
										foreach ($post_term_objects as $post_term_object) {
											$post_terms[] = $post_term_object->slug;
										}
										$common_terms = array_intersect($post_terms, $terms);
										if (empty($common_terms)) {
											$match = false;
											break;
										}
									} else continue;
								}
							}
						}
						if ($match) {
							$target_details = $row;
							break;
						}
					}
				}
			}
			if (!empty($target_details)) {
				$targets[$key] = $this->parse_target($target_details);
			}
		}
		return $targets;
	}
}
?>