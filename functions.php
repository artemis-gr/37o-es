<?php
// Use a safe handle that doesn't start with a dot or weird chars.
define( 'THIRTYSEVEN_OES_VERSION', '0.1.0' );

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
  register_nav_menus([
    'footer' => __('Footer Menu', '37o-es'),
  ]);
  // Image sizes (tweak later)
  add_image_size('project-portrait', 1200, 1600, true);
});

add_action('wp_enqueue_scripts', function () {
  // Main stylesheet
  wp_enqueue_style('thirtysevenoes-style', get_stylesheet_uri(), [], THIRTYSEVEN_OES_VERSION);
  // Optional: a main JS file
  $js = get_template_directory_uri() . '/assets/js/main.js';
  if ( file_exists( get_template_directory() . '/assets/js/main.js' ) ) {
    wp_enqueue_script('thirtysevenoes-js', $js, [], THIRTYSEVEN_OES_VERSION, true);
  }
});
