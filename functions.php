<?php
define( 'THIRTYSEVEN_OES_VERSION', '0.1.0' );

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);

  register_nav_menus([
    'footer' => __('Footer Menu', '37o-es'),
  ]);
});

add_action('wp_enqueue_scripts', function () {
  $theme_dir = get_stylesheet_directory();
  $theme_uri = get_stylesheet_directory_uri();

  $ver = function($rel_path) use ($theme_dir) {
    $path = $theme_dir . $rel_path;
    return file_exists($path) ? filemtime($path) : THIRTYSEVEN_OES_VERSION;
  };

  // Keep style.css (for theme header and any tiny globals)
  wp_enqueue_style('theme-core', get_stylesheet_uri(), [], THIRTYSEVEN_OES_VERSION);

  wp_enqueue_style('theme-app', $theme_uri . '/assets/css/app.css', [], $ver('/assets/css/app.css'));
  wp_enqueue_style('theme-footer', $theme_uri . '/assets/css/footer.css', ['theme-app'], $ver('/assets/css/footer.css'));

  // Optional main script
  if ( file_exists( $theme_dir . '/assets/js/main.js' ) ) {
    wp_enqueue_script('theme-main', $theme_uri . '/assets/js/main.js', [], $ver('/assets/js/main.js'), true);
  }

    // Front page styles (only load on the front page)
  if ( is_front_page() ) {
    wp_enqueue_style(
      'theme-front',
      $theme_uri . '/assets/css/front-page.css',
      ['theme-app'],
      $ver('/assets/css/front-page.css')
    );
  }

  if ( is_page_template('page-contact.php') ) {
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    $ver = function($rel) use ($dir){ $p=$dir.$rel; return file_exists($p)?filemtime($p):THIRTYSEVEN_OES_VERSION; };

    wp_enqueue_style('theme-contact', $uri.'/assets/css/contact.css', ['theme-app'], $ver('/assets/css/contact.css'));
    wp_enqueue_script('theme-contact', $uri.'/assets/js/contact.js', [], $ver('/assets/js/contact.js'), true);
  }
});

/* === Projects Custom Post Type === */
add_action('init', function () {
  $labels = [
    'name'               => __('Projects', '37o-es'),
    'singular_name'      => __('Project', '37o-es'),
    'add_new'            => __('Add New', '37o-es'),
    'add_new_item'       => __('Add New Project', '37o-es'),
    'edit_item'          => __('Edit Project', '37o-es'),
    'new_item'           => __('New Project', '37o-es'),
    'view_item'          => __('View Project', '37o-es'),
    'search_items'       => __('Search Projects', '37o-es'),
    'not_found'          => __('No projects found', '37o-es'),
    'not_found_in_trash' => __('No projects found in Trash', '37o-es'),
    'all_items'          => __('All Projects', '37o-es'),
    'menu_name'          => __('Projects', '37o-es'),
  ];

  register_post_type('project', [
    'labels'             => $labels,
    'public'             => true,
    'has_archive'        => true,
    'rewrite'            => ['slug' => 'projects'],
    'menu_icon'          => 'dashicons-art',
    'supports'           => ['title','editor','thumbnail','excerpt','revisions'],
    'show_in_rest'       => true, // Gutenberg compatible
  ]);
});


