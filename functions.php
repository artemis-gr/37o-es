<?php
define('THIRTYSEVEN_OES_VERSION', '0.1.0');

/** -------------------------------------------------
 * Theme setup
 * ------------------------------------------------- */
add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);

  // Editor preview stylesheet
  add_theme_support('editor-styles');
  add_editor_style('assets/css/editor.css');

  // Remove starter patterns and wide/full alignment from the theme
  remove_theme_support('core-block-patterns');
  remove_theme_support('align-wide');

  register_nav_menus([
    'footer' => __('Footer Menu', '37o-es'),
  ]);
}, 11);

/** -------------------------------------------------
 * Front-end assets
 * ------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();
  $ver = function (string $rel) use ($dir) {
    $p = $dir . $rel;
    return file_exists($p) ? filemtime($p) : THIRTYSEVEN_OES_VERSION;
  };

  // Base + global
  wp_enqueue_style('theme-core', get_stylesheet_uri(), [], THIRTYSEVEN_OES_VERSION);
  wp_enqueue_style('theme-fonts',  $uri . '/assets/css/fonts.css',  [], $ver('/assets/css/fonts.css'));
  wp_enqueue_style('theme-app',    $uri . '/assets/css/app.css',    ['theme-fonts'], $ver('/assets/css/app.css'));
  wp_enqueue_style('theme-footer', $uri . '/assets/css/footer.css', ['theme-app'],   $ver('/assets/css/footer.css'));

  if (file_exists($dir . '/assets/js/main.js')) {
    wp_enqueue_script('theme-main', $uri . '/assets/js/main.js', [], $ver('/assets/js/main.js'), true);
  }

  // Page-specific
  if (is_front_page() && file_exists($dir . '/assets/css/front-page.css')) {
    wp_enqueue_style('theme-front', $uri . '/assets/css/front-page.css', ['theme-app'], $ver('/assets/css/front-page.css'));
  }

  if (is_page_template('page-contact.php')) {
    wp_enqueue_style('theme-contact', $uri . '/assets/css/contact.css', ['theme-app'], $ver('/assets/css/contact.css'));
    if (file_exists($dir . '/assets/js/contact.js')) {
      wp_enqueue_script('theme-contact', $uri . '/assets/js/contact.js', [], $ver('/assets/js/contact.js'), true);
    }
  }

  if (is_post_type_archive('project') || is_tax(['project_category','project_tag'])) {
    if (file_exists($dir . '/assets/css/archive-project.css')) {
      wp_enqueue_style('archive-project', $uri . '/assets/css/archive-project.css', ['theme-app'], $ver('/assets/css/archive-project.css'));
    }
  }

  if (is_singular('project') && file_exists($dir . '/assets/css/single-project.css')) {
    wp_enqueue_style('single-project', $uri . '/assets/css/single-project.css', ['theme-app'], $ver('/assets/css/single-project.css'));
  }
});

/** -------------------------------------------------
 * CPT + Taxonomy
 * ------------------------------------------------- */
add_action('init', function () {
  register_post_type('project', [
    'labels'       => ['name' => 'Projects', 'singular_name' => 'Project'],
    'public'       => true,
    'has_archive'  => true,
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

/** -------------------------------------------------
 * Limit available blocks (keep editor simple)
 * ------------------------------------------------- */
add_filter('allowed_block_types_all', function ($allowed_blocks, $editor_context) {
  return [
    'core/paragraph',
    'core/image',
    'core/gallery',
  ];
}, 10, 2);

/** -------------------------------------------------
 * Block styles (register ours + remove core "Rounded")
 * ------------------------------------------------- */
// Block styles (register ours + remove core "Rounded")
add_action('init', function () {
  // Image styles
  register_block_style('core/image', [
    'name'       => 'proj-img-large',
    'label'      => 'Large',
    'is_default' => true,
  ]);
  register_block_style('core/image', ['name' => 'proj-img-full',  'label' => 'Full-bleed (theme)']);
  register_block_style('core/image', ['name' => 'proj-img-small', 'label' => 'Small']);

  // GALLERY styles
  // 2 images → same total width as “Large” image
  register_block_style('core/gallery', [
    'name'  => 'proj-gallery-two',
    'label' => 'Two images',
  ]);

  // Multiple images (3–5) → same total width as “Full-bleed”
  register_block_style('core/gallery', [
    'name'  => 'proj-gallery-multi',
    'label' => 'Multi images (wide)',
  ]);

  // Remove old or unwanted styles
  unregister_block_style('core/gallery', 'proj-gallery-two-small'); // if it existed
  unregister_block_style('core/image', 'rounded');
}, 99);

/** -------------------------------------------------
 * Editor-only UI hacks (CSS + JS)
 * ------------------------------------------------- */
add_action('enqueue_block_editor_assets', function () {
  $css = "
    /* Hide Rounded in the styles panel */
    .block-editor-block-styles__item[aria-label*='Rounded'],
    .components-toggle-group-control-option[aria-label*='Rounded']{
      display:none !important;
    }
    /* Hide Wide / Full in the align menu */
    .block-editor-block-toolbar [aria-label*='Wide'][role='menuitem'],
    .block-editor-block-toolbar [aria-label*='Full'][role='menuitem']{
      display:none !important;
    }
  ";
  wp_add_inline_style('wp-edit-blocks', $css);
});

/** -------------------------------------------------
 * ACF: Normalize project_date to 'Y-m-d'
 * ------------------------------------------------- */
add_filter('acf/format_value/name=project_date', function ($value) {
  if (!$value || !is_string($value)) return $value;
  $value = trim($value);

  $dt =
    DateTime::createFromFormat('d/m/Y', $value) ?:
    DateTime::createFromFormat('Y/m/d', $value) ?:
    DateTime::createFromFormat('Y-m-d', $value) ?:
    DateTime::createFromFormat('Ymd',   $value) ?:
    (ctype_digit($value) ? (new DateTime('@' . $value)) : null);

  if ($dt) {
    $dt->setTimezone(wp_timezone());
    return $dt->format('Y-m-d');
  }
  return $value;
}, 10, 1);

/** -------------------------------------------------
 * Archive order: newest project_date first
 * ------------------------------------------------- */
add_action('pre_get_posts', function ($q) {
  if (is_admin() || !$q->is_main_query()) return;

  if (is_post_type_archive('project') || is_tax(['project_category', 'project_tag'])) {
    $q->set('meta_key', 'project_date');
    $q->set('orderby', 'meta_value_num');
    $q->set('order', 'DESC');
    $q->set('meta_query', [[
      'key' => 'project_date',
      'compare' => 'EXISTS',
    ]]);
  }
});
