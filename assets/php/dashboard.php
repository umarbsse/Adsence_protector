<?php
	function dashboard(){
		dashboard_html();
	}
?>
<?php
	function dashboard_html(){

		$images_url = plugin_dir_url(__FILE__);
		$images_url = str_replace("/assets/php", "", $images_url);
		$images_url = $images_url."assets/images/flags/";
		global $wpdb;
		$plugin_table_prefix = "ads_prtctr_";
		$click_tbl = $wpdb->prefix . $plugin_table_prefix.'clicks';
		$pageviews_tbl = $wpdb->prefix . $plugin_table_prefix.'pageviews';
		$bann_country_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_countries';
		$ip_detail_tbl = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
		$limit = 10;
		$where1="";
		$where2="";
		$where3="";
		$where5="";
		$where6="";
        if(isset($_POST['date']) && $_POST['date']!=""){
        	if ($_POST['date']=="today") {
        		$date = ads_get_current_time_stamp_db();
				$where1=" date(clicked_time)=date('$date') ";
				$where2=" date(pageview_time)=date('$date') ";
				$where3=" date(insertion_time)=date('$date') AND UNIX_TIMESTAMP(ban_lift_time)>UNIX_TIMESTAMP('$date') OR is_permanent_ban=1";
				$where5=" date($click_tbl.clicked_time)=date('$date') ";
				$where6=" date($pageviews_tbl.pageview_time)=date('$date') ";
        	}else if ($_POST['date']=="yesterday") {
        		$date =  wap_get_previous_date("-1");
				$where1=" date(clicked_time)=date('$date') ";
				$where2=" date(pageview_time)=date('$date') ";
				$where3=" date(insertion_time)=date('$date') AND UNIX_TIMESTAMP(ban_lift_time)>UNIX_TIMESTAMP('$date') OR is_permanent_ban=1";
				$where5=" date($click_tbl.clicked_time)=date('$date') ";
				$where6=" date($pageviews_tbl.pageview_time)=date('$date') ";
        	}else if ($_POST['date']=="last_7_days") {
        		$current_date = ads_get_current_date_db();
        		$previous_date =  wap_get_previous_date("-7");
				$where1=" DATE(clicked_time) BETWEEN '$previous_date' AND '$current_date' ";
				$where2=" DATE(pageview_time) BETWEEN '$previous_date' AND '$current_date' ";
				$where3 =" is_permanent_ban=1 OR DATE(ban_lift_time) BETWEEN '$previous_date' AND '$current_date' ";
				$where3.=" AND DATE(insertion_time) BETWEEN '$previous_date' AND '$current_date' ";
				$where5=" DATE($click_tbl.clicked_time) BETWEEN '$previous_date' AND '$current_date'";
				$where6=" DATE($pageviews_tbl.pageview_time) BETWEEN '$previous_date' AND '$current_date' ";
        	}else if ($_POST['date']=="this_month") {
        		$date = ads_get_current_time_stamp_db();
				$where1=" MONTH(clicked_time) = MONTH('$date') AND YEAR(clicked_time) = YEAR('$date') ";
				$where2=" MONTH(pageview_time) = MONTH('$date') AND YEAR(pageview_time) = YEAR('$date') ";
				$where3=" MONTH(insertion_time) = MONTH('$date') AND YEAR(insertion_time) = YEAR('$date')
							MONTH(ban_lift_time) = MONTH('$date') AND YEAR(ban_lift_time) = YEAR('$date') 
							OR is_permanent_ban=1";
				$where5=" MONTH($click_tbl.clicked_time)=MONTH('$date')  AND YEAR($click_tbl.clicked_time) = YEAR('$date') ";
				$where6=" MONTH($pageviews_tbl.pageview_time)=MONTH('$date')  AND YEAR($pageviews_tbl.pageview_time) = YEAR('$date') ";
        	}
        }else{
        	$date = ads_get_current_time_stamp_db();
			$where1=" date(clicked_time)=date('$date') ";
			$where2=" date(pageview_time)=date('$date') ";
			$where3=" date(insertion_time)=date('$date') AND UNIX_TIMESTAMP(ban_lift_time)>UNIX_TIMESTAMP('$date') OR is_permanent_ban=1";
			$where5=" date($click_tbl.clicked_time)=date('$date') ";
			$where6=" date($pageviews_tbl.pageview_time)=date('$date') ";
        }
		$sql1 = " SELECT COUNT(*) as total FROM `$click_tbl` WHERE $where1";
		
		$sql2 = " SELECT COUNT(*) as total FROM `$pageviews_tbl` WHERE $where2";

		$sql5 = " SELECT $ip_detail_tbl.country as country_name,$bann_country_tbl.icon, COUNT(*) as total FROM `$click_tbl` ";
		$sql5.= " INNER JOIN $ip_detail_tbl on $ip_detail_tbl.id=$click_tbl.ip ";
		$sql5.= " LEFT JOIN $bann_country_tbl on $bann_country_tbl.country_name=$ip_detail_tbl.country  ";
		$sql5.= " WHERE $where5";
		$sql5.= " GROUP BY $ip_detail_tbl.country ORDER BY COUNT(*) DESC LIMIT $limit";

		//echo $sql5;

		$sql6 = " SELECT $ip_detail_tbl.country as country_name,$bann_country_tbl.icon, COUNT(*) as total FROM `$pageviews_tbl` ";
		$sql6.= " INNER JOIN $ip_detail_tbl on $ip_detail_tbl.id=$pageviews_tbl.ip ";
		$sql6.= " LEFT JOIN $bann_country_tbl on $bann_country_tbl.country_name=$ip_detail_tbl.country  ";
		$sql6.= " WHERE $where6";
		$sql6.= " GROUP BY $ip_detail_tbl.country ORDER BY COUNT(*) DESC LIMIT $limit";
		//echo $sql6;

		$sql7 =" SELECT $ip_detail_tbl.ip,$ip_detail_tbl.id,$ip_detail_tbl.country,$ip_detail_tbl.region,$ip_detail_tbl.city, ";
		$sql7.=" COUNT(*) as total,wp_ads_prtctr_bann_countries.icon  FROM `$click_tbl` ";
		$sql7.=" INNER JOIN $ip_detail_tbl ON $ip_detail_tbl.id=$click_tbl.ip ";
		$sql7.= " LEFT JOIN $bann_country_tbl on $bann_country_tbl.country_name=$ip_detail_tbl.country  ";
		$sql7.=" WHERE $where5 AND $ip_detail_tbl.ip<>''"; 
		$sql7.=" GROUP BY $ip_detail_tbl.id ORDER BY COUNT(*) DESC LIMIT $limit ";
		//echo $sql7;

		$sql8 =" SELECT $ip_detail_tbl.ip,$ip_detail_tbl.country,$ip_detail_tbl.region,$ip_detail_tbl.city, ";
		$sql8.=" COUNT(*) as total,wp_ads_prtctr_bann_countries.icon  FROM `$pageviews_tbl` ";
		$sql8.=" INNER JOIN $ip_detail_tbl ON $ip_detail_tbl.id=$pageviews_tbl.ip ";
		$sql8.= " LEFT JOIN $bann_country_tbl on $bann_country_tbl.country_name=$ip_detail_tbl.country  ";
		$sql8.=" WHERE $where6 AND $ip_detail_tbl.ip<>''"; 
		$sql8.=" GROUP BY $ip_detail_tbl.id ORDER BY COUNT(*) DESC LIMIT $limit ";
		//echo $sql7;
		//Page CTR = Clicks / Page views
		$db = new WP_Adsense_Protector_db();
		$result = $db->get($sql1);
		$click=0;
		foreach ( $result as $print )   {
			$click=$print->total;
		}
		$result = $db->get($sql2);
		$pageviews=0;
		foreach ( $result as $print )   {
			$pageviews=$print->total;
		}
		$ctr = wap_calculate_ctr($click,$pageviews);
		$today_date = explode(" ", $date);
		$today_date = wap_date_to_humen($today_date[0]);
		$top_clicks_result = $db->get($sql5);
		$top_impression_result = $db->get($sql6);
		$top_ip_click_result = $db->get($sql7);
		$top_ip_pageview_result = $db->get($sql8);
		$top_ip_arr= array();
		foreach ( $top_ip_click_result as $print )   {
			array_push($top_ip_arr, $print->id);
		}
		$ips = implode(",", $top_ip_arr);

		$sql9 =" SELECT $ip_detail_tbl.ip,$ip_detail_tbl.country,$ip_detail_tbl.region,$ip_detail_tbl.city, ";
		$sql9.=" COUNT(*) as total,$bann_country_tbl.icon FROM `$pageviews_tbl` ";
		$sql9.=" INNER JOIN $ip_detail_tbl ON $ip_detail_tbl.id=$pageviews_tbl.ip ";
		$sql9.= " LEFT JOIN $bann_country_tbl on $bann_country_tbl.country_name=$ip_detail_tbl.country  ";
		$sql9.=" WHERE $where6";
		if ($ips!="" && $ips!=null) {
			$sql9.=" AND $pageviews_tbl.ip IN (".$ips.")"; 
		}
		$sql9.=" GROUP BY $ip_detail_tbl.id ORDER BY COUNT(*) DESC LIMIT $limit ";
		$top_ip_ctr_result = $db->get($sql9);


		$top_ctr_country_arr= array();
		foreach ( $top_clicks_result as $print )   {
			array_push($top_ctr_country_arr, $print->country_name);
		}
		$top_ctr_country_arr = "'" . implode("','", $top_ctr_country_arr) . "'";
		$sql10 = " SELECT $ip_detail_tbl.country as country_name,$bann_country_tbl.icon, COUNT(*) as total FROM `$pageviews_tbl` ";
		$sql10.= " INNER JOIN $ip_detail_tbl on $ip_detail_tbl.id=$pageviews_tbl.ip ";
		$sql10.= " LEFT JOIN $bann_country_tbl on $bann_country_tbl.country_name=$ip_detail_tbl.country  ";
		$sql10.= " WHERE $where6";
		if ($top_ctr_country_arr!="" && $top_ctr_country_arr!=null) {
			$sql10.=" AND $ip_detail_tbl.country IN (".$top_ctr_country_arr.")"; 
		}
		$sql10.= " GROUP BY $ip_detail_tbl.country ORDER BY COUNT(*) DESC LIMIT $limit";
		//echo $sql10;
		$top_country_ctr_result = $db->get($sql10);
?>
<style type="text/css">
	.fixed .column-author{
    width: 50%;
}
</style>
	<div class="ai1wm-container"> 
		<h1 style="float: left;">Summary
			<?php
				if(isset($_POST['date']) && $_POST['date']=="last_7_days"){
        			echo "(".wap_date_to_humen($previous_date)."-".wap_date_to_humen($current_date).")";
				}else if(isset($_POST['date']) && $_POST['date']=="this_month"){
        			echo "(".wap_month_to_humen(ads_get_current_date_db()).")";
				}else{
					echo "(".wap_date_to_humen($today_date).")";
				}
			?>
		</h1>
		<form style="float: right;" action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
			<br>
			<select name="date" required="">
				<option value="">--Select Date--</option>
				<option <?php if(isset($_POST['date']) && $_POST['date']=="today"){echo "selected";} ?> value="today">Today</option>
				<option <?php if(isset($_POST['date']) && $_POST['date']=="yesterday"){echo "selected";} ?> value="yesterday">Yesterday</option>
				<option <?php if(isset($_POST['date']) && $_POST['date']=="last_7_days"){echo "selected";} ?> value="last_7_days">Last 7 Days</option>
				<option <?php if(isset($_POST['date']) && $_POST['date']=="this_month"){echo "selected";} ?> value="this_month">This Month</option>
			</select>
			<button type="submit" value="submit" class="submit_btn" name="submit">Filter</button>
		</form>  
	    <div class="ai1wm-row" style="margin-right: 0;">
	        <div class="ai1wm-left">
	            <div class="ai1wm-holder">
	            	<br>
					<div class="column-left text-center">
	                	<h1>
	                		<?php $number = $click; echo number_format($number); ?>
	                	</h1>
	                	<h3>Click</h3>
	                </div>
	                <div class="column-center text-center">
	                	<h1>
	                		<?php $number = $pageviews; echo number_format($number); ?>
	                	</h1>
	                	<h3>Pageviews</h3>
	                </div>
					<div class="column-right text-center">
	                	<h1>
	                		<?php echo $ctr."%"; ?>
	                	</h1>
	                	<h3>Clickthrough Rate (CTR)</h3>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
	<br>
					<div class="column-left">
	                	<h3>Clicks (Country Wise)</h3>
	                	<table class="wp-list-table widefat fixed striped posts">
	                        <thead>
	                            <tr>
	                                <th class="manage-column column-author">Country Name</th>
	                                <th scope="col" id="categories" class="manage-column column-categories">Clicks</th>
	                            </tr>
	                        </thead>
	                        <tbody id="the-list">
	                        	<?php foreach ( $top_clicks_result as $print )   {?>
	                        	<tr>            
	                                <td>
	                                <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
	                                <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">
	                                <?php if($print->country_name==""){echo "Other";}else{echo $print->country_name;} ?>
	                                </td>                               
	                                <td><?php echo $print->total; ?></td>
	                            </tr>
	                        	<?php } ?>
	                        	<?php for ($i=0; $i <$limit-count($top_clicks_result) ; $i++) {?>
	                        	<tr>            
	                                <td>-</td>                               
	                                <td>-</td>
	                            </tr>
	                        	<?php } ?>
	                        </tbody>
                        </table>
	                </div>
	                <div class="column-center">
	                	<h3>Pageviews (Country Wise)</h3>
	                	<table class="wp-list-table widefat fixed striped posts">
	                        <thead>
	                            <tr>
	                                <th class="manage-column column-author">Country Name</th>
	                                <th scope="col" id="categories" class="manage-column column-categories">Pageviews</th>
	                            </tr>
	                        </thead>
	                        <tbody id="the-list">
	                        	<?php foreach ( $top_impression_result as $print )   {?>
	                        	<tr>            
	                                <td>
	                                <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
	                                <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">
	                                <?php if($print->country_name==""){echo "Other";}else{echo $print->country_name;} ?></td>
	                                <td><?php echo $print->total; ?></td>
	                            </tr>
	                        	<?php } ?>
	                        	<?php for ($i=0; $i <$limit-count($top_impression_result) ; $i++) {?>
	                        	<tr>            
	                                <td>-</td>                               
	                                <td>-</td>
	                            </tr>
	                        	<?php } ?>
	                        </tbody>
                        </table>
	                </div>
					<div class="column-right">
	                	<h3>Clickthrough Rate (Country Wise)</h3>
	                	<table class="wp-list-table widefat fixed striped posts">
	                        <thead>
	                            <tr>
	                                <th class="manage-column column-author">Country Name</th>
	                                <th scope="col" id="categories" class="manage-column column-categories">CTR</th>
	                            </tr>
	                        </thead>
	                        <tbody id="the-list">
	                        	<?php foreach ( $top_country_ctr_result as $print )   {?>
	                        	<tr>            
	                                <td>	                                
	                                <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
	                                <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">
	                                <?php if($print->country_name==""){echo "Other";}else{echo $print->country_name;} ?></td>
	                                <td><?php echo wap_top_ctr_calculate($top_clicks_result,$top_country_ctr_result, $print->country_name)."%"; ?></td>
	                            </tr>
	                        	<?php } ?>
	                        	<?php for ($i=0; $i <$limit-count($top_country_ctr_result) ; $i++) {?>
	                        	<tr>            
	                                <td>-</td>                               
	                                <td>-</td>
	                            </tr>
	                        	<?php } ?>
	                        </tbody>
                        </table>
	                </div>
	                <div class="column-left">
	                	<h3>Clicks (IP Wise)</h3>
	                	<table class="wp-list-table widefat fixed striped posts">
	                        <thead>
	                            <tr>
	                                <th class="manage-column column-author">Country Name</th>
	                                <th scope="col" id="categories" class="manage-column column-categories">Clicks</th>
	                            </tr>
	                        </thead>
	                        <tbody id="the-list">
	                        	<?php foreach ( $top_ip_click_result as $print )   {?>
	                        	<tr>            
	                                <td>
	                                <strong>
	                                <?php echo $print->ip."</strong><br/>"; ?>
	                                <p>
	                                <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
	                                <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">
	                                <?php echo ads_set_location_string($print->country, $print->region, $print->city); ?>
	                                </p>                               	
	                                </td>                               
	                                <td><?php echo $print->total; ?></td>
	                            </tr>
	                        	<?php } ?>
	                        	<?php for ($i=0; $i <$limit-count($top_ip_click_result) ; $i++) {?>
	                        	<tr>            
	                                <td>-</td>                               
	                                <td>-</td>
	                            </tr>
	                        	<?php } ?>
	                        </tbody>
                        </table>
	                </div>
	                <div class="column-center">
	                	<h3>Pageviews (IP Wise)</h3>
	                	<table class="wp-list-table widefat fixed striped posts">
	                        <thead>
	                            <tr>
	                                <th class="manage-column column-author">Country Name</th>
	                                <th scope="col" id="categories" class="manage-column column-categories">Pageviews</th>
	                            </tr>
	                        </thead>
	                        <tbody id="the-list">
	                        	<?php foreach ( $top_ip_pageview_result as $print )   {?>
	                        	<tr>            

	                                <td>
	                                <strong>
	                                <?php echo $print->ip."</strong><br/>"; ?>
	                                <p>
	                                <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
	                                <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">
	                                <?php echo ads_set_location_string($print->country, $print->region, $print->city); ?>
	                                </p>                               	
	                                </td>                               
	                                <td><?php echo $print->total; ?></td>
	                            </tr>
	                        	<?php } ?>
	                        	<?php for ($i=0; $i <$limit-count($top_ip_pageview_result) ; $i++) {?>
	                        	<tr>            
	                                <td>-</td>                               
	                                <td>-</td>
	                            </tr>
	                        	<?php } ?>
	                        </tbody>
                        </table>
	                </div>
					<div class="column-right">
	                	<h3>Clickthrough Rate (IP Wise)</h3>
	                	<table class="wp-list-table widefat fixed striped posts">
	                        <thead>
	                            <tr>
	                                <th class="manage-column column-author">Country Name</th>
	                                <th scope="col" id="categories" class="manage-column column-categories">CTR</th>
	                            </tr>
	                        </thead>
	                        <tbody id="the-list">
	                        	<?php foreach ( $top_ip_ctr_result as $print )   {?>
	                        	<tr>       
	                                <td>
	                                <strong>
	                                <?php echo $print->ip."</strong><br/>"; ?>
	                                <p>
	                                <?php if($print->icon==""){$print->icon = get_other_location_image();} ?>
	                                <img class="flag_small" src="<?php echo $images_url.$print->icon; ?>">
	                                <?php echo ads_set_location_string($print->country, $print->region, $print->city); ?>
	                                </p>                               	
	                                </td>                        
	                                <td><?php echo wap_top_ctr_ip_calculate($top_ip_click_result,$top_ip_ctr_result, $print->ip)."%"; ?></td>
	                            </tr>
	                        	<?php } ?>
	                        	<?php for ($i=0; $i <$limit-count($top_ip_ctr_result) ; $i++) {?>
	                        	<tr>            
	                                <td>-</td>                               
	                                <td>-</td>
	                            </tr>
	                        	<?php } ?>
	                        </tbody>
                        </table>
	                </div>
<br>
<?php
	}
?>