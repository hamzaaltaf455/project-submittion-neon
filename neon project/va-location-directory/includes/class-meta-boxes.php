<?php
/**
 * Meta Boxes Handler
 * 
 * Handles custom meta fields for locations:
 * - street, city, state, zip
 * - services[] (multi-select)
 * 
 * @package VA_Location_Directory
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Location Meta Boxes Class
 */
class VA_Location_Meta_Boxes {
    
    /**
     * Singleton instance
     * 
     * @var VA_Location_Meta_Boxes
     */
    private static $instance = null;
    
    /**
     * Meta field prefix
     * 
     * @var string
     */
    const META_PREFIX = '_va_location_';
    
    /**
     * Get singleton instance
     * 
     * @return VA_Location_Meta_Boxes
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
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_' . VA_Location_Post_Type::POST_TYPE, array($this, 'save_meta'), 10, 2);
    }
    
    /**
     * Register meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'va_location_details',
            __('Location Details', 'va-location-directory'),
            array($this, 'render_meta_box'),
            VA_Location_Post_Type::POST_TYPE,
            'normal',
            'high'
        );
    }
    
    /**
     * Render meta box content
     * 
     * @param WP_Post $post The post object
     */
    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('va_location_meta_box', 'va_location_meta_box_nonce');
        
        // Get existing values
        $street = get_post_meta($post->ID, self::META_PREFIX . 'street', true);
        $city = get_post_meta($post->ID, self::META_PREFIX . 'city', true);
        $state = get_post_meta($post->ID, self::META_PREFIX . 'state', true);
        $zip = get_post_meta($post->ID, self::META_PREFIX . 'zip', true);
        $services = get_post_meta($post->ID, self::META_PREFIX . 'services', true);
        
        // Ensure services is an array
        if (!is_array($services)) {
            $services = array();
        }
        
        // Available services (can be expanded or made dynamic)
        $available_services = $this->get_available_services();
        
