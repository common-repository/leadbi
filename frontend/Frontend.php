<?php

namespace LeadBI;

/**
 * The code used on the frontend.
 */
 class Frontend {

    public function __construct($plugin_slug, $version, $option_name) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;

        $this->settings = get_option($this->option_name);
        $this->settings_group = $this->option_name.'_group';
        $this->wooCommerce = null;
    }

    public function assets() {
        // Empty for now
    }

    /**
     * Load frontend integrations
     */
    public function pluginLoaded(){
        $settings = $this->settings;
        if(Info::isWooCommerceActive() && $settings['wooCommerceEnabled']){
            include_once 'WooCommerceIntegration.php';
            $this->wooCommerce = WooCommerceIntegration::getInstance();
            $this->wooCommerce->init();
        }
    }
    /**
     * Render the view using MVC pattern.
     */
     public function render() {
        $settings = $this->settings;

        if($settings['connected']){
            $websiteId = $settings['websiteId'];
            $websiteDomain = $settings['websiteDomain'];
            return require_once plugin_dir_path(dirname(__FILE__)).'frontend/partials/tag.php';
        }
     }
     
 }