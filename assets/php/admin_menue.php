<?php
	class WP_Adsense_Protector_admin_menue{
		
    	public $plugin_table_prefix = "ads_prtctr_";
  		// Constructor
    function __construct() {

        add_action( 'admin_menu', array( $this, 'wpa_add_menu' ));
    }
    function wpa_add_menu() {
        $images_url = plugin_dir_url(__FILE__);
        $images_url = str_replace("/assets/php", "", $images_url);
        $images_url = $images_url."assets/images/";
        add_menu_page(
            'Adsense Protector',
            'Adsense Protector',
            'manage_options',
            'adsense-protector-dashboard',
            dashboard,
            $images_url.'logo.jpg',
            '2.2.9'
        );
        add_submenu_page(
            'adsense-protector-dashboard',                          // Parent Slug
            'Adsense Protector' . ' Dashboard',             // Page Title
            'Dashboard',                                    // menu Title 
            'manage_options',                               // capability
            'adsense-protector-dashboard',                          // menu_slug 
            dashboard               // File Path
        );
        add_submenu_page(
          'adsense-protector-dashboard',              // Parent Slug
          'Adsense Protector' . ' AD-Code',        // Page Title
          'Adsense Code',                 // menu Title 
          'manage_options',               // capability
          'adsense-protector-ad-code',             // menu_slug 
          adcode        // File Path
        );
        add_submenu_page(
          null,              // Parent Slug
          'Adsense Protector' . ' AD-Code',        // Page Title
          'AD-Code',                 // menu Title 
          'manage_options',               // capability
          'adsense-protector-ad-code-insert',             // menu_slug 
          adcode_insert        // File Path
        );
        add_submenu_page(
            'adsense-protector-dashboard',                          // Parent Slug
            'Adsense Protector' . ' Clicks',                // Page Title
            'Clicks',                                   // menu Title 
            'manage_options',                               // capability
            'adsense-protector-clicks',                         // menu_slug 
            clicks              // File Path
        );
        add_submenu_page(
            'adsense-protector-dashboard',                          // Parent Slug
            'Adsense Protector' . ' Pageview',                // Page Title
            'Pageviews',                                   // menu Title 
            'manage_options',                               // capability
            'adsense-protector-impression',                         // menu_slug 
            impression              // File Path
        );
        add_submenu_page(
            'adsense-protector-dashboard',                          // Parent Slug
            'Adsense Protector' . ' IP Block',              // Page Title
            'IP Block',                                 // menu Title 
            'manage_options',                               // capability
            'adsense-protector-ip-block',                           // menu_slug 
            ip_block                // File Path
        );
        add_submenu_page(
            'adsense-protector-dashboard', 
            'Adsense Protector' . ' Settings',
            '<b style="color:#f9845b">Settings</b>',
            'manage_options',
            'adsense-protector-setting',
            setting
        );
    }

}
?>