<?php
/**
 * Template Name: Página de Inicio
 * Homepage principal con Hero, Categorías, Productos Destacados, Lealtad y Nosotros
 */

get_header();

$hero_bg      = elrancho_get_mod('hero_background');
$hero_badge   = elrancho_get_mod('hero_badge', __('Horneado Fresco Diario', 'elrancho'));
$hero_title   = elrancho_get_mod('hero_title', __('Sabores Auténticos, Calidad Artesanal', 'elrancho'));
$hero_sub     = elrancho_get_mod('hero_subtitle', __('Experimenta la calidez de la panadería tradicional con nuestro pan artesanal, pan dulce y pasteles personalizados.', 'elrancho'));
$loyalty_title= elrancho_get_mod('loyalty_title', __('Gana Pan con Cada Compra', 'elrancho'));
$loyalty_desc = elrancho_get_mod('loyalty_description', __('Únete a nuestro programa familiar de recompensas. Obtén descuentos exclusivos, antojos de cumpleaños y puntos por cada peso que gastes.', 'elrancho'));
$loyalty_img  = elrancho_get_mod('loyalty_image');
$about_img    = elrancho_get_mod('about_image');
$about_year   = elrancho_get_mod('about_year', '1995');
$shop_url     = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : '#';

$hero_slides = get_posts([
    'post_type'      => 'elrancho_slide',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
]);

if (empty($hero_slides)) {
    $hero_slides = [(object) [
        'ID'           => 0,
        'post_title'   => $hero_title,
        'post_content' => $hero_sub,
        'hero_badge'   => $hero_badge,
        'image_url'    => $hero_bg,
        'primary_text' => __('Comprar Ahora', 'elrancho'),
        'primary_url'  => $shop_url,
        'secondary_text' => __('Ver Menú', 'elrancho'),
        'secondary_url'  => $shop_url,
    ]];
}
?>

<main id="site-main" class="section">
<div class="container">

<!-- ================================================
     HERO SECTION
     ================================================ -->
<section class="hero-section" aria-label="<?php esc_attr_e('Banner principal', 'elrancho'); ?>">
    <div class="hero-slides" data-hero-carousel="<?php echo count($hero_slides) > 1 ? 'true' : 'false'; ?>">
        <?php foreach ($hero_slides as $idx => $slide) :
            $slide_id = intval($slide->ID);

            if ($slide_id > 0) {
                $image_url = get_the_post_thumbnail_url($slide_id, 'elrancho-hero');
                $badge = get_post_meta($slide_id, '_elrancho_slide_badge', true);
                $primary_text = get_post_meta($slide_id, '_elrancho_slide_primary_text', true) ?: __('Comprar Ahora', 'elrancho');
                $primary_url = get_post_meta($slide_id, '_elrancho_slide_primary_url', true) ?: $shop_url;
                $secondary_text = get_post_meta($slide_id, '_elrancho_slide_secondary_text', true) ?: __('Ver Menú', 'elrancho');
                $secondary_url = get_post_meta($slide_id, '_elrancho_slide_secondary_url', true) ?: $shop_url;
                $title = get_the_title($slide_id);
                $desc = wp_strip_all_tags(get_post_field('post_content', $slide_id));
            } else {
                $image_url = $slide->image_url ?? '';
                $badge = $slide->hero_badge ?? '';
                $primary_text = $slide->primary_text ?? __('Comprar Ahora', 'elrancho');
                $primary_url = $slide->primary_url ?? $shop_url;
                $secondary_text = $slide->secondary_text ?? __('Ver Menú', 'elrancho');
                $secondary_url = $slide->secondary_url ?? $shop_url;
                $title = $slide->post_title ?? '';
                $desc = $slide->post_content ?? '';
            }
            ?>
            <article class="hero-slide <?php echo $idx === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo esc_attr($idx); ?>">
                <div class="hero-bg" <?php if ($image_url) : ?>style="background-image:url('<?php echo esc_url($image_url); ?>')"<?php endif; ?>></div>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <?php if (!empty($badge)) : ?>
                        <div class="hero-badge" aria-label="<?php esc_attr_e('Destacado', 'elrancho'); ?>">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
                            <?php echo esc_html($badge); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($title)) : ?>
                        <h1 class="hero-title"><?php echo esc_html($title); ?></h1>
                    <?php endif; ?>
                    <?php if (!empty($desc)) : ?>
                        <p class="hero-description"><?php echo esc_html($desc); ?></p>
                    <?php endif; ?>
                    <div class="hero-actions">
                        <a href="<?php echo esc_url($primary_url); ?>" class="btn btn-primary btn-lg">
                            <?php echo esc_html($primary_text); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                        <a href="<?php echo esc_url($secondary_url); ?>" class="btn btn-secondary btn-lg hero-btn-secondary">
                            <?php echo esc_html($secondary_text); ?>
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (count($hero_slides) > 1) : ?>
        <div class="hero-dots" role="tablist" aria-label="<?php esc_attr_e('Slides del banner principal', 'elrancho'); ?>">
            <?php foreach ($hero_slides as $idx => $slide) : ?>
                <button
                    class="hero-dot <?php echo $idx === 0 ? 'active' : ''; ?>"
                    type="button"
                    role="tab"
                    aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                    aria-label="<?php printf(esc_attr__('Ir al slide %d', 'elrancho'), $idx + 1); ?>"
                    data-slide-to="<?php echo esc_attr($idx); ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


