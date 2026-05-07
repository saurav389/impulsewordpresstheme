<?php
/**
 * Diagnostic script to check banner system status
 * Usage: Run from WordPress admin or include this in functions.php temporarily
 */

if (!defined('ABSPATH')) {
    exit;
}

// This should be called from WordPress context
if (!function_exists('get_option')) {
    die('This script requires WordPress to be loaded');
}

echo "<h2>Banner System Diagnostic Report</h2>";

// 1. Check if Banner Manager class exists
echo "<h3>1. Class Status</h3>";
if (class_exists('Impulse_Clone_Banner_Manager')) {
    echo "<p>✅ Impulse_Clone_Banner_Manager class exists</p>";
} else {
    echo "<p>❌ Impulse_Clone_Banner_Manager class NOT found</p>";
    return;
}

// 2. Check settings
echo "<h3>2. Banner Settings</h3>";
$settings = Impulse_Clone_Banner_Manager::get_settings();
echo "<pre>";
echo "Enabled: " . (!empty($settings['enabled']) ? "✅ Yes" : "❌ No") . "\n";
echo "Autoplay: " . (!empty($settings['autoplay']) ? "✅ Yes" : "❌ No") . "\n";
echo "Loop: " . (!empty($settings['loop']) ? "✅ Yes" : "❌ No") . "\n";
echo "Fallback Mode: " . $settings['fallback_mode'] . "\n";
echo "</pre>";

// 3. Check published banners in database
echo "<h3>3. Published Banners in Database</h3>";
$all_banners = get_posts(array(
    'post_type' => 'ica_banner',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
));

echo "<p>Total published banners: <strong>" . count($all_banners) . "</strong></p>";

if (empty($all_banners)) {
    echo "<p>⚠️ No published banners found!</p>";
} else {
    echo "<table style='border-collapse:collapse; width:100%;'>";
    echo "<tr><th style='border:1px solid #ccc; padding:8px;'>ID</th><th style='border:1px solid #ccc; padding:8px;'>Title</th><th style='border:1px solid #ccc; padding:8px;'>Active</th><th style='border:1px solid #ccc; padding:8px;'>Has Image</th><th style='border:1px solid #ccc; padding:8px;'>Status</th></tr>";
    
    foreach ($all_banners as $banner) {
        $meta = Impulse_Clone_Banner_Manager::get_banner_meta($banner->ID);
        $has_image = get_post_thumbnail_id($banner->ID) ? "✅" : "❌";
        $is_active = !empty($meta['is_active']) ? "✅" : "❌";
        $runtime_status = Impulse_Clone_Banner_Manager::get_runtime_status_label($banner->ID, $meta);
        
        echo "<tr>";
        echo "<td style='border:1px solid #ccc; padding:8px;'>" . $banner->ID . "</td>";
        echo "<td style='border:1px solid #ccc; padding:8px;'>" . $banner->post_title . "</td>";
        echo "<td style='border:1px solid #ccc; padding:8px;'>" . $is_active . "</td>";
        echo "<td style='border:1px solid #ccc; padding:8px;'>" . $has_image . "</td>";
        echo "<td style='border:1px solid #ccc; padding:8px;'>" . $runtime_status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Check active banners
echo "<h3>4. Active Banners (Should display on homepage)</h3>";
$active_banners = Impulse_Clone_Banner_Manager::get_active_banners();
echo "<p>Active banners count: <strong>" . count($active_banners) . "</strong></p>";

if (empty($active_banners)) {
    echo "<p>⚠️ No active banners found! This is why nothing shows on homepage.</p>";
} else {
    foreach ($active_banners as $banner) {
        $meta = Impulse_Clone_Banner_Manager::get_banner_meta($banner->ID);
        echo "<p>✅ Banner #{$banner->ID}: {$banner->post_title}</p>";
    }
}

// 5. Check homepage payload
echo "<h3>5. Homepage Payload</h3>";
$payload = Impulse_Clone_Banner_Manager::get_homepage_payload();
echo "<pre>";
echo "Mode: " . $payload['mode'] . "\n";
echo "Banners count: " . count($payload['banners']) . "\n";
echo "</pre>";

// 6. Show all banner meta data for first banner if exists
if (!empty($all_banners)) {
    echo "<h3>6. Detailed Meta for First Banner (ID: " . $all_banners[0]->ID . ")</h3>";
    $meta = Impulse_Clone_Banner_Manager::get_banner_meta($all_banners[0]->ID);
    echo "<pre>";
    print_r($meta);
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>Generated at:</strong> " . current_time('mysql') . "</p>";
?>
