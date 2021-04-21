<?php
	class WP_Adsense_Protector_db{
		
    	public $plugin_table_prefix = "ads_prtctr_";
  		// Constructor
    function __construct() {
    }
    function insert($table_name, $data){
    	global $wpdb;
    	foreach( $data as $key => $value){
    		$data[$key]=$wpdb->escape($value); 
    	}
    	$wpdb->insert( $table_name, $data);
        return $wpdb->insert_id;

    }
    function update($table_name, $data, $where){
    	global $wpdb;
    	foreach( $data as $key => $value){
    		$data[$key]=$wpdb->escape($value); 
    	}
    	$updated = $wpdb->update( $table_name, $data, $where );
		if (false === $updated ) {
			return false;
		} else {
			return true;
		}
    }
    function delete($table_name, $where){
    	global $wpdb;
    	$wpdb->delete( $table_name, $where );
    }
    function get($sql){
    	global $wpdb;
    	return $wpdb->get_results( $sql);
    }
    function remove_extra_chars($str){
    	$str =  stripslashes_deep($str);
    	$str = str_replace('\"', '"', $str);
        return $str;
    }

}
?>