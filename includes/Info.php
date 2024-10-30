<?php

namespace LeadBI;

/**
 * The class containing information about the plugin.
 */
class Info {
    /**
     * The plugin slug.
     *
     * @var string
     */
     const SLUG = 'leadbi';
     
     /**
      * The plugin version.
      *
      * @var string
      */
     const VERSION = '1.0';

     /**
      * The nae for the entry in the options table.
      *
      * @var string
      */
     const OPTION_NAME = 'leadbi_settings';

     /**
      * Connect endpoint
      */
     const LEADBI_ENDPOINT = 'https://app.leadbi.com';


    /**
     * Check if wooCommerce is installed and active
     */
    public static function isWooCommerceActive(){
        if(class_exists('WC_Integration') && defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0', '>=') ) {
            return true;
        }

        return false;
    }
}