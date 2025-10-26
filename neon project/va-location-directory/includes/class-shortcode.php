<?php
/**
 * Shortcode Handler
 * 
 * Handles the [va_locations] shortcode with search/filter functionality
 * 
 * @package VA_Location_Directory
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Location Shortcode Class
 */
class VA_Location_Shortcode {
    
    /**
     * Singleton instance
     * 
     * @var VA_Location_Shortcode
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * 
     * @return VA_Location_Shortcode
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
        add_shortcode('va_locations', array($this, 'render_shortcode'));
    }
    
    /**
     * Render the shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_shortcode($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'city'    => '',
            'service' => '',
            'columns' => 3,
        ), $atts, 'va_locations');
        
        // Start output buffering
        ob_start();
        
        // Get available cities and services for filters
        $cities = VA_Location_Ajax_Handler::get_all_cities();
        $services = VA_Location_Meta_Boxes::get_instance()->get_available_services();
        
        ?>
        <div class="va-location-directory" data-nonce="<?php echo esc_attr(wp_create_nonce('va_location_search')); ?>">
            
            <!-- Search/Filter Form -->
            <div class="va-location-filters">
                <form class="va-location-search-form" id="vaLocationSearchForm">
                    <div class="filter-row">
                        <div class="filter-field">
                            <label for="va-filter-city">
                                <?php esc_html_e('City', 'va-location-directory'); ?>
                            </label>
                            <select id="va-filter-city" name="city" class="va-filter-select">
                                <option value=""><?php esc_html_e('All Cities', 'va-location-directory'); ?></option>
                                <?php foreach ($cities as $city) : ?>
                                    <option value="<?php echo esc_attr($city); ?>" <?php selected($atts['city'], $city); ?>>
                                        <?php echo esc_html($city); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-field">
                            <label for="va-filter-service">
                                <?php esc_html_e('Service', 'va-location-directory'); ?>
                            </label>
                            <select id="va-filter-service" name="service" class="va-filter-select">
                                <option value=""><?php esc_html_e('All Services', 'va-location-directory'); ?></option>
                                <?php foreach ($services as $service_key => $service_label) : ?>
                                    <option value="<?php echo esc_attr($service_key); ?>" <?php selected($atts['service'], $service_key); ?>>
                                        <?php echo esc_html($service_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-field filter-actions">
                            <button type="submit" class="va-btn va-btn-primary">
                                <?php esc_html_e('Search', 'va-location-directory'); ?>
                            </button>
                            <button type="button" class="va-btn va-btn-secondary" id="vaResetFilters">
                                <?php esc_html_e('Reset', 'va-location-directory'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Loading Indicator -->
            <div class="va-location-loading" style="display: none;">
                <div class="va-spinner"></div>
                <p><?php esc_html_e('Loading locations...', 'va-location-directory'); ?></p>
            </div>
            
            <!-- Results Container -->
            <div class="va-location-results" data-columns="<?php echo esc_attr($atts['columns']); ?>">
                <?php echo $this->render_initial_locations($atts); ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render initial locations (before AJAX)
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    private function render_initial_locations($atts) {
        // Build query args
        $args = array(
            'post_type'      => VA_Location_Post_Type::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );
        
        // Add meta query if filters provided
        $meta_query = array('relation' => 'AND');
        
        if (!empty($atts['city'])) {
            $meta_query[] = array(
                'key'     => VA_Location_Meta_Boxes::META_PREFIX . 'city',
                'value'   => sanitize_text_field($atts['city']),
                'compare' => 'LIKE',
            );
        }
        
        if (!empty($atts['service'])) {
            $meta_query[] = array(
                'key'     => VA_Location_Meta_Boxes::META_PREFIX . 'services',
                'value'   => serialize(sanitize_text_field($atts['service'])),
                'compare' => 'LIKE',
            );
        }
        
        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="va-locations-grid">';
            
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_location_card(get_the_ID());
            }
            
            echo '</div>';
            wp_reset_postdata();
        } else {
            $this->render_empty_state();
        }
        
        return ob_get_clean();
    }
    
    /**
     * Render a single location card
     * 
     * @param int $post_id Post ID
     */
    private function render_location_card($post_id) {
        $street = VA_Location_Meta_Boxes::get_meta($post_id, 'street');
        $city = VA_Location_Meta_Boxes::get_meta($post_id, 'city');
        $state = VA_Location_Meta_Boxes::get_meta($post_id, 'state');
        $zip = VA_Location_Meta_Boxes::get_meta($post_id, 'zip');
        $services = VA_Location_Meta_Boxes::get_meta($post_id, 'services', array());
        
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
        
        $all_services = VA_Location_Meta_Boxes::get_instance()->get_available_services();
        ?>
        <div class="va-location-card" data-location-id="<?php echo esc_attr($post_id); ?>">
            <?php if ($thumbnail_url) : ?>
                <div class="va-location-thumbnail">
                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>">
                </div>
            <?php endif; ?>
            
            <div class="va-location-content">
                <h3 class="va-location-title">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    </a>
                </h3>
                
                <?php if (has_excerpt($post_id)) : ?>
                    <div class="va-location-excerpt">
                        <?php echo wp_kses_post(get_the_excerpt($post_id)); ?>
                    </div>
                <?php endif; ?>
                
                <div class="va-location-address">
                    <svg class="va-icon" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8 0C5.2 0 3 2.2 3 5c0 3.5 5 11 5 11s5-7.5 5-11c0-2.8-2.2-5-5-5zm0 7c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                    </svg>
                    <?php if (!empty($street)) : ?>
                        <span><?php echo esc_html($street); ?><br></span>
                    <?php endif; ?>
                    <?php if (!empty($city) || !empty($state) || !empty($zip)) : ?>
                        <span>
                            <?php 
                            $address_parts = array_filter(array($city, $state, $zip));
                            echo esc_html(implode(', ', $address_parts)); 
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($services) && is_array($services)) : ?>
                    <div class="va-location-services">
                        <?php foreach ($services as $service_key) : ?>
                            <?php if (isset($all_services[$service_key])) : ?>
                                <span class="va-service-badge">
                                    <?php echo esc_html($all_services[$service_key]); ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render empty state message
     */
    private function render_empty_state() {
        ?>
        <div class="va-location-empty">
            <svg class="va-empty-icon" width="64" height="64" viewBox="0 0 64 64" fill="none">
                <circle cx="32" cy="32" r="30" stroke="#ccc" stroke-width="2"/>
                <path d="M32 16C24.8 16 19 21.8 19 29c0 11 13 29 13 29s13-18 13-29c0-7.2-5.8-13-13-13zm0 17c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z" fill="#ccc"/>
            </svg>
            <h3><?php esc_html_e('No locations found', 'va-location-directory'); ?></h3>
            <p><?php esc_html_e('Try adjusting your filters to find more locations.', 'va-location-directory'); ?></p>
        </div>
        <?php
    }
}
