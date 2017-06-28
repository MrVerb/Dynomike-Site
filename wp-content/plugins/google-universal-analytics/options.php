<div class="wrap">
  <?php if( isset($_GET['settings-updated']) ) { ?>    <div id="message" class="updated">        <p><strong><?php _e('Settings saved.') ?></strong></p>    </div>  <?php } ?>    <h2><?php echo __('Google Universal Analytics', 'gua'); ?></h2>  
  <br />
  <div class="col-lg-6 row">
    <form class="form-horizontal" method="post" action="options.php" role="form" id="google-universal-options">
    <?php settings_fields('google-universal-settings'); ?>
      <div class="form-group">
        <label for="web_property_id" class="col-sm-4 control-label"><?php echo __('Status', 'gua'); ?></label>
        <div class="col-sm-8">
          <input id="plugin_switch" type="checkbox" name="plugin_switch" <?php if(get_option('plugin_switch')=='on'): ?> checked="checked" <?php endif; ?>>
        </div>
      </div>
      <div class="form-group">
        <label for="web_property_id" class="col-sm-4 control-label"><?php echo __('Tracking ID', 'gua'); ?></label>
        <div class="col-sm-8">
          <input type="text" class="form-control" name="web_property_id" id="web_property_id" placeholder="Tracking code example: UA-­23710711-­7" value="<?php echo get_option('web_property_id'); ?>">
          <span class="error hide" id="code-error"><strong><?php echo __('Error!', 'gua'); ?> </strong><?php echo __('match your code with this format: UA-41115660-1', 'gua'); ?> </span> </div>
      </div>
      <div class="form-group">
        <label for="web_property_id" class="col-sm-4 control-label"></label>
        <div class="col-sm-8"> <i><?php echo __('Advanced settings:', 'gua'); ?></i> </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="in_footer" id="in_footer" <?php if(get_option('in_footer')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Place code in footer', 'gua'); ?> </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="track_links" id="track_links" <?php if(get_option('track_links')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Track events (<em>Downloads, Mailto, Outbound URLs & Phone calls tracking</em>)', 'gua'); ?>  </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="enable_display" id="enable_display" <?php if(get_option('enable_display')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Enable Display Advertising (<em>Remarketing, Demographics and Interests</em>)', 'gua'); ?> </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="anonymize_ip" id="anonymize_ip" <?php if(get_option('anonymize_ip')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Anonymize IP', 'gua'); ?> </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="enhancedlink_u" id="enhancedlink_u" <?php if(get_option('enhancedlink_u')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Enhanced Link Attribution', 'gua'); ?> <!--span style="color:green;">New!</span -->			  
			</label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="set_domain" id="set_domain" <?php if(get_option('set_domain')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Set Domain', 'gua'); ?> <input type="" name="set_domain_domain" id="set_domain_domain" placeholder="nexsuad.com" <?php if(get_option('set_domain')=='on'): ?> value="<?php echo get_option('set_domain_domain'); ?>" <?php endif; ?>  /></label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <?php global $wp_roles;
			$roles = $wp_roles->get_names(); ?>
          <div class="checkbox">
            <label>
              <input type="checkbox" name="tracking_off_for_role" id="tracking_off_for_role" <?php if(get_option('tracking_off_for_role')=='on'): ?> checked="checked" <?php endif; ?>>
	      <?php echo __('Disable Tracking For', 'gua'); ?>
	    </label>
              <select id="tracking_off_for_this_role" name="tracking_off_for_this_role">
		<?php// echo "<pre/>"; print_r($roles); ?>
		<?php foreach($roles as $role) { ?>
		
                <option value="<?php echo $role;?>" <?php if(get_option('tracking_off_for_this_role')== $role){echo 'selected="selected"';} ?>><?php echo $role;?></option>
                <?php } ?>
              </select>
            
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">		  
          <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
          <!--<button type="button" class="button button-primary" id="save-gua-settings">Save Changes</button>-->
        </div>
      </div>
    </form>
  </div>
  <div class="clearfix"></div>
  <div class="row col-lg-6"><?php echo __('Have a question? Drop it at', 'gua'); ?> <a href="http://onlineads.lt/?utm_source=WordPress&utm_medium=Google%20Universal%20Analytics%202.4.1&utm_content=Google%20Universal%20Analytics&utm_campaign=WordPress%20plugins" title="Google Universal Analytics">OnlineAds.lt</a> </div>
  </br></br>
  <strong>Pro Tip:</strong> For periodic Google Analytics data reporting use <a href="https://nexusad.com/?utm_source=wordpress&utm_medium=Google%2BUniversal%2BAnalytics%2B2.4.1&utm_campaign=wp_plugins" title="nexusAd" target="_blank">nexusAd tool</a>. 
</div>
</br>