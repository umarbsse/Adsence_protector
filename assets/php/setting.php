<?php
	function setting(){
        update_setting();
		setting_html();
	}
?>
<?php
    function update_setting(){
        if(isset($_POST['submit']) && $_POST['submit']=="submit")
        {
            // If the nonce does not verify, do NOT process the form.
            $nonce = $_POST['ticket_nonce'];
            if ( ! wp_verify_nonce($nonce, 'MyNonceAction')) {
                 // If this spits out an error, that means the nonce failed
                 //echo 'Security error. Do not process the form.';
                 return;
            }
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $table = $wpdb->prefix . $plugin_table_prefix.'config';
            $data = array( 
                'max_click_limit' => $_POST['max_click_limit'],
                'max_click_lift_time_in_hour' => $_POST['max_click_lift_time_in_hour'],
                'max_pageviews' => $_POST['max_pageviews'],
                'max_pageview_lift_time_in_hour' => $_POST['max_pageview_lift_time_in_hour'],
                'manual_ban_days' => $_POST['manual_ban_days'],
                'pageviews_record_limit' => $_POST['pageviews_record_limit'],
                'clicks_record_limit' => $_POST['clicks_record_limit'],
                'timezone' => $_POST['timezone']
            ); 
            $where = array( 'ID' => $_POST['token'] );
            $db = new WP_Adsense_Protector_db();
            $response = $db->update($table, $data, $where);
            if ($response == true) {
                echo '<br/><div class="updated notice">
                        <p>Setting Updated Successfully</p>
                    </div>';
            }else{
                echo '<br/><div class="error notice">
                        <p>Updation failed! Try Again</p>
                    </div>';
            }
        }
    }
?>
<?php
	function setting_html(){
?>
<div class="ai1wm-container">
    <div class="ai1wm-row">
        <div class="ai1wm-left">
            <div class="ai1wm-holder">
                <h1>Settings</h1><br>
                <?php
                    global $wpdb;
                    $plugin_table_prefix = "ads_prtctr_";
                    $table = $wpdb->prefix . $plugin_table_prefix.'config';
                    $timezone_tbl = $wpdb->prefix . $plugin_table_prefix.'timezones';
                    $timezone_results = $wpdb->get_results( "SELECT * FROM  $timezone_tbl ORDER BY time_zone ASC ");
                    $result = $wpdb->get_results( "SELECT * FROM  $table LIMIT 1 ");
                    foreach ( $result as $print )   { ?>
                <form action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
                <?php wp_nonce_field('MyNonceAction', 'ticket_nonce'); ?>
                    <p>
                        <strong>Maximum Clicks Limit (0 = Unlimited)</strong> <br>
                        <input type="number" name="max_click_limit" min="0" value="<?php echo $print->max_click_limit; ?>" class="ap-input">
                        <input type="hidden" name="token" min="0" value="<?php echo $print->id; ?>">
                    </p>
                    <p>
                        <strong>Set the Visitor Ban by Clicks Duration (Hourly)</strong> <br>
                        <input type="number" name="max_click_lift_time_in_hour" min="0" value="<?php echo $print->max_click_lift_time_in_hour; ?>" class="ap-input">
                    </p>
                    <p>
                        <strong>Maximum Pageviews Limit (0 = Unlimited)</strong> <br>
                        <input type="number" name="max_pageviews" min="0" value="<?php echo $print->max_pageviews; ?>" class="ap-input">
                    </p>
                    <p>
                        <strong>Set the Visitor Ban by page views Duration (Hourly)</strong> <br>
                        <input type="number" name="max_pageview_lift_time_in_hour" min="0" value="<?php echo $print->max_pageview_lift_time_in_hour; ?>" class="ap-input">
                    </p>
                    <p>
                        <strong>Block Days (Manual Block)</strong> <br>
                        <input type="number" name="manual_ban_days" min="0" value="<?php echo $print->manual_ban_days; ?>" class="ap-input">
                    </p>
                    <p>
                        <strong>Pageviews record limit (Default 10,000)</strong> <br>
                        <input type="number" name="pageviews_record_limit" min="1000" value="<?php echo $print->pageviews_record_limit; ?>" class="ap-input">
                    </p>
                    <p>
                        <strong>Clicks record limit (Default 3000)</strong> <br>
                        <input type="number" name="clicks_record_limit" min="50" value="<?php echo $print->clicks_record_limit; ?>" class="ap-input">
                    </p>
                    <p>
                        <strong>Time Zone</strong> <br>
                        <select name="timezone" required="">
                            <option value="">--Select Timezone--</option>
                            <?php foreach ( $timezone_results as $t_print )   { ?>
                            <option value="<?php echo $t_print->id; ?>" <?php if($t_print->id==$print->timezone){echo "selected";} ?> ><?php echo $t_print->time_zone; ?></option>
                            <?php } ?>
                        </select>
                    </p>
                    <p>
                        <button type="submit" value="submit" class="submit_btn" name="submit">Save Settings</button>
                    </p>
                </form>                
                <?php } ?>
            </div>
        </div>
        <?php include ('right_sidebar.php'); ?>
        </div>
    </div>
</div>
<?php
	}
?>