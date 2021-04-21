<?php
	function adcode_insert(){
		adcode_insert_html();
	}
?>
<?php
	function adcode_insert_html(){
        $is_update = false;
        $is_add = true;
        if (isset($_GET['action']) && $_GET['action']!="" && $_GET['action']=="edit" && 
            isset($_GET['token']) && $_GET['token']!="" && $_GET['token']>0 ) {
            $is_update = true;
            $is_add = false;            
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $table = $wpdb->prefix . $plugin_table_prefix.'code';
            $sql = "SELECT * FROM $table WHERE id=".$wpdb->escape($_GET['token'])." LIMIT 1";
            $db = new WP_Adsense_Protector_db();
            $result = $db->get($sql);

        }
?>
<div class="ai1wm-container">
    <div class="ai1wm-row">
        <div class="ai1wm-left">
            <div class="ai1wm-holder">
                <div class="wrap">
                    <h1 class="wp-heading-inline">
                    <?php if($is_add == true){echo "Insert Adsense Code";}else{echo "Update Adsense Code";} ?>
                    </h1>
                    <hr class="wp-header-end">
                    <br>
                    <?php if($is_add == true){?>
                    <form action="<?php echo esc_url( admin_url()."admin.php?page=adsense-protector-ad-code" ); ?>" method="post">
                    <?php wp_nonce_field('MyNonceAction', 'ticket_nonce'); ?>
                        <p>
                            <strong>Title (50 Characters)</strong> <br>
                            <input type="text" style="width: 100%;"  maxlength="50" name="title" required min="0" value="" class="ap-input">
                        </p>
                        <p>
                            <strong>ID (You may apply styling to the ID)</strong> <br>
                            <input type="text" style="width: 100%;"  maxlength="50" name="attr_id" min="0" value="" class="ap-input">
                        </p>
                        <p>
                            <strong>Class Name (Custom Class name which is later used to apply styling or events)</strong> <br>
                            <input type="text" style="width: 100%;"  maxlength="50" name="attr_class" min="0" value="" class="ap-input">
                        </p>
                        <p>
                            <strong>Adsense Code</strong><br>
                            <textarea required class="wp-editor-area ap-textarea" style="width: 100%;"  rows="12" cols="50" name="code"></textarea>
                        </p>
                        <p>
                            <button type="submit" value="submit" class="submit_btn" name="submit">Insert Ad Code</button><br>
                        </p>
                    </form> <br>
                    <?php }else{ ?>
                    <form action="<?php echo esc_url( admin_url()."admin.php?page=adsense-protector-ad-code" ); ?>" method="post">
                    <?php wp_nonce_field('MyNonceAction', 'ticket_nonce'); ?>
                    <?php foreach ( $result as $print )   { ?>
                        <p>
                            <strong>Title (50 Characters)</strong> <br>
                            <input type="hidden" name="token" min="0" value="<?php echo $print->id ?>">
                            <input type="text" style="width: 100%;"  maxlength="50" name="title" min="0" value="<?php echo $print->title ?>" class="ap-input">
                        </p>
                        <p>
                            <strong>ID (You may apply styling to the ID)</strong> <br>
                            <input type="text" style="width: 100%;"  maxlength="50" name="attr_id" min="0" value="<?php echo $print->attr_id; ?>" class="ap-input">
                        </p>
                        <p>
                            <strong>Class Name (Custom Class name which is later used to apply styling or events)</strong> <br>
                            <input type="text" style="width: 100%;"  maxlength="50" name="attr_class" min="0" value="<?php echo $print->attr_class; ?>" class="ap-input">
                        </p>
                        <p>
                            <strong>Adsens Code</strong><br>
                            <textarea required class="wp-editor-area ap-textarea" style="width: 100%;"  rows="12" cols="50" name="code"><?php $code =  $db->remove_extra_chars($print->code); echo $code; ?></textarea>
                        </p>
                        <p>
                            <button type="submit" value="submit" class="submit_btn" name="submit">Insert Ad Code</button><br>
                        </p>
                    </form> <br>
                    <?php } } ?>
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