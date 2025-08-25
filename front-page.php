<?php get_header(); ?>

<?php
  // Use the front page's Featured Image as background; fallback to a theme asset
  $bg_url = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
  if ( ! $bg_url ) {
    $bg_url = get_stylesheet_directory_uri() . '/assets/img/home-bg-temp.jpg';
  }
?>
<main id="site-content" class="front-hero" role="main" style="--front-bg: url('<?php echo esc_url( $bg_url ); ?>');">
  <h1 class="visually-hidden"><?php bloginfo('name'); ?></h1>
</main>

<?php get_footer(); ?>
