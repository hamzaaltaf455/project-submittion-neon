<?php
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('menus');
    
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'va-broken-theme'),
    ));
});

add_action('wp_enqueue_scripts', function () {
    // Fixed: correct file path to theme.css
    wp_enqueue_style(
        'va-broken-style',
        get_stylesheet_directory_uri() . '/assets/css/theme.css',
        [],
        filemtime(get_stylesheet_directory() . '/assets/css/theme.css')
    );

    // Removed: JS file doesn't exist and isn't needed
    // wp_enqueue_script(
    //     'va-broken-app',
    //     get_stylesheet_directory_uri() . '/assets/js/app.js',
    //     [],
    //     '1.0',
    //     true
    // );
});
