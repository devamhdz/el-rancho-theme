<?php
$footer_tagline = elrancho_get_mod('footer_tagline', __('Trayendo el auténtico sabor de la panadería artesanal a tu vecindario desde 1995.', 'elrancho'));
$footer_logo    = elrancho_get_mod('footer_logo', '');
$elrancho_is_checkout_flow = function_exists('is_checkout')
    && is_checkout()
    && !is_order_received_page();
?>

</div><!-- /#main-content -->

<?php if ($elrancho_is_checkout_flow) : ?>
    <footer class="checkout-footer" role="contentinfo">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('Todos los derechos reservados.', 'elrancho'); ?></p>
        </div>
    </footer>

    <?php wp_footer(); ?>
    </body>
    </html>
    <?php return; ?>
<?php endif; ?>

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-top">

            <!-- Branding -->
            <div class="footer-brand">
                <?php if (!empty($footer_logo)) : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-brand-logo-link" aria-label="<?php bloginfo('name'); ?>">
                        <img src="<?php echo esc_url($footer_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="footer-brand-logo">
                    </a>
                <?php elseif (has_custom_logo()) : ?>
                    <?php echo get_custom_logo(); ?>
                <?php else : ?>
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
                        <div class="site-logo-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/>
                            </svg>
                        </div>
                        <span class="site-title"><?php bloginfo('name'); ?></span>
                    </div>
                <?php endif; ?>
                <p class="site-tagline"><?php echo esc_html($footer_tagline); ?></p>
                <div class="footer-social">
                    <?php if ($fb = elrancho_get_mod('social_facebook', '#')) : ?>
                        <a href="<?php echo esc_url($fb); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($ig = elrancho_get_mod('social_instagram', '#')) : ?>
                        <a href="<?php echo esc_url($ig); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($tt = elrancho_get_mod('social_tiktok', '#')) : ?>
                        <a href="<?php echo esc_url($tt); ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.67a8.21 8.21 0 004.84 1.56V6.78a4.85 4.85 0 01-1.07-.09z"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna Tienda -->
            <div class="footer-column">
                <h4 class="footer-heading"><?php esc_html_e('Tienda', 'elrancho'); ?></h4>
                <?php if (function_exists('wc_get_page_id')) : ?>
                    <ul class="footer-links">
                        <?php
                        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 5, 'parent' => 0]);
                        if (!is_wp_error($categories) && !empty($categories)) :
                            foreach ($categories as $cat) : ?>
                                <li><a href="<?php echo esc_url(get_term_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a></li>
                            <?php endforeach;
                        else : ?>
                            <li><a href="#"><?php esc_html_e('Pan Dulce', 'elrancho'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('Bolillos & Salados', 'elrancho'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('Pasteles Especiales', 'elrancho'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('Bebidas', 'elrancho'); ?></a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Columna Atención al Cliente -->
            <div class="footer-column">
                <h4 class="footer-heading"><?php esc_html_e('Atención al Cliente', 'elrancho'); ?></h4>
                <ul class="footer-links">
                    <li><a href="#"><?php esc_html_e('Contáctanos', 'elrancho'); ?></a></li>
                    <li><a href="#"><?php esc_html_e('Preguntas Frecuentes', 'elrancho'); ?></a></li>
                    <li><a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>"><?php esc_html_e('Mi Cuenta', 'elrancho'); ?></a></li>
                    <?php if (function_exists('wc_get_page_id')) : ?>
                        <li><a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php esc_html_e('La Tienda', 'elrancho'); ?></a></li>
                    <?php endif; ?>
                    <li><a href="#"><?php esc_html_e('Política de Envíos', 'elrancho'); ?></a></li>
                    <li><a href="#"><?php esc_html_e('Términos y Condiciones', 'elrancho'); ?></a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="footer-column">
                <h4 class="footer-heading"><?php esc_html_e('Newsletter', 'elrancho'); ?></h4>
                <p style="font-size:0.875rem;color:rgba(255,255,255,0.6);margin-bottom:1rem;"><?php esc_html_e('Suscríbete para recibir novedades, ofertas exclusivas y más.', 'elrancho'); ?></p>
                <div class="footer-newsletter-form">
                    <input type="email" id="footer-email" placeholder="<?php esc_attr_e('tu@email.com', 'elrancho'); ?>" aria-label="<?php esc_attr_e('Tu email', 'elrancho'); ?>">
                    <button class="btn btn-primary" id="footer-newsletter-btn" type="button">
                        <?php esc_html_e('Suscribirme', 'elrancho'); ?>
                    </button>
                    <p id="newsletter-message" style="font-size:0.8125rem;display:none;"></p>
                </div>
            </div>

        </div><!-- /.footer-top -->

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('Todos los derechos reservados.', 'elrancho'); ?></p>
            <div class="footer-bottom-links">
                <a href="#"><?php esc_html_e('Privacidad', 'elrancho'); ?></a>
                <a href="#"><?php esc_html_e('Cookies', 'elrancho'); ?></a>
            </div>
        </div>

    </div><!-- /.container -->
</footer>

<?php wp_footer(); ?>
</body>
</html>
