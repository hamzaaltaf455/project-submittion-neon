<?php
/**
 * Custom Post Type Registration
 * 
 * Handles registration of the va_location custom post type
 * 
 * @package VA_Location_Directory
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Location Post Type Class
 */
class VA_Location_Post_Type {
    
    /**
     * Singleton instance
     * 
     * @var VA_Location_Post_Type
     */
    private static $instance = null;
    
    /**
     * Post type slug
     * 
     * @var string
     */
    const POST_TYPE = 'va_location';
    
    /**
     * Get singleton instance
     * 
     * @return VA_Location_Post_Type
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
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
    }
    
    /**
     * Register the custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Locations', 'Post type general name', 'va-location-directory'),
            'singular_name'         => _x('Location', 'Post type singular name', 'va-location-directory'),
            'menu_name'             => _x('Locations', 'Admin Menu text', 'va-location-directory'),
            'name_admin_bar'        => _x('Location', 'Add New on Toolbar', 'va-location-directory'),
            'add_new'               => __('Add New', 'va-location-directory'),
            'add_new_item'          => __('Add New Location', 'va-location-directory'),
            'new_item'              => __('New Location', 'va-location-directory'),
            'edit_item'             => __('Edit Location', 'va-location-directory'),
            'view_item'             => __('View Location', 'va-location-directory'),
            'all_items'             => __('All Locations', 'va-location-directory'),
            'search_items'          => __('Search Locations', 'va-location-directory'),
            'parent_item_colon'     => __('Parent Locations:', 'va-location-directory'),
            'not_found'             => __('No locations found.', 'va-location-directory'),
            'not_found_in_trash'    => __('No locations found in Trash.', 'va-location-directory'),
            'featured_image'        => _x('Location Image', 'Overrides the "Featured Image" phrase', 'va-location-directory'),
            'set_featured_image'    => _x('Set location image', 'Overrides the "Set featured image" phrase', 'va-location-directory'),
            'remove_featured_image' => _x('Remove location image', 'Overrides the "Remove featured image" phrase', 'va-location-directory'),
            'use_featured_image'    => _x('Use as location image', 'Overrides the "Use as featured image" phrase', 'va-location-directory'),
            'archives'              => _x('Location archives', 'The post type archive label', 'va-location-directory'),
            'insert_into_item'      => _x('Insert into location', 'Overrides the "Insert into post" phrase', 'va-location-directory'),
            'uploaded_to_this_item' => _x('Uploaded to this location', 'Overrides the "Uploaded to this post" phrase', 'va-location-directory'),
            'filter_items_list'     => _x('Filter locations list', 'Screen reader text for the filter links', 'va-location-directory'),
            'items_list_navigation' => _x('Locations list navigation', 'Screen reader text for the pagination', 'va-location-directory'),
            'items_list'            => _x('Locations list', 'Screen reader text for the items list', 'va-location-directory'),
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Location directory entries', 'va-location-directory'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'locations'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-location',
            'supports'           => array('title', 'editor', 'excerpt', 'thumbnail'),
            'show_in_rest'       => true, // Enable Gutenberg editor
        );
        
        register_post_type(self::POST_TYPE, $args);
    }
    
    /**
     * Register taxonomies for the post type (optional, for future expansion)
     */
    public function register_taxonomies() {
        // Service taxonomy for categorizing locations by service type
        $labels = array(
            'name'              => _x('Service Types', 'taxonomy general name', 'va-location-directory'),
            'singular_name'     => _x('Service Type', 'taxonomy singular name', 'va-location-directory'),
            'search_items'      => __('Search Service Types', 'va-location-directory'),
            'all_items'         => __('All Service Types', 'va-location-directory'),
            'parent_item'       => __('Parent Service Type', 'va-location-directory'),
            'parent_item_colon' => __('Parent Service Type:', 'va-location-directory'),
            'edit_item'         => __('Edit Service Type', 'va-location-directory'),
            'update_item'       => __('Update Service Type', 'va-location-directory'),
            'add_new_item'      => __('Add New Service Type', 'va-location-directory'),
            'new_item_name'     => __('New Service Type Name', 'va-location-directory'),
            'menu_name'         => __('Service Types', 'va-location-directory'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'service-type'),
            'show_in_rest'      => true,
        );
        
        register_taxonomy('va_service_type', array(self::POST_TYPE), $args);
    }
    
    /**
     * Get post type slug
     * 
     * @return string
     */
    public static function get_post_type() {
        return self::POST_TYPE;
    }
}