<!-- ================================================
     CATEGORÍAS
     ================================================ -->
<section class="section-sm" aria-label="<?php esc_attr_e('Categorías', 'elrancho'); ?>">
    <div class="section-header">
        <div>
            <h2 class="section-title"><?php esc_html_e('Comprar por Categoría', 'elrancho'); ?></h2>
        </div>
        <a href="<?php echo esc_url($shop_url); ?>" class="view-all-link">
            <?php esc_html_e('Ver Todo', 'elrancho'); ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="category-filters" role="list">
        <a href="<?php echo esc_url($shop_url); ?>" class="category-pill active" role="listitem">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/></svg>
            <?php esc_html_e('Todo', 'elrancho'); ?>
        </a>
        <?php
        $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 6, 'parent' => 0]);
        if (!is_wp_error($cats) && !empty($cats)) {
            foreach ($cats as $cat) {
                printf(
                    '<a href="%s" class="category-pill" role="listitem">%s</a>',
                    esc_url(get_term_link($cat)),
                    esc_html($cat->name)
                );
            }
        } else {
            $default_cats = [__('Pan Dulce', 'elrancho'), __('Bolillos', 'elrancho'), __('Pasteles', 'elrancho'), __('Bebidas', 'elrancho'), __('Temporada', 'elrancho')];
            foreach ($default_cats as $cat) {
                echo '<span class="category-pill">' . esc_html($cat) . '</span>';
            }
        }
        ?>
    </div>
</section>


<!-- ================================================
     PRODUCTOS DESTACADOS
     ================================================ -->
<section class="section-sm" aria-label="<?php esc_attr_e('Productos destacados', 'elrancho'); ?>">
    <div class="section-header">
        <div>
            <h2 class="section-title"><?php esc_html_e('Productos Destacados', 'elrancho'); ?></h2>
        </div>
        <a href="<?php echo esc_url($shop_url); ?>" class="view-all-link">
            <?php esc_html_e('Ver Todo', 'elrancho'); ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <?php
    $featured_query = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 4,
        'meta_query'     => [['key' => '_featured', 'value' => 'yes']],
        'post_status'    => 'publish',
    ]);

    if (!$featured_query->have_posts()) {
        $featured_query = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => 4,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
    }
    ?>

    <?php if ($featured_query->have_posts()) : ?>
        <div class="products-grid featured-products-grid">
            <?php while ($featured_query->have_posts()) : $featured_query->the_post();
                global $product;
                if (!$product instanceof WC_Product) continue;
                $avg    = $product->get_average_rating();
                $count  = $product->get_review_count();
                $img    = get_the_post_thumbnail_url(null, 'elrancho-product-card');
                $is_new = elrancho_is_new_product($product);
            ?>
                <article class="product-card" itemscope itemtype="https://schema.org/Product">
                    <div class="product-card-image">
                        <?php if (!$product->is_in_stock()) : ?>
                            <span class="product-badge" style="background:#6b7280;color:#fff;"><?php esc_html_e('Agotado', 'elrancho'); ?></span>
                        <?php elseif ($product->is_on_sale()) : ?>
                            <span class="product-badge badge-sale"><?php esc_html_e('Oferta', 'elrancho'); ?></span>
                        <?php elseif ($product->is_featured()) : ?>
                            <span class="product-badge badge-bestseller"><?php esc_html_e('Bestseller', 'elrancho'); ?></span>
                        <?php elseif ($is_new) : ?>
                            <span class="product-badge badge-new"><?php esc_html_e('Nuevo', 'elrancho'); ?></span>
                        <?php endif; ?>

                        <button class="product-wishlist-btn" aria-label="<?php esc_attr_e('Agregar a favoritos', 'elrancho'); ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                            </svg>
                        </button>

                        <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                            <?php if ($img) : ?>
                                <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" itemprop="image">
                            <?php else : ?>
                                <div style="width:100%;height:100%;background:var(--color-background-warm);display:flex;align-items:center;justify-content:center;">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-border-warm)"><path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/></svg>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="product-card-body">
                        <?php if ($avg > 0) : ?>
                            <div class="product-rating" aria-label="<?php printf(esc_attr__('Calificación: %s de 5', 'elrancho'), $avg); ?>">
                                <?php for ($i = 1; $i <= 5; $i++) :
                                    $color = $i <= round($avg) ? '#f59e0b' : '#e2d5c3'; ?>
                                    <span class="star" style="color:<?php echo $color; ?>" aria-hidden="true">★</span>
                                <?php endfor; ?>
                                <span class="rating-count">(<?php echo $count; ?>)</span>
                            </div>
                        <?php endif; ?>

                        <h3 class="product-title" itemprop="name">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="product-excerpt"><?php echo esc_html(wp_strip_all_tags($product->get_short_description())); ?></p>

                        <div class="product-card-footer">
                            <div class="price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
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
                                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                                    </svg>
                                </button>
                            <?php elseif ($product->is_type('variable') || $product->is_type('grouped')) : ?>
                                <a href="<?php the_permalink(); ?>" class="add-to-cart-btn" style="background:var(--color-text-main);" aria-label="<?php esc_attr_e('Ver opciones', 'elrancho'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>" class="add-to-cart-btn" style="background:var(--color-text-muted);" aria-label="<?php esc_attr_e('Ver producto', 'elrancho'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php else : ?>
        <p style="text-align:center;padding:3rem;color:var(--color-text-light);"><?php esc_html_e('Pronto habrá productos disponibles.', 'elrancho'); ?></p>
    <?php endif; ?>
