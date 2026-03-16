<?php
/**
 * Template para el Catálogo de Productos (Tienda)
 * Página de tienda con sidebar de filtros y grid de productos
 */

get_header();
?>

<main id="site-main" class="site-main woocommerce-page">
<div class="container" style="padding-top:2rem;padding-bottom:4rem;">

    <!-- Breadcrumb -->
    <?php elrancho_breadcrumbs(); ?>

    <!-- Encabezado de la tienda -->
    <div style="margin-bottom:2rem;">
        <h1 class="shop-page-title" style="font-size:clamp(1.75rem,4vw,2.5rem);margin-bottom:0.375rem;">
            <?php
            if (is_product_category()) {
                single_term_title();
            } elseif (is_search()) {
                printf(esc_html__('Resultados para: "%s"', 'elrancho'), get_search_query());
            } else {
                esc_html_e('Fresco de Nuestros Hornos', 'elrancho');
            }
            ?>
        </h1>
        <p style="color:var(--color-text-light);font-size:0.9375rem;margin:0;">
            <?php
            if (is_product_category()) {
                $term_desc = term_description();
                echo $term_desc ? wp_kses_post($term_desc) : esc_html__('Explora nuestra selección artesanal.', 'elrancho');
            } else {
                esc_html_e('Panes, pasteles y pan dulce tradicional mexicano.', 'elrancho');
            }
            ?>
        </p>
    </div>

    <div class="shop-layout">

        <!-- SIDEBAR -->
        <aside class="shop-sidebar" aria-label="<?php esc_attr_e('Filtros de tienda', 'elrancho'); ?>">

            <!-- Categorías -->
            <div class="sidebar-widget widget_product_categories">
                <h3 class="sidebar-widget-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/></svg>
                    <?php esc_html_e('Categorías', 'elrancho'); ?>
                </h3>
                <?php
                $current_cat = is_product_category() ? get_queried_object_id() : 0;
                $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'orderby' => 'name']);
                ?>
                <ul style="margin:0;padding:0;">
                    <li style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--color-border);">
                        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" style="font-size:0.9rem;font-weight:600;color:var(--color-text-main);display:flex;align-items:center;gap:0.5rem;<?php echo !$current_cat ? 'color:var(--color-primary);' : ''; ?>">
                            <?php esc_html_e('Todos los Productos', 'elrancho'); ?>
                        </a>
                        <?php
                        $total = wp_count_posts('product')->publish;
                        echo '<span class="count">' . intval($total) . '</span>';
                        ?>
                    </li>
                    <?php if (!is_wp_error($cats) && $cats) : foreach ($cats as $cat) : ?>
                        <li style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--color-border);">
                            <a href="<?php echo esc_url(get_term_link($cat)); ?>"
                               style="font-size:0.9rem;font-weight:600;color:<?php echo $cat->term_id == $current_cat ? 'var(--color-primary)' : 'var(--color-text-main)'; ?>;">
                                <?php echo esc_html($cat->name); ?>
                            </a>
                            <span class="count"><?php echo intval($cat->count); ?></span>
                        </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>

            <?php elrancho_render_shop_sidebar_widgets(); ?>

        </aside><!-- /.shop-sidebar -->

        <!-- PRODUCTS AREA -->
        <div class="shop-products-area">

            <!-- Barra superior: resultados + ordenar -->
            <div class="shop-header-bar">
                <?php woocommerce_result_count(); ?>
                <?php woocommerce_catalog_ordering(); ?>
            </div>

            <?php if (have_posts()) : ?>

                <?php woocommerce_product_loop_start(); ?>

                <?php while (have_posts()) : the_post(); ?>
                    <?php wc_get_template_part('content', 'product'); ?>
                <?php endwhile; ?>

                <?php woocommerce_product_loop_end(); ?>

            <?php else : ?>
                <?php do_action('woocommerce_no_products_found'); ?>
            <?php endif; ?>

            <?php woocommerce_pagination(); ?>

        </div><!-- /.shop-products-area -->

    </div><!-- /.shop-layout -->

</div><!-- /.container -->
</main>

<?php get_footer(); ?>
