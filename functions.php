<?php
define('THIRTYSEVEN_OES_VERSION', '0.1.0');

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

  // Version helper (mtime if file exists, fallback to theme constant)
  $ver = function (string $rel_path) use ($theme_dir) {
    $path = $theme_dir . $rel_path;
    return file_exists($path) ? filemtime($path) : THIRTYSEVEN_OES_VERSION;
  };

  // Base stylesheet (style.css)
  wp_enqueue_style('theme-core', get_stylesheet_uri(), [], THIRTYSEVEN_OES_VERSION);

  // Fonts first
  wp_enqueue_style('theme-fonts', $theme_uri . '/assets/css/fonts.css', [], $ver('/assets/css/fonts.css'));

  // Global app styles next
  wp_enqueue_style('theme-app', $theme_uri . '/assets/css/app.css', ['theme-fonts'], $ver('/assets/css/app.css'));

  // Footer styles (depends on app)
  wp_enqueue_style('theme-footer', $theme_uri . '/assets/css/footer.css', ['theme-app'], $ver('/assets/css/footer.css'));

  // Optional main script
  if (file_exists($theme_dir . '/assets/js/main.js')) {
    wp_enqueue_script('theme-main', $theme_uri . '/assets/js/main.js', [], $ver('/assets/js/main.js'), true);
  }

  // Front page only (guard file)
  if (is_front_page() && file_exists($theme_dir . '/assets/css/front-page.css')) {
    wp_enqueue_style('theme-front', $theme_uri . '/assets/css/front-page.css', ['theme-app'], $ver('/assets/css/front-page.css'));
  }

  // Contact template assets
  if (is_page_template('page-contact.php')) {
    wp_enqueue_style('theme-contact', $theme_uri . '/assets/css/contact.css', ['theme-app'], $ver('/assets/css/contact.css'));

    if (file_exists($theme_dir . '/assets/js/contact.js')) {
      wp_enqueue_script('theme-contact', $theme_uri . '/assets/js/contact.js', [], $ver('/assets/js/contact.js'), true);
    }
  }

  // Project archive (CPT archive & tax pages)
  if (is_post_type_archive('project') || is_tax(['project_category', 'project_tag'])) {
    if (file_exists($theme_dir . '/assets/css/archive-project.css')) {
      wp_enqueue_style('archive-project', $theme_uri . '/assets/css/archive-project.css', ['theme-app'], $ver('/assets/css/archive-project.css'));
    }
  }

  // Single project page
  if (is_singular('project')) {
    if (file_exists($theme_dir . '/assets/css/single-project.css')) {
      wp_enqueue_style('single-project', $theme_uri . '/assets/css/single-project.css', ['theme-app'], $ver('/assets/css/single-project.css'));
    }
  }
});

// CPT + taxonomy
add_action('init', function () {
  register_post_type('project', [
    'labels' => [
      'name'          => 'Projects',
      'singular_name' => 'Project',
    ],
    'public'       => true,
    'has_archive'  => true,                 // /projects
    'rewrite'      => ['slug' => 'projects'],
    'show_in_rest' => true,
    'menu_position'=> 20,
    'supports'     => ['title','editor','thumbnail','excerpt','revisions','page-attributes'],
  ]);

  register_taxonomy('project_category', 'project', [
    'label'        => 'Project Categories',
    'public'       => true,
    'rewrite'      => ['slug' => 'project-category'],
    'hierarchical' => true,
    'show_in_rest' => true,
  ]);
});

// Block styles for editor
add_action('init', function () {
  register_block_style('core/image', ['name' => 'proj-img-full',  'label' => 'Full width']);
  register_block_style('core/image', ['name' => 'proj-img-large', 'label' => 'Large']);
  register_block_style('core/image', ['name' => 'proj-img-small', 'label' => 'Small']);

  register_block_style('core/gallery',   ['name' => 'proj-gallery-two-small', 'label' => 'Two Small']);
  register_block_style('core/paragraph', ['name' => 'proj-credits',           'label' => 'Credits (small)']);
});

// Normalize ACF "project_date" so get_field('project_date') always returns "Y-m-d"
add_filter('acf/format_value/name=project_date', function ($value, $post_id, $field) {
  if (!$value || !is_string($value)) return $value;
  $value = trim($value);

  // Try common formats ACF / editors might produce
  $dt =
    DateTime::createFromFormat('d/m/Y', $value) ?: // e.g. 19/09/2024  ← add this
    DateTime::createFromFormat('Y/m/d', $value) ?: // e.g. 2024/09/19
    DateTime::createFromFormat('Y-m-d', $value) ?: // e.g. 2024-09-19
    DateTime::createFromFormat('Ymd',   $value) ?: // e.g. 20240919 (ACF default save)
    (ctype_digit($value) ? (new DateTime('@' . $value)) : null); // timestamp fallback

  if ($dt) {
    $dt->setTimezone( wp_timezone() );
    return $dt->format('Y-m-d'); // normalized output for templates
  }

  return $value; // if parsing failed, return raw (avoids fatal)
}, 10, 3);

// Order Project archives by ACF date (newest first)
add_action('pre_get_posts', function ($q) {
  if (is_admin() || !$q->is_main_query()) return;

  if (is_post_type_archive('project') || is_tax(['project_category','project_tag'])) {
    $q->set('meta_key', 'project_date');

    // If your DB stores Ymd (ACF default), order numerically:
    $q->set('orderby', 'meta_value_num');
    $q->set('order', 'DESC');

    // Optional: ensure only posts with a date appear first
    $q->set('meta_query', [[
      'key'     => 'project_date',
      'compare' => 'EXISTS',
    ]]);
  }
});


