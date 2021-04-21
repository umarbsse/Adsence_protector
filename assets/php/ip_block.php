<?php
	function ip_block(){
        update_ip_block();
		ip_block_html();
	}
    function update_ip_block(){
        if (isset($_GET['action']) && $_GET['action']=="unblock") {
                global $wpdb;
                $plugin_table_prefix = "ads_prtctr_";
                $bann_ip_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_ip';
                $id = $_GET['token'];
                $db = new WP_Adsense_Protector_db();
                $data = array(
                    'is_permanent_ban' => 0,
                    'ban_lift_time' => ads_add_hour_to_curent_time(100, '-')
                );
                $where = array( 'id' => $id );
                $db->update($bann_ip_tbl, $data, $where );
                wp_redirect(admin_url().'admin.php?page=adsense-protector-ip-block');
        }
        if (isset($_GET['action']) && $_GET['action']=="pblock") {
                global $wpdb;
                $plugin_table_prefix = "ads_prtctr_";
                $bann_ip_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_ip';
                $id = $_GET['token'];
                $db = new WP_Adsense_Protector_db();
                $data = array(
                    'is_permanent_ban' => 1,
                    'insertion_time'=>ads_get_current_time_stamp_db()
                );
                $where = array( 'id' => $id );
                $db->update($bann_ip_tbl, $data, $where );
                wp_redirect(admin_url().'admin.php?page=adsense-protector-ip-block');
        }

    }
?>
<?php
	function ip_block_html(){

     //   $this->general_obj = new WP_Adsense_Protector_General();
     // $this->general_obj->unblock_ip();
?>

<div class="ai1wm-container">
    <div class="ai1wm-row">
        <div class="ai1wm-left">
            <div class="ai1wm-holder">
                <div class="wrap">
                    <h1 class="wp-heading-inline">IP Blocking</h1>
                    <hr class="wp-header-end">
                    <br>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="manage-column column-author">Sr #</th>
                                <th scope="col" id="categories" class="manage-column column-categories">IP</th>
                                <th scope="col" id="categories" class="manage-column column-categories">Status</th>
                                <th scope="col" id="categories" class="manage-column column-categories">Reason</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Location</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Action</th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                        
                            <?php

                                $images_url = plugin_dir_url(__FILE__);
                                $images_url = str_replace("/assets/php", "", $images_url);
                                $images_url = $images_url."assets/images/flags/";
                                $db = new WP_Adsense_Protector_db();
                                global $wpdb;
                                $plugin_table_prefix = "ads_prtctr_";
                                $bann_ip_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_ip';
                                $ip_detail_ip_tbl = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
                                $bann_countries_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_countries';
                                //$sql = "SELECT * FROM $table order by id desc ";
                                $sql = " SELECT $ip_detail_ip_tbl.*,$bann_ip_tbl.*,$bann_countries_tbl.icon , ";
                                $sql.= " $ip_detail_ip_tbl.ip as real_ip, $bann_ip_tbl.id as ban_id ";
                                $sql.= " FROM `$bann_ip_tbl` ";
                                $sql.= " INNER JOIN $ip_detail_ip_tbl  ";
                                $sql.= " ON $ip_detail_ip_tbl.id = $bann_ip_tbl.ip ";
                                $sql.= " LEFT JOIN $bann_countries_tbl on $bann_countries_tbl.country_name=$ip_detail_ip_tbl.country  ";
                                $sql.= " ORDER BY $bann_ip_tbl.insertion_time DESC ";
                                $result = $db->get($sql);
                                $count=0;
                                foreach ( $result as $print )   {$count++;
                            ?>
                            <tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-uncategorized">
                                <td><?php echo $count; ?></td>
                                <td class="date column-date" data-colname="Date"><?php echo $print->real_ip; ?></td>
                                <td class="date column-date" data-colname="Date">
                                    <?php
                                        $current_time = $print->ban_lift_time;
                                        $block_time   = ads_get_current_time_stamp_db();
                                        $is_ban_lifted = false;
                                        $ban_lift = wap_compare_datetime($current_time, $block_time);
                                        if($print->is_permanent_ban=="1"){
                                            echo "Permanent Block";
                                        } 
                                        else if($print->is_permanent_ban=="0" && $ban_lift==false){
                                            echo "Temporary Block<br/>(Unblock On ";
                                            echo ads_mysql_timestamp_to_humen($print->ban_lift_time);
                                            echo ")";
                                        }else{
                                            echo "Ban Lifted";
                                            $is_ban_lifted = true;
                                        }
                                    ?>
                                </td>
                                <td class="date column-date" data-colname="Date">
                                    <?php
                                        if ($is_ban_lifted==false) {
                                            echo wap_ads_ban_reason($print->ban_type);
                                        }else{
                                            echo "--";
                                        }
                                    ?>    
                                </td>
                                <td>
                                    <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
                                    <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">&nbsp;<?php echo ads_set_location_string($print->country, $print->region, $print->city); ?></td>
                                <td>
                                    <div>
                                        <?php
                                        if ($is_ban_lifted==false) {
                                        ?>
                                        <span class="edit"><a href="<?php echo admin_url(); ?>admin.php?page=adsense-protector-ip-block&amp;action=unblock&amp;token=<?php echo $print->ban_id; ?>">Unblock | </a></span>
                                        <?php } ?>
                                        <?php if($print->is_permanent_ban=="0"){ ?>
                                        <span class="trash"><a style="color: red;" onclick="return confirm('Do you want to block ?php echo $print->real_ip; ?> Permanently ?')" href="<?php echo admin_url(); ?>admin.php?page=adsense-protector-ip-block&amp;action=pblock&amp;token=<?php echo $print->ban_id; ?>">Permanent Block</a></span>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if($count==0){ ?>
                            <tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-uncategorized">
                                <td colspan="6">No Record Found.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th style="width: 5%;" class="manage-column column-author">Sr #</th>
                                <th scope="col" id="categories" class="manage-column column-categories">IP</th>
                                <th scope="col" id="categories" class="manage-column column-categories">Status</th>
                                <th scope="col" id="categories" class="manage-column column-categories">Reason</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Location</th>
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