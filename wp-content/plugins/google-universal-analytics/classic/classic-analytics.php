<div class="wrap">
  <h2><?php echo __('Google Classic Analytics', 'gua'); ?><em> <?php echo __('(ga.js)', 'gua'); ?></em></h2>
  <br />
  <em><?php echo __('This is an older version of Google Analytics. Use this version only if you have not upgraded to Universal Analytics yet or want to use both trackers (which is totally fine!).', 'gua'); ?></em> <br />
  <br />
  <div class="col-lg-6 row">
    <form class="form-horizontal" method="post" action="options.php" role="form" id="classic-google-universal-options">
    <?php settings_fields('google-universal-settings-classic'); ?>
      <div class="form-group">
        <label for="web_property_id" class="col-sm-4 control-label"><?php echo __('Status', 'gua'); ?></label>
        <div class="col-sm-8">
          <input id="classic_plugin_switch" type="checkbox" name="classic_plugin_switch" <?php if(get_option('classic_plugin_switch')=='on'): ?> checked="checked" <?php endif; ?>>
        </div>
      </div>
      <div class="form-group">
        <label for="classic_web_property_id" class="col-sm-4 control-label"><?php echo __('Tracking ID', 'gua'); ?></label>
        <div class="col-sm-8">
          <input type="text" class="form-control" name="classic_property_id" id="classic_property_id" placeholder="Property ID example: UA-­23710779-­7" value="<?php echo get_option('classic_property_id'); ?>">
          <span class="error hide"><strong><?php echo __('Error!', 'gua'); ?> </strong> <?php echo __('match your code with this forma: UA-41335660-1', 'gua'); ?></span> </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="classic_in_footer" id="classic_in_footer" <?php if(get_option('classic_in_footer')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Place code in footer', 'gua'); ?> </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <?php global $wp_roles;

     $roles = $wp_roles->get_names(); ?>
          <div class="checkbox">
            <label>
              <input type="checkbox" name="classic_tracking_off_for_role" id="classic_tracking_off_for_role" <?php if(get_option('classic_tracking_off_for_role')=='on'): ?> checked="checked" <?php endif; ?>>
              <?php echo __('Disable Tracking For', 'gua'); ?>
              
            </label>
              <select id="classic_tracking_off_for_this_role" name="classic_tracking_off_for_this_role">
                <?php foreach($roles as $role) { ?>
                <option value="<?php echo $role;?>" <?php if(get_option('classic_tracking_off_for_this_role')== $role){echo 'selected="selected"';} ?>><?php echo $role;?></option>
                <?php } ?>
              </select>
            
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <input type="hidden" id="ajax_url" name="ajax_url" value="<?php echo admin_url('admin-ajax.php'); ?>" />
          <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
          <!--<button type="button" class="button button-primary" id="save-classic-settings">Save Changes</button>-->
           </div>
      </div>
    </form>
  </div>
  <div class="clearfix"></div>
  <div class="row col-lg-6"><?php echo __('Have a question? Drop it at', 'gua'); ?> <a href="http://onlineads.lt/?utm_source=WordPress&utm_medium=Google%20Universal%20Analytics%202.4.2&utm_content=Google%20Classic%20Analytics&utm_campaign=WordPress%20plugins" title="Google Universal Analytics">OnlineAds.lt</a> </div>      </br></br>  <strong>Pro Tip:</strong> For periodic Google Analytics data reporting use <a href="https://nexusad.com/?utm_source=wordpress&utm_medium=Google%2BUniversal%2BAnalytics%2B2.4.2&utm_campaign=wp_plugins" title="nexusAd" target="_blank">nexusAd tool</a>.    
</div>
</br>
