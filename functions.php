<?php
if (!defined('ABSPATH')) {
    exit;
}

function impulse_clone_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    
    // Register navigation menu
    register_nav_menus(array(
        'primary-menu' => 'Primary Navigation Menu',
        'footer-menu' => 'Footer Menu'
    ));
}
add_action('after_setup_theme', 'impulse_clone_setup');

// function impulse_clone_get_asset_uri($pattern) {
//     $matches = glob(get_template_directory() . '/dist/assets/' . $pattern);
//     if (!empty($matches)) {
//         return get_template_directory_uri() . '/dist/assets/' . basename($matches[0]);
//     }
//     return '';
// }

// function impulse_clone_get_asset_ver($pattern) {
//     $matches = glob(get_template_directory() . '/dist/assets/' . $pattern);
//     if (!empty($matches) && file_exists($matches[0])) {
//         return (string) filemtime($matches[0]);
//     }
//     return wp_get_theme()->get('Version');
// }

function impulse_clone_enqueue_assets() {
    wp_enqueue_style(
        'impulse-clone-style',
        get_stylesheet_uri(),
        array(),
        filemtime(get_template_directory() . '/style.css')
    );

    // $react_css = impulse_clone_get_asset_uri('index-*.css');
    // if ($react_css) {
    //     wp_enqueue_style(
    //         'impulse-clone-react-style',
    //         $react_css,
    //         array('impulse-clone-style'),
    //         impulse_clone_get_asset_ver('index-*.css')
    //     );
    // }

    // $react_js = impulse_clone_get_asset_uri('index-*.js');
    // if ($react_js) {
    //     wp_enqueue_script(
    //         'impulse-clone-react-app',
    //         $react_js,
    //         array(),
    //         impulse_clone_get_asset_ver('index-*.js'),
    //         true
    //     );
    // }
}
add_action('wp_enqueue_scripts', 'impulse_clone_enqueue_assets');

function impulse_clone_module_script($tag, $handle, $src) {
    if ('impulse-clone-react-app' === $handle) {
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}
add_filter('script_loader_tag', 'impulse_clone_module_script', 10, 3);

// function impulse_clone_body_class($classes) {
//     if (is_front_page() || is_home()) {
//         $classes[] = 'react-shell-page';
//         $classes[] = 'page-home';
//     }
//     return $classes;
// }
// add_filter('body_class', 'impulse_clone_body_class');

// function impulse_clone_title($title) {
//     if (is_front_page() || is_home()) {
//         return 'impulsecomputeracademy.com';
//     }
//     return $title;
// }
// add_filter('pre_get_document_title', 'impulse_clone_title');
function create_courses_cpt() {

register_post_type('courses', array(
    'labels' => array(
        'name' => 'Courses',
        'singular_name' => 'Course'
    ),
    'public' => true,
    'has_archive' => true,
    'rewrite' => array('slug' => 'courses'),
    'menu_icon' => 'dashicons-welcome-learn-more',
    'supports' => array('title','editor','thumbnail'),
));

}

add_action('init','create_courses_cpt');
function my_theme_register_menus() {
    register_nav_menus(
        array(
            'primary-menu' => __( 'Primary Menu', 'Top Menu' ),
            'footer-menu'  => __( 'Footer Menu', 'Bottom Menu' ),
        )
    );
}
add_action( 'after_setup_theme', 'my_theme_register_menus' );
