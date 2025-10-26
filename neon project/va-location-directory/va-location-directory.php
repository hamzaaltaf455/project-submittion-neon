<?php
/**
 * Plugin Name: VA Location Directory
 * Plugin URI: https://example.com/va-location-directory
 * Description: A secure, efficient location directory with real-time AJAX search and filtering capabilities. Includes custom post type, meta fields, and shortcode functionality.
 * Version: 1.0.0
 * Author: Developer
 * Author URI: https://example.com
 * Text Domain: va-location-directory
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 * 
 * Handles plugin initialization, constants, and autoloading
 */
final class VA_Location_Directory {
    
    /**
     * Plugin version
     * 
     * @var string
     */
    const VERSION = '1.0.0';
    
    /**
     * Singleton instance
     * 
     * @var VA_Location_Directory
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * 
     * @return VA_Location_Directory
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Initialize plugin
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('VA_LOC_DIR_VERSION', self::VERSION);
        define('VA_LOC_DIR_PLUGIN_FILE', __FILE__);
        define('VA_LOC_DIR_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('VA_LOC_DIR_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('VA_LOC_DIR_PLUGIN_BASENAME', plugin_basename(__FILE__));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once VA_LOC_DIR_PLUGIN_DIR . 'includes/class-post-type.php';
        require_once VA_LOC_DIR_PLUGIN_DIR . 'includes/class-meta-boxes.php';
        require_once VA_LOC_DIR_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once VA_LOC_DIR_PLUGIN_DIR . 'includes/class-ajax-handler.php';
        require_once VA_LOC_DIR_PLUGIN_DIR . 'includes/class-assets.php';
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Initialize components
        VA_Location_Post_Type::get_instance();
        VA_Location_Meta_Boxes::get_instance();
        VA_Location_Shortcode::get_instance();
        VA_Location_Ajax_Handler::get_instance();
        VA_Location_Assets::get_instance();
        
        do_action('va_location_directory_loaded');
    }
    
    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'va-location-directory',
            false,
            dirname(VA_LOC_DIR_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Register post type
        VA_Location_Post_Type::get_instance();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        set_transient('va_location_directory_activated', true, 30);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
}

/**
 * Initialize the plugin
 */
function va_location_directory() {
    return VA_Location_Directory::get_instance();
}

// Start the plugin
va_location_directory();
