<?php
define('THIRTYSEVEN_OES_VERSION', '0.1.0');

/** -------------------------------------------------
 * Theme setup
 * ------------------------------------------------- */
add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
  add_theme_support('disable-layout-styles');

  // Editor stylesheet
  add_theme_support('editor-styles');
  add_editor_style([
    'assets/css/fonts.css',
    'assets/css/editor.css',
  ]);

  // Remove starter patterns + wide/full alignment
  remove_theme_support('core-block-patterns');
  remove_theme_support('align-wide');

  // Typography presets (Normal / Small) & lock custom sizes
  add_theme_support('editor-font-sizes', [
    ['name' => __('Normal','37o-es'), 'slug' => 'proj-normal', 'size' => '1.3rem'],
    ['name' => __('Small','37o-es'),  'slug' => 'proj-small',  'size' => '1.05rem'],
  ]);
  add_theme_support('disable-custom-font-sizes');

  register_nav_menus(['footer' => __('Footer Menu', '37o-es')]);
}, 11);

/** -------------------------------------------------
 * Front-end assets
 * ------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();
  $ver = function ($rel) use ($dir) {
    $p = $dir . $rel;
    return file_exists($p) ? filemtime($p) : THIRTYSEVEN_OES_VERSION;
  };

  // Base + global
  wp_enqueue_style('theme-core', get_stylesheet_uri(), [], THIRTYSEVEN_OES_VERSION);
  wp_enqueue_style('theme-fonts',  $uri . '/assets/css/fonts.css',  [], $ver('/assets/css/fonts.css'));
  wp_enqueue_style('theme-app',    $uri . '/assets/css/app.css',    ['theme-fonts'], $ver('/assets/css/app.css'));
  wp_enqueue_style('theme-footer', $uri . '/assets/css/footer.css', ['theme-app'],   $ver('/assets/css/footer.css'));

  // Dequeue WP defaults you don’t want
  wp_dequeue_style('global-styles');
  wp_dequeue_style('wp-block-library-theme');

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

  if (is_singular('project')) {
    if (file_exists($dir . '/assets/css/single-project.css')) {
      wp_enqueue_style(
        'single-project',
        $uri . '/assets/css/single-project.css',
        ['theme-app'],
        $ver('/assets/css/single-project.css')
      );
    }

    if (file_exists($dir . '/assets/js/back-to-top.js')) {
      wp_enqueue_script(
        'back-to-top',
        $uri . '/assets/js/back-to-top.js',
        [],
        $ver('/assets/js/back-to-top.js'),
        true
      );
    }
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
 * Limit available blocks (keep Gallery so we override it)
 * ------------------------------------------------- */
add_filter('allowed_block_types_all', function () {
  return [
    'core/paragraph',
    'core/image',
    'core/spacer',
    'thirtyseven/two-image-gallery',
    'thirtyseven/multi-image-gallery',
  ];
});

/** -------------------------------------------------
 * Image block styles
 * ------------------------------------------------- */
add_action('init', function () {
  @unregister_block_style('core/image', 'rounded');
  @unregister_block_style('core/image', 'default');
  @unregister_block_style('core/image', 'proj-img-full');
  @unregister_block_style('core/image', 'proj-img-large');
  @unregister_block_style('core/image', 'proj-img-medium');
  @unregister_block_style('core/image', 'proj-img-small');

  register_block_style('core/image', ['name'=>'proj-img-small','label'=>'Small','is_default'=>true]);
  register_block_style('core/image', ['name'=>'proj-img-medium','label'=>'Medium']);
  register_block_style('core/image', ['name'=>'proj-img-large','label'=>'Large']);
}, 99);

/** -------------------------------------------------
 * Frontend: tweak Large <img> sizes attr
 * ------------------------------------------------- */
add_filter('render_block', function ($html, $block) {
  if (($block['blockName'] ?? '') !== 'core/image') return $html;
  if (strpos($html, 'is-style-proj-img-large') === false) return $html;

  if (preg_match('/\ssizes="[^"]*"/', $html)) {
    $html = preg_replace('/\ssizes="[^"]*"/', ' sizes="100vw"', $html, 1);
  } else {
    $html = preg_replace('/<img\b([^>]*?)\s(src="[^"]+")/i', '<img$1 $2 sizes="100vw"', $html, 1);
  }
  return $html;
}, 10, 2);

/** -------------------------------------------------
 * Two-Image Gallery — block styles
 * ------------------------------------------------- */
add_action('init', function () {
  register_block_style('thirtyseven/two-image-gallery', [
    'name'       => 'grid-medium',
    'label'      => __('Grid — Medium', '37o-es'),
    'is_default' => true,
  ]);
  register_block_style('thirtyseven/two-image-gallery', [
    'name'  => 'grid-large',
    'label' => __('Grid — Large', '37o-es'),
  ]);
});
/** -------------------------------------------------
 * Spacer styles
 * ------------------------------------------------- */
