<?php

namespace LeadBI;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}



class WooCommerceIntegration {
    /*
    * Instance of this class
    */
    protected static $instance = null;

   /*
    * Init plugin
    */
    private function __construct(){
    }

    public function init(){
        include_once 'WooCommerce.php';

        // Register integration
        add_filter('woocommerce_integrations', array($this, 'addIntegration'));
    }

    /*
    * Return an instance of this class
    */
    public static function getInstance(){
        if(null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
  }

    /*
    * Add new integration to WooCommerce
    */
    public function addIntegration($integrations) {
        $integrations[] = 'LeadBI\WooCommerce';
        return $integrations;
    }

}