<?php
/*
  Plugin Name: Adsense Protector
  Plugin URI: #
  Description: Main purpose of Google adsense protector is a to protect your adsens against invalid clicks, impressions and other invalid account activities.
  Version: 1.0
  Author: Neil Deniel
  Author URI: #
  License: GPLv2+
  Text Domain: Adsense Protector
*/
?>
<?php
    ob_start();
    $ROOTDIR = plugin_dir_path(__FILE__);
    $include_php_file_path = plugin_dir_path(__FILE__) . 'assets/php/';
    include( $include_php_file_path . 'db.php');
    include( $include_php_file_path . 'db_schema.php');
    include( $include_php_file_path . 'admin_menue.php');
    include( $include_php_file_path . 'dashboard.php');
    include( $include_php_file_path . 'clicks.php');
    include( $include_php_file_path . 'ip_block.php');
    include( $include_php_file_path . 'ip_block_ad.php');
    include( $include_php_file_path . 'setting.php');
    include( $include_php_file_path . 'impression.php');
    include( $include_php_file_path . 'adcode.php');
    include( $include_php_file_path . 'adcode_insert.php');
    include( $include_php_file_path . 'General.php');
    include( $include_php_file_path . 'functions.php');
  class WP_Adsense_Protector{
    var $general_obj;
    function __construct() {

        //add_action( 'admin_menu', array( $this, 'wpa_add_menu' ));
        add_action( 'admin_enqueue_scripts', array( $this, 'wpdocs_enqueue_custom_admin_style' ));
        add_action( 'wp_enqueue_scripts', array( $this, 'WPAP_public_scripts' ));
        add_shortcode('Adsense_Protector_Ads', array($this, 'Adsense_protector_display_ads'));
        register_activation_hook( __FILE__, array( $this, 'wpa_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'wpa_uninstall' ) );
        add_action( 'wp_ajax_handle_impression', array( $this, 'wap_handle_impression' ) );
        add_action( 'wp_ajax_nopriv_handle_impression', array( $this, 'wap_handle_impression' ) );

        add_action( 'wp_ajax_handle_click', array( $this, 'wap_handle_clicks' ) );
        add_action( 'wp_ajax_nopriv_handle_click', array( $this, 'wap_handle_clicks' ) );
        add_action( 'wp_ajax_handle_ban_countries', array( $this, 'wap_handle_ban_countries' ) );
        add_action( 'wp_ajax_handle_ban_ip', array( $this, 'wap_handle_ban_ip' ) );
        add_action( 'wp_footer', array( $this, 'wap_model_html' ) );

        add_filter( 'cron_schedules', array( $this, 'isa_add_cron_recurrence_interval' ) );
        if ( ! wp_next_scheduled( 'check_ip_info_action_hook' ) ) {
          wp_schedule_event( time(), 'every_three_minutes', 'check_ip_info_action_hook' );
          //echo "schedules doing now";
        }else{
          //echo "schedules alreay done";
        }
        add_action('check_ip_info_action_hook', array( $this, 'cron_job' ));


        $this-> wpa_add_menu();
        $this->general_obj = new WP_Adsense_Protector_General();
    }



    function isa_add_cron_recurrence_interval( $schedules ) {

        $minutes = 1;
        $minutes = $minutes*60;
     
        $schedules['every_three_minutes'] = array(
                'interval'  => $minutes,
                'display'   => __( 'Every 3 Minutes', 'textdomain' )
        );
         
        return $schedules;
    } 
    function cron_job() {
      $this->general_obj->wap_maintain_record_limitation();
      $this->general_obj->wap_set_empty_ips_data();
      exit();      
     
    }






    function wap_model_html(){
      echo ' <!-- Trigger/Open The Modal -->
            <div id="wap_myModal" class="modal">
              <!-- Modal content -->
              <div class="modal-content">
                <div class="modal-body">
                  <h1 style="text-align: center;">We are Processing Your Request...<br/>';
      echo "<img src='".plugin_dir_url(__FILE__)."assets/images/loading.gif'>";
      echo '</h1>
                </div>
              </div>
            </div>';
    }


    function wap_handle_ban_ip(){
      $this->general_obj->manual_ban_ip();
      //echo "<pre>";print_r($_POST);echo "</pre>";
      exit();
    }
    function wap_handle_ban_countries(){
      $this->general_obj->add_ban();
      //echo "<pre>";print_r($_POST);echo "</pre>";
      exit();
    }
    function wap_handle_impression(){
      //$obj = new WP_Adsense_Protector_General();
      //$obj->add_pageview();
      $this->general_obj->add_pageview();
      //echo "pageview added";
      exit();
    }
    function wap_handle_clicks(){
      //$obj = new WP_Adsense_Protector_General();
      //$obj->add_click();
      //echo "click added";
      $this->general_obj->add_click();
      exit();
    }
    function wpdocs_enqueue_custom_admin_style() {
        $page = $_GET['page'];
        if ($page!=null && $page!="" && string_find($page, 'adsense-protector')==true) {
          wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'assets/css/plugin.css', false, '1.0.0' );
          wp_enqueue_style( 'custom_wp_admin_css' );
          wp_register_style( 'custom_wp_admin_css1', plugin_dir_url( __FILE__ ) . 'assets/css/custom.css', false, '1.0.0' );
          wp_enqueue_style( 'custom_wp_admin_css1' );
          wp_register_script( 'custom_js_5', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js', false, '1.0.0' );
          wp_enqueue_script( 'custom_js_5' );
          wp_register_script( 'custom_js_6', plugin_dir_url( __FILE__ ) . 'assets/js/admin_custom.js', false, '1.0.0' );
          wp_enqueue_script( 'custom_js_6' );
        }
    }
    function WPAP_public_scripts(){
      wp_register_style( 'custom_css_1', plugin_dir_url( __FILE__ ) . 'assets/css/custom.css', false, '1.0.0' );
      wp_enqueue_style( 'custom_css_1' );
      wp_register_script( 'custom_js_1', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js', false, '1.0.0' );
      wp_enqueue_script( 'custom_js_1' );
      wp_register_script( 'custom_js_2', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.iframetracker.js', false, '1.0.0' );
      wp_enqueue_script( 'custom_js_2' );
      wp_register_script( 'custom_js_3', plugin_dir_url( __FILE__ ) . 'assets/js/custom.js', false, '1.0.0' );
      wp_enqueue_script( 'custom_js_3' );
      wp_localize_script( 'custom_js_3', 'ajax_urls', 
        array( 
              'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
              'public_ajax_url' => 'public ajax url' ) );
  
    }
    /*
      * Actions perform at loading of admin menu
      */
    function wpa_add_menu() {

      $obj = new WP_Adsense_Protector_admin_menue();
    }

    /*
     * Actions perform on loading of menu pages
     */
    function wpa_page_file_path() {
    }

    /*
     * Actions perform on activation of plugin
     */
    function wpa_install() {
      $db_schema = new WP_Adsense_Protector_db_schema();
      $db_schema->wpa_check_tables();
    }

    /*
     * Actions perform on de-activation of plugin
     */
    function wpa_uninstall() {
      $db_schema = new WP_Adsense_Protector_db_schema();
      $db_schema->wpa_delete_tables();
    }
    function Adsense_protector_display_ads($atts){
      $args = shortcode_atts( array(
          'token' => '0',
          'baz' => 'default baz',
      ), $atts );
      $code_id = "{$args['token']}";
      if ($code_id!="" && $code_id!="0" ) {
        $this->general_obj->code_id = $code_id;
        $str = $this->general_obj->wap_get_adcode();
        return $str;
      }
      return '';
    }
}
$obj = new WP_Adsense_Protector();
ob_flush();
?>