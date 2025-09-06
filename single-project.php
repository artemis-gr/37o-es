<?php
/* single-project.php */
get_header();

$subtitle     = get_field('subtitle');
$location     = get_field('location');
$area         = get_field('area');
$hero         = get_field('hero_image');
$project_date = get_field('project_date'); // guaranteed "Y-m-d" if valid
$year = $project_date ? (new DateTime($project_date))->format('Y') : '';

if ($project_date) {
  // try common formats
  $dt =
    DateTime::createFromFormat('Y/m/d', $project_date) ?:
    DateTime::createFromFormat('Y-m-d', $project_date) ?:
    DateTime::createFromFormat('Ymd',   $project_date) ?:
    (is_numeric($project_date) ? (new DateTime('@'.$project_date)) : null);

  if ($dt) {
    // if it was a timestamp, set WP timezone just in case
    $dt->setTimezone( wp_timezone() );
    $year_text = $dt->format('Y');
  }
}
?>
<main class="project-page">
  <header class="project-hero">
    <!-- Sticky title block -->
    <div class="project-hero__title-pin">
      <h1 class="project-title"><?php the_title(); ?></h1>
      <?php if ($subtitle): ?>
        <p class="project-subtitle"><?php echo esc_html($subtitle); ?></p>
      <?php endif; ?>
    </div>

    <!-- Sticky meta row -->
    <div class="project-hero__meta-pin">
      <?php if ($location): ?>
        <span class="meta__item">
          <span class="meta__label">Location:</span>
          <span class="meta__value"><?php echo esc_html($location); ?></span>
        </span>
      <?php endif; ?>

      <?php if ($area): ?>
        <span class="meta__item">
          <span class="meta__label">Area:</span>
          <span class="meta__value"><?php echo esc_html($area); ?></span>
        </span>
      <?php endif; ?>

      <?php if ($year_text): ?>
        <span class="meta__item">
          <span class="meta__label">Year:</span>
          <span class="meta__value"><?php echo esc_html($year_text); ?></span>
        </span>
      <?php endif; ?>
    </div>
  </header>

  <article class="project-content">
    <?php the_content(); ?>
  </article>
</main>
<?php get_footer(); ?>
