<?php
/**
 * Template para cada producto en el loop (tarjeta de producto)
 * Override de: woocommerce/templates/content-product.php
 */

defined('ABSPATH') || exit;

global $product;
if (!$product || !$product->is_visible()) return;

$avg   = $product->get_average_rating();
$count = $product->get_review_count();
$img   = get_the_post_thumbnail_url(null, 'woocommerce_thumbnail');
?>

<li <?php wc_product_class('product-card', $product); ?>>

    <!-- IMAGEN -->
    <div class="product-card-image">

        <?php
        // Badge de estado
        if (!$product->is_in_stock()) :
            echo '<span class="product-badge" style="background:#6b7280;color:#fff;">' . esc_html__('Agotado', 'elrancho') . '</span>';
        elseif ($product->is_on_sale()) :
            echo '<span class="product-badge badge-sale">' . esc_html__('Oferta', 'elrancho') . '</span>';
        elseif ($product->is_featured()) :
            echo '<span class="product-badge badge-bestseller">' . esc_html__('Bestseller', 'elrancho') . '</span>';
        elseif (function_exists('elrancho_is_new_product') && elrancho_is_new_product($product)) :
            echo '<span class="product-badge badge-new">' . esc_html__('Nuevo', 'elrancho') . '</span>';
        endif;
        ?>

        <!-- Botón wishlist -->
        <button class="product-wishlist-btn"
                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                aria-label="<?php esc_attr_e('Agregar a favoritos', 'elrancho'); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
            </svg>
        </button>

        <!-- Imagen del producto -->
        <a href="<?php echo esc_url(get_permalink()); ?>" aria-label="<?php the_title_attribute(); ?>" tabindex="-1">
            <?php if ($img) : ?>
                <img src="<?php echo esc_url($img); ?>"
                     alt="<?php echo esc_attr(get_the_title()); ?>"
                     loading="lazy"
                     width="480" height="480">
            <?php else : ?>
                <div style="width:100%;height:100%;background:var(--color-background-warm);display:flex;align-items:center;justify-content:center;aspect-ratio:1;">
                    <svg width="52" height="52" viewBox="0 0 24 24" fill="var(--color-border-warm)">
                        <path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/>
                    </svg>
                </div>
            <?php endif; ?>
        </a>
    </div><!-- /.product-card-image -->

    <!-- CUERPO DE LA TARJETA -->
    <div class="product-card-body">

        <!-- Calificación -->
        <?php if ($avg > 0) : ?>
            <div class="product-rating" aria-label="<?php printf(esc_attr__('%s de 5 estrellas', 'elrancho'), $avg); ?>">
                <?php for ($i = 1; $i <= 5; $i++) :
                    $color = $i <= round($avg) ? '#f59e0b' : '#e2d5c3'; ?>
                    <span style="color:<?php echo $color; ?>;font-size:0.8rem;" aria-hidden="true">★</span>
                <?php endfor; ?>
                <span class="rating-count">(<?php echo intval($count); ?>)</span>
            </div>
        <?php endif; ?>

        <!-- Nombre -->
        <h3 class="product-title woocommerce-loop-product__title">
            <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
        </h3>

        <!-- Descripción corta -->
        <?php $short_desc = $product->get_short_description();
        if ($short_desc) : ?>
            <p class="product-excerpt"><?php echo wp_trim_words(wp_strip_all_tags($short_desc), 12); ?></p>
        <?php endif; ?>

        <!-- Precio + Botón -->
        <div class="product-card-footer">
            <div class="price">
                <?php echo $product->get_price_html(); ?>
            </div>

            <?php if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) : ?>
                <button class="add-to-cart-btn add_to_cart_button ajax_add_to_cart"
                        data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                        data-quantity="1"
                        data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                        data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                        data-product-type="<?php echo esc_attr($product->get_type()); ?>"
                        type="button"
                        aria-label="<?php printf(esc_attr__('Agregar %s al carrito', 'elrancho'), get_the_title()); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                </button>
            <?php elseif ($product->get_type() === 'variable' || $product->get_type() === 'grouped') : ?>
                <a href="<?php echo esc_url(get_permalink()); ?>" class="add-to-cart-btn" style="background:var(--color-text-main);" aria-label="<?php esc_attr_e('Ver opciones', 'elrancho'); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url(get_permalink()); ?>" class="add-to-cart-btn" style="background:var(--color-text-muted);" aria-label="<?php esc_attr_e('Ver producto', 'elrancho'); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            <?php endif; ?>
        </div><!-- /.product-card-footer -->

    </div><!-- /.product-card-body -->

</li>
