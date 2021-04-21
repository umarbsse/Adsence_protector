<?php
	function ip_block_ad(){
		ip_block_ad_html();
	}
?>
<?php
	function ip_block_ad_html(){

?>
<div class="ai1wm-container">
    <div class="ai1wm-row">
        <div class="ai1wm-left">
            <div class="ai1wm-holder">
                <div class="wrap">
                    <h1 class="wp-heading-inline">
                    Block New IP
                    </h1>
                    <hr class="wp-header-end">
                    <br>
                    <form action="<?php echo esc_url( admin_url()."admin.php?page=adsense-protector-ad-code" ); ?>" method="post">
                    <?php wp_nonce_field('MyNonceAction', 'ticket_nonce'); ?>
                        <p>
                            <strong>IP</strong> <br>
                            <input type="text" style="width: 50%;" name="ip" class="ap-input">
                        </p>
                        
                            <strong>Is Permanent Ban</strong> <br><br>
                            <label class="switch">
                                <input type="checkbox" name="is_permanent_ban" class="bann_countries_checkbox">
                                <div class="slider round"></div>
                            </label>

                        <br>
                        <?php
                            global $wpdb;
                            $plugin_table_prefix = "ads_prtctr_";
                            $table = $wpdb->prefix . $plugin_table_prefix.'config';
                            $result = $wpdb->get_results( "SELECT * FROM  $table LIMIT 1 ");
                            $block_days = 10;
                            foreach ( $result as $print ){
                                $block_days = $print->manual_ban_days;
                            }
                        ?>
                        <p>
                            <strong>Block (Days)</strong> <br>
                            <input type="number" style="width: 50%;" min="1" value="<?php echo $block_days; ?>" max="999" name="ban_lift_time" class="ap-input">
                        </p>
                        <p>
                            <button type="submit" value="submit" class="submit_btn" name="submit">Insert Ad Code</button><br>
                        </p>
                    </form> <br>
                </div>
            </div>
        </div>
        <?php include ('right_sidebar.php'); ?>
        </div>
    </div>
</div>
<?php
	}
?>