<?php get_header(); ?>

<main class="projects-page" role="main">
  <section class="projects-grid">
    <?php if (have_posts()) :
      $i = 0;
      while (have_posts()) : the_post();

        // gather fields (same as your current file) …
        $subtitle = get_field('subtitle');
        $hero_raw = get_field('hero_image');
        $img_url = ''; $img_alt = '';
        if (is_array($hero_raw)) { $img_url = $hero_raw['sizes']['large'] ?? $hero_raw['url'] ?? ''; $img_alt = $hero_raw['alt'] ?? ''; }
        elseif (is_numeric($hero_raw)) { $img_url = wp_get_attachment_image_url((int)$hero_raw, 'large') ?: ''; $img_alt = get_post_meta((int)$hero_raw, '_wp_attachment_image_alt', true) ?: ''; }
        elseif (is_string($hero_raw)) { $img_url = $hero_raw; }

        if (!$img_url && has_post_thumbnail()) {
          $img_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
          $img_alt = get_the_title();
        }

        // open a row every 2 items
        if ($i % 2 === 0) echo '<div class="projects-row">';
    ?>
        <article class="project-card">
          <a class="project-card__link" href="<?php the_permalink(); ?>">
            <div class="project-card__media">
              <?php if ($img_url): ?>
                <img src="<?php echo esc_url($img_url); ?>"
                     alt="<?php echo esc_attr($img_alt ?: get_the_title()); ?>"
                     loading="lazy" decoding="async" />
              <?php endif; ?>
            </div>

            <div class="project-card__head">
              <h2 class="project-title"><?php the_title(); ?></h2>
              <div class="project-meta">
                <?php if ($location = get_field('location')): ?>
                  <span class="project-location"><?php echo esc_html($location); ?></span>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($subtitle): ?>
              <p class="project-subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
          </a>
        </article>
    <?php
        $i++;
        // close the row after 2 items OR at the very end
        if ($i % 2 === 0) echo '</div>';
      endwhile;

      if ($i % 2 !== 0) echo '</div>'; // close a trailing odd row

      the_posts_pagination([
        'mid_size'  => 1,
        'prev_text' => '« Prev',
        'next_text' => 'Next »',
      ]);
    else:
      echo '<p>No projects yet.</p>';
    endif; ?>
  </section>
</main>

<?php get_footer(); ?>

<script>
  // Ensure no project card stays focused when coming back
  window.addEventListener('pageshow', function () {
    document.querySelectorAll('.project-card__link').forEach(a => a.blur());
  });
</script>
