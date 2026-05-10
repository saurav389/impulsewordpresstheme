<?php
// Template Name: Magic Code

get_header();

$mom_magic_name = get_query_var('mom_name');

if ($mom_magic_name) {
    $_GET['name'] = $mom_magic_name;
    $_REQUEST['name'] = $mom_magic_name;
}
?>

<main id="primary" class="site-main" role="main">
    <?php
    if (shortcode_exists('mom_magic_view')) {
        echo do_shortcode('[mom_magic_view]');
    } else {
        echo '<p>The Mom Magic Tribute plugin is not active or the shortcode is unavailable.</p>';
    }
    ?>
</main>

<?php get_footer(); ?>
