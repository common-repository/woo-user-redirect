<?php
/* /
  Plugin Name: Woo user redirect
  Plugin URI: http://techmintra.com
  Description:  Woo user redirect is a addon for woocommerce which will redirect restricted user to a custom page based on User Role
  Version: 1.2
  Author: Baban Sharma @ techmintra.com
  Text Domain: woo-user-redirect
  Author URI: http://codecanyon.net/user/babanynr
  License : woo user redirect plugin is used to set the restrictions on some page and define a redirect url if user visited on them.
    Copyright (C) 2016  Baban Sharma
	Any customization in this are prohibited .
    for customization or update you can contact us at https://techmintra.com/contact	
  / */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    class Woo_User_redirect {

        /**
         * Bootstraps the class and hooks required actions & filters.
         *
         */
        public static function init() {
            add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50);
            add_action('woocommerce_settings_tabs_redirect_setting', __CLASS__ . '::settings_tab');
            add_action('woocommerce_update_options_redirect_setting', __CLASS__ . '::update_settings');           
            add_action('woocommerce_admin_field_redirect_user', __CLASS__ . '::render_loggedin_redirect_field');            
            add_action('woocommerce_update_options_redirect_setting', __CLASS__ . '::save_redirect_user_field');
        }

        /**
         * Add a new settings tab to the WooCommerce settings tabs array.
         *
         * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
         * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
         */
        public static function add_settings_tab($settings_tabs) {
            $settings_tabs['redirect_setting'] = __('User Redirect', 'woo-user-redirect');
            return $settings_tabs;
        }
       

        /**
         * Signup redirect if logged in
         */
        public static function render_loggedin_redirect_field($field='') {
            global $wp_roles;
            $all_roles = $wp_roles->roles;
            $editable_roles = apply_filters('editable_roles', $all_roles);
            //get saved setting if any
			update_option('woo_redirect_setting', '');
            $redirect_setting = get_option('woo_redirect_setting');
			if(!$redirect_setting) {
				foreach ($editable_roles as $single_role => $role_cap) { 
					if ($single_role != 'administrator') {
						$temp['wholesale'] = '';
						$temp['login_redirect'] = '';
						$redirect_setting[$single_role] = $temp;
					}
				}
			}
			
            ?>	
            <tr valign="top">
                <th>
                    <?php esc_attr_e("Logged In user redirect setting", "woo-user-redirect"); ?>
                </th>
            </tr>
            <tr valign="top" style="width:100%;">
                <td id="signup_redirect" colspan="8">
                    <!--Internal table-->
                    <table class="widefat wc_input_table sortable signup_tab" cellspacing="0" >					
                        <thead>
                            <tr valign="top">
                                <th class="role" align="center" ><?php esc_attr_e("User Role", "woo-user-redirect"); ?></th>                               
                                <th class="redirect" ><?php esc_attr_e("Restricted Page URL ", "woo-user-redirect"); ?></th>
                                <th class="redirect" ><?php esc_attr_e("Redirect to URL", "woo-user-redirect"); ?></th>
                            </tr>

                        </thead>
                        <tbody class="keys">
                            <?php
                            $result_rows = get_option('woo_redirect_user');
                            if (empty($result_rows)) {
                                $result_rows = array(array('signup' => '', 'wholesale' => '', 'login_redirect' => ''),);
                            }
                            $row_count = 0;
                            ?>
                            <?php foreach ($result_rows as $row) { ?>
                                <tr class="key">
                                    <td class="role" >                                    
                                        <select name="signup[<?php echo $row_count; ?>]" style="width:100%;">
                                            <?php
											
                                            foreach ($editable_roles as $single_role => $role_cap) {
												if ($single_role != 'administrator') {
													$user_setting = $redirect_setting[$single_role];
													$selected = '';
													
                                                    if ($row['signup'] == $single_role) {
                                                        $selected = 'selected="selected"';
													}
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $single_role; ?>"><?php echo $role_cap['name']; ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                        </select>                                   
                                    </td>
                                    <!--wholesale page url-->
                                    <td>
                                        <input type="text" placeholder="Enter Wholesale page URL" value="<?php echo $row['wholesale']; ?>" name="wholesale[<?php echo $row_count; ?>]" />
                                    </td>
                                    <!--redirect page url-->
                                    <td>
                                        <input type="text" placeholder="Enter Wholesale page URL" value="<?php echo $row['login_redirect']; ?>" name="login_redirect[<?php echo $row_count; ?>]" />
                                    </td>
                                </tr>
                                <?php $row_count++;
                            }
                            ?>
                        </tbody>
                        <!--End external tr-->
                    </table>                     
                </td>
            </tr>
            <tr id="addgroup">
                <th colspan="7">
                    <a href="#" class="add_row button"><?php _e('+ Add Row', 'woocommerce'); ?></a> 
                    <a href="#" class="remove_row button"><?php _e('Remove selected Row', 'woocommerce'); ?></a>
                    <script type="text/javascript">
                        jQuery(function() {
                            jQuery('#addgroup').on( 'click', 'a.add_row', function(){
                                var size = jQuery('table.signup_tab tbody tr').length;
								var newrow = '<tr class="key"><td class="role" ><select name="signup['+size+']" style="width:100%" >';
								<?php foreach($editable_roles as $single_role => $role_cap){
									$user_setting=$redirect_setting[$single_role]; 
									if ($single_role!="administrator"){?>
										newrow = newrow+' <option value="<?php echo $single_role; ?>" ><?php echo $role_cap["name"];?>';
									newrow = newrow+'</option>';
										<?php }
										} 
										?>
									newrow = newrow+'</select></td><td><input type="text" placeholder="Enter Wholesale page URL" name="wholesale['+size+']" /></td><td><input type="text" placeholder="Enter Wholesale page URL" name="login_redirect['+size+']"/></td></tr>';										
                                jQuery(newrow).appendTo('table.signup_tab tbody');
                        return false;
                    });
                    jQuery('#addgroup').on( 'click', 'a.remove_row', function(){
                        jQuery("table.signup_tab tbody tr.last_selected").remove();

                    });
                });
                    </script>
                </th>
            </tr>
            <?php
        }        

        /**
         * Save user redirect settings
         */
        public static function save_redirect_user_field() {           

            //Save redirect additional fields
            if (isset($_POST['signup']) && !empty($_POST['signup'])) {
                $result = array();
                $i = 0;
                foreach ($_POST['signup'] as $key => $row) {
                    $result[$i]['signup'] = $row;
                    $result[$i]['wholesale'] = $_POST['wholesale'][$key];
                    $result[$i]['login_redirect'] = $_POST['login_redirect'][$key];
                    $i++;
                }

                update_option('woo_redirect_user', $result);
            }            
        }

        /**
         * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
         *
         * @uses woocommerce_admin_fields()
         * @uses self::get_settings()
         */
        public static function settings_tab() {
            woocommerce_admin_fields(self::get_settings());
        }

        /**
         * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
         *
         * @uses woocommerce_update_options()
         * @uses self::get_settings()
         */
        public static function update_settings() {
            woocommerce_update_options(self::get_settings());
        }

        /**
         * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
         *
         * @return array Array of settings for @see woocommerce_admin_fields() function.
         */
        public static function get_settings() {
            $settings = array(
                'section_title' => array(
                    'name' => __('Redirct settings', 'woo-user-redirect'),
                    'type' => 'title',                   
                    'id' => 'wc_redirect_setting_section_title'
                ),
                'title' => array(
                    'name' => __('user roles', 'woo-user-redirect'),
                    'type' => 'redirect_user',
                    'id' => 'wc_redirect_setting_title'
                ),                
                'section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wc_redirect_setting_section_end'
                )
            );

            return apply_filters('wc_redirect_setting_settings', $settings);
        }

    }

    Woo_User_redirect::init();

    /**
     * Redirect on the basis of redirect setting
     */
    function get_user_redirect() {
        //Redirect user according to current page url
        $result_rows = get_option('woo_redirect_user');        
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_info = get_userdata($user_id);
            $user_role = $user_info->roles[0];
            if($user_role != 'administrator') {
                foreach($result_rows as $role_url) {
                    if($role_url['signup'] == $user_role) {
                        $wholesale_url = $role_url['wholesale'];
                        $login_redirect = $role_url['login_redirect'];
                        $actual_link = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        //remove http:// or https
                        $wholesale_url = str_replace('http://', '', $wholesale_url);
                        $wholesale_url = str_replace('https://', '', $wholesale_url);
                        if($wholesale_url == $actual_link) {
                            wp_safe_redirect($login_redirect);
                            die;
                        }
                        
                    }
                }
            }
        }
    }

    add_action('wp', 'get_user_redirect');

}