</section>


<!-- ================================================
     BANNER PROGRAMA DE LEALTAD
     ================================================ -->
<section class="loyalty-banner section-sm" aria-label="<?php esc_attr_e('Programa de lealtad', 'elrancho'); ?>">
    <div class="loyalty-content">
        <div class="loyalty-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            <?php bloginfo('name'); ?> Rewards
        </div>
        <h2 class="loyalty-title"><?php echo esc_html($loyalty_title); ?></h2>
        <p class="loyalty-description"><?php echo esc_html($loyalty_desc); ?></p>
        <div class="loyalty-actions">
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(add_query_arg('loyalty', '1', get_permalink(get_option('woocommerce_myaccount_page_id')))); ?>" class="btn btn-dark">
                    <?php esc_html_e('Ver mis puntos', 'elrancho'); ?>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url(wp_registration_url()); ?>" class="btn btn-dark">
                    <?php esc_html_e('Unirse Gratis', 'elrancho'); ?>
                </a>
                <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-secondary">
                    <?php esc_html_e('Iniciar Sesión', 'elrancho'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="loyalty-image" aria-hidden="true">
        <?php if ($loyalty_img) : ?>
            <img src="<?php echo esc_url($loyalty_img); ?>" alt="">
        <?php else : ?>
            <div style="width:100%;height:100%;min-height:220px;background:linear-gradient(135deg,#e8d5c4,#f3e7e8);display:flex;align-items:center;justify-content:center;">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="var(--color-border-warm)"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </div>
        <?php endif; ?>
    </div>
</section>


<!-- ================================================
     SECCIÓN NOSOTROS
     ================================================ -->
<section class="about-section section" aria-label="<?php esc_attr_e('Sobre nosotros', 'elrancho'); ?>">
    <div class="about-image">
        <?php if ($about_img) : ?>
            <img src="<?php echo esc_url($about_img); ?>" alt="<?php esc_attr_e('El arte de hornear', 'elrancho'); ?>" loading="lazy">
        <?php else : ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg,#e8d5c4,#d4a27a);"></div>
        <?php endif; ?>
    </div>
    <div class="about-content">
        <h2><?php printf(esc_html__('Memorias de Panadería Desde %s', 'elrancho'), esc_html($about_year)); ?></h2>
        <p><?php esc_html_e('En nuestra panadería, creemos en el poder de los ingredientes simples y las tradiciones de toda la vida. Nuestras recetas han pasado de generación en generación, asegurándonos de que cada bocado traiga un sabor de hogar.', 'elrancho'); ?></p>
        <p><?php esc_html_e('Desde nuestro horno a tu mesa. Pan hecho con amor, sin prisas y con los mejores ingredientes artesanales.', 'elrancho'); ?></p>
        <a href="#" class="about-read-more">
            <?php esc_html_e('Conoce Nuestra Historia', 'elrancho'); ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
</section>

</div><!-- /.container -->
</main>

<?php get_footer(); ?>
