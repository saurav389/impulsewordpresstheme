<?php
/**
 * Script to fix scheduled banners by clearing their start dates
 * This makes them "Live" immediately instead of "Scheduled"
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('get_option')) {
    die('This script requires WordPress to be loaded');
}

echo "<h2>Banner Start Date Fix</h2>";

// Get all published banners
$all_banners = get_posts(array(
    'post_type' => 'ica_banner',
    'post_status' => 'publish',
    'posts_per_page' => -1,
));

echo "<p>Processing " . count($all_banners) . " banners...</p>";

$fixed_count = 0;
$already_live = 0;

foreach ($all_banners as $banner) {
    $meta = get_post_meta($banner->ID, '_impulse_banner_start_date', true);
    $is_active = get_post_meta($banner->ID, '_impulse_banner_is_active', true);
    
    // Get runtime status
    $banner_meta = array(
        'is_active' => !empty($is_active) ? 1 : 0,
        'start_date' => $meta,
        'end_date' => get_post_meta($banner->ID, '_impulse_banner_end_date', true),
    );
    
    if (!class_exists('Impulse_Clone_Banner_Manager')) {
        echo "Error: Banner Manager class not found";
        return;
    }
    
    $runtime_status = Impulse_Clone_Banner_Manager::get_runtime_status_label($banner->ID, $banner_meta);
    
    echo "<p>Banner #{$banner->ID} ({$banner->post_title}): <strong>{$runtime_status}</strong>";
    
    if ('Scheduled' === $runtime_status && !empty($meta)) {
        // Clear the start date to make it live immediately
        delete_post_meta($banner->ID, '_impulse_banner_start_date');
        $fixed_count++;
        echo " ✅ FIXED - Start date cleared";
    } else {
        $already_live++;
        echo " ✓ Already live/appropriate status";
    }
    echo "</p>";
}

echo "<hr>";
echo "<p><strong>Summary:</strong></p>";
echo "<p>✅ Banners fixed: <strong>{$fixed_count}</strong></p>";
echo "<p>✓ Already live: <strong>{$already_live}</strong></p>";

// Verify the fix
echo "<h3>Verification: Active Banners After Fix</h3>";
$active_banners = Impulse_Clone_Banner_Manager::get_active_banners();
echo "<p>Active banners count: <strong>" . count($active_banners) . "</strong></p>";

if (!empty($active_banners)) {
    echo "<ul>";
    foreach ($active_banners as $banner) {
        echo "<li>✅ Banner #{$banner->ID}: {$banner->post_title}</li>";
    }
    echo "</ul>";
}

$payload = Impulse_Clone_Banner_Manager::get_homepage_payload();
echo "<p>Homepage payload mode: <strong>" . $payload['mode'] . "</strong></p>";
?>
