<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main-content"><?php esc_html_e('Ir al contenido', 'elrancho'); ?></a>

<?php
// Aviso de demo de WooCommerce
if (function_exists('is_woocommerce')) {
    do_action('woocommerce_demo_store');
}

$elrancho_is_checkout_flow = function_exists('is_checkout')
    && is_checkout()
    && !is_order_received_page();

if ($elrancho_is_checkout_flow) :
?>

<header id="masthead" class="site-header checkout-header" role="banner">
    <div class="container">
        <div class="checkout-header-inner">
            <div class="checkout-brand">
                <?php if (has_custom_logo()) : the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?>">
                    <span class="site-logo-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/>
                        </svg>
                    </span>
                    <span class="site-title"><?php bloginfo('name'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="checkout-secure">
                <?php esc_html_e('Checkout Seguro', 'elrancho'); ?>
            </div>
        </div>
    </div>
</header>

<div id="main-content" class="site-content checkout-content">
<?php
    return;
endif;
?>

<header id="masthead" class="site-header" role="banner">
    <div class="container">
        <div class="site-header-inner">

            <!-- Logo / Branding -->
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php bloginfo('name'); ?>">
                        <div class="site-logo-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/>
                            </svg>
                        </div>
                        <span class="site-title"><?php bloginfo('name'); ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Navegación Principal -->
            <nav class="main-navigation" id="main-navigation" aria-label="<?php esc_attr_e('Navegación principal', 'elrancho'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => function() {
                        echo '<ul>
                            <li><a href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '">' . __('Tienda', 'elrancho') . '</a></li>
                            <li><a href="#">' . __('Nosotros', 'elrancho') . '</a></li>
                            <li><a href="#">' . __('Programa de Lealtad', 'elrancho') . '</a></li>
                            <li><a href="#">' . __('Contacto', 'elrancho') . '</a></li>
                        </ul>';
                    },
                ]);
                ?>
            </nav>

            <!-- Acciones del Header -->
            <div class="header-actions">
                <!-- Búsqueda -->
                <?php get_search_form(); ?>

                <!-- Botón búsqueda móvil -->
                <button class="header-action-btn" id="mobile-search-btn" aria-label="<?php esc_attr_e('Buscar', 'elrancho'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>

                <!-- Carrito WooCommerce -->
                <?php if (function_exists('wc_get_cart_url')) : ?>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
                       class="header-action-btn <?php echo WC()->cart->get_cart_contents_count() > 0 ? 'cart-has-items' : ''; ?>"
                       aria-label="<?php printf(esc_attr__('Carrito (%s artículos)', 'elrancho'), WC()->cart->get_cart_contents_count()); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                        <span class="header-cart-count" aria-live="polite"><?php $c = WC()->cart->get_cart_contents_count(); echo $c > 0 ? intval($c) : ''; ?></span>
                        <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
                            <span class="sr-only"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- Mi cuenta -->
                <?php if (function_exists('wc_get_account_endpoint_url')) : ?>
                    <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>"
                       class="header-action-btn"
                       aria-label="<?php esc_attr_e('Mi cuenta', 'elrancho'); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </a>
                <?php endif; ?>

                <!-- Toggle Menú Móvil -->
                <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-nav" aria-label="<?php esc_attr_e('Abrir menú', 'elrancho'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menú Móvil -->
        <nav class="mobile-nav" id="mobile-nav" aria-label="<?php esc_attr_e('Menú móvil', 'elrancho'); ?>" role="navigation">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'fallback_cb'    => function() {
                    echo '<a href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '">' . __('Tienda', 'elrancho') . '</a>';
                    echo '<a href="#">' . __('Nosotros', 'elrancho') . '</a>';
                    echo '<a href="#">' . __('Programa de Lealtad', 'elrancho') . '</a>';
                    echo '<a href="#">' . __('Contacto', 'elrancho') . '</a>';
                },
                'items_wrap'     => '%3$s',
                'walker'         => new class extends Walker_Nav_Menu {
                    public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0) {
                        $output .= '<a href="' . esc_url($data_object->url) . '">' . esc_html($data_object->title) . '</a>';
                    }
                },
            ]);
            ?>
        </nav>
    </div>
</header>

<div id="main-content" class="site-content">
