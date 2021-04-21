<?php
    function ad_set_num_of_tabs(){
        $cookie_name="number_of_tabs_opened";
        $cookie_value=ads_get_cookie($cookie_name);
        if ($cookie_value===false) {
            $cookie_value=0;
        }
        return $cookie_value;
    }
    function ad_filter_all_bans(){
        $is_ban = ad_pageviews();
        if ($is_ban==true) {
            return true;
        }
        $is_ban = ad_clicks();
        if ($is_ban==true) {
            return true;
        }
        $is_ban = ad_is_ip_ban();
        if ($is_ban==true) {
            return true;
        }
        $is_ban = ad_is_country_ban();
        if ($is_ban==true) {
            return true;
        }
        
        return false;
    }
    function ad_is_country_ban(){
        $CI = & get_instance();
        $country = ads_get_geo_location();
        $country = $country['geoplugin_countryName'];
        $is_ban_countries = $CI->db->query("SELECT * FROM `ads_bann_countries` where is_ban=1 AND LOWER(country_name)=LOWER('".$country."')")->result_array();
        if (count($is_ban_countries)>0) {
            return true;
        }
        return false;
    }
    function ad_is_ip_ban(){
        $CI = & get_instance();
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
        return false;
    }
    function ad_clicks(){
        $CI = & get_instance();
        $CI->load->library('user_agent');
        $clicks_config = $CI->db->query("SELECT * FROM `ads_config` LIMIT 1")->result_array();
        $total_clicks = $CI->db->query("SELECT count(*) as total FROM `ads_clicks` WHERE ip='".ads_get_ip()."' AND clicked_time BETWEEN '".date('Y-m-d H:i:s',strtotime('-'.$clicks_config[0]['max_click_lift_time_in_hour'].' hours'))."' AND '".ads_current_time_stamp_db()."'")->result_array();
        $total_clicks = (int)$total_clicks[0]['total'];
        if ($total_clicks>=(int)$clicks_config[0]['max_click_limit']) {
            return true;
        }
        return false;
    }
    function ad_pageviews(){
        $CI = & get_instance();
        $CI->load->library('user_agent');
        $pageviews_config = $CI->db->query("SELECT * FROM `ads_config` LIMIT 1")->result_array();
        $total_pageviews = $CI->db->query("SELECT count(*) as total FROM `ads_pageviews` WHERE ip='".ads_get_ip()."' AND pageview_time BETWEEN '".date('Y-m-d H:i:s',strtotime('-'.$pageviews_config[0]['max_pageview_lift_time_in_hour'].' hours'))."' AND '".ads_current_time_stamp_db()."'")->result_array();
        $total_pageviews = (int)$total_pageviews[0]['total'];
        if ($total_pageviews>(int)$pageviews_config[0]['max_pageviews']) {
            return true;
        }
        return false;
    }
    function remove_empty_spaces($array=null){
        $tmp=array();
        if ($array!=null) {
            for ($i=0; $i <count($array) ; $i++) { 
                if ($array[$i]!="") {
                    array_push($tmp, $array[$i]);
                }
            }
        }
        return $tmp;
    }
    function ads_get_ip(){

        if ($_SERVER['HTTP_HOST']=="localhost") {
            //return get_black_list_ip();
            //return '66.249.66.1';    ## Verified googlebot.com IP
            //return '66.249.90.77';    ## Verified google.com IP
            //return '207.46.13.106';    ## Verified msn.com IP
            //return '141.8.144.21';    ## Verified yandex.com IP
            //return '54.197.179.134';    ## Verified amazonaws IP
            //return '166.62.81.69';    ## Verified secureserver.net IP
            //return '107.161.94.17';
            return '197.220.240.153';
        }
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && $_SERVER["HTTP_CF_CONNECTING_IP"]!="" && $_SERVER["HTTP_CF_CONNECTING_IP"]!=null) {
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if (ads_validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
        /*
        if ($_SERVER['HTTP_HOST']=="localhost") {
            //return '66.249.93.149';
            //return '31.215.179.96';
            return '197.220.240.153';
        }
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && $_SERVER["HTTP_CF_CONNECTING_IP"]!="" && $_SERVER["HTTP_CF_CONNECTING_IP"]!=null) {
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        return $_SERVER['REMOTE_ADDR'];*/
    }
    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     */
    function ads_validate_ip($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }
    function ads_empty_response(){
        return "ADSENS BAN ON YOUR IP";
    }
    function ads_current_time_stamp_db(){
        return date("Y-m-d H:i:s");
    }
    function ads_get_geo_location(){
        if ($_SERVER['HTTP_HOST']!="localhost") {
            $geo = ads_get_geo_from_db();
            if ($geo==false) {
                return ads_get_geo_live();
            }else{
                return $geo;
            }
        }
    }
    function ads_get_geo_from_db(){     
        $CI = & get_instance();
        $geo = $CI->db->query("SELECT * FROM `ads_ip_detail` where ip='".ads_get_ip()."' LIMIT 1")->result_array();
        if (count($geo)==0) {
            return false;
        }else{
            $geo_location = array();
            $geo_location['geoplugin_countryName'] = $geo[0]['country'];
            $geo_location['geoplugin_regionName']  = $geo[0]['region'];
            $geo_location['geoplugin_city']        = $geo[0]['city'];
            $geo_location['geoplugin_latitude']    = $geo[0]['latitude'];
            $geo_location['geoplugin_longitude']   = $geo[0]['longitude'];
            return $geo_location;
        }       
    }
    function ads_get_geo_live($ip=null){
        if ($ip==null) {
            $ip = ads_get_ip();
        }
        $url = 'http://www.geoplugin.net/php.gp?ip='.$ip;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $geo = unserialize($response);
        //ads_add_geo_db($geo);
        return $geo;
    }
    function ads_add_geo_db($geo_location){ 
        $CI = & get_instance();
        $CI->load->model('generalm', 'general_model');// LOAD GENERAL MODEL
        $data['country']=$geo_location['geoplugin_countryName'];
        $data['region']=$geo_location['geoplugin_regionName'];
        $data['city']=$geo_location['geoplugin_city'];
        $data['latitude']=$geo_location['geoplugin_latitude'];
        $data['longitude']=$geo_location['geoplugin_longitude'];
        $data['ip']=ads_get_ip();
        $CI->general_model->insert_record('ads_ip_detail', $data);
    }
    function ads_get_visitor_country(){
    }
    function ads_get_domain_name($url){
        if ($url=="") {
            return "";
        }
        $host = parse_url ($url);
        return $host['host'];
    }
    function ads_get_current_date_db(){
        
        $timezone = send_timezone();
        date_default_timezone_set($timezone);
        return date("Y-m-d");
    }
    function ads_get_current_time_stamp_db(){
        
        $timezone = send_timezone();
        date_default_timezone_set($timezone);
        return date("Y-m-d H:i:s");
    }
    function ads_add_hour_to_curent_time($hour, $att='+'){
        $timezone = send_timezone();
        date_default_timezone_set($timezone);
        return date('Y-m-d H:i:s',strtotime($att.$hour.' hours'));
    }
    function ads_set_cookie($cookie_name,$cookie_value,$days){
        setcookie($cookie_name, $cookie_value, time() + (86400 * $days), "/");
    }
    function ads_get_cookie($cookie_name){
        if(!isset($_COOKIE[$cookie_name])) {
            return false;
        } else {
            return $_COOKIE[$cookie_name];
        }
    }
    function ads_get_useragent_string($type){
        $ret = "";
        if ($type=="1") {
            $ret = "PC/Laptop";
        }else if ($type=="2") {
            $ret = "Mobile";
        }else if ($type=="3") {
            $ret = "Tablet";
        }else if ($type=="4") {
            $ret = "AOL";
        }else if ($type=="5") {
            $ret = "Facebook";
        }else if ($type=="6") {
            $ret = "robot";
        }else if ($type=="7" || $type=="" || $type==null ) {
            $ret = "Unknown";
        }
        return $ret;
    }
    function ads_is_browser_useragent_string($type){
        $ret = "";
        if ($type=="1") {
            $ret = "PC/Laptop";
        }else if ($type=="2") {
            $ret = "Mobile";
        }else if ($type=="3") {
            $ret = "Tablet";
        }else if ($type=="4") {
            $ret = "AOL";
        }else if ($type=="5") {
            $ret = "Facebook";
        }else if ($type=="6") {
            $ret = "robot";
        }else if ($type=="7" || $type=="" || $type==null ) {
            $ret = "Unknown";
        }
        return $ret;
    }
    function ads_set_location_string($country, $state, $city){
        $str="";
        if ($country!="" && $country!=null) {
            $str=$country;
            if ($state!="" && $state!=null) {
                $str=$str.", ".$state;
                if ($city!="" && $city!=null) {
                    $str=$str.", ".$city;
                }
            }
        }
        if ($str=="" || $str==null) {
            $str = "Other";
        }
        return $str;
    }
    function ads_mysql_timestamp_to_humen($timestamp){
        return date('j M Y h:i:s A',strtotime($timestamp));
    }
    function ads_is_ip_ban($is_permanent_ban, $ban_lift_time){
        if ($is_permanent_ban!="" && $is_permanent_ban=="1") {
            return true;
        }
        if ($ban_lift_time!='0000-00-00 00:00:00') {               
            $current_time = ads_get_current_time_stamp_db();
            $ban_lift_time = strtotime($ban_lift_time);
            if($current_time > date('Y-m-d H:i:s', $ban_lift_time)){
                return false;  
            }else{
                return true;
            }
        }
        return false;
    }
    function send_timezone(){
        /*global $wpdb;
        $plugin_table_prefix = "ads_prtctr_";
        $config_tbl = $wpdb->prefix . $plugin_table_prefix.'config';
        $timezon_tbl = $wpdb->prefix . $plugin_table_prefix.'timezones';
        $sql.= "SELECT $timezon_tbl.* FROM `$config_tbl` ";
        $sql.= " left JOIN $timezon_tbl ON ";
        $sql.= " $timezon_tbl.id=$config_tbl.timezone";
        $db = new WP_Adsense_Protector_db();
        $result = $db->get($sql);
        $timezone = "";
        foreach ( $result as $print )   {
            $timezone = $print->time_zone;
        }
        if ($timezone=="" || $timezone==NULL) {
            $timezone = "Asia/Karachi";
        }*/
        $timezone = "Asia/Karachi"; 
        return $timezone;
    }
    function wap_compare_datetime($current_time, $block_time){
        $current_time = new DateTime($current_time);
        $block_time = new DateTime($block_time);
        if ($current_time>=$block_time) {
            return false;
        }
        return true;
    }
    function wap_ads_ban_reason($type){
        //1=Manual Ban,2=pageview excedded,3=clicks excedded
        if($type=="1"){
            return "Manual Ban";
        }else if($type=="2"){
            return "Max Impression Limit Excedded";
        }else if($type=="3"){
            return "Max Clicks Limit Excedded";
        }
    }
    function wap_month_to_humen($date){
        return date('M Y',strtotime($date));
    }
    function wap_date_to_humen($date){
        return date('j M Y',strtotime($date));
    }
    function wap_calculate_ctr($click,$pageviews){
        if($pageviews==0){return 0;}
        return round($click/$pageviews*100,2);
    }
    function wap_top_ctr_calculate($clicks_array,$pageview_array, $country_name){
        foreach ($clicks_array as $print )   {
            if ($country_name==$print->country_name) {
                foreach ($pageview_array as $print2 )   {
                    if ($country_name==$print2->country_name) {
                        return wap_calculate_ctr($print->total,$print2->total);
                    }

                }
            }

        }
        return 0;
    }
    function wap_top_ctr_ip_calculate($clicks_array,$pageview_array, $ip){
        foreach ($clicks_array as $print )   {
            if ($ip==$print->ip) {
                foreach ($pageview_array as $print2 )   {
                    if ($ip==$print2->ip) {
                        return wap_calculate_ctr($print->total,$print2->total);
                    }

                }
            }

        }
        return 0;
    }
    function wap_get_previous_date($previous_date=null){

        $timezone = send_timezone();
        date_default_timezone_set($timezone);
        if ($previous_date==null) {
            $previous_date=="-1";
        }

        return date('Y-m-d',strtotime($previous_date." days"));
    }

    function string_find($big_str,$small_str){
        $big_str = strtolower($big_str);
        $small_str = strtolower($small_str);
        if (strpos($big_str, $small_str) !== FALSE){
            return true;
        }else{
            return false;
        }
    }
    function get_other_location_image(){
        return "other.png";
    }
?>