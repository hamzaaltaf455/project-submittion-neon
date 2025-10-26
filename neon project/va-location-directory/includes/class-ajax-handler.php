<?php
/**
 * AJAX Handler
 * 
 * Handles AJAX requests for location search and filtering
 * 
 * @package VA_Location_Directory
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Location AJAX Handler Class
 */
class VA_Location_Ajax_Handler {
    
    /**
     * Singleton instance
     * 
     * @var VA_Location_Ajax_Handler
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * 
     * @return VA_Location_Ajax_Handler
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
        // Register AJAX handlers for both logged-in and non-logged-in users
        add_action('wp_ajax_va_search_locations', array($this, 'search_locations'));
        add_action('wp_ajax_nopriv_va_search_locations', array($this, 'search_locations'));
    }
    
    /**
     * Handle location search AJAX request
     * 
     * Security: Nonce verification, input sanitization, output escaping
     */
    public function search_locations() {
        // Verify nonce for security
        if (!check_ajax_referer('va_location_search', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed. Please refresh the page and try again.', 'va-location-directory')
            ), 403);
        }
        
        // Sanitize inputs
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        $service = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : '';
        
        // Build query args
        $args = array(
            'post_type'      => VA_Location_Post_Type::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1, // Get all matching locations
            'orderby'        => 'title',
            'order'          => 'ASC',
        );
        
        // Add meta query for city and/or service filtering
        $meta_query = array('relation' => 'AND');
        
        // Filter by city if provided
        if (!empty($city)) {
            $meta_query[] = array(
                'key'     => VA_Location_Meta_Boxes::META_PREFIX . 'city',
                'value'   => $city,
                'compare' => 'LIKE', // Case-insensitive partial match
            );
        }
        
        // Filter by service if provided
        if (!empty($service)) {
            $meta_query[] = array(
                'key'     => VA_Location_Meta_Boxes::META_PREFIX . 'services',
                'value'   => serialize($service), // Services stored as serialized array
                'compare' => 'LIKE',
            );
        }
        
        // Only add meta_query if we have conditions
        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }
        
        // Execute query
        $query = new WP_Query($args);
        
        // Prepare response data
        $locations = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Get meta data
                $street = VA_Location_Meta_Boxes::get_meta($post_id, 'street');
                $location_city = VA_Location_Meta_Boxes::get_meta($post_id, 'city');
                $state = VA_Location_Meta_Boxes::get_meta($post_id, 'state');
                $zip = VA_Location_Meta_Boxes::get_meta($post_id, 'zip');
                $services = VA_Location_Meta_Boxes::get_meta($post_id, 'services', array());
                
                // Get thumbnail
                $thumbnail_id = get_post_thumbnail_id($post_id);
                $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
                
                // Build location data array
                $locations[] = array(
                    'id'        => $post_id,
                    'title'     => get_the_title(),
                    'excerpt'   => get_the_excerpt(),
                    'permalink' => get_permalink(),
                    'thumbnail' => $thumbnail_url,
                    'street'    => $street,
                    'city'      => $location_city,
                    'state'     => $state,
                    'zip'       => $zip,
                    'services'  => is_array($services) ? $services : array(),
                );
            }
            wp_reset_postdata();
        }
        
        // Send success response with locations data
        wp_send_json_success(array(
            'locations' => $locations,
            'count'     => count($locations),
            'filters'   => array(
                'city'    => $city,
                'service' => $service,
            ),
        ));
    }
    
    /**
     * Get all unique cities from locations
     * 
     * @return array Array of city names
     */
    public static function get_all_cities() {
        global $wpdb;
        
        $meta_key = VA_Location_Meta_Boxes::META_PREFIX . 'city';
        $post_type = VA_Location_Post_Type::POST_TYPE;
        
        // Efficient query to get distinct cities
        $cities = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT pm.meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type = %s
            AND p.post_status = 'publish'
            AND pm.meta_key = %s
            AND pm.meta_value != ''
            ORDER BY pm.meta_value ASC",
            $post_type,
            $meta_key
        ));
        
        return $cities ? $cities : array();
    }
    
    /**
     * Get all unique services from locations
     * 
     * @return array Array of service keys
     */
    public static function get_all_services() {
        $args = array(
            'post_type'      => VA_Location_Post_Type::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids', // Only get IDs for performance
        );
        
        $query = new WP_Query($args);
        $all_services = array();
        
        if ($query->have_posts()) {
            foreach ($query->posts as $post_id) {
                $services = VA_Location_Meta_Boxes::get_meta($post_id, 'services', array());
                if (is_array($services)) {
                    $all_services = array_merge($all_services, $services);
                }
            }
        }
        
        // Get unique services
        $all_services = array_unique($all_services);
        sort($all_services);
        
        return $all_services;
    }
}
