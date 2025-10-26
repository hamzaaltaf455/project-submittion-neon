<?php
/**
 * Assets Handler
 * 
 * Handles enqueuing of CSS and JavaScript files
 * 
 * @package VA_Location_Directory
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Location Assets Class
 */
class VA_Location_Assets {
    
    /**
     * Singleton instance
     * 
     * @var VA_Location_Assets
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * 
     * @return VA_Location_Assets
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Enqueue CSS
        wp_enqueue_style(
            'va-location-directory',
            VA_LOC_DIR_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            VA_LOC_DIR_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'va-location-directory',
            VA_LOC_DIR_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            VA_LOC_DIR_VERSION,
            true
        );
        
        // Localize script with AJAX URL and translations
        wp_localize_script('va-location-directory', 'vaLocationDirectory', array(
            'ajaxUrl'      => admin_url('admin-ajax.php'),
            'nonce'        => wp_create_nonce('va_location_search'),
            'strings'      => array(
                'loading'    => __('Loading locations...', 'va-location-directory'),
                'noResults'  => __('No locations found', 'va-location-directory'),
                'tryAgain'   => __('Try adjusting your filters to find more locations.', 'va-location-directory'),
                'error'      => __('An error occurred. Please try again.', 'va-location-directory'),
            ),
        ));
    }
    
    /**
     * Enqueue admin assets
     * 
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on location post type pages
        global $post_type;
        
        if (VA_Location_Post_Type::POST_TYPE !== $post_type) {
            return;
        }
        
        // Enqueue admin CSS if needed
        wp_enqueue_style(
            'va-location-directory-admin',
            VA_LOC_DIR_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            VA_LOC_DIR_VERSION
        );
    }
}