        ?>
        <div class="va-location-meta-box">
            <style>
                .va-location-meta-box .meta-field {
                    margin-bottom: 20px;
                }
                .va-location-meta-box label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 5px;
                }
                .va-location-meta-box input[type="text"] {
                    width: 100%;
                    max-width: 400px;
                }
                .va-location-meta-box .services-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                    gap: 10px;
                    margin-top: 10px;
                }
                .va-location-meta-box .service-checkbox {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .va-location-meta-box .description {
                    font-style: italic;
                    color: #666;
                    font-size: 13px;
                    margin-top: 5px;
                }
            </style>
            
            <div class="meta-field">
                <label for="va_location_street">
                    <?php esc_html_e('Street Address', 'va-location-directory'); ?>
                </label>
                <input 
                    type="text" 
                    id="va_location_street" 
                    name="va_location_street" 
                    value="<?php echo esc_attr($street); ?>"
                    placeholder="<?php esc_attr_e('123 Main Street', 'va-location-directory'); ?>"
                />
            </div>
            
            <div class="meta-field">
                <label for="va_location_city">
                    <?php esc_html_e('City', 'va-location-directory'); ?>
                </label>
                <input 
                    type="text" 
                    id="va_location_city" 
                    name="va_location_city" 
                    value="<?php echo esc_attr($city); ?>"
                    placeholder="<?php esc_attr_e('San Francisco', 'va-location-directory'); ?>"
                />
            </div>
            
            <div class="meta-field">
                <label for="va_location_state">
                    <?php esc_html_e('State', 'va-location-directory'); ?>
                </label>
                <input 
                    type="text" 
                    id="va_location_state" 
                    name="va_location_state" 
                    value="<?php echo esc_attr($state); ?>"
                    placeholder="<?php esc_attr_e('CA', 'va-location-directory'); ?>"
                    maxlength="2"
                    style="width: 100px;"
                />
                <p class="description">
                    <?php esc_html_e('2-letter state code (e.g., CA, NY, TX)', 'va-location-directory'); ?>
                </p>
            </div>
            
            <div class="meta-field">
                <label for="va_location_zip">
                    <?php esc_html_e('ZIP Code', 'va-location-directory'); ?>
                </label>
                <input 
                    type="text" 
                    id="va_location_zip" 
                    name="va_location_zip" 
                    value="<?php echo esc_attr($zip); ?>"
                    placeholder="<?php esc_attr_e('94102', 'va-location-directory'); ?>"
                    maxlength="10"
                    style="width: 150px;"
                />
            </div>
            
            <div class="meta-field">
                <label>
                    <?php esc_html_e('Services Offered', 'va-location-directory'); ?>
                </label>
                <div class="services-grid">
                    <?php foreach ($available_services as $service_key => $service_label) : ?>
                        <div class="service-checkbox">
                            <input 
                                type="checkbox" 
                                id="va_service_<?php echo esc_attr($service_key); ?>" 
                                name="va_location_services[]" 
                                value="<?php echo esc_attr($service_key); ?>"
                                <?php checked(in_array($service_key, $services)); ?>
                            />
                            <label for="va_service_<?php echo esc_attr($service_key); ?>">
                                <?php echo esc_html($service_label); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="description">
                    <?php esc_html_e('Select all services available at this location', 'va-location-directory'); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save meta box data
     * 
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     */
    public function save_meta($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['va_location_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['va_location_meta_box_nonce'], 'va_location_meta_box')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Sanitize and save street
        if (isset($_POST['va_location_street'])) {
            update_post_meta(
                $post_id,
                self::META_PREFIX . 'street',
                sanitize_text_field($_POST['va_location_street'])
            );
        }
        
        // Sanitize and save city
        if (isset($_POST['va_location_city'])) {
            update_post_meta(
                $post_id,
                self::META_PREFIX . 'city',
                sanitize_text_field($_POST['va_location_city'])
            );
        }
        
        // Sanitize and save state (uppercase, 2 chars max)
        if (isset($_POST['va_location_state'])) {
            $state = strtoupper(sanitize_text_field($_POST['va_location_state']));
            $state = substr($state, 0, 2); // Limit to 2 characters
            update_post_meta($post_id, self::META_PREFIX . 'state', $state);
        }
        
        // Sanitize and save zip
        if (isset($_POST['va_location_zip'])) {
            update_post_meta(
                $post_id,
                self::META_PREFIX . 'zip',
                sanitize_text_field($_POST['va_location_zip'])
            );
        }
        
        // Sanitize and save services
        if (isset($_POST['va_location_services']) && is_array($_POST['va_location_services'])) {
            $services = array_map('sanitize_text_field', $_POST['va_location_services']);
            update_post_meta($post_id, self::META_PREFIX . 'services', $services);
        } else {
            // If no services selected, save empty array
            update_post_meta($post_id, self::META_PREFIX . 'services', array());
        }
    }
    
    /**
     * Get available services
     * 
     * @return array Array of service key => label pairs
     */
    public function get_available_services() {
        $services = array(
            'consulting'    => __('Consulting', 'va-location-directory'),
            'web_design'    => __('Web Design', 'va-location-directory'),
            'development'   => __('Development', 'va-location-directory'),
            'marketing'     => __('Marketing', 'va-location-directory'),
            'seo'           => __('SEO', 'va-location-directory'),
            'support'       => __('Support', 'va-location-directory'),
            'training'      => __('Training', 'va-location-directory'),
            'maintenance'   => __('Maintenance', 'va-location-directory'),
        );
        
        // Allow filtering of available services
        return apply_filters('va_location_available_services', $services);
    }
    
    /**
     * Get meta field value
     * 
     * @param int    $post_id Post ID
     * @param string $field   Field name (without prefix)
     * @param mixed  $default Default value
     * @return mixed Field value
     */
    public static function get_meta($post_id, $field, $default = '') {
        $value = get_post_meta($post_id, self::META_PREFIX . $field, true);
        return !empty($value) ? $value : $default;
    }
}