add_action('init', function () {
  register_block_style('core/spacer', ['name'=>'gap-small','label'=>'Gap — Small','is_default'=>true]);
  register_block_style('core/spacer', ['name'=>'gap-medium','label'=>'Gap — Medium']);
}, 99);

/** -------------------------------------------------
 * Paragraph width styles
 * ------------------------------------------------- */
add_action('init', function () {
  @unregister_block_style('core/paragraph', 'proj-text-size-normal');
  @unregister_block_style('core/paragraph', 'proj-text-size-small');
  @unregister_block_style('core/paragraph', 'proj-text-small');
  @unregister_block_style('core/paragraph', 'proj-text-large');

  register_block_style('core/paragraph', ['name'=>'proj-text-w-xsmall','label'=>'Width — Extra-Small']);
  register_block_style('core/paragraph', ['name'=>'proj-text-w-small', 'label'=>'Width — Small']);
  register_block_style('core/paragraph', ['name'=>'proj-text-w-medium','label'=>'Width — Medium','is_default'=>true]);
  register_block_style('core/paragraph', ['name'=>'proj-text-w-large', 'label'=>'Width — Large']);
}, 99);

/** -------------------------------------------------
 * Editor-only CSS (minimal, no gallery hiding hacks)
 * ------------------------------------------------- */
add_action('enqueue_block_editor_assets', function () {
  $css = '
  /* Hide Rounded/Default image styles */
  .block-editor-block-inspector [data-type="core/image"] .block-editor-block-styles__item[aria-label="Rounded"],
  .components-toggle-group-control-option[aria-label="Rounded"][data-type="core/image"],
  .components-popover .components-menu-item__button[aria-label="Rounded"],
  .block-editor-block-inspector [data-type="core/image"] .block-editor-block-styles__item[aria-label="Default"],
  .components-toggle-group-control-option[aria-label="Default"][data-type="core/image"],
  .components-popover .components-menu-item__button[aria-label="Default"]{display:none!important;}

  /* Hide Wide/Full toolbar buttons */
  .block-editor-block-toolbar [aria-label*="Wide"][role="menuitem"],
  .block-editor-block-toolbar [aria-label*="Full"][role="menuitem"]{display:none!important;}

  /* Spacer preview sizes */
  .editor-styles-wrapper .wp-block-spacer.is-style-gap-small{height:24px!important;}
  .editor-styles-wrapper .wp-block-spacer.is-style-gap-medium{height:56px!important;}
  @media (min-width:900px){
    .editor-styles-wrapper .wp-block-spacer.is-style-gap-small{height:40px!important;}
    .editor-styles-wrapper .wp-block-spacer.is-style-gap-medium{height:96px!important;}
  }';
  wp_add_inline_style('wp-edit-blocks', $css);
}, 20);

/** -------------------------------------------------
 * Editor-only JS
 *  - enqueue helper scripts (optional)
 *  - enqueue our custom two-image gallery block
 * ------------------------------------------------- */
add_action('enqueue_block_editor_assets', function () {
  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();

  $enqueue_if_exists = function ($handle, $rel, $deps = [], $in_footer = true) use ($dir, $uri) {
    if (file_exists($dir . $rel)) {
      wp_enqueue_script($handle, $uri . $rel, $deps, filemtime($dir . $rel), $in_footer);
    }
  };

  // Optional helpers (keep if you actually use them)
  $enqueue_if_exists('theme-editor-spacer-default',  '/assets/js/editor-spacer-default.js', ['wp-blocks','wp-hooks','wp-dom-ready']);
  $enqueue_if_exists('theme-editor-image-clean',     '/assets/js/editor-image-clean.js',    ['wp-blocks','wp-hooks','wp-dom-ready']);
  $enqueue_if_exists('theme-editor-paragraph-clean', '/assets/js/editor-paragraph-clean.js',
    ['wp-blocks','wp-hooks','wp-dom-ready','wp-data','wp-rich-text','wp-element','wp-components','wp-compose','wp-block-editor','lodash']
  );

  $enqueue_if_exists(
    'theme-block-two-image-gallery',
    '/assets/js/block-two-image-gallery.js',
    ['wp-blocks','wp-element','wp-i18n','wp-block-editor','wp-components'],
    true
  );
  $enqueue_if_exists(
    'theme-block-multi-image-gallery',
    '/assets/js/block-multi-image-gallery.js',
    ['wp-blocks','wp-element','wp-i18n','wp-block-editor','wp-components','wp-data'],
    true
  );
}, 20);

/** -------------------------------------------------
 * ACF: Normalize project_date to Y-m-d
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

  if (is_post_type_archive('project') || is_tax(['project_category','project_tag'])) {
    $q->set('meta_key', 'project_date');
    $q->set('orderby', 'meta_value_num');
    $q->set('order', 'DESC');
    $q->set('meta_query', [[ 'key' => 'project_date', 'compare' => 'EXISTS' ]]);
  }
});