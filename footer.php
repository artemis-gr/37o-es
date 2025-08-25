<?php
  // Detect if we're on the front page to toggle footer color scheme
  $footer_classes = ['site-footer'];
  if ( is_front_page() ) { $footer_classes[] = 'is-front'; }
?>
<footer class="<?php echo esc_attr(implode(' ', $footer_classes)); ?>" role="contentinfo">
  <div class="footer-inner" aria-label="<?php esc_attr_e('Footer', '37o-es'); ?>">
    <a class="footer-link footer-link--contact" href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ?: home_url('/contact') ); ?>">
      <?php esc_html_e('Contact', '37o-es'); ?>
    </a>

    <a class="footer-logo" href="<?php echo esc_url( home_url('/') ); ?>">
      <img
        src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/logo.png' ); ?>"
        alt="<?php bloginfo('name'); ?>"
        decoding="async"
        fetchpriority="low"
      />
    </a>

    <a class="footer-link footer-link--projects" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ?: home_url('/projects') ); ?>">
      <?php esc_html_e('Projects', '37o-es'); ?>
    </a>
  </div>

  <?php wp_footer(); ?>
</footer>
</body>
</html>
