<?php
	function impression(){
		impression_setting();
        impression_html();
	}
?>
<?php
    function impression_setting(){
        if (isset($_GET['action']) && $_GET['action']!="" && $_GET['action']=="clear_records") {
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $table = $wpdb->prefix . $plugin_table_prefix.'pageviews';
            $delete = $wpdb->query("TRUNCATE TABLE $table");
            wp_redirect(admin_url().'admin.php?page=adsense-protector-impression');
        }
    }
?>
<?php
	function impression_html(){
        $images_url = plugin_dir_url(__FILE__);
        $images_url = str_replace("/assets/php", "", $images_url);
        $images_url = $images_url."assets/images/flags/";
        $db = new WP_Adsense_Protector_db();
        global $wpdb;
        $plugin_table_prefix = "ads_prtctr_";
        $pageview_tbl = $wpdb->prefix . $plugin_table_prefix.'pageviews';
        $ip_detail_tbl = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
        $bann_ip_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_ip';
        $bann_countries_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_countries';
        $where="";
        if (isset($_POST['ip']) && $_POST['ip']!="") {
            $where = " $ip_detail_tbl.ip ='".$wpdb->escape($_POST['ip'])."' ";
        }
        if (isset($_POST['country']) && $_POST['country']!="") {            
            if ($where!="") {
                $where = $where." AND ";
            }
            $where.= " $ip_detail_tbl.country ='".$wpdb->escape($_POST['country'])."' ";
        }
        if ($where!="") {
            $where = " WHERE ".$where;
        }
        $sql = " SELECT $pageview_tbl.*,$ip_detail_tbl.*,$bann_ip_tbl.*,$ip_detail_tbl.ip as real_ip,$bann_countries_tbl.icon ";
        $sql.= " FROM `$pageview_tbl` "; 
        $sql.= " LEFT JOIN $ip_detail_tbl ";
        $sql.= " ON $ip_detail_tbl.id=$pageview_tbl.ip ";
        $sql.= " LEFT JOIN $bann_countries_tbl on $bann_countries_tbl.country_name=$ip_detail_tbl.country  ";
        $sql.= " LEFT JOIN $bann_ip_tbl  ";
        $sql.= " ON $bann_ip_tbl.ip=$pageview_tbl.ip ".$where;
        $sql.= " ORDER BY  $pageview_tbl.id DESC ";
        //echo $sql;
        $result = $db->get($sql);
        $sql1 = " SELECT country_name FROM `$bann_countries_tbl` ";
        $sql1.= "  ORDER BY `$bann_countries_tbl`.`country_name` ASC ";
        $country_list = $db->get($sql1);
        $count=0;
?>
    <div class="ai1wm-container">
        <h1 style="float: left;">Pageviews</h1>

        <form style="float: right;margin-top: 7px;" action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
             <br>
            <a class="clear_record" onclick="return confirm('Are you sure to clear all clicks record ?')" href="<?php echo admin_url(); ?>admin.php?page=adsense-protector-clicks&amp;action=clear_records" name="submit">Clear Records</a>
        </form>
        <form style="float: right;margin-right: 10px;" action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
            <div style="float: left;">
                <strong>IP</strong><br>
                <input type="text" value="<?php if (isset($_POST['ip']) && $_POST['ip']!="") {echo $_POST['ip'];} ?>" style="width: 200px;margin: 0;padding: 0;margin-right: 2px;" name="ip" class="ap-input">
            </div>
            <div style="float: left;">
                <strong>Country</strong><br>
                <select name="country" style="width: 140px;">
                    <option value="">--Select Country--</option>
                    <?php foreach ($country_list as $print ){?>
                    <option <?php if(isset($_POST['country']) && $_POST['country']!="" && $print->country_name==$_POST['country']){echo "selected";} ?> value="<?php echo $print->country_name; ?>"><?php echo $print->country_name; ?></option>
                    <?php }?>
                </select>
            </div>
            <div style="float: left;">
             <br>
                <button type="submit" value="submit" class="submit_btn" name="submit">Filter</button>
            </div>
        </form>             
        <div class="ai1wm-row" style="margin-right: 0;">
            <div class="ai1wm-left">
                <div class="">
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="manage-column column-author">Sr #</th>
                                <th scope="col" id="categories" class="manage-column column-categories">IP</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Location</th>
                                <th scope="col" id="tags" class="manage-column column-tags">User agent Type</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Browser</th>
                                <th scope="col" id="tags" class="manage-column column-tags">OS</th>
                                <th style="width: 10%;" scope="col" id="tags" class="manage-column column-tags">Time</th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php
                                foreach ( $result as $print )   {
                                    $count++;
                            ?>
                            <tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-uncategorized">
                                <td><?php echo $count; ?></td>
                                <td class="date column-date" data-colname="Date"><?php echo $print->real_ip; ?><br>
                                    <?php
                                        $is_ban =  ads_is_ip_ban($print->is_permanent_ban, $print->ban_lift_time);
                                        if ($is_ban==true) {
                                    ?>

                                    <span data-status="block" data-ip="<?php echo $print->real_ip; ?>" style="background: yellowgreen;" class="block_ip">Unblock IP</span> 
                                    <?php
                                        }else{
                                    ?>
                                    <span data-status="unblock" data-ip="<?php echo $print->real_ip; ?>" class="block_ip">Block IP</span> 
                                    <?php
                                        }
                                    ?>
                                </td>
                                <td class="date column-date" data-colname="Date">
                                    <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
                                    <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">
                                <?php echo ads_set_location_string($print->country, $print->region, $print->city); ?></td>
                                <td class="date column-date" data-colname="Date">
                                    <?php echo ads_get_useragent_string($print->user_agent_type); ?>
                                </td>
                                <td class="date column-date" data-colname="Date">
                                    <?php 
                                        echo $print->user_agent_name; 
                                        if ($print->browser_version!=""){echo " (".$print->browser_version.")";} 
                                    ?>
                                </td>
                                <td class="date column-date" data-colname="Date">
                                    <?php echo $print->OS; ?>
                                </td>
                                <td class="date column-date" data-colname="Date">
                                    <?php echo ads_mysql_timestamp_to_humen($print->pageview_time); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if($count==0){ ?>
                            <tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-uncategorized">
                                <td colspan="7">No Record Found.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th style="width: 5%;" class="manage-column column-author">Sr #</th>
                                <th scope="col" id="categories" class="manage-column column-categories">IP</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Location</th>
                                <th scope="col" id="tags" class="manage-column column-tags">User agent Type</th>
                                <th scope="col" id="tags" class="manage-column column-tags">Browser</th>
                                <th scope="col" id="tags" class="manage-column column-tags">OS</th>
                                <th style="width: 10%;" scope="col" id="tags" class="manage-column column-tags">Time</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
	}
?>