<?php
/*
Template Name: Contact
*/
get_header();

function t37o_field($key, $default = '') {
  if ( function_exists('get_field') ) {
    $v = get_field($key);
    if ($v !== null && $v !== '' ) return $v;
  }
  return $default;
}

$map_img = t37o_field('map_image', get_stylesheet_directory_uri().'/assets/img/map-placeholder.png');
$map_img_mobile = t37o_field('map_image_mobile', ''); 

$dot1 = [
  'x' => t37o_field('dot1_x', '62'),
  'y' => t37o_field('dot1_y', '50'),
  'label' => t37o_field('dot1_label', 'Greece'),
];
$dot2 = [
  'x' => t37o_field('dot2_x', '21'),
  'y' => t37o_field('dot2_y', '53'),
  'label' => t37o_field('dot2_label', 'Spain'),
];

/** Architect 1 fields **/
$a1 = [
  'name'   => t37o_field('architect1_name', 'Architect 1'),
  'role'   => 'Architect',
  'phone1' => t37o_field('architect1_phone_1', ''),
  'phone2' => t37o_field('architect1_phone_2', ''),
  'email'  => t37o_field('architect1_email', 'a1@example.com'),
  'cv'     => t37o_field('architect1_cv_url', home_url('/cv/architect-1/index.html')),
];

/** Architect 2 fields **/
$a2 = [
  'name'   => t37o_field('architect2_name', 'Architect 2'),
  'role'   => 'Architect',
  'phone1' => t37o_field('architect2_phone_1', ''),
  'phone2' => t37o_field('architect2_phone_2', ''),
  'email'  => t37o_field('architect2_email', 'a2@example.com'),
  'cv'     => t37o_field('architect2_cv_url', home_url('/cv/architect-2/index.html')),
];

/* Studio details */
$studio_heading   = t37o_field('studio_heading', 'Architecture Studio');
$studio_locations = t37o_field('studio_locations', 'Madrid | Athens | Málaga');
?>
<main id="site-content" role="main" class="contact-page">
  <section class="contact-layout">
    <!-- Architect 1 (left) -->
    <div class="architect-card" data-architect="left">
      <a class="architect-name js-arch-trigger"
         href="<?php echo esc_url($a1['cv']); ?>"
         data-target="left"
         target="_blank" rel="noopener">
         <?php echo esc_html($a1['name']); ?>
      </a>
      <div class="architect-role"><?php echo esc_html($a1['role']); ?></div>

      <?php if ($a1['phone1']) : ?>
        <a class="architect-phone" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $a1['phone1'])); ?>">
          <?php echo esc_html($a1['phone1']); ?>
        </a>
      <?php endif; ?>
      <?php if ($a1['phone2']) : ?>
        <a class="architect-phone" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $a1['phone2'])); ?>">
          <?php echo esc_html($a1['phone2']); ?>
        </a>
      <?php endif; ?>

      <a class="architect-email" href="mailto:<?php echo esc_attr($a1['email']); ?>">
        <?php echo esc_html($a1['email']); ?>
      </a>
      <a class="architect-cv" href="<?php echo esc_url($a1['cv']); ?>" target="_blank" rel="noopener">CV</a>
    </div>

    <!-- Center map -->
    <div class="contact-map"
            data-dot1-x="<?php echo esc_attr($dot1['x']); ?>"
            data-dot1-y="<?php echo esc_attr($dot1['y']); ?>"
            data-dot1-label="<?php echo esc_attr($dot1['label']); ?>"
            data-dot2-x="<?php echo esc_attr($dot2['x']); ?>"
            data-dot2-y="<?php echo esc_attr($dot2['y']); ?>"
            data-dot2-label="<?php echo esc_attr($dot2['label']); ?>">

        <picture class="contact-map__picture">
            <?php if ($map_img_mobile) : ?>
                <source media="(max-width: 899px)" srcset="<?php echo esc_url($map_img_mobile); ?>" />
            <?php endif; ?>
            <img
                class="contact-map__img"
                src="<?php echo esc_url($map_img); ?>"
                alt="Studio map"
                decoding="async"
            />
        </picture>

        <span class="map-dot map-dot--1" style="--x:<?php echo esc_attr($dot1['x']); ?>%; --y:<?php echo esc_attr($dot1['y']); ?>%;"></span>
        <span class="map-dot map-dot--2" style="--x:<?php echo esc_attr($dot2['x']); ?>%; --y:<?php echo esc_attr($dot2['y']); ?>%;"></span>
    </div>


    <!-- Studio details -->
    <div class="studio-details">
      <h4 class="studio-heading"><?php echo esc_html($studio_heading); ?></h4>
      <p class="studio-locations"><?php echo esc_html($studio_locations); ?></p>
    </div>

    <!-- Architect 2 (right) -->
    <div class="architect-card" data-architect="right">
      <a class="architect-name js-arch-trigger"
         href="<?php echo esc_url($a2['cv']); ?>"
         data-target="right"
         target="_blank" rel="noopener">
         <?php echo esc_html($a2['name']); ?>
      </a>
      <div class="architect-role"><?php echo esc_html($a2['role']); ?></div>

      <?php if ($a2['phone1']) : ?>
        <a class="architect-phone" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $a2['phone1'])); ?>">
          <?php echo esc_html($a2['phone1']); ?>
        </a>
      <?php endif; ?>
      <?php if ($a2['phone2']) : ?>
        <a class="architect-phone" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $a2['phone2'])); ?>">
          <?php echo esc_html($a2['phone2']); ?>
        </a>
      <?php endif; ?>

      <a class="architect-email" href="mailto:<?php echo esc_attr($a2['email']); ?>">
        <?php echo esc_html($a2['email']); ?>
      </a>
      <a class="architect-cv" href="<?php echo esc_url($a2['cv']); ?>" target="_blank" rel="noopener">CV</a>
    </div>

    <!-- NEW: full-row overlay for the animated line + label -->
    <svg class="contact-overlay" aria-hidden="true" focusable="false" preserveAspectRatio="none">
        <line class="contact-line" x1="0" y1="0" x2="0" y2="0" />
    </svg>
    <div class="contact-label" aria-hidden="true"></div>
  </section>
</main>
<?php get_footer(); ?>
