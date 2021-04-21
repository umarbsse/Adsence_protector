<?php
    
    function adcode(){
        insert_ad_code();
        adcode_html();
    }
?>
<?php

    function insert_ad_code(){
            //echo "<pre>";print_r($_POST);
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
            $table = $wpdb->prefix . $plugin_table_prefix.'code';
            $data = array( 
                'title' => $_POST['title'],
                'attr_id' => $_POST['attr_id'],
                'attr_class' => $_POST['attr_class'],
                'code' => $_POST['code']
            ); 
            $db = new WP_Adsense_Protector_db();
            if (isset($_POST['token']) && $_POST['token']!="" ) {
                $where = array( 'id' => $_POST['token'] );
                //echo "<pre>";print_r($data);echo "</pre>";
                //echo $table." ".$where ; 
                //die();
                $db->update($table, $data, $where);
                 
                echo '<br/><div class="updated notice">
                        <p>Adsense Code Updated Successfully</p>
                    </div>';
            }else{
                $db->insert($table, $data);
                echo '<br/><div class="updated notice">
                        <p>Adsense Code Added Successfully</p>
                    </div>';
            }
        }

        if (isset($_GET['action']) && $_GET['action']!="" && $_GET['action']=="delete" && 
            isset($_GET['token']) && $_GET['token']!="" && $_GET['token']>0 ) {
            $where = array( 'id' => $_GET['token']);
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $table = $wpdb->prefix . $plugin_table_prefix.'code';
            $db = new WP_Adsense_Protector_db();
            $db->delete($table, $where);
            wp_redirect(admin_url().'admin.php?page=adsense-protector-ad-code');

        }
    }
?>
<?php
    function adcode_html(){
?>
<div class="ai1wm-container">
    <div class="ai1wm-row">
        <div class="ai1wm-left">
            <div class="ai1wm-holder">
                <div class="wrap">
                    <h1 class="wp-heading-inline">Adsense Code</h1>
                    <a href="<?php echo admin_url(); ?>admin.php?page=adsense-protector-ad-code-insert" class="page-title-action">Add New</a>
                    <hr class="wp-header-end">
                    <br>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="manage-column column-author">Sr #</th>
                                <th scope="col" id="categories" class="manage-column column-categories">Title</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Short Code</th>
                                <th style="width: 10%;" scope="col" id="tags" class="manage-column column-tags">Action</th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                        
                            <?php
                                $db = new WP_Adsense_Protector_db();
                                global $wpdb;
                                $plugin_table_prefix = "ads_prtctr_";
                                $table = $wpdb->prefix . $plugin_table_prefix.'code';
                                $sql = "SELECT * FROM $table order by id desc ";
                                $result = $db->get($sql);
                                $count=0;
                                foreach ( $result as $print )   {$count++;
                            ?>
                            <tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-uncategorized">
                                <td><?php echo $count; ?></td>
                                <td class="date column-date" data-colname="Date"><?php echo $print->title; ?></td>
                                <td class="date column-date" data-colname="Date">[Adsense_Protector_Ads token="<?php echo $print->id; ?>"]</td>
                                <td>
                                    <div >
                                        <span class="edit"><a href="<?php echo admin_url(); ?>admin.php?page=adsense-protector-ad-code-insert&amp;action=edit&amp;token=<?php echo $print->id; ?>">Edit</a> | </span>
                                        <span class="trash"><a style="color: red;" onclick="return confirm('Are you sure to delete that Adsense code ?')" href="<?php echo admin_url(); ?>admin.php?page=adsense-protector-ad-code&amp;action=delete&amp;token=<?php echo $print->id; ?>">Delete</a></span>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if($count==0){ ?>
                            <tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-uncategorized">
                                <td colspan="4">No Adsense Code Found.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="col" id="author" class="manage-column column-author">Sr #</th>
                                <th scope="col" id="categories" class="manage-column column-categories">Title</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Short Code</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Action</th>
                            </tr>
                        </tfoot>
                    </table>
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