<?php

namespace LeadBI;

class Admin {

    private $plugin_slug;
    private $version;
    private $option_name;
    private $settings;
    private $settings_group;

    public function __construct($plugin_slug, $version, $option_name) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;

        $this->settings = get_option($this->option_name);
        $this->settings_group = $this->option_name.'_group';
    }
    
    /*
     * Implement the init hook
     */
    public function init(){
        // Empty for now
    }

    /**
     * Add plugin admin assets
     */
    public function assets() {
        wp_register_script( "leadbi_options_update_script", plugin_dir_url(__FILE__).'js/leadbi-options.js', array('jquery') );
        wp_localize_script( 'leadbi_options_update_script', 'leadbiAjaxUpdate', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
     
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'leadbi_options_update_script' );
        
    }

    /**
     * Register settings  
     */
    public function registerSettings() {
        register_setting($this->settings_group, $this->option_name);

    }

    /**
     * Add menus hook
     */
    public function addMenus() {
        add_menu_page(
            'LeadBI',
            'LeadBI',
            'manage_options',
            $this->plugin_slug,
            [$this, 'render'],
            plugin_dir_url(__FILE__) . 'img/favicon-16x16.png'
        );  
    }

    /**
     * Check if wooCommerce is installed and active
     */
    private function isWooCommerceActive(){
        return Info::isWooCommerceActive();
    }

    /**
     * Render the view using MVC pattern.
     */
    public function render() {

       // Model
       $settings = $this->settings;

       $nonce = wp_create_nonce("leadbi_update_options_nonce");
       $url = parse_url(get_site_url());
       $domain = $url['host'];

       if(!$settings['connected']){
            $endpoint = Info::LEADBI_ENDPOINT . '/connect/wordpress.html';
            // show the connect page
            return require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/connect.php';
       }
        
        $endpoint = Info::LEADBI_ENDPOINT . '/connect/wordpress/' . $settings['websiteId'] . '/view.html';

        // View
        require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/view.php';
    }

    /**
     * Update options ajax hook
     */
    public function updateOptions(){

        if ( !wp_verify_nonce( $_REQUEST['nonce'], "leadbi_update_options_nonce")) {
            exit("No naughty business please");
        }

        $newOptions = [];

        if(isset($_REQUEST['connected'])){
            $newOptions['connected'] = $_REQUEST['connected'];
        }

        if(isset($_REQUEST['websiteId'])){
            $newOptions['websiteId'] = $_REQUEST['websiteId'];
        }

        if(isset($_REQUEST['websiteDomain'])){
            $newOptions['websiteDomain'] = $_REQUEST['websiteDomain'];
        }

        if(isset($_REQUEST['wooCommerceEnabled'])){
            $newOptions['wooCommerceEnabled'] = $_REQUEST['wooCommerceEnabled'];
        }

        $option_name = Info::OPTION_NAME;

        $options = get_option($option_name);
        $options = $options ? $options : [];
        
        $options = array_merge($options, $newOptions);
        update_option($option_name, $options);

        $result = ['type' => 'success'];

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
         }
         else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
         }
      
         die();
    }

    /**
     * Update option ajax hook no auth
     */
    public function updateOptionsNoAuth(){
        echo "You must log in first";
        die();
    }

}