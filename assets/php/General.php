<?php
	class WP_Adsense_Protector_General{
        var $code_id=0;
        var $max_tabs=4;
        //Maximum ads where site can open ads
        function __construct() {
            //include('functions.php');
            include('useragent/browser.php');
            include('useragent/operating_system.php');

        }
        function manual_ban_ip(){
            if (isset($_POST['action']) && $_POST['action']=="handle_ban_ip") {
                global $wpdb;
                $plugin_table_prefix = "ads_prtctr_";
                $config_tbl = $wpdb->prefix . $plugin_table_prefix.'config';
                $bann_ip_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_ip';
                $ip = $this->send_ip_id($_POST['ip']);
                $db = new WP_Adsense_Protector_db();
                if (isset($_POST['status']) && $_POST['status']=="unblock") {
                    $data = array( 
                        'ip' => $ip,
                        'is_permanent_ban' => 0,
                        'ban_type' => 1,
                        'ban_lift_time' => $ip,
                        'insertion_time'=>ads_get_current_time_stamp_db()
                    );
                    $sql = "SELECT * FROM $config_tbl LIMIT 1";
                    $result = $db->get($sql);
                    $manual_ban_days = 2;
                    foreach ( $result as $print )   {
                        $manual_ban_days = (int)$print->manual_ban_days;
                    }
                    $ban_lift_time = ads_add_hour_to_curent_time($manual_ban_days*24, '+');
                    $data['ban_lift_time'] =  $ban_lift_time;
                    $sql = "SELECT * FROM $bann_ip_tbl where ip='".$ip."' LIMIT 1 ";
                    $result = $db->get($sql);
                    $count = 0;
                    $insert_id = 0;
                    $number_of_time_blocks = 0;
                    foreach ( $result as $print )   {
                        $count++;
                        $insert_id= $print->id;
                        $number_of_time_blocks= (int)$print->number_of_block;
                    }
                    if ($count==1) {
                        $data['number_of_block'] =  $number_of_time_blocks+1;
                        $where = array( 'ID' => $insert_id );
                        $db->update($bann_ip_tbl, $data, $where );
                    }else{
                        $data['number_of_block'] =  1;
                        $db->insert($bann_ip_tbl, $data);
                    }
                }
                if (isset($_POST['status']) && $_POST['status']=="block") {
                    $data = array(
                        'is_permanent_ban' => 0,
                        'ban_lift_time' => ads_add_hour_to_curent_time(100, '-'),
                    );
                    $where = array( 'ip' => $ip );
                    $db->update($bann_ip_tbl, $data, $where );
                }
            }
        }
        function add_ban(){
            if (isset($_POST['action']) && $_POST['action']=="handle_ban_countries") {
                $data = array( 
                    'is_ban' => $_POST['is_ban']
                );
                global $wpdb;
                $plugin_table_prefix = "ads_prtctr_";
                $table_name = $wpdb->prefix . $plugin_table_prefix.'bann_countries';
                $where = array( 'ID' => $_POST['token'] );
                $obj =  new WP_Adsense_Protector_db();
                $obj->update($table_name, $data, $where );
            }
        }
        function wap_get_adcode() {
            $show_ads=true;
            if ($this->code_id==0 || $this->code_id=="" || $this->code_id==NULL) {
                $this->wap_ads_empty_response();
                $show_ads=false;
                return '';
            }            
            $is_ban = $this->wap_filter_all_bans();
            if ($is_ban==true) {
                $show_ads=false;
            }            
            if ($show_ads==true) {
                return $this->print_ad_code();
            }else{
                $this->wap_ads_empty_response();
            }
        }        
        function wap_filter_all_bans(){
            $ip_id = ads_get_ip();
            //echo $ip_id;die();
            if ($ip_id==null || $ip_id=="") {
                return true;
            }
            $is_ban = $this->wap_filter_is_ip_ban();
            if ($is_ban==true) {
                return true;
            }
            $is_ban = $this->wap_filter_pageviews_exceed();
            if ($is_ban==true) {
                return true;
            }
            $is_ban = $this->wap_filter_clicks_exceed();
            if ($is_ban==true) {
                return true;
            }            
            $is_ban = $this->wap_filter_tabs_exceed();
            if ($is_ban==true) {
                return true;
            }
            $is_ban = $this->wap_ad_is_country_ban();
            if ($is_ban==true) {
                return true;
            }            
            return false;
        }        
        function wap_filter_is_ip_ban(){

            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $tbl = $wpdb->prefix . $plugin_table_prefix.'bann_ip';    
            $country = $this->wap_get_countryname();
            $ip = $this->send_ip_id();
            $sql = "SELECT * FROM `$tbl` where ip='".$ip."'";
            $db = new WP_Adsense_Protector_db();
            $result = $db->get($sql);
            if (count($result)>0) {
                foreach ( $result as $print )   {
                    $is_ban =  ads_is_ip_ban($print->is_permanent_ban, $print->ban_lift_time);
                }
                if ($is_ban==true) {
                    return true;
                }
            }
            return false; 
            /*$CI = & get_instance();
            $is_ban = $CI->db->query("SELECT * FROM `ads_bann_ip` where ip='".ads_get_ip()."' ORDER BY id DESC LIMIT 1")->result_array();
            if (count($is_ban)==1) {
                if ($is_ban[0]['is_permanent_ban']=="1") {
                    return true;
                }
                if ($is_ban[0]['ban_lift_time']!='0000-00-00 00:00:00') {               
                    $current_time = ads_current_time_stamp_db();
                    $ban_lift_time = strtotime($is_ban[0]['ban_lift_time']);
                    if($current_time > date('Y-m-d H:i:s', $ban_lift_time)){
                        return false;  
                    }else{
                        return true;
                    }
                }
            }
            return false;*/
        }
        function wap_ad_is_country_ban(){
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $tbl = $wpdb->prefix . $plugin_table_prefix.'bann_countries';    
            $country = $this->wap_get_countryname();
            $sql = "SELECT * FROM `$tbl` where is_ban=1 AND LOWER(country_name)=LOWER('".$country."')";
            $db = new WP_Adsense_Protector_db();
            $result = $db->get($sql);
            if (count($result)>0) {
                return true;
            }
            return false; 
        }

        function wap_filter_pageviews_exceed(){
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $config_tbl = $wpdb->prefix . $plugin_table_prefix.'config';
            $pgeviews_tbl = $wpdb->prefix . $plugin_table_prefix.'pageviews';
            $ip_detail = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
            $sql = "SELECT * FROM $config_tbl LIMIT 1";
            $max_pageviews_limit=10;
            $max_pageview_lift_time_in_hour=24;
            $db = new WP_Adsense_Protector_db();
            $result = $db->get($sql);
            foreach ( $result as $print )   {
                $max_pageviews_limit = (int)$print->max_pageviews;
                $max_pageview_lift_time_in_hour = (int)$print->max_pageview_lift_time_in_hour;
            }
            $current_time = ads_get_current_time_stamp_db();
            $last_24_hour_time = ads_add_hour_to_curent_time($max_pageview_lift_time_in_hour, '-');
            $sql = "SELECT count($pgeviews_tbl.id) as total FROM `$pgeviews_tbl` ";
            $sql.= "INNER JOIN $ip_detail on $ip_detail.id=$pgeviews_tbl.ip ";
            $sql.= "WHERE $ip_detail.ip='".ads_get_ip()."' AND ";
            $sql.= "$pgeviews_tbl.pageview_time BETWEEN '$last_24_hour_time' AND '$current_time' ";
            $result = $db->get($sql);
            $total_pageviews =0 ;
            foreach ( $result as $print )   {
                $total_pageviews = (int)$print->total;
            }
            if ($max_pageviews_limit==0) {
                return false;
            }
            if ($total_pageviews>=$max_pageviews_limit) {
                $this->wap_block_ip_for_pageview_clicks_increase("pageview");
                return true;
            }
            return false;
        }
        function wap_filter_clicks_exceed(){
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $config_tbl = $wpdb->prefix . $plugin_table_prefix.'config';
            $clicks_tbl = $wpdb->prefix . $plugin_table_prefix.'clicks';
            $ip_detail = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
            $sql = "SELECT * FROM $config_tbl LIMIT 1";
            $max_click_limit=1;
            $max_click_lift_time_in_hour=24;
            $db = new WP_Adsense_Protector_db();
            $result = $db->get($sql);
            foreach ( $result as $print )   {
                $max_click_limit = (int)$print->max_click_limit;
                $max_click_lift_time_in_hour = (int)$print->max_click_lift_time_in_hour;
            }
            $current_time = ads_get_current_time_stamp_db();
            $last_24_hour_time = ads_add_hour_to_curent_time($max_click_lift_time_in_hour, '-');
            $sql = "SELECT count($clicks_tbl.id) as total FROM `$clicks_tbl` ";
            $sql.= "INNER JOIN $ip_detail on $ip_detail.id=$clicks_tbl.ip ";
            $sql.= "WHERE $ip_detail.ip='".ads_get_ip()."' AND ";
            $sql.= "$clicks_tbl.clicked_time BETWEEN '$last_24_hour_time' AND '$current_time' ";
            $result = $db->get($sql);
            $total_clicks =0 ;
            foreach ( $result as $print )   {
                $total_clicks = (int)$print->total;
            }
            if ($max_click_limit==0) {
                return false;
            }
            if ($total_clicks>=$max_click_limit) {
                $this->wap_block_ip_for_pageview_clicks_increase("click");
                return true;
            }
            return false;
        }
        function wap_filter_tabs_exceed(){            
            $number_of_tabs = $this->wap_ad_set_num_of_tabs();
            //echo $number_of_tabs." ".$this->max_tabs;
            if ($number_of_tabs>$this->max_tabs) {
                $this->wap_ads_empty_response();
                return true;
            }
            return false;
        }
        function wap_ad_set_num_of_tabs(){
            $cookie_name="number_of_tabs_opened";
            $cookie_value=ads_get_cookie($cookie_name);
            if ($cookie_value===false) {
                $cookie_value=0;
            }
            return $cookie_value;
        }        
        function wap_ads_empty_response(){
            /*
            echo '<p style="background: red;color: white;padding: 5px;border-radius: 9px;">';
            echo "ADSENS BAN ON YOUR IP<br/>";
            echo '</p>';
            */
            return '';
        }
        function print_ad_code(){
            $code_id = $this->code_id;
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $table = $wpdb->prefix . $plugin_table_prefix.'code';
            $sql = "SELECT * FROM $table WHERE id=".$code_id." LIMIT 1";
            $db = new WP_Adsense_Protector_db();
            $result = $db->get($sql);
            foreach ( $result as $print )   {
              $code =  $db->remove_extra_chars($print->code);
              $html = "<div class='advertisement_block";
              if ($print->attr_class!="") {
                  $html.=" ".$print->attr_class;
              }
              $html.= "'";
              if ($print->attr_id!="") {
                  $html.=" id='".$print->attr_id."'";
              }

              $html.= ">".$code."</div>";
              return $html;
            }
        }
        function add_pageview(){
            if (isset($_POST['action']) && $_POST['action']=="handle_impression") {
                $data = array( 
                    'referrer' => $_POST['adUrl'],
                    'referrer_host' => ads_get_domain_name($_POST['adUrl']),
                    'pageview_time' => ads_get_current_time_stamp_db()
                );
                $obj = new WP_Adsense_Protector_Browser();
                $data['user_agent_name']=$obj->getBrowser();
                $data['browser_version']=$obj->getVersion();
                if ($obj->isMobile()){
                    $data['user_agent_type']=2;
                }else if ($obj->isTablet()){
                    $data['user_agent_type']=3;
                }else if ($obj->isAol()){
                    $data['user_agent_type']=4;
                } else if ($obj->isFacebook()){
                    $data['user_agent_type']=5;
                } else if ($obj->isRobot()){
                    $data['user_agent_type']=6;
                } else{
                    $data['user_agent_type']=1;
                }          
                $data['OS']=getOS();
                $data['ip']=$this->send_ip_id();
                global $wpdb;
                $plugin_table_prefix = "ads_prtctr_";
                $your_db_name = $wpdb->prefix . $plugin_table_prefix.'pageviews';
                $obj =  new WP_Adsense_Protector_db();
                $obj->insert($your_db_name, $data);
            }
        }
        function add_click(){
            if (isset($_POST['action']) && $_POST['action']=="handle_click") {
                $data = array( 
                    'referrer' => $_POST['adUrl'],
                    'referrer_host' => ads_get_domain_name($_POST['adUrl'])
                );
                $obj = new WP_Adsense_Protector_Browser();
                $data['user_agent_name']=$obj->getBrowser();
                $data['browser_version']=$obj->getVersion();
                if ($obj->isMobile()){
                    $data['user_agent_type']=2;
                }else if ($obj->isTablet()){
                    $data['user_agent_type']=3;
                }else if ($obj->isAol()){
                    $data['user_agent_type']=4;
                } else if ($obj->isFacebook()){
                    $data['user_agent_type']=5;
                } else if ($obj->isRobot()){
                    $data['user_agent_type']=6;
                } else{
                    $data['user_agent_type']=1;
                }          
                $data['OS']=getOS();
                $data['clicked_time']=ads_get_current_time_stamp_db();
                $data['ip']=$this->send_ip_id();
                global $wpdb;
                $plugin_table_prefix = "ads_prtctr_";
                $your_db_name = $wpdb->prefix . $plugin_table_prefix.'clicks';
                $obj =  new WP_Adsense_Protector_db();
                $obj->insert($your_db_name, $data);
            }
        }
        function send_ip_id($ip_id=null){
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $your_db_name = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
            if ($ip_id==null) {
               $ip_id = ads_get_ip();
            }
            $sql = "SELECT * FROM $your_db_name where ip='".$ip_id."' LIMIT 1 ";
            $obj =  new WP_Adsense_Protector_db();
            $geo = $obj->get($sql);
            if (count($geo)==0) {
                $id = $this->insert_ip_id_in_db($ip_id);
                return $id;
            }else{
                foreach ( $geo as $print )   {
                    if ($print->country=="" || $print->country==NULL) {
                        $this->insert_ip_id_in_db($print->ip,$print->id);
                    }
                    return $print->id;
                }
            }
            return 0;
        }
        function insert_ip_id_in_db($ip=null, $id=null){    
            global $wpdb;
            $obj =  new WP_Adsense_Protector_db();
            $plugin_table_prefix = "ads_prtctr_";
            $your_db_name = $wpdb->prefix . $plugin_table_prefix.'ip_detail';        
            $geo_location = ads_get_geo_live($ip);
            $data['country']=$geo_location['geoplugin_countryName'];
            $data['region']=$geo_location['geoplugin_regionName'];
            $data['city']=$geo_location['geoplugin_city'];
            $data['latitude']=$geo_location['geoplugin_latitude'];
            $data['longitude']=$geo_location['geoplugin_longitude'];
            $data['insertion_time']=ads_get_current_time_stamp_db();
            $data['ip']=$ip;
            if ($id==null) {
                $id = $obj->insert($your_db_name, $data);
                return $id;
            }else{               
                $where = array( 'ID' => $id );
                $obj->update($your_db_name, $data, $where);
            }
        }
        function wap_get_countryname(){
            $ip = $this->send_ip_id();
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $ip_detail = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
            $sql = "SELECT * FROM $ip_detail WHERE id=".$ip;
            $obj =  new WP_Adsense_Protector_db();
            $geo = $obj->get($sql);
            foreach ( $geo as $print )   {
                return $print->country;
            }
            return '';
        }

        function wap_block_ip_for_pageview_clicks_increase($calling_fun){
                global $wpdb;
                $plugin_table_prefix = "ads_prtctr_";
                $config_tbl = $wpdb->prefix . $plugin_table_prefix.'config';
                $bann_ip_tbl = $wpdb->prefix . $plugin_table_prefix.'bann_ip';
                $ip = $this->send_ip_id(ads_get_ip());
                $db = new WP_Adsense_Protector_db();
                $data = array( 
                    'ip' => $ip,
                    'is_permanent_ban' => 0,
                    'ban_lift_time' => $ip,
                    'insertion_time'=>ads_get_current_time_stamp_db()
                );
                $sql = "SELECT * FROM $config_tbl LIMIT 1";
                $result = $db->get($sql);
                $block_hours = 24;
                foreach ( $result as $print )   {
                    if($calling_fun=="click"){
                        $data['ban_type'] =  3;                        
                        $block_hours = (int)$print->max_click_lift_time_in_hour;
                    }else{
                        $data['ban_type'] =  2;   
                        $block_hours = (int)$print->max_pageview_lift_time_in_hour;
                    }
                }
                $ban_lift_time = ads_add_hour_to_curent_time($block_hours, '+');
                $data['ban_lift_time'] =  $ban_lift_time;
                $sql = "SELECT * FROM $bann_ip_tbl where ip='".$ip."' LIMIT 1 ";
                $result = $db->get($sql);
                $count = 0;
                $insert_id = 0;
                $number_of_time_blocks = 0;
                foreach ( $result as $print )   {
                    $count++;
                    $insert_id= $print->id;
                    $number_of_time_blocks= (int)$print->number_of_block;
                }
                if ($count==1) {
                    $data['number_of_block'] =  $number_of_time_blocks+1;
                    $where = array( 'ID' => $insert_id );
                    $db->update($bann_ip_tbl, $data, $where );
                }else{
                    $data['number_of_block'] =  1;
                    $db->insert($bann_ip_tbl, $data);
                }
        }

        function wap_set_empty_ips_data(){
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $ip_detail = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
            $LIMIT = " LIMIT 10 ";
            $sql = "SELECT * FROM $ip_detail WHERE country='' ORDER BY insertion_time DESC ".$LIMIT;
            $obj =  new WP_Adsense_Protector_db();
            $geo = $obj->get($sql);
            foreach ( $geo as $print )   {
                $this->insert_ip_id_in_db($print->ip, $print->id);
            }
        }

        function wap_maintain_record_limitation(){
            global $wpdb;
            $plugin_table_prefix = "ads_prtctr_";
            $config = $wpdb->prefix . $plugin_table_prefix.'config';
            $pageview = $wpdb->prefix . $plugin_table_prefix.'pageviews';
            $clicks = $wpdb->prefix . $plugin_table_prefix.'clicks';
            $ip_detail = $wpdb->prefix . $plugin_table_prefix.'ip_detail';
            $sql = "SELECT * FROM $config ";
            $obj =  new WP_Adsense_Protector_db();
            $result = $obj->get($sql);
            $pageviews_record_limit=0;
            $click_record_limit=0;
            foreach ( $result as $print )   {
                $pageviews_record_limit=(int)$print->pageviews_record_limit;
                $click_record_limit=(int)$print->clicks_record_limit;
            }
            /*---------------------START PAGEVIEW RECORD LIMITATION-----------------------*/
            $sql = "SELECT count(id) as total FROM $pageview ";
            $result = $obj->get($sql);
            $total_pageviews=0;
            foreach ( $result as $print )   {
                $total_pageviews=(int)$print->total;
            }
            if ($total_pageviews>$pageviews_record_limit) {
                $limit = $total_pageviews-$pageviews_record_limit;
                $sql = "DELETE FROM `$pageview` ORDER BY pageview_time ASC LIMIT ".$limit;
                $wpdb->query($sql);
            }
            /*---------------------END CLICK PAGEVIEW LIMITATION-----------------------*/
            /*---------------------START CLICK RECORD LIMITATION-----------------------*/
            $sql = "SELECT count(id) as total FROM $clicks ";
            $result = $obj->get($sql);
            $total_clicks=0;
            foreach ( $result as $print )   {
                $total_clicks=(int)$print->total;
            }
            if ($total_clicks>$click_record_limit) {
                $limit = $total_clicks-$click_record_limit;
                $sql = "DELETE FROM `$clicks` ORDER BY clicked_time ASC LIMIT ".$limit;
                $wpdb->query($sql);
            }
            /*---------------------END CLICK RECORD LIMITATION------------------------*/
        }
    }
?>