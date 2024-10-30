<?php

namespace LeadBI;

/**
 * The main plugin class.
 */
class Plugin {

    private $loader;
    private $plugin_slug;
    private $version;
    private $option_name;

    public function __construct() {
        
        $this->plugin_slug = Info::SLUG;
        $this->version     = Info::VERSION;
        $this->option_name = Info::OPTION_NAME;

        $this->load();
    }

    /**
     * Load plugin components    
     */
    private function load() {

        // include components
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/Loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/Admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'frontend/Frontend.php';

        // create new loader
        $this->loader = new Loader();

        // add admin section actions
        $admin = new Admin($this->plugin_slug, $this->version, $this->option_name);
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'assets');
        $this->loader->add_action('admin_init', $admin, 'registerSettings');
        $this->loader->add_action('admin_menu', $admin, 'addMenus');
        $this->loader->add_action('init', $admin, 'init');

        // add ajax update options action
        $this->loader->add_action('wp_ajax_leadbi_update_options', $admin, 'updateOptions');
        $this->loader->add_action('wp_ajax_nopriv_leadbi_update_options', $admin, 'updateOptionsNoAuth');

        // add frontend hooks
        $frontend = new Frontend($this->plugin_slug, $this->version, $this->option_name);
        $this->loader->add_action('wp_enqueue_scripts', $frontend, 'assets');
        $this->loader->add_action('wp_footer', $frontend, 'render');
        $this->loader->add_action('plugins_loaded', $frontend, 'pluginLoaded');

        add_shortcode('leadbi_form', array($this, 'leadbiForm'));
        add_shortcode('leadbi_placeholder', array($this, 'leadbiPlaceholder'));
    }


    /**
     * LeadBI form short code callback 
     */
    
    public function leadbiForm($atts) {
        $atts = shortcode_atts( array(
            'form_id' => null,
        ), $atts, 'leadbi_form' );

        if(!$atts['form_id']){
            return '';
        }

        return '<script src="https://a.leadbi.com/f/' . $atts['form_id'] . '.js" id="' . $atts['form_id'] . '" defer></script>';
    }

    /**
     * LeadBI placeholder short code callback 
     */
    public function leadbiPlaceholder($attrs){
        $attrs = shortcode_atts( array(
            'var' => null,
            'default' => null
        ), $attrs, 'leadbi_form' );

        if(!$attrs['var']){
            return '';
        }

        if($attrs['var'] && $attrs['default']){
            return '<{{ ' . $attrs['var'] . ' | ' . $attrs['default'] . ' }}>';
        }

        if($attrs['var']){
            return '<{{ ' . $attrs['var'] . ' }}>';
        }
    }

    // start loading
    public function run() {
        $this->loader->load();
    }
}