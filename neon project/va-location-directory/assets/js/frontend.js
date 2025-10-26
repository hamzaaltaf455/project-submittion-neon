/**
 * VA Location Directory - Frontend JavaScript
 * 
 * Handles AJAX search and filtering
 * 
 * @package VA_Location_Directory
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function() {
        
        const $directory = $('.va-location-directory');
        
        if (!$directory.length) {
            return;
        }
        
        const $form = $('#vaLocationSearchForm');
        const $results = $('.va-location-results');
        const $loading = $('.va-location-loading');
        const $cityFilter = $('#va-filter-city');
        const $serviceFilter = $('#va-filter-service');
        const $resetBtn = $('#vaResetFilters');
        
        // Get nonce from data attribute
        const nonce = $directory.data('nonce');
        
        /**
         * Perform AJAX search
         */
        function searchLocations() {
            const city = $cityFilter.val();
            const service = $serviceFilter.val();
            
            // Show loading indicator
            $loading.fadeIn(200);
            $results.fadeOut(200);
            
            // Make AJAX request
            $.ajax({
                url: vaLocationDirectory.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'va_search_locations',
                    nonce: nonce,
                    city: city,
                    service: service
                },
                success: function(response) {
                    // Hide loading
                    $loading.fadeOut(200);
                    
                    if (response.success) {
                        renderResults(response.data.locations);
                    } else {
                        showError(response.data.message || vaLocationDirectory.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    $loading.fadeOut(200);
                    showError(vaLocationDirectory.strings.error);
                }
            });
        }
        
        /**
         * Render search results
         * 
         * @param {Array} locations Array of location objects
         */
        function renderResults(locations) {
            if (!locations || locations.length === 0) {
                renderEmptyState();
                return;
            }
            
            let html = '<div class="va-locations-grid">';
            
            locations.forEach(function(location) {
                html += renderLocationCard(location);
            });
            
            html += '</div>';
            
            $results.html(html).fadeIn(300);
        }
        
        /**
         * Render a single location card
         * 
         * @param {Object} location Location data object
         * @return {string} HTML string
         */
        function renderLocationCard(location) {
            let html = '<div class="va-location-card" data-location-id="' + location.id + '">';
            
            // Thumbnail
            if (location.thumbnail) {
                html += '<div class="va-location-thumbnail">';
                html += '<img src="' + escapeHtml(location.thumbnail) + '" alt="' + escapeHtml(location.title) + '">';
                html += '</div>';
            }
            
            html += '<div class="va-location-content">';
            
            // Title
            html += '<h3 class="va-location-title">';
            html += '<a href="' + escapeHtml(location.permalink) + '">' + escapeHtml(location.title) + '</a>';
            html += '</h3>';
            
            // Excerpt
            if (location.excerpt) {
                html += '<div class="va-location-excerpt">';
                html += escapeHtml(location.excerpt);
                html += '</div>';
            }
            
            // Address
            html += '<div class="va-location-address">';
            html += '<svg class="va-icon" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">';
            html += '<path d="M8 0C5.2 0 3 2.2 3 5c0 3.5 5 11 5 11s5-7.5 5-11c0-2.8-2.2-5-5-5zm0 7c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>';
            html += '</svg>';
            html += '<span>';
            if (location.street) {
                html += escapeHtml(location.street) + '<br>';
            }
            
            const addressParts = [];
            if (location.city) addressParts.push(location.city);
            if (location.state) addressParts.push(location.state);
            if (location.zip) addressParts.push(location.zip);
            
            if (addressParts.length > 0) {
                html += escapeHtml(addressParts.join(', '));
            }
            html += '</span>';
            html += '</div>';
            
            // Services
            if (location.services && location.services.length > 0) {
                html += '<div class="va-location-services">';
                location.services.forEach(function(serviceKey) {
                    const serviceLabel = getServiceLabel(serviceKey);
                    if (serviceLabel) {
                        html += '<span class="va-service-badge">' + escapeHtml(serviceLabel) + '</span>';
                    }
                });
                html += '</div>';
            }
            
            html += '</div>'; // .va-location-content
            html += '</div>'; // .va-location-card
            
            return html;
        }
        
        /**
         * Get service label from key
         * 
         * @param {string} key Service key
         * @return {string} Service label
         */
        function getServiceLabel(key) {
            const services = {
                'consulting': 'Consulting',
                'web_design': 'Web Design',
                'development': 'Development',
                'marketing': 'Marketing',
                'seo': 'SEO',
                'support': 'Support',
                'training': 'Training',
                'maintenance': 'Maintenance'
            };
            return services[key] || key;
        }
        
        /**
         * Render empty state
         */
        function renderEmptyState() {
            const html = '<div class="va-location-empty">' +
                '<svg class="va-empty-icon" width="64" height="64" viewBox="0 0 64 64" fill="none">' +
                '<circle cx="32" cy="32" r="30" stroke="#ccc" stroke-width="2"/>' +
                '<path d="M32 16C24.8 16 19 21.8 19 29c0 11 13 29 13 29s13-18 13-29c0-7.2-5.8-13-13-13zm0 17c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z" fill="#ccc"/>' +
                '</svg>' +
                '<h3>' + vaLocationDirectory.strings.noResults + '</h3>' +
                '<p>' + vaLocationDirectory.strings.tryAgain + '</p>' +
                '</div>';
            
            $results.html(html).fadeIn(300);
        }
        
        /**
         * Show error message
         * 
         * @param {string} message Error message
         */
        function showError(message) {
            const html = '<div class="va-location-empty">' +
                '<p style="color: #ef4444;">' + escapeHtml(message) + '</p>' +
                '</div>';
            
            $results.html(html).fadeIn(300);
        }
        
        /**
         * Escape HTML to prevent XSS
         * 
         * @param {string} text Text to escape
         * @return {string} Escaped text
         */
        function escapeHtml(text) {
            if (!text) return '';
            
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        /**
         * Reset filters
         */
        function resetFilters() {
            $cityFilter.val('');
            $serviceFilter.val('');
            $form.submit();
        }
        
        // =====================================================
        // Event Handlers
        // =====================================================
        
        // Form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            searchLocations();
        });
        
        // Filter change triggers search
        $cityFilter.on('change', function() {
            searchLocations();
        });
        
        $serviceFilter.on('change', function() {
            searchLocations();
        });
        
        // Reset button
        $resetBtn.on('click', function(e) {
            e.preventDefault();
            resetFilters();
        });
        
        // Keyboard accessibility
        $(document).on('keydown', function(e) {
            // Escape key clears filters
            if (e.key === 'Escape') {
                resetFilters();
            }
        });
        
        // =====================================================
        // Initialization
        // =====================================================
        
        console.log('VA Location Directory initialized');
    });

})(jQuery);
