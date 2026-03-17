<?php
/**
 * El Rancho Bakery - functions.php
 * Configuración principal del tema con soporte completo de WooCommerce
 */

defined('ABSPATH') || exit;

define('ELRANCHO_VERSION', '1.0.0');
define('ELRANCHO_URI', get_template_directory_uri());
define('ELRANCHO_DIR', get_template_directory());

/* =============================================
   CONFIGURACIÓN INICIAL DEL TEMA
   ============================================= */
function elrancho_setup() {
    load_theme_textdomain('elrancho', ELRANCHO_DIR . '/languages');

    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    // Tamaños de imágenes para panadería
    add_image_size('elrancho-product-card', 480, 480, true);
    add_image_size('elrancho-product-hero', 800, 800, true);
    add_image_size('elrancho-hero', 1440, 640, true);
    add_image_size('elrancho-thumbnail', 120, 120, true);

    // Custom logo
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 220,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => ['site-title'],
    ]);

    // WooCommerce
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 480,
        'gallery_thumbnail_image_width' => 120,
        'single_image_width' => 800,
        'product_grid' => [
            'default_rows'    => 4,
            'min_rows'        => 1,
            'max_rows'        => 8,
            'default_columns' => 4,
            'min_columns'     => 2,
            'max_columns'     => 4,
        ],
    ]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Menus de navegación
    register_nav_menus([
        'primary'  => __('Menú Principal', 'elrancho'),
        'footer'   => __('Menú del Footer', 'elrancho'),
        'shop'     => __('Menú de Tienda', 'elrancho'),
    ]);
}
add_action('after_setup_theme', 'elrancho_setup');

/* =============================================
   SOPORTE SVG (LOGOS)
   ============================================= */
function elrancho_allow_svg_uploads($mimes) {
    if (current_user_can('manage_options')) {
        $mimes['svg']  = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
    }
    return $mimes;
}
add_filter('upload_mimes', 'elrancho_allow_svg_uploads');

function elrancho_fix_svg_filetype($data, $file, $filename, $mimes, $real_mime = '') {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ['svg', 'svgz'], true)) {
        $data['ext']  = $ext;
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'elrancho_fix_svg_filetype', 10, 5);

function elrancho_svg_media_library_preview_fix() {
    echo '<style>
    .attachment-266x266[src$=".svg"],
    img[src$=".svg"].attachment-post-thumbnail,
    td.media-icon img[src$=".svg"]{
      width:100% !important;
      height:auto !important;
    }</style>';
}
add_action('admin_head', 'elrancho_svg_media_library_preview_fix');

/* =============================================
   WIDGETS
   ============================================= */
function elrancho_register_widgets() {
    register_sidebar([
        'name'          => __('Sidebar de Tienda', 'elrancho'),
        'id'            => 'shop-sidebar',
        'description'   => __('Widgets para el sidebar de la tienda.', 'elrancho'),
        'before_widget' => '<div class="sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="sidebar-widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Footer Col 1', 'elrancho'),
        'id'            => 'footer-1',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-heading">',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => __('Footer Col 2', 'elrancho'),
        'id'            => 'footer-2',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-heading">',
        'after_title'   => '</h4>',
    ]);

    if (class_exists('Elrancho_Shop_Price_Filter_Widget')) {
        register_widget('Elrancho_Shop_Price_Filter_Widget');
    }
    if (class_exists('Elrancho_Shop_Ingredients_Widget')) {
        register_widget('Elrancho_Shop_Ingredients_Widget');
    }
    if (class_exists('Elrancho_Shop_Free_Shipping_Widget')) {
        register_widget('Elrancho_Shop_Free_Shipping_Widget');
    }
}
add_action('widgets_init', 'elrancho_register_widgets');

/**
 * Helpers para widgets de tienda.
 */
function elrancho_get_shop_price_bounds() {
    global $wpdb;

    $prices = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price, MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
            '_price'
        )
    );

    $min = isset($prices[0]->min_price) ? intval($prices[0]->min_price) : 0;
    $max = isset($prices[0]->max_price) ? intval($prices[0]->max_price) : 100;

    if ($min < 0) {
        $min = 0;
    }
    if ($max <= 0) {
        $max = 100;
    }
    if ($max < $min) {
        $max = $min;
    }

    return [$min, $max];
}

function elrancho_render_price_filter_widget($title = '', $widget_uid = 'default') {
    list($min, $max) = elrancho_get_shop_price_bounds();
    $current_max = isset($_GET['max_price']) ? intval(wp_unslash($_GET['max_price'])) : $max;
    $current_max = max($min, min($current_max, $max));
    $input_id = 'price-range-' . sanitize_html_class($widget_uid);
    ?>
    <div class="elrancho-price-filter-widget">
        <?php if (!empty($title)) : ?>
            <h3 class="sidebar-widget-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                <?php echo esc_html($title); ?>
            </h3>
        <?php endif; ?>
        <div class="price-filter-range">
            <input
                type="range"
                min="<?php echo esc_attr($min); ?>"
                max="<?php echo esc_attr($max); ?>"
                value="<?php echo esc_attr($current_max); ?>"
                step="1"
                id="<?php echo esc_attr($input_id); ?>"
                class="js-price-range"
                aria-label="<?php esc_attr_e('Precio máximo', 'elrancho'); ?>">
        </div>
        <div class="price-filter-labels">
            <span>$<?php echo esc_html($min); ?></span>
            <span class="js-price-range-display">$<?php echo esc_html($current_max); ?>+</span>
        </div>
    </div>
    <?php
}

function elrancho_render_ingredients_widget($title = '', $limit = 8, $manual_slugs = []) {
    $limit = max(1, min(30, intval($limit)));
    $manual_slugs = is_array($manual_slugs) ? array_filter(array_map('sanitize_title', $manual_slugs)) : [];

    if (!empty($manual_slugs)) {
        $tags = [];
        foreach ($manual_slugs as $slug) {
            $tag = get_term_by('slug', $slug, 'product_tag');
            if ($tag instanceof WP_Term) {
                $tags[] = $tag;
            }
        }
    } else {
        $tags = get_terms([
            'taxonomy'   => 'product_tag',
            'hide_empty' => true,
            'number'     => $limit,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);
    }

    $current_tag = '';
    if (is_tax('product_tag')) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $current_tag = $term->slug;
        }
    } elseif (isset($_GET['product_tag'])) {
        $current_tag = sanitize_title(wp_unslash($_GET['product_tag']));
    }
    ?>
    <div class="elrancho-ingredients-widget">
        <?php if (!empty($title)) : ?>
            <h3 class="sidebar-widget-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <?php echo esc_html($title); ?>
            </h3>
        <?php endif; ?>

        <div class="ingredient-tags">
            <?php if (!is_wp_error($tags) && !empty($tags)) : ?>
                <?php foreach ($tags as $tag) : ?>
                    <?php $active = $current_tag === $tag->slug ? ' active' : ''; ?>
                    <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="ingredient-tag<?php echo esc_attr($active); ?>">
                        <?php echo esc_html($tag->name); ?>
                    </a>
                <?php endforeach; ?>
            <?php else : ?>
                <span class="ingredient-tag"><?php esc_html_e('Canela', 'elrancho'); ?></span>
                <span class="ingredient-tag"><?php esc_html_e('Chocolate', 'elrancho'); ?></span>
                <span class="ingredient-tag"><?php esc_html_e('Vainilla', 'elrancho'); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function elrancho_render_free_shipping_widget($title = '', $text = '', $button_text = '', $button_url = '#', $min_amount = 35) {
    $button_url = !empty($button_url) ? $button_url : '#';
    $min_amount = max(0, floatval($min_amount));
    $amount_label = function_exists('wc_price') ? wp_strip_all_tags(wc_price($min_amount)) : ('$' . number_format_i18n($min_amount, 2));

    if (empty($text)) {
        $text = __('En pedidos locales mayores a {{amount}}', 'elrancho');
    }
    $text = str_replace('{{amount}}', $amount_label, $text);
    ?>
    <?php if (!empty($title)) : ?>
        <h3 class="sidebar-widget-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <?php echo esc_html($title); ?>
        </h3>
    <?php endif; ?>
    <?php if (!empty($text)) : ?>
        <p><?php echo esc_html($text); ?></p>
    <?php endif; ?>
    <?php if (!empty($button_text)) : ?>
        <a href="<?php echo esc_url($button_url); ?>" class="btn btn-secondary btn-sm"><?php echo esc_html($button_text); ?></a>
    <?php endif; ?>
    <?php
}

function elrancho_render_shop_sidebar_widgets() {
    if (is_active_sidebar('shop-sidebar')) {
        ob_start();
        dynamic_sidebar('shop-sidebar');
        $widgets_html = ob_get_clean();

        // Limpia wrappers de widgets vacíos para evitar bloques en blanco en el sidebar.
        $widgets_html = preg_replace('/<div class="sidebar-widget[^"]*>\s*(?:<!--.*?-->\s*)*<\/div>/si', '', (string) $widgets_html);
        $widgets_html = preg_replace('/<div class="sidebar-widget[^"]*>\s*<p>\s*<\/p>\s*<\/div>/si', '', (string) $widgets_html);

        echo $widgets_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return;
    }

    echo '<div class="sidebar-widget">';
    elrancho_render_price_filter_widget(__('Rango de Precio', 'elrancho'), 'fallback');
    echo '</div>';

    echo '<div class="sidebar-widget">';
    elrancho_render_ingredients_widget(__('Ingredientes Populares', 'elrancho'), 8);
    echo '</div>';

    echo '<div class="sidebar-widget delivery-widget">';
    elrancho_render_free_shipping_widget(
        __('Envío Gratis', 'elrancho'),
        __('En pedidos locales mayores a {{amount}}', 'elrancho'),
        __('Saber Más', 'elrancho'),
        '#',
        35
    );
    echo '</div>';
}

/**
 * Evita que WooCommerce inyecte "subcategory tiles" dentro del grid de productos.
 * En este tema se muestran categorías en el sidebar, no como tarjeta en el loop.
 */
function elrancho_disable_shop_subcategory_tiles() {
    if (function_exists('remove_filter')) {
        remove_filter('woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories', 10);
    }
}
add_action('init', 'elrancho_disable_shop_subcategory_tiles', 20);

/**
 * Regla de monto mínimo para envío gratis (tomada del widget de tienda).
 */
function elrancho_get_free_shipping_min_amount() {
    $default_amount = 35.0;
    $sidebars = get_option('sidebars_widgets', []);
    $shop_sidebar_widgets = isset($sidebars['shop-sidebar']) && is_array($sidebars['shop-sidebar'])
        ? $sidebars['shop-sidebar']
        : [];

    if (empty($shop_sidebar_widgets)) {
        return $default_amount;
    }

    $instances = get_option('widget_elrancho_shop_free_shipping', []);
    if (!is_array($instances)) {
        return $default_amount;
    }

    foreach ($shop_sidebar_widgets as $widget_id) {
        if (strpos($widget_id, 'elrancho_shop_free_shipping-') !== 0) {
            continue;
        }
        $number = intval(str_replace('elrancho_shop_free_shipping-', '', $widget_id));
        if ($number <= 0 || !isset($instances[$number]) || !is_array($instances[$number])) {
            continue;
        }
        $amount = isset($instances[$number]['min_amount']) ? floatval($instances[$number]['min_amount']) : 0;
        if ($amount > 0) {
            return $amount;
        }
    }

    return $default_amount;
}

/**
 * Aplica envío gratis automáticamente cuando el subtotal supera el monto mínimo.
 */
function elrancho_maybe_apply_free_shipping_threshold($rates, $package) {
    if (empty($rates) || !is_array($rates)) {
        return $rates;
    }

    $threshold = elrancho_get_free_shipping_min_amount();
    if ($threshold <= 0) {
        return $rates;
    }

    $package_subtotal = 0.0;
    if (!empty($package['contents']) && is_array($package['contents'])) {
        foreach ($package['contents'] as $item) {
            $line_total = isset($item['line_total']) ? floatval($item['line_total']) : 0;
            $package_subtotal += $line_total;
        }
    }

    if ($package_subtotal < $threshold) {
        return $rates;
    }

    foreach ($rates as $rate_id => $rate) {
        if (!isset($rate->method_id) || $rate->method_id === 'local_pickup') {
            continue;
        }

        $rates[$rate_id]->cost = 0;
        if (isset($rates[$rate_id]->taxes) && is_array($rates[$rate_id]->taxes)) {
            foreach ($rates[$rate_id]->taxes as $tax_id => $tax_amount) {
                $rates[$rate_id]->taxes[$tax_id] = 0;
            }
        }

        if ($rate->method_id !== 'free_shipping') {
            $rates[$rate_id]->label = __('Envío Gratis', 'elrancho');
        }
    }

    return $rates;
}
add_filter('woocommerce_package_rates', 'elrancho_maybe_apply_free_shipping_threshold', 100, 2);

if (class_exists('WP_Widget') && !class_exists('Elrancho_Shop_Price_Filter_Widget')) {
    class Elrancho_Shop_Price_Filter_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'elrancho_shop_price_filter',
                __('El Rancho: Rango de Precio', 'elrancho'),
                ['description' => __('Filtro de precio para la tienda.', 'elrancho')]
            );
        }

        public function widget($args, $instance) {
            $title = !empty($instance['title']) ? $instance['title'] : __('Rango de Precio', 'elrancho');
            echo $args['before_widget'];
            elrancho_render_price_filter_widget($title, $this->id);
            echo $args['after_widget'];
        }

        public function form($instance) {
            $title = isset($instance['title']) ? $instance['title'] : __('Rango de Precio', 'elrancho');
            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Título:', 'elrancho'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </p>
            <?php
        }

        public function update($new_instance, $old_instance) {
            $instance = [];
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            return $instance;
        }
    }
}

if (class_exists('WP_Widget') && !class_exists('Elrancho_Shop_Ingredients_Widget')) {
    class Elrancho_Shop_Ingredients_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'elrancho_shop_ingredients',
                __('El Rancho: Ingredientes Populares', 'elrancho'),
                ['description' => __('Muestra tags populares de productos.', 'elrancho')]
            );
        }

        public function widget($args, $instance) {
            $title = !empty($instance['title']) ? $instance['title'] : __('Ingredientes Populares', 'elrancho');
            $limit = !empty($instance['limit']) ? intval($instance['limit']) : 8;
            $mode = (!empty($instance['mode']) && $instance['mode'] === 'manual') ? 'manual' : 'auto';
            $manual_tags_raw = !empty($instance['manual_tags']) ? $instance['manual_tags'] : '';
            $manual_slugs = array_filter(array_map('trim', explode(',', (string) $manual_tags_raw)));
            echo $args['before_widget'];
            elrancho_render_ingredients_widget($title, $limit, $mode === 'manual' ? $manual_slugs : []);
            echo $args['after_widget'];
        }

        public function form($instance) {
            $title = isset($instance['title']) ? $instance['title'] : __('Ingredientes Populares', 'elrancho');
            $limit = isset($instance['limit']) ? intval($instance['limit']) : 8;
            $mode = (isset($instance['mode']) && $instance['mode'] === 'manual') ? 'manual' : 'auto';
            $manual_tags = isset($instance['manual_tags']) ? (string) $instance['manual_tags'] : '';
            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Título:', 'elrancho'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('mode')); ?>"><?php esc_html_e('Modo:', 'elrancho'); ?></label>
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('mode')); ?>" name="<?php echo esc_attr($this->get_field_name('mode')); ?>">
                    <option value="auto" <?php selected($mode, 'auto'); ?>><?php esc_html_e('Automático (por popularidad)', 'elrancho'); ?></option>
                    <option value="manual" <?php selected($mode, 'manual'); ?>><?php esc_html_e('Manual (por slugs)', 'elrancho'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php esc_html_e('Cantidad de ingredientes:', 'elrancho'); ?></label>
                <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="30" step="1" value="<?php echo esc_attr($limit); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('manual_tags')); ?>"><?php esc_html_e('Slugs manuales (coma):', 'elrancho'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('manual_tags')); ?>" name="<?php echo esc_attr($this->get_field_name('manual_tags')); ?>" type="text" value="<?php echo esc_attr($manual_tags); ?>" placeholder="chocolate,vainilla,canela">
                <small><?php esc_html_e('Solo se usa en modo manual.', 'elrancho'); ?></small>
            </p>
            <?php
        }

        public function update($new_instance, $old_instance) {
            $instance = [];
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            $instance['limit'] = max(1, min(30, intval($new_instance['limit'] ?? 8)));
            $instance['mode'] = (!empty($new_instance['mode']) && $new_instance['mode'] === 'manual') ? 'manual' : 'auto';
            $instance['manual_tags'] = sanitize_text_field($new_instance['manual_tags'] ?? '');
            return $instance;
        }
    }
}

if (class_exists('WP_Widget') && !class_exists('Elrancho_Shop_Free_Shipping_Widget')) {
    class Elrancho_Shop_Free_Shipping_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'elrancho_shop_free_shipping',
                __('El Rancho: Envío Gratis', 'elrancho'),
                [
                    'description' => __('Bloque promocional de envío gratis.', 'elrancho'),
                    'classname'   => 'delivery-widget',
                ]
            );
        }

        public function widget($args, $instance) {
            $title = !empty($instance['title']) ? $instance['title'] : __('Envío Gratis', 'elrancho');
            $text = !empty($instance['text']) ? $instance['text'] : __('En pedidos locales mayores a {{amount}}', 'elrancho');
            $button_text = !empty($instance['button_text']) ? $instance['button_text'] : __('Saber Más', 'elrancho');
            $button_url = !empty($instance['button_url']) ? $instance['button_url'] : '#';
            $min_amount = !empty($instance['min_amount']) ? floatval($instance['min_amount']) : 35;

            echo $args['before_widget'];
            elrancho_render_free_shipping_widget($title, $text, $button_text, $button_url, $min_amount);
            echo $args['after_widget'];
        }

        public function form($instance) {
            $title = isset($instance['title']) ? $instance['title'] : __('Envío Gratis', 'elrancho');
            $text = isset($instance['text']) ? $instance['text'] : __('En pedidos locales mayores a {{amount}}', 'elrancho');
            $button_text = isset($instance['button_text']) ? $instance['button_text'] : __('Saber Más', 'elrancho');
            $button_url = isset($instance['button_url']) ? $instance['button_url'] : '#';
            $min_amount = isset($instance['min_amount']) ? floatval($instance['min_amount']) : 35;
            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Título:', 'elrancho'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('min_amount')); ?>"><?php esc_html_e('Monto mínimo para envío gratis:', 'elrancho'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('min_amount')); ?>" name="<?php echo esc_attr($this->get_field_name('min_amount')); ?>" type="number" min="0" step="0.01" value="<?php echo esc_attr($min_amount); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('text')); ?>"><?php esc_html_e('Texto:', 'elrancho'); ?></label>
                <textarea class="widefat" rows="3" id="<?php echo esc_attr($this->get_field_id('text')); ?>" name="<?php echo esc_attr($this->get_field_name('text')); ?>"><?php echo esc_textarea($text); ?></textarea>
                <small><?php esc_html_e('Usa {{amount}} para insertar el monto automáticamente.', 'elrancho'); ?></small>
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('button_text')); ?>"><?php esc_html_e('Texto botón:', 'elrancho'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_text')); ?>" name="<?php echo esc_attr($this->get_field_name('button_text')); ?>" type="text" value="<?php echo esc_attr($button_text); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('button_url')); ?>"><?php esc_html_e('URL botón:', 'elrancho'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_url')); ?>" name="<?php echo esc_attr($this->get_field_name('button_url')); ?>" type="text" value="<?php echo esc_attr($button_url); ?>" placeholder="/tienda/">
            </p>
            <?php
        }

        public function update($new_instance, $old_instance) {
            $instance = [];
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            $instance['text'] = sanitize_textarea_field($new_instance['text'] ?? '');
            $instance['button_text'] = sanitize_text_field($new_instance['button_text'] ?? '');
            $instance['min_amount'] = max(0, floatval($new_instance['min_amount'] ?? 35));
            $raw_url = trim((string) ($new_instance['button_url'] ?? ''));
            if ($raw_url === '') {
                $raw_url = '#';
            }
            $instance['button_url'] = (strpos($raw_url, '/') === 0) ? sanitize_text_field($raw_url) : esc_url_raw($raw_url);
            return $instance;
        }
    }
}

/* =============================================
   HERO CAROUSEL (ADMINISTRABLE)
   ============================================= */
function elrancho_register_hero_slides_cpt() {
    $labels = [
        'name'                  => __('Carrusel de Inicio', 'elrancho'),
        'singular_name'         => __('Slide', 'elrancho'),
        'menu_name'             => __('Carrusel Inicio', 'elrancho'),
        'name_admin_bar'        => __('Slide', 'elrancho'),
        'add_new'               => __('Nuevo Slide', 'elrancho'),
        'add_new_item'          => __('Agregar Slide', 'elrancho'),
        'edit_item'             => __('Editar Slide', 'elrancho'),
        'new_item'              => __('Nuevo Slide', 'elrancho'),
        'view_item'             => __('Ver Slide', 'elrancho'),
        'search_items'          => __('Buscar Slides', 'elrancho'),
        'not_found'             => __('No hay slides.', 'elrancho'),
        'not_found_in_trash'    => __('No hay slides en papelera.', 'elrancho'),
        'featured_image'        => __('Imagen del Slide', 'elrancho'),
        'set_featured_image'    => __('Asignar imagen', 'elrancho'),
        'remove_featured_image' => __('Quitar imagen', 'elrancho'),
        'use_featured_image'    => __('Usar esta imagen', 'elrancho'),
    ];

    register_post_type('elrancho_slide', [
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'exclude_from_search' => true,
        'menu_icon'           => 'dashicons-images-alt2',
        'menu_position'       => 25,
        'supports'            => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);
}
add_action('init', 'elrancho_register_hero_slides_cpt');

function elrancho_hero_slide_metabox($post) {
    wp_nonce_field('elrancho_hero_slide_meta', 'elrancho_hero_slide_meta_nonce');

    $badge          = get_post_meta($post->ID, '_elrancho_slide_badge', true);
    $primary_text   = get_post_meta($post->ID, '_elrancho_slide_primary_text', true);
    $primary_url    = get_post_meta($post->ID, '_elrancho_slide_primary_url', true);
    $secondary_text = get_post_meta($post->ID, '_elrancho_slide_secondary_text', true);
    $secondary_url  = get_post_meta($post->ID, '_elrancho_slide_secondary_url', true);

    echo '<p><label for="elrancho_slide_badge"><strong>' . esc_html__('Badge', 'elrancho') . '</strong></label><br>';
    echo '<input type="text" id="elrancho_slide_badge" name="elrancho_slide_badge" value="' . esc_attr($badge) . '" style="width:100%;" placeholder="' . esc_attr__('Ej. Horneado Fresco Diario', 'elrancho') . '"></p>';

    echo '<p><label for="elrancho_slide_primary_text"><strong>' . esc_html__('Texto botón principal', 'elrancho') . '</strong></label><br>';
    echo '<input type="text" id="elrancho_slide_primary_text" name="elrancho_slide_primary_text" value="' . esc_attr($primary_text) . '" style="width:100%;" placeholder="' . esc_attr__('Comprar Ahora', 'elrancho') . '"></p>';

    echo '<p><label for="elrancho_slide_primary_url"><strong>' . esc_html__('URL botón principal', 'elrancho') . '</strong></label><br>';
    echo '<input type="text" id="elrancho_slide_primary_url" name="elrancho_slide_primary_url" value="' . esc_attr($primary_url) . '" style="width:100%;" placeholder="/tienda/"></p>';

    echo '<p><label for="elrancho_slide_secondary_text"><strong>' . esc_html__('Texto botón secundario', 'elrancho') . '</strong></label><br>';
    echo '<input type="text" id="elrancho_slide_secondary_text" name="elrancho_slide_secondary_text" value="' . esc_attr($secondary_text) . '" style="width:100%;" placeholder="' . esc_attr__('Ver Menú', 'elrancho') . '"></p>';

    echo '<p><label for="elrancho_slide_secondary_url"><strong>' . esc_html__('URL botón secundario', 'elrancho') . '</strong></label><br>';
    echo '<input type="text" id="elrancho_slide_secondary_url" name="elrancho_slide_secondary_url" value="' . esc_attr($secondary_url) . '" style="width:100%;" placeholder="/tienda/"></p>';

    echo '<p class="description">' . esc_html__('Usa la imagen destacada como fondo del slide. El título del slide será el encabezado principal y el contenido será la descripción.', 'elrancho') . '</p>';
}

function elrancho_register_hero_slide_metabox() {
    add_meta_box(
        'elrancho-hero-slide-meta',
        __('Opciones del Slide', 'elrancho'),
        'elrancho_hero_slide_metabox',
        'elrancho_slide',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'elrancho_register_hero_slide_metabox');

function elrancho_save_hero_slide_meta($post_id) {
    if (!isset($_POST['elrancho_hero_slide_meta_nonce']) || !wp_verify_nonce($_POST['elrancho_hero_slide_meta_nonce'], 'elrancho_hero_slide_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, '_elrancho_slide_badge', sanitize_text_field($_POST['elrancho_slide_badge'] ?? ''));
    update_post_meta($post_id, '_elrancho_slide_primary_text', sanitize_text_field($_POST['elrancho_slide_primary_text'] ?? ''));
    update_post_meta($post_id, '_elrancho_slide_primary_url', elrancho_sanitize_slide_url($_POST['elrancho_slide_primary_url'] ?? ''));
    update_post_meta($post_id, '_elrancho_slide_secondary_text', sanitize_text_field($_POST['elrancho_slide_secondary_text'] ?? ''));
    update_post_meta($post_id, '_elrancho_slide_secondary_url', elrancho_sanitize_slide_url($_POST['elrancho_slide_secondary_url'] ?? ''));
}
add_action('save_post_elrancho_slide', 'elrancho_save_hero_slide_meta');

function elrancho_sanitize_slide_url($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    // Permite rutas relativas amigables, por ejemplo /tienda/
    if (strpos($value, '/') === 0) {
        return esc_url_raw(home_url($value));
    }

    return esc_url_raw($value);
}

/* =============================================
   ESTILOS Y SCRIPTS
   ============================================= */
function elrancho_enqueue_scripts() {
    $wc_ajax_url = home_url('/?wc-ajax=%%endpoint%%');
    $style_file  = ELRANCHO_DIR . '/style.css';
    $main_js     = ELRANCHO_DIR . '/assets/js/main.js';
    $style_ver   = file_exists($style_file) ? filemtime($style_file) : ELRANCHO_VERSION;
    $script_ver  = file_exists($main_js) ? filemtime($main_js) : ELRANCHO_VERSION;

    if (class_exists('WC_AJAX')) {
        $candidate = WC_AJAX::get_endpoint('%%endpoint%%');
        // Algunos entornos devuelven admin-ajax.php; forzamos wc-ajax para add-to-cart.
        if (!empty($candidate) && strpos($candidate, 'admin-ajax.php') === false) {
            $wc_ajax_url = $candidate;
        }
    }

    // Google Fonts
    wp_enqueue_style(
        'elrancho-fonts',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
        [],
        null
    );

    // Hoja de estilos principal
    wp_enqueue_style(
        'elrancho-style',
        get_stylesheet_uri(),
        ['elrancho-fonts'],
        $style_ver
    );

    // Script principal
    wp_enqueue_script(
        'elrancho-main',
        ELRANCHO_URI . '/assets/js/main.js',
        ['jquery'],
        $script_ver,
        true
    );

    // Variables para JS
    wp_localize_script('elrancho-main', 'elRancho', [
        'ajaxUrl'    => admin_url('admin-ajax.php'),
        'wcAjaxUrl'  => $wc_ajax_url, // URL correcta: ?wc-ajax=%%endpoint%%
        'homeUrl'    => home_url('/'),
        'nonce'      => wp_create_nonce('elrancho_nonce'),
        'addToCartNonce' => wp_create_nonce('woocommerce-process_checkout'), // nonce de WC
        'cartUrl'    => wc_get_cart_url(),
        'shopUrl'    => get_permalink(wc_get_page_id('shop')),
        'i18n'       => [
            'addedToCart'    => __('¡Agregado al carrito!', 'elrancho'),
            'viewCart'       => __('Ver carrito', 'elrancho'),
            'loading'        => __('Cargando...', 'elrancho'),
        ],
    ]);

    // Comment reply
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'elrancho_enqueue_scripts');

/* =============================================
   WOOCOMMERCE - HOOKS Y PERSONALIZACIONES
   ============================================= */

// Remover breadcrumbs por defecto (usamos los propios)
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

// Remover el sidebar por defecto de WC
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// Número de productos por página
add_filter('loop_shop_per_page', function() { return 12; }, 20);

// Columnas de productos en el loop — forzamos 4 para que WC no inyecte CSS de float
add_filter('loop_shop_columns', function() { return 4; });

// Deshabilitar el CSS inline de columnas que WooCommerce inyecta vía JS/PHP
// (ese CSS usa float y clear que rompen nuestro CSS Grid)
add_filter('woocommerce_enqueue_styles', function($styles) {
    // Desactivar solo el CSS de columnas del loop, no el resto
    return $styles;
});

// Anular las clases first/last que WC agrega con clear:both
add_filter('woocommerce_post_class', function($classes) {
    // Eliminar las clases que WooCommerce usa para aplicar clear:left
    $classes = array_diff($classes, ['first', 'last']);
    return $classes;
}, 20);

// Remover título de la página de tienda (lo ponemos en la plantilla)
add_filter('woocommerce_show_page_title', '__return_false');

// Reordenar resumen del producto
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

add_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 8);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

// Registrar fragmento del carrito para el header (contador del ícono)
// Esto evita los errores 400 en admin-ajax.php al cargar la página
add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

    // Actualizar el span del contador en el header
    $fragments['span.header-cart-count'] =
        '<span class="header-cart-count" aria-live="polite">'
        . ($count > 0 ? intval($count) : '')
        . '</span>';

    return $fragments;
});

// Revisar si el producto es nuevo (últimos 30 días)
function elrancho_is_new_product($product) {
    $post_date = get_the_date('U', $product->get_id());
    $diff = (time() - $post_date) / 86400;
    return $diff < 30;
}

// Los badges se manejan directamente en content-product.php (nuestro template override)
// No se necesita hook aquí — evita el cuadro vacío en el grid

// Botón "Añadir al carrito" con clase personalizada
add_filter('woocommerce_loop_add_to_cart_link', function($link, $product) {
    return str_replace('class="button', 'class="btn btn-primary button', $link);
}, 10, 2);

// Breadcrumbs personalizados
function elrancho_breadcrumbs() {
    if (function_exists('woocommerce_breadcrumb')) {
        woocommerce_breadcrumb([
            'delimiter'   => ' <span class="breadcrumb-sep">/</span> ',
            'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="Breadcrumb">',
            'wrap_after'  => '</nav>',
        ]);
    }
}

/* =============================================
   PERSONALIZACIÓN DE CHECKOUT
   ============================================= */
function elrancho_is_checkout_flow_page() {
    if (!function_exists('is_checkout') || !is_checkout()) {
        return false;
    }

    if (function_exists('is_order_received_page') && is_order_received_page()) {
        return false;
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay')) {
        return false;
    }

    return true;
}

function elrancho_get_checkout_flow_intro_markup() {
    if (!elrancho_is_checkout_flow_page()) {
        return '';
    }

    $show_login_prompt = !is_user_logged_in();
    $login_url = function_exists('wc_get_checkout_url')
        ? wp_login_url(wc_get_checkout_url())
        : wp_login_url(home_url('/'));

    ob_start();
    ?>
    <section class="elr-checkout-flow-intro" aria-label="<?php esc_attr_e('Progreso de checkout', 'elrancho'); ?>">
        <ol class="elr-checkout-stepper">
            <li class="is-active">
                <span class="step-index">1</span>
                <span class="step-label"><?php esc_html_e('Details', 'elrancho'); ?></span>
            </li>
            <li>
                <span class="step-index">2</span>
                <span class="step-label"><?php esc_html_e('Fulfillment', 'elrancho'); ?></span>
            </li>
            <li>
                <span class="step-index">3</span>
                <span class="step-label"><?php esc_html_e('Payment', 'elrancho'); ?></span>
            </li>
        </ol>

        <?php if ($show_login_prompt) : ?>
            <div class="elr-checkout-login-card">
                <div class="elr-checkout-login-copy">
                    <span class="elr-checkout-login-icon" aria-hidden="true">◉</span>
                    <span><?php esc_html_e('Already have an account?', 'elrancho'); ?></span>
                </div>
                <a href="<?php echo esc_url($login_url); ?>" class="elr-checkout-login-link">
                    <?php esc_html_e('Login for faster checkout', 'elrancho'); ?>
                </a>
            </div>
        <?php endif; ?>
    </section>
    <?php

    return (string) ob_get_clean();
}

function elrancho_render_checkout_flow_intro() {
    echo elrancho_get_checkout_flow_intro_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action('woocommerce_before_checkout_form', 'elrancho_render_checkout_flow_intro', 5);

// Cambiar textos
add_filter('woocommerce_checkout_fields', function($fields) {
    $fields['billing']['billing_first_name']['placeholder'] = 'Juan';
    $fields['billing']['billing_last_name']['placeholder'] = 'García';
    $fields['billing']['billing_email']['placeholder'] = 'juan@email.com';
    $fields['billing']['billing_phone']['placeholder'] = '+52 555 000 0000';
    return $fields;
});

/* =============================================
   LOYALTY MODULE — RANCHO REWARDS
   Versión 2.0 — Sistema completo con tiers,
   referidos, retos, redención y REST API.
   ============================================= */

/* --------------------------------------------------
   1. INSTALACIÓN / ACTIVACIÓN — Tablas y defaults
   -------------------------------------------------- */
function erbl_install() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $t_tx    = $wpdb->prefix . 'erbl_transactions';
    $t_ch    = $wpdb->prefix . 'erbl_challenges';
    $t_cp    = $wpdb->prefix . 'erbl_challenge_progress';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( "CREATE TABLE $t_tx (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id     BIGINT UNSIGNED NOT NULL,
        delta       INT             NOT NULL,
        balance     INT UNSIGNED    NOT NULL DEFAULT 0,
        type        VARCHAR(40)     NOT NULL DEFAULT 'order',
        ref_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
        note        VARCHAR(255)    NOT NULL DEFAULT '',
        created_at  DATETIME        NOT NULL,
        PRIMARY KEY (id),
        KEY user_id    (user_id),
        KEY type       (type),
        KEY created_at (created_at)
    ) $charset;" );

    dbDelta( "CREATE TABLE $t_ch (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        title       VARCHAR(120)    NOT NULL DEFAULT '',
        description VARCHAR(255)    NOT NULL DEFAULT '',
        type        VARCHAR(40)     NOT NULL DEFAULT 'orders_count',
        target      INT UNSIGNED    NOT NULL DEFAULT 1,
        bonus_pts   INT UNSIGNED    NOT NULL DEFAULT 100,
        tier_req    VARCHAR(20)     NOT NULL DEFAULT 'bronze',
        active      TINYINT(1)      NOT NULL DEFAULT 1,
        expires_at  DATETIME                 DEFAULT NULL,
        created_at  DATETIME        NOT NULL,
        PRIMARY KEY (id)
    ) $charset;" );

    dbDelta( "CREATE TABLE $t_cp (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id      BIGINT UNSIGNED NOT NULL,
        challenge_id BIGINT UNSIGNED NOT NULL,
        progress     INT UNSIGNED    NOT NULL DEFAULT 0,
        completed    TINYINT(1)      NOT NULL DEFAULT 0,
        completed_at DATETIME                 DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY user_challenge (user_id, challenge_id)
    ) $charset;" );

    update_option( 'erbl_db_version', '2.0' );

    // Insertar retos de ejemplo si la tabla está vacía
    $has = $wpdb->get_var( "SELECT COUNT(*) FROM $t_ch" );
    if ( ! $has ) {
        $now = current_time('mysql');
        $wpdb->insert( $t_ch, [ 'title' => 'Racha semanal',    'description' => 'Compra 4 semanas seguidas',              'type' => 'streak_weeks',    'target' => 4,  'bonus_pts' => 300, 'tier_req' => 'bronze', 'active' => 1, 'created_at' => $now ] );
        $wpdb->insert( $t_ch, [ 'title' => 'Explorador',       'description' => 'Prueba 3 categorías distintas en un mes','type' => 'categories_month','target' => 3,  'bonus_pts' => 200, 'tier_req' => 'bronze', 'active' => 1, 'created_at' => $now ] );
        $wpdb->insert( $t_ch, [ 'title' => 'Ticket grande',    'description' => 'Haz una compra mayor a $35 USD',         'type' => 'single_order_min','target' => 35, 'bonus_pts' => 150, 'tier_req' => 'bronze', 'active' => 1, 'created_at' => $now ] );
        $wpdb->insert( $t_ch, [ 'title' => 'Reto Oro — lunes', 'description' => 'Compra todos los lunes del mes',         'type' => 'mondays_month',  'target' => 4,  'bonus_pts' => 500, 'tier_req' => 'gold',   'active' => 1, 'created_at' => $now ] );
    }
}
add_action( 'after_switch_theme', 'erbl_install' );
add_action( 'after_switch_theme', function() {
    erbl_register_account_endpoint();
    flush_rewrite_rules();
} );
add_action( 'init', function() {
    if ( get_option('erbl_db_version') !== '2.0' ) {
        erbl_install();
    }
});

/* --------------------------------------------------
   2. CONFIGURACIÓN
   -------------------------------------------------- */
function elrancho_loyalty_default_settings() {
    return [
        // ── Básico ──────────────────────────────────
        'enabled'              => 'yes',
        'points_rate'          => 10,      // pts por $1 USD
        'point_value'          => 0.01,    // 100 pts = $1 USD
        'points_base'          => 'items', // 'items' | 'total'
        'points_rounding'      => 'floor',
        'max_points_per_order' => 0,
        'min_order_total'      => 0,
        'award_status'         => 'completed',
        // ── Redención ───────────────────────────────
        'redeem_enabled'       => 'yes',
        'redeem_minimum'       => 500,     // pts mínimos para redimir
        'redeem_step'          => 100,     // incremento de redención
        'redeem_max_pct'       => 50,      // % máximo del total de la orden
        // ── Bonos de eventos ────────────────────────
        'bonus_registration'   => 200,     // pts al registrarse
        'bonus_birthday_mult'  => 2,       // multiplicador semana de cumpleaños
        'bonus_referrer'       => 500,     // pts al referidor
        'bonus_referred'       => 300,     // pts al nuevo cliente
        // ── Tiers ───────────────────────────────────
        'tier_silver_spend'    => 500,     // USD acumulados para Silver
        'tier_gold_spend'      => 1200,    // USD acumulados para Gold
        'tier_silver_mult'     => 1.25,
        'tier_gold_mult'       => 1.5,
        // ── Multiplicadores de categoría ─────────────
        'cat_mult_cakes'       => 1.5,     // slug de cat custom cakes
        'cat_mult_cakes_slug'  => 'custom-cakes',
        // ── Caducidad ───────────────────────────────
        'expiry_months'        => 12,      // meses sin actividad
    ];
}

/* --------------------------------------------------
   3. SETTINGS & SANITIZACIÓN
   -------------------------------------------------- */
function elrancho_loyalty_get_settings() {
    $saved = get_option( 'elrancho_loyalty_settings', [] );
    if ( ! is_array( $saved ) ) { $saved = []; }
    return wp_parse_args( $saved, elrancho_loyalty_default_settings() );
}

function elrancho_loyalty_sanitize_settings( $input ) {
    $d = elrancho_loyalty_default_settings();
    $input = is_array( $input ) ? $input : [];

    $award = sanitize_key( $input['award_status'] ?? $d['award_status'] );
    if ( ! in_array( $award, [ 'processing', 'completed' ], true ) ) { $award = $d['award_status']; }

    $base = sanitize_key( $input['points_base'] ?? $d['points_base'] );
    if ( ! in_array( $base, [ 'items', 'total' ], true ) ) { $base = $d['points_base']; }

    $round = sanitize_key( $input['points_rounding'] ?? $d['points_rounding'] );
    if ( ! in_array( $round, [ 'floor', 'round', 'ceil' ], true ) ) { $round = $d['points_rounding']; }

    return [
        'enabled'              => ! empty( $input['enabled'] ) ? 'yes' : 'no',
        'points_rate'          => max( 0, floatval( $input['points_rate']          ?? $d['points_rate'] ) ),
        'point_value'          => max( 0, floatval( $input['point_value']          ?? $d['point_value'] ) ),
        'points_base'          => $base,
        'points_rounding'      => $round,
        'max_points_per_order' => max( 0, intval( $input['max_points_per_order']   ?? $d['max_points_per_order'] ) ),
        'min_order_total'      => max( 0, floatval( $input['min_order_total']      ?? $d['min_order_total'] ) ),
        'award_status'         => $award,
        'redeem_enabled'       => ! empty( $input['redeem_enabled'] ) ? 'yes' : 'no',
        'redeem_minimum'       => max( 0, intval( $input['redeem_minimum']         ?? $d['redeem_minimum'] ) ),
        'redeem_step'          => max( 1, intval( $input['redeem_step']            ?? $d['redeem_step'] ) ),
        'redeem_max_pct'       => min( 100, max( 0, intval( $input['redeem_max_pct'] ?? $d['redeem_max_pct'] ) ) ),
        'bonus_registration'   => max( 0, intval( $input['bonus_registration']     ?? $d['bonus_registration'] ) ),
        'bonus_birthday_mult'  => max( 1, floatval( $input['bonus_birthday_mult']  ?? $d['bonus_birthday_mult'] ) ),
        'bonus_referrer'       => max( 0, intval( $input['bonus_referrer']         ?? $d['bonus_referrer'] ) ),
        'bonus_referred'       => max( 0, intval( $input['bonus_referred']         ?? $d['bonus_referred'] ) ),
        'tier_silver_spend'    => max( 0, floatval( $input['tier_silver_spend']    ?? $d['tier_silver_spend'] ) ),
        'tier_gold_spend'      => max( 0, floatval( $input['tier_gold_spend']      ?? $d['tier_gold_spend'] ) ),
        'tier_silver_mult'     => max( 1, floatval( $input['tier_silver_mult']     ?? $d['tier_silver_mult'] ) ),
        'tier_gold_mult'       => max( 1, floatval( $input['tier_gold_mult']       ?? $d['tier_gold_mult'] ) ),
        'cat_mult_cakes'       => max( 1, floatval( $input['cat_mult_cakes']       ?? $d['cat_mult_cakes'] ) ),
        'cat_mult_cakes_slug'  => sanitize_key( $input['cat_mult_cakes_slug']      ?? $d['cat_mult_cakes_slug'] ),
        'expiry_months'        => max( 0, intval( $input['expiry_months']          ?? $d['expiry_months'] ) ),
    ];
}

function elrancho_loyalty_register_settings() {
    register_setting( 'elrancho_loyalty_group', 'elrancho_loyalty_settings', [
        'type'              => 'array',
        'sanitize_callback' => 'elrancho_loyalty_sanitize_settings',
        'default'           => elrancho_loyalty_default_settings(),
    ] );
}
add_action( 'admin_init', 'elrancho_loyalty_register_settings' );

/* --------------------------------------------------
   5. FUNCIONES CORE DE PUNTOS Y TIERS
   -------------------------------------------------- */
function erbl_get_user_points( $user_id ) {
    return max( 0, intval( get_user_meta( $user_id, '_erbl_points', true ) ) );
}

/** Compatibilidad con el sistema anterior */
function elrancho_loyalty_get_user_points( $user_id ) {
    $old = get_user_meta( $user_id, '_elrancho_loyalty_points', true );
    if ( $old !== '' && $old !== false ) {
        $new = erbl_get_user_points( $user_id );
        if ( $new === 0 ) {
            update_user_meta( $user_id, '_erbl_points', max( 0, intval($old) ) );
        }
        delete_user_meta( $user_id, '_elrancho_loyalty_points' );
    }
    return erbl_get_user_points( $user_id );
}

function erbl_get_user_total_spend( $user_id ) {
    $spend = get_user_meta( $user_id, '_erbl_total_spend', true );
    return $spend !== '' ? (float) $spend : 0.0;
}

function erbl_get_user_tier( $user_id ) {
    $settings = elrancho_loyalty_get_settings();
    $spend    = erbl_get_user_total_spend( $user_id );
    if ( $spend >= floatval( $settings['tier_gold_spend'] ) )   { return 'gold'; }
    if ( $spend >= floatval( $settings['tier_silver_spend'] ) ) { return 'silver'; }
    return 'bronze';
}

function erbl_tier_label( $tier ) {
    return [ 'bronze' => '🥉 Bronce', 'silver' => '🥈 Plata', 'gold' => '🥇 Oro' ][ $tier ] ?? '🥉 Bronce';
}

function erbl_adjust_points( $user_id, $delta, $type = 'manual', $ref_id = 0, $note = '' ) {
    global $wpdb;
    $user_id = intval( $user_id );
    $delta   = intval( $delta );
    if ( ! $user_id ) { return false; }
    $current = erbl_get_user_points( $user_id );
    $balance = max( 0, $current + $delta );
    update_user_meta( $user_id, '_erbl_points', $balance );
    $wpdb->insert(
        $wpdb->prefix . 'erbl_transactions',
        [ 'user_id' => $user_id, 'delta' => $delta, 'balance' => $balance, 'type' => sanitize_key( $type ), 'ref_id' => intval( $ref_id ), 'note' => sanitize_text_field( $note ), 'created_at' => current_time('mysql') ],
        [ '%d', '%d', '%d', '%s', '%d', '%s', '%s' ]
    );
    return $balance;
}

function erbl_get_user_multiplier( $user_id ) {
    $settings = elrancho_loyalty_get_settings();
    $tier     = erbl_get_user_tier( $user_id );
    $mult     = 1.0;
    if ( $tier === 'silver' ) { $mult = floatval( $settings['tier_silver_mult'] ); }
    if ( $tier === 'gold' )   { $mult = floatval( $settings['tier_gold_mult'] ); }
    $bday = get_user_meta( $user_id, '_erbl_birthday', true );
    if ( $bday ) {
        $bday_this_year = date('Y') . '-' . date('m-d', strtotime($bday));
        $today          = strtotime('today');
        $bday_ts        = strtotime($bday_this_year);
        if ( $bday_ts && abs( $today - $bday_ts ) <= ( 3 * DAY_IN_SECONDS ) ) {
            $mult *= floatval( $settings['bonus_birthday_mult'] );
        }
    }
    return $mult;
}

function elrancho_loyalty_calculate_order_points( $order, $settings = null ) {
    if ( ! $order instanceof WC_Order ) { return 0; }
    $settings = is_array( $settings ) ? $settings : elrancho_loyalty_get_settings();
    if ( $settings['enabled'] !== 'yes' ) { return 0; }
    if ( $settings['points_base'] === 'total' ) {
        $base = floatval( $order->get_total() );
    } else {
        $base = floatval( $order->get_total() ) - floatval( $order->get_shipping_total() ) - floatval( $order->get_shipping_tax() );
    }
    $base = max( 0, $base );
    if ( $base < floatval( $settings['min_order_total'] ) ) { return 0; }
    $cat_mult = 1.0;
    $cake_slug = $settings['cat_mult_cakes_slug'];
    if ( $cake_slug ) {
        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();
            if ( $product && has_term( $cake_slug, 'product_cat', $product->get_id() ) ) {
                $cat_mult = floatval( $settings['cat_mult_cakes'] );
                break;
            }
        }
    }
    $user_mult = erbl_get_user_multiplier( intval( $order->get_user_id() ) );
    $raw    = $base * floatval( $settings['points_rate'] ) * $user_mult * $cat_mult;
    $points = match ( $settings['points_rounding'] ) {
        'ceil'  => intval( ceil($raw) ),
        'round' => intval( round($raw) ),
        default => intval( floor($raw) ),
    };
    $points = max( 0, $points );
    $max    = intval( $settings['max_points_per_order'] );
    if ( $max > 0 ) { $points = min( $points, $max ); }
    return $points;
}

/* --------------------------------------------------
   6. HOOKS DE ÓRDENES
   -------------------------------------------------- */
function elrancho_loyalty_maybe_award_points( $order_id ) {
    $settings = elrancho_loyalty_get_settings();
    if ( $settings['enabled'] !== 'yes' ) { return; }
    $order = wc_get_order( $order_id );
    if ( ! $order instanceof WC_Order ) { return; }
    if ( $settings['award_status'] !== $order->get_status() ) { return; }
    $user_id = intval( $order->get_user_id() );
    if ( $user_id <= 0 ) { return; }
    if ( intval( $order->get_meta('_erbl_pts_awarded') ) > 0 ) { return; }
    $points = elrancho_loyalty_calculate_order_points( $order, $settings );
    $prev_spend  = erbl_get_user_total_spend( $user_id );
    $order_spend = floatval( $order->get_total() ) - floatval( $order->get_shipping_total() );
    update_user_meta( $user_id, '_erbl_total_spend', $prev_spend + max( 0, $order_spend ) );
    if ( $points <= 0 ) {
        $order->update_meta_data( '_erbl_pts_awarded', 0 );
        $order->save();
        return;
    }
    erbl_adjust_points( $user_id, $points, 'order', $order->get_id(), sprintf( 'Pedido #%d', $order->get_id() ) );
    erbl_check_challenges_on_order( $user_id, $order );
    erbl_maybe_award_referral_bonus( $user_id, $order->get_id(), $settings );
    $order->update_meta_data( '_erbl_pts_awarded', $points );
    $order->update_meta_data( '_erbl_pts_reversed', 0 );
    $order->save();
    $order->add_order_note( sprintf( 'Rancho Rewards: +%d pts acreditados.', $points ) );
    $user       = get_userdata( $user_id );
    $new_balance = erbl_get_user_points( $user_id );
    $new_tier    = erbl_tier_label( erbl_get_user_tier( $user_id ) );
    $pts_url     = wc_get_account_endpoint_url('my-points');
    if ( $user && $user->user_email ) {
        $subject = sprintf( '🎉 Ganaste %d puntos en %s', $points, get_bloginfo('name') );
        $body    = '<html><body style="font-family:sans-serif;color:#4A3B32;">'
            . '<h2 style="color:#b81417;">¡Ganaste ' . number_format($points) . ' puntos!</h2>'
            . '<p>Tu saldo actual: <strong>' . number_format($new_balance) . ' puntos</strong></p>'
            . '<p>Tu nivel: <strong>' . esc_html($new_tier) . '</strong></p>'
            . '<p><a href="' . esc_url($pts_url) . '" style="background:#b81417;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;">Ver mis puntos</a></p>'
            . '</body></html>';
        wp_mail( $user->user_email, $subject, $body, ['Content-Type: text/html; charset=UTF-8'] );
    }
}
add_action( 'woocommerce_order_status_processing', 'elrancho_loyalty_maybe_award_points' );
add_action( 'woocommerce_order_status_completed',  'elrancho_loyalty_maybe_award_points' );

function elrancho_loyalty_maybe_revoke_points( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order instanceof WC_Order ) { return; }
    $user_id  = intval( $order->get_user_id() );
    if ( $user_id <= 0 ) { return; }
    $awarded  = intval( $order->get_meta('_erbl_pts_awarded') );
    $reversed = intval( $order->get_meta('_erbl_pts_reversed') );
    if ( $awarded <= 0 || $reversed === 1 ) { return; }
    erbl_adjust_points( $user_id, -$awarded, 'reversal', $order_id, sprintf( 'Reversa pedido #%d', $order_id ) );
    $order->update_meta_data( '_erbl_pts_reversed', 1 );
    $order->save();
    $order->add_order_note( sprintf( 'Rancho Rewards: -%d pts revertidos.', $awarded ) );
}
add_action( 'woocommerce_order_status_refunded',  'elrancho_loyalty_maybe_revoke_points' );
add_action( 'woocommerce_order_status_cancelled', 'elrancho_loyalty_maybe_revoke_points' );
add_action( 'woocommerce_order_status_failed',    'elrancho_loyalty_maybe_revoke_points' );

/* --------------------------------------------------
   7. BONO DE REGISTRO
   -------------------------------------------------- */
add_action( 'user_register', function( $user_id ) {
    $settings = elrancho_loyalty_get_settings();
    if ( $settings['enabled'] !== 'yes' ) { return; }
    $code = strtoupper( substr( md5( $user_id . wp_generate_password(8, false) ), 0, 8 ) );
    update_user_meta( $user_id, '_erbl_referral_code', $code );
    $bonus = intval( $settings['bonus_registration'] );
    if ( $bonus > 0 ) {
        erbl_adjust_points( $user_id, $bonus, 'registration', $user_id, 'Bono de bienvenida' );
    }
} );

/* --------------------------------------------------
   8. REFERIDOS
   -------------------------------------------------- */
function erbl_maybe_award_referral_bonus( $user_id, $order_id, $settings ) {
    $orders = wc_get_orders( [ 'customer' => $user_id, 'status' => [ 'completed', 'processing' ], 'limit' => 2 ] );
    if ( count($orders) > 1 ) { return; }
    $ref_code = sanitize_text_field( get_user_meta( $user_id, '_erbl_used_referral_code', true ) );
    if ( ! $ref_code ) { return; }
    $referrer_id = erbl_get_user_by_referral_code( $ref_code );
    if ( ! $referrer_id || $referrer_id === $user_id ) { return; }
    if ( get_user_meta( $user_id, '_erbl_referral_bonus_given', true ) ) { return; }
    erbl_adjust_points( $referrer_id, intval( $settings['bonus_referrer'] ), 'referral_bonus', $user_id,     'Bono por referido exitoso' );
    erbl_adjust_points( $user_id,     intval( $settings['bonus_referred'] ), 'referral_bonus', $referrer_id, 'Bono por usar código de referido' );
    update_user_meta( $user_id, '_erbl_referral_bonus_given', 1 );
    $referrer_data  = get_userdata($referrer_id);
    $referred_data  = get_userdata($user_id);
    $referred_name  = $referred_data ? $referred_data->display_name : 'Un amigo';
    $ref_new_bal    = erbl_get_user_points($referrer_id);
    $pts_url        = wc_get_account_endpoint_url('my-points');
    if ($referrer_data && $referrer_data->user_email) {
        $subject = sprintf('¡Tu amigo %s hizo su primera compra! +%d pts', $referred_name, intval($settings['bonus_referrer']));
        $body    = '<html><body style="font-family:sans-serif;color:#4A3B32;">'
            . '<h2 style="color:#b81417;">¡Referido exitoso! 🎉</h2>'
            . '<p>' . esc_html($referred_name) . ' hizo su primera compra y tú ganaste <strong>' . number_format(intval($settings['bonus_referrer'])) . ' puntos</strong>.</p>'
            . '<p>Tu saldo actual: <strong>' . number_format($ref_new_bal) . ' puntos</strong></p>'
            . '<p><a href="' . esc_url($pts_url) . '" style="background:#b81417;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;">Ver mis puntos</a></p>'
            . '</body></html>';
        wp_mail($referrer_data->user_email, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
    }
    update_user_meta( $user_id, '_erbl_referred_by', $referrer_id );
}

function erbl_get_user_by_referral_code( $code ) {
    global $wpdb;
    $id = $wpdb->get_var( $wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='_erbl_referral_code' AND meta_value=%s LIMIT 1",
        strtoupper( sanitize_text_field( $code ) )
    ) );
    return $id ? intval($id) : 0;
}

add_action( 'user_register', function( $user_id ) {
    $code = sanitize_text_field( $_COOKIE['erbl_ref'] ?? ( $_GET['ref'] ?? '' ) );
    if ( $code ) {
        update_user_meta( $user_id, '_erbl_used_referral_code', strtoupper($code) );
    }
}, 20 );

add_action( 'init', function() {
    if ( isset($_GET['ref']) && ! is_user_logged_in() ) {
        $code = strtoupper( sanitize_text_field( $_GET['ref'] ) );
        if ( $code && ! isset($_COOKIE['erbl_ref']) ) {
            setcookie( 'erbl_ref', $code, time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
        }
    }
} );

/* --------------------------------------------------
   9. REDENCIÓN EN CHECKOUT
   -------------------------------------------------- */
add_action( 'woocommerce_before_order_notes', 'erbl_checkout_redeem_section' );
function erbl_checkout_redeem_section( $checkout ) {
    if ( ! is_user_logged_in() ) { return; }
    $settings = elrancho_loyalty_get_settings();
    if ( $settings['enabled'] !== 'yes' || $settings['redeem_enabled'] !== 'yes' ) { return; }
    $user_id  = get_current_user_id();
    $points   = erbl_get_user_points( $user_id );
    $minimum  = intval( $settings['redeem_minimum'] );
    if ( $points < $minimum ) { return; }
    $max_pct    = intval( $settings['redeem_max_pct'] );
    $total      = WC()->cart->get_subtotal();
    $point_val  = floatval( $settings['point_value'] );
    $step       = intval( $settings['redeem_step'] );
    $max_redeem = min( $points, (int) floor( $total * ( $max_pct / 100 ) / $point_val / $step ) * $step );
    $currently  = min( intval( WC()->session->get('erbl_redeem_points', 0) ), $max_redeem );
    ?>
    <div class="erbl-redeem-section" style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:8px;padding:16px 20px;margin:0 0 20px;">
        <h3 style="margin:0 0 8px;font-size:15px;display:flex;align-items:center;gap:6px;">
            🎁 Usar Rancho Rewards
            <span style="font-size:13px;font-weight:400;color:#7D6B60;">— Tienes <strong><?php echo number_format($points); ?> pts</strong> (~$<?php echo number_format($points * $point_val, 2); ?> USD)</span>
        </h3>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <label style="font-size:13px;color:#4A3B32;">Puntos a usar:</label>
            <input type="number" id="erbl_redeem_input" name="erbl_redeem_points"
                   min="0" max="<?php echo esc_attr($max_redeem); ?>" step="<?php echo esc_attr($step); ?>"
                   value="<?php echo esc_attr($currently); ?>"
                   style="width:100px;padding:6px 10px;border:1px solid #ccc;border-radius:6px;"
                   oninput="erblUpdateRedeem(this.value)">
            <span id="erbl_redeem_value" style="font-size:13px;color:#4A3B32;">= $<?php echo number_format($currently * $point_val, 2); ?> de descuento</span>
            <button type="button" onclick="erblApplyRedeem()" class="button" style="font-size:13px;padding:6px 14px;">Aplicar</button>
            <?php if ($currently > 0) : ?><button type="button" onclick="erblClearRedeem()" style="font-size:12px;color:#c0392b;background:none;border:none;cursor:pointer;text-decoration:underline;">Quitar</button><?php endif; ?>
        </div>
        <p style="font-size:11px;color:#7D6B60;margin:8px 0 0;">Máximo <?php echo $max_pct; ?>% del total del pedido.</p>
    </div>
    <script>
    const erblPtVal = <?php echo floatval($point_val); ?>;
    const erblStep  = <?php echo intval($step); ?>;
    const erblMax   = <?php echo intval($max_redeem); ?>;
    function erblUpdateRedeem(v) {
        v = Math.min(erblMax, Math.max(0, Math.round(parseFloat(v)/erblStep)*erblStep));
        document.getElementById('erbl_redeem_input').value = v;
        document.getElementById('erbl_redeem_value').textContent = '= $' + (v * erblPtVal).toFixed(2) + ' de descuento';
    }
    function erblApplyRedeem() {
        const v = parseInt(document.getElementById('erbl_redeem_input').value) || 0;
        jQuery.post(wc_checkout_params.ajax_url, { action:'erbl_set_redeem', pts:v, nonce:'<?php echo wp_create_nonce("erbl_redeem"); ?>' }, function() {
            jQuery('body').trigger('update_checkout');
        });
    }
    function erblClearRedeem() {
        document.getElementById('erbl_redeem_input').value = 0;
        erblUpdateRedeem(0); erblApplyRedeem();
    }
    </script>
    <?php
}

add_action( 'wp_ajax_erbl_set_redeem', function() {
    check_ajax_referer('erbl_redeem', 'nonce');
    WC()->session->set( 'erbl_redeem_points', max( 0, intval( $_POST['pts'] ?? 0 ) ) );
    wp_send_json_success();
} );

add_action( 'woocommerce_cart_calculate_fees', 'erbl_apply_redeem_fee' );
function erbl_apply_redeem_fee() {
    if ( ! is_user_logged_in() || is_admin() ) { return; }
    $settings   = elrancho_loyalty_get_settings();
    if ( $settings['enabled'] !== 'yes' || $settings['redeem_enabled'] !== 'yes' ) { return; }
    $pts_to_use = intval( WC()->session->get('erbl_redeem_points', 0) );
    if ( $pts_to_use <= 0 ) { return; }
    $user_id    = get_current_user_id();
    $pts_to_use = min( $pts_to_use, erbl_get_user_points( $user_id ) );
    $max_disc   = WC()->cart->get_subtotal() * ( intval($settings['redeem_max_pct']) / 100 );
    $discount   = min( $pts_to_use * floatval($settings['point_value']), $max_disc );
    if ( $discount > 0 ) {
        WC()->cart->add_fee( 'Rancho Rewards (-pts)', -$discount );
    }
}

add_action( 'woocommerce_checkout_order_processed', 'erbl_deduct_redeemed_points', 10, 3 );
function erbl_deduct_redeemed_points( $order_id, $posted_data, $order ) {
    if ( ! is_user_logged_in() ) { return; }
    $pts = intval( WC()->session->get('erbl_redeem_points', 0) );
    if ( $pts <= 0 ) { return; }
    $user_id = get_current_user_id();
    $pts     = min( $pts, erbl_get_user_points( $user_id ) );
    erbl_adjust_points( $user_id, -$pts, 'redemption', $order_id, sprintf('Redención en pedido #%d', $order_id) );
    $order->update_meta_data( '_erbl_pts_redeemed', $pts );
    $order->save();
    $order->add_order_note( sprintf('Rancho Rewards: -%d pts redimidos.', $pts) );
    WC()->session->set( 'erbl_redeem_points', 0 );
}

/* --------------------------------------------------
   10. RETOS
   -------------------------------------------------- */
function erbl_check_challenges_on_order( $user_id, $order ) {
    global $wpdb;
    $t_ch      = $wpdb->prefix . 'erbl_challenges';
    $t_cp      = $wpdb->prefix . 'erbl_challenge_progress';
    $tier_rank = [ 'bronze' => 1, 'silver' => 2, 'gold' => 3 ];
    $user_rank = $tier_rank[ erbl_get_user_tier( $user_id ) ] ?? 1;
    $challenges = $wpdb->get_results( "SELECT * FROM $t_ch WHERE active=1" );
    foreach ( $challenges as $ch ) {
        if ( $user_rank < ( $tier_rank[ $ch->tier_req ] ?? 1 ) ) { continue; }
        $prog = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t_cp WHERE user_id=%d AND challenge_id=%d", $user_id, $ch->id ) );
        if ( $prog && $prog->completed ) { continue; }
        $new_p = $prog ? intval($prog->progress) : 0;
        switch ( $ch->type ) {
            case 'orders_count': case 'streak_weeks': case 'mondays_month': $new_p++; break;
            case 'single_order_min':
                if ( floatval( $order->get_total() ) >= floatval($ch->target) ) { $new_p = intval($ch->target); } break;
            case 'categories_month':
                $cats = [];
                foreach ( $order->get_items() as $item ) {
                    $p = $item->get_product();
                    if ( ! $p ) { continue; }
                    $terms = get_the_terms( $p->get_id(), 'product_cat' );
                    if ( $terms ) { foreach ($terms as $t) { $cats[$t->term_id] = true; } }
                }
                $new_p = min( intval($ch->target), $new_p + count($cats) ); break;
        }
        $done = $new_p >= intval($ch->target) ? 1 : 0;
        $done_at = $done ? current_time('mysql') : null;
        if ( $prog ) {
            $wpdb->update( $t_cp, [ 'progress' => $new_p, 'completed' => $done, 'completed_at' => $done_at ], [ 'id' => $prog->id ], [ '%d','%d','%s' ], [ '%d' ] );
        } else {
            $wpdb->insert( $t_cp, [ 'user_id' => $user_id, 'challenge_id' => $ch->id, 'progress' => $new_p, 'completed' => $done, 'completed_at' => $done_at ], [ '%d','%d','%d','%d','%s' ] );
        }
        if ( $done ) {
            erbl_adjust_points( $user_id, intval($ch->bonus_pts), 'challenge', $ch->id, 'Reto completado: ' . $ch->title );
        }
    }
}

/* --------------------------------------------------
   11. CADUCIDAD — Cron mensual
   -------------------------------------------------- */
add_filter( 'cron_schedules', function($s) {
    $s['monthly'] = [ 'interval' => 30 * DAY_IN_SECONDS, 'display' => 'Once Monthly' ];
    return $s;
} );
if ( ! wp_next_scheduled('erbl_expire_points_event') ) {
    wp_schedule_event( time(), 'monthly', 'erbl_expire_points_event' );
}
add_action( 'erbl_expire_points_event', function() {
    global $wpdb;
    $settings = elrancho_loyalty_get_settings();
    $months   = intval( $settings['expiry_months'] );
    if ( $months <= 0 ) { return; }
    $cutoff = date('Y-m-d H:i:s', strtotime("-{$months} months") );
    $t_tx   = $wpdb->prefix . 'erbl_transactions';
    $users  = $wpdb->get_col( $wpdb->prepare(
        "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key='_erbl_points' AND CAST(meta_value AS UNSIGNED)>0 AND user_id NOT IN (SELECT DISTINCT user_id FROM $t_tx WHERE created_at > %s)",
        $cutoff
    ) );
    foreach ( $users as $uid ) {
        $pts = erbl_get_user_points( intval($uid) );
        if ( $pts > 0 ) { erbl_adjust_points( intval($uid), -$pts, 'expiry', 0, 'Expiración por inactividad' ); }
    }
    // Resetear progreso de retos periódicos
    $t_ch = $wpdb->prefix . 'erbl_challenges';
    $t_cp = $wpdb->prefix . 'erbl_challenge_progress';
    // Reset mensual: categories_month y mondays_month
    $monthly_ids = $wpdb->get_col("SELECT id FROM $t_ch WHERE type IN ('categories_month','mondays_month') AND active=1");
    if ($monthly_ids) {
        $placeholders = implode(',', array_fill(0, count($monthly_ids), '%d'));
        $wpdb->query($wpdb->prepare("UPDATE $t_cp SET progress=0, completed=0, completed_at=NULL WHERE challenge_id IN ($placeholders)", ...$monthly_ids));
    }
    // Reset streak_weeks: si el usuario no compró en los últimos 7 días, resetear
    $streak_ids = $wpdb->get_col("SELECT id FROM $t_ch WHERE type='streak_weeks' AND active=1");
    if ($streak_ids) {
        $week_ago   = date('Y-m-d H:i:s', strtotime('-7 days'));
        $t_tx_local = $wpdb->prefix . 'erbl_transactions';
        $active_users = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT user_id FROM $t_tx_local WHERE type='order' AND created_at >= %s", $week_ago));
        $ph = implode(',', array_fill(0, count($streak_ids), '%d'));
        if ($active_users) {
            $u_ph = implode(',', array_fill(0, count($active_users), '%d'));
            $wpdb->query($wpdb->prepare("UPDATE $t_cp SET progress=0, completed=0, completed_at=NULL WHERE challenge_id IN ($ph) AND user_id NOT IN ($u_ph)", ...array_merge($streak_ids, $active_users)));
        } else {
            $wpdb->query($wpdb->prepare("UPDATE $t_cp SET progress=0, completed=0, completed_at=NULL WHERE challenge_id IN ($ph)", ...$streak_ids));
        }
    }
} );

/* --------------------------------------------------
   12. REST API
   -------------------------------------------------- */
add_action( 'rest_api_init', function() {
    $ns = 'erbl/v1';
    $auth = function() { return is_user_logged_in() || ( defined('REST_REQUEST') && REST_REQUEST && get_current_user_id() > 0 ); };

    register_rest_route( $ns, '/wallet',          [ 'methods' => 'GET',  'callback' => 'erbl_api_wallet',          'permission_callback' => $auth ] );
    register_rest_route( $ns, '/transactions',    [ 'methods' => 'GET',  'callback' => 'erbl_api_transactions',    'permission_callback' => $auth ] );
    register_rest_route( $ns, '/challenges',      [ 'methods' => 'GET',  'callback' => 'erbl_api_challenges',      'permission_callback' => $auth ] );
    register_rest_route( $ns, '/referral/apply',  [ 'methods' => 'POST', 'callback' => 'erbl_api_apply_referral',  'permission_callback' => $auth,
        'args' => [ 'code' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ] ] ] );
    register_rest_route( $ns, '/redeem-token',         [ 'methods' => 'POST', 'callback' => 'erbl_api_redeem_token',         'permission_callback' => $auth,
        'args' => [ 'points' => [ 'required' => true, 'validate_callback' => 'is_numeric' ] ] ] );
    register_rest_route( $ns, '/redeem-token/consume', [ 'methods' => 'POST', 'callback' => 'erbl_api_consume_redeem_token', 'permission_callback' => function() {
        return current_user_can('manage_woocommerce') || ( defined('REST_REQUEST') && REST_REQUEST && current_user_can('manage_woocommerce') );
    }, 'args' => [ 'token' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ] ] ] );
    register_rest_route( $ns, '/profile',              [ 'methods' => 'PUT',  'callback' => 'erbl_api_update_profile',       'permission_callback' => $auth,
        'args' => [ 'birthday' => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ] ] ] );
} );

function erbl_api_wallet( $request ) {
    $uid       = get_current_user_id();
    $settings  = elrancho_loyalty_get_settings();
    $points    = erbl_get_user_points($uid);
    $spend     = erbl_get_user_total_spend($uid);
    $tier      = erbl_get_user_tier($uid);
    $pv        = floatval($settings['point_value']);
    $ref_code  = get_user_meta($uid, '_erbl_referral_code', true);
    $tier_data = [];
    if ( $tier === 'bronze' ) { $t = floatval($settings['tier_silver_spend']); $tier_data = [ 'next' => 'silver', 'pct' => $t > 0 ? min(100, round(($spend/$t)*100)) : 0, 'remain' => max(0, $t-$spend) ]; }
    elseif ( $tier === 'silver' ) { $t = floatval($settings['tier_gold_spend']); $tier_data = [ 'next' => 'gold', 'pct' => $t > 0 ? min(100, round(($spend/$t)*100)) : 0, 'remain' => max(0, $t-$spend) ]; }
    return rest_ensure_response( [
        'points'           => $points, 'value_usd' => round($points * $pv, 2),
        'tier'             => $tier, 'tier_label' => erbl_tier_label($tier),
        'tier_multiplier'  => erbl_get_user_multiplier($uid),
        'next_tier'        => $tier_data['next'] ?? null,
        'next_tier_pct'    => $tier_data['pct'] ?? 100,
        'next_tier_remain' => round($tier_data['remain'] ?? 0, 2),
        'total_spend_usd'  => round($spend, 2),
        'referral_code'    => $ref_code ?: null,
        'referral_link'    => $ref_code ? add_query_arg('ref', $ref_code, home_url('/')) : null,
        'redeem_minimum'   => intval($settings['redeem_minimum']),
        'point_value'      => $pv,
    ] );
}

function erbl_api_transactions( $request ) {
    global $wpdb;
    $uid    = get_current_user_id();
    $page   = max(1, intval($request->get_param('page') ?? 1));
    $per    = 20;
    $t_tx   = $wpdb->prefix . 'erbl_transactions';
    $rows   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t_tx WHERE user_id=%d ORDER BY id DESC LIMIT %d OFFSET %d", $uid, $per, ($page-1)*$per ) );
    $total  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t_tx WHERE user_id=%d", $uid ) );
    $labels = [ 'order'=>'Compra','registration'=>'Bienvenida','referral_bonus'=>'Referido','redemption'=>'Redención','reversal'=>'Reversa','challenge'=>'Reto','expiry'=>'Expiración','manual'=>'Ajuste' ];
    return rest_ensure_response( [
        'transactions' => array_map( fn($r) => [ 'id'=>intval($r->id),'delta'=>intval($r->delta),'balance'=>intval($r->balance),'type'=>$r->type,'type_label'=>$labels[$r->type]??ucfirst($r->type),'note'=>$r->note,'date'=>$r->created_at ], $rows ),
        'total' => $total, 'pages' => (int) ceil($total/$per), 'page' => $page,
    ] );
}

function erbl_api_challenges( $request ) {
    global $wpdb;
    $uid        = get_current_user_id();
    $tier       = erbl_get_user_tier($uid);
    $t_ch       = $wpdb->prefix . 'erbl_challenges';
    $t_cp       = $wpdb->prefix . 'erbl_challenge_progress';
    $tier_rank  = [ 'bronze'=>1,'silver'=>2,'gold'=>3 ];
    $user_rank  = $tier_rank[$tier] ?? 1;
    $challenges = $wpdb->get_results("SELECT * FROM $t_ch WHERE active=1 ORDER BY bonus_pts DESC");
    $data = [];
    foreach ( $challenges as $ch ) {
        $prog = $wpdb->get_row( $wpdb->prepare("SELECT progress, completed FROM $t_cp WHERE user_id=%d AND challenge_id=%d", $uid, $ch->id) );
        $p    = $prog ? intval($prog->progress) : 0;
        $data[] = [ 'id'=>intval($ch->id),'title'=>$ch->title,'description'=>$ch->description,'bonus_pts'=>intval($ch->bonus_pts),'tier_req'=>$ch->tier_req,'locked'=>$user_rank<($tier_rank[$ch->tier_req]??1),'progress'=>$p,'target'=>intval($ch->target),'pct'=>intval($ch->target)>0?min(100,round(($p/intval($ch->target))*100)):0,'completed'=>$prog?(bool)$prog->completed:false ];
    }
    return rest_ensure_response( [ 'challenges' => $data ] );
}

function erbl_api_apply_referral( $request ) {
    $uid  = get_current_user_id();
    $code = strtoupper( sanitize_text_field( $request->get_param('code') ) );
    if ( get_user_meta($uid, '_erbl_used_referral_code', true) ) { return new WP_Error('already_used','Ya usaste un código.',['status'=>400]); }
    $ref = erbl_get_user_by_referral_code($code);
    if ( ! $ref )           { return new WP_Error('invalid_code','Código inválido.',['status'=>404]); }
    if ( $ref === $uid )    { return new WP_Error('self_referral','No puedes referirte a ti mismo.',['status'=>400]); }
    update_user_meta($uid, '_erbl_used_referral_code', $code);
    return rest_ensure_response( [ 'success'=>true,'message'=>'Código guardado. Recibirás tus puntos en tu primera compra.' ] );
}

function erbl_api_redeem_token( $request ) {
    $uid      = get_current_user_id();
    $pts      = intval($request->get_param('points'));
    $settings = elrancho_loyalty_get_settings();
    $balance  = erbl_get_user_points($uid);
    if ( $pts < intval($settings['redeem_minimum']) ) { return new WP_Error('below_minimum','Puntos insuficientes.',['status'=>400]); }
    if ( $pts > $balance )                            { return new WP_Error('insufficient','No tienes suficientes puntos.',['status'=>400]); }
    $token = wp_generate_password(24, false);
    set_transient( 'erbl_redeem_' . $token, [ 'user_id'=>$uid,'points'=>$pts ], 30 * MINUTE_IN_SECONDS );
    return rest_ensure_response( [ 'token'=>$token,'points'=>$pts,'value_usd'=>round($pts*floatval($settings['point_value']),2),'expires_in'=>1800,'qr_data'=>json_encode(['token'=>$token,'pts'=>$pts]) ] );
}

function erbl_api_consume_redeem_token( $request ) {
    $token = sanitize_text_field( $request->get_param('token') );
    $data  = get_transient( 'erbl_redeem_' . $token );
    if ( ! $data ) { return new WP_Error('invalid_token', 'Token inválido o expirado.', ['status' => 404]); }
    $uid  = intval($data['user_id']);
    $pts  = intval($data['points']);
    $bal  = erbl_adjust_points( $uid, -$pts, 'redemption', 0, 'Redención en tienda física' );
    delete_transient( 'erbl_redeem_' . $token );
    $user = get_userdata($uid);
    return rest_ensure_response([
        'success'     => true,
        'user'        => $user ? $user->display_name : '#' . $uid,
        'points_used' => $pts,
        'new_balance' => $bal,
    ]);
}

function erbl_api_update_profile( $request ) {
    $uid      = get_current_user_id();
    $birthday = sanitize_text_field( $request->get_param('birthday') ?? '' );
    $updated  = [];
    if ( $birthday ) {
        if ( ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday) ) {
            return new WP_Error('invalid_date', 'Formato de fecha inválido. Use YYYY-MM-DD.', ['status' => 400]);
        }
        update_user_meta( $uid, '_erbl_birthday', $birthday );
        $updated['birthday'] = $birthday;
    }
    if ( empty($updated) ) { return new WP_Error('no_data', 'No se proporcionaron datos para actualizar.', ['status' => 400]); }
    return rest_ensure_response(['success' => true, 'updated' => $updated]);
}

/* --------------------------------------------------
   13. MY ACCOUNT — Dashboard
   -------------------------------------------------- */
add_action( 'woocommerce_account_dashboard', 'elrancho_loyalty_account_dashboard', 5 );
function elrancho_loyalty_account_dashboard() {
    if ( ! is_user_logged_in() ) { return; }
    $settings = elrancho_loyalty_get_settings();
    if ( $settings['enabled'] !== 'yes' ) { return; }
    global $wpdb;
    $uid       = get_current_user_id();
    $points    = erbl_get_user_points($uid);
    $spend     = erbl_get_user_total_spend($uid);
    $tier      = erbl_get_user_tier($uid);
    $pv        = floatval($settings['point_value']);
    $ref_code  = get_user_meta($uid, '_erbl_referral_code', true);
    $ref_link  = $ref_code ? add_query_arg('ref', $ref_code, home_url('/')) : '';
    $tier_data = [];
    if ( $tier === 'bronze' ) { $t = floatval($settings['tier_silver_spend']); $tier_data = [ 'next'=>'Plata 🥈','pct'=>$t>0?min(100,round(($spend/$t)*100)):0,'remain'=>max(0,$t-$spend) ]; }
    elseif ( $tier === 'silver' ) { $t = floatval($settings['tier_gold_spend']); $tier_data = [ 'next'=>'Oro 🥇','pct'=>$t>0?min(100,round(($spend/$t)*100)):0,'remain'=>max(0,$t-$spend) ]; }
    $t_tx  = $wpdb->prefix . 'erbl_transactions';
    $t_ch  = $wpdb->prefix . 'erbl_challenges';
    $t_cp  = $wpdb->prefix . 'erbl_challenge_progress';
    $txs   = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t_tx WHERE user_id=%d ORDER BY id DESC LIMIT 5", $uid));
    $chs   = $wpdb->get_results("SELECT ch.*, cp.progress, cp.completed FROM $t_ch ch LEFT JOIN $t_cp cp ON cp.challenge_id=ch.id AND cp.user_id={$uid} WHERE ch.active=1 LIMIT 4");
    $tx_labels = [ 'order'=>'Compra','registration'=>'Bienvenida','referral_bonus'=>'Referido','redemption'=>'Redención','reversal'=>'Reversa','challenge'=>'Reto','expiry'=>'Expiración','manual'=>'Ajuste' ];
    ?>
    <div class="erbl-dashboard" style="margin-bottom:32px;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
            <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:26px;font-weight:600;color:#b81417;"><?php echo number_format($points); ?></div>
                <div style="font-size:12px;color:#7D6B60;margin-top:2px;">puntos disponibles</div>
                <div style="font-size:11px;color:#4A3B32;margin-top:4px;">≈ $<?php echo number_format($points * $pv, 2); ?> USD</div>
            </div>
            <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:22px;font-weight:600;color:#b81417;"><?php echo erbl_tier_label($tier); ?></div>
                <div style="font-size:12px;color:#7D6B60;margin-top:2px;">tu nivel actual</div>
                <div style="font-size:11px;color:#4A3B32;margin-top:4px;">$<?php echo number_format($spend, 0); ?> USD gastados</div>
            </div>
            <?php if ($ref_code) : ?>
            <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:20px;font-weight:600;color:#b81417;letter-spacing:2px;"><?php echo esc_html($ref_code); ?></div>
                <div style="font-size:12px;color:#7D6B60;margin-top:2px;">código de referido</div>
                <div style="font-size:11px;color:#4A3B32;margin-top:4px;">+<?php echo number_format(intval($settings['bonus_referrer'])); ?> pts por amigo</div>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($tier_data)) : ?>
        <div style="background:#fff;border:1px solid #e0d8cf;border-radius:10px;padding:16px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:13px;font-weight:500;color:#4A3B32;">Progreso hacia <?php echo esc_html($tier_data['next']); ?></span>
                <span style="font-size:12px;color:#7D6B60;">$<?php echo number_format($tier_data['remain'], 0); ?> USD más</span>
            </div>
            <div style="background:#f0e8de;border-radius:99px;height:8px;overflow:hidden;">
                <div style="background:#b81417;height:8px;border-radius:99px;width:<?php echo esc_attr($tier_data['pct']); ?>%;transition:width .4s;"></div>
            </div>
            <div style="font-size:11px;color:#7D6B60;margin-top:6px;"><?php echo esc_html($tier_data['pct']); ?>% completado</div>
        </div>
        <?php endif; ?>
        <?php if ($chs) : ?>
        <div style="background:#fff;border:1px solid #e0d8cf;border-radius:10px;padding:16px;margin-bottom:20px;">
            <h3 style="font-size:14px;font-weight:600;margin:0 0 12px;color:#4A3B32;">🎯 Retos activos</h3>
            <?php foreach ($chs as $ch) :
                $p    = intval($ch->progress ?? 0);
                $tgt  = intval($ch->target);
                $done = !empty($ch->completed);
                $pct  = $tgt > 0 ? min(100, round(($p/$tgt)*100)) : 0; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0e8de;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:<?php echo $done ? '#0a7c42' : '#4A3B32'; ?>;"><?php echo $done ? '✓ ' : ''; echo esc_html($ch->title); ?></div>
                        <div style="font-size:11px;color:#7D6B60;"><?php echo esc_html($ch->description); ?></div>
                        <?php if (!$done) : ?><div style="background:#f0e8de;border-radius:99px;height:4px;width:120px;margin-top:4px;"><div style="background:#b81417;height:4px;border-radius:99px;width:<?php echo esc_attr($pct); ?>%;"></div></div><?php endif; ?>
                    </div>
                    <span style="font-size:12px;font-weight:500;color:#b81417;white-space:nowrap;margin-left:12px;">+<?php echo number_format($ch->bonus_pts); ?> pts</span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($txs) : ?>
        <div style="background:#fff;border:1px solid #e0d8cf;border-radius:10px;padding:16px;">
            <h3 style="font-size:14px;font-weight:600;margin:0 0 12px;color:#4A3B32;">📋 Últimas actividades</h3>
            <?php foreach ($txs as $tx) :
                $pos   = intval($tx->delta) > 0;
                $color = $pos ? '#0a7c42' : '#c0392b';
                $sign  = $pos ? '+' : ''; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f0e8de;font-size:13px;">
                    <div>
                        <span style="color:#4A3B32;"><?php echo esc_html($tx_labels[$tx->type]??ucfirst($tx->type)); ?></span>
                        <?php if ($tx->note) : ?><span style="color:#7D6B60;font-size:11px;"> — <?php echo esc_html($tx->note); ?></span><?php endif; ?>
                    </div>
                    <div style="text-align:right;flex-shrink:0;margin-left:12px;">
                        <span style="color:<?php echo esc_attr($color); ?>;font-weight:600;"><?php echo esc_html($sign . number_format($tx->delta)); ?> pts</span>
                        <div style="font-size:10px;color:#7D6B60;"><?php echo esc_html(date_i18n('d M y', strtotime($tx->created_at))); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($ref_link) : ?>
        <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px;margin-top:20px;text-align:center;">
            <p style="font-size:13px;color:#4A3B32;margin:0 0 10px;">Comparte tu link y gana <strong><?php echo number_format(intval($settings['bonus_referrer'])); ?> pts</strong> por cada amigo que compre 🎁</p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <input type="text" value="<?php echo esc_url($ref_link); ?>" readonly style="border:1px solid #ccc;border-radius:6px;padding:6px 12px;font-size:12px;max-width:300px;width:100%;" onclick="this.select()">
                <button onclick="navigator.clipboard.writeText('<?php echo esc_js($ref_link); ?>').then(()=>this.textContent='¡Copiado!')" style="background:#b81417;color:#fff;border:none;border-radius:6px;padding:7px 16px;font-size:12px;cursor:pointer;">Copiar link</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/* --------------------------------------------------
   14. ADMIN — Perfil de usuario
   -------------------------------------------------- */
add_action( 'show_user_profile', 'erbl_user_profile_fields' );
add_action( 'edit_user_profile', 'erbl_user_profile_fields' );
function erbl_user_profile_fields( $user ) {
    if ( ! current_user_can('manage_woocommerce') ) { return; }
    wp_nonce_field( 'erbl_user_profile', 'erbl_profile_nonce' );
    ?>
    <h2>Rancho Rewards</h2>
    <table class="form-table">
        <tr><th>Tier actual</th><td><?php echo esc_html(erbl_tier_label(erbl_get_user_tier($user->ID))); ?></td></tr>
        <tr><th>Gasto histórico</th><td>$<?php echo number_format(erbl_get_user_total_spend($user->ID), 2); ?> USD</td></tr>
        <tr><th>Puntos actuales</th><td><input type="number" min="0" name="erbl_points" value="<?php echo esc_attr(erbl_get_user_points($user->ID)); ?>" class="small-text"> pts</td></tr>
        <tr><th>Cumpleaños</th><td><input type="date" name="erbl_birthday" value="<?php echo esc_attr(get_user_meta($user->ID,'_erbl_birthday',true)); ?>"><p class="description">Para el bono de cumpleaños.</p></td></tr>
        <tr><th>Código de referido</th><td><code><?php echo esc_html(get_user_meta($user->ID,'_erbl_referral_code',true) ?: '—'); ?></code></td></tr>
    </table>
    <?php
}
add_action( 'personal_options_update',  'erbl_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'erbl_save_user_profile_fields' );
function erbl_save_user_profile_fields( $user_id ) {
    if ( ! current_user_can('manage_woocommerce') ) { return; }
    if ( ! isset($_POST['erbl_profile_nonce']) || ! wp_verify_nonce($_POST['erbl_profile_nonce'], 'erbl_user_profile') ) { return; }
    if ( isset($_POST['erbl_points']) ) {
        $new = max(0, intval($_POST['erbl_points']));
        $delta = $new - erbl_get_user_points($user_id);
        if ( $delta !== 0 ) { erbl_adjust_points($user_id, $delta, 'manual', 0, 'Ajuste manual desde admin'); }
    }
    if ( isset($_POST['erbl_birthday']) ) {
        update_user_meta($user_id, '_erbl_birthday', sanitize_text_field($_POST['erbl_birthday']));
    }
}

add_filter( 'manage_users_columns', function($cols) {
    if (current_user_can('manage_woocommerce')) { $cols['erbl_points'] = 'Puntos'; $cols['erbl_tier'] = 'Tier'; }
    return $cols;
} );
add_filter( 'manage_users_custom_column', function($val, $col, $uid) {
    if ($col === 'erbl_points') { return number_format(erbl_get_user_points($uid)); }
    if ($col === 'erbl_tier')   { return erbl_tier_label(erbl_get_user_tier($uid)); }
    return $val;
}, 10, 3 );

/* --------------------------------------------------
   15. SHORTCODES
   -------------------------------------------------- */
add_shortcode( 'elrancho_loyalty_points', function() {
    return is_user_logged_in() ? number_format(erbl_get_user_points(get_current_user_id())) : '—';
} );
add_shortcode( 'erbl_tier', function() {
    return is_user_logged_in() ? erbl_tier_label(erbl_get_user_tier(get_current_user_id())) : '—';
} );

/* --------------------------------------------------
   16. MY ACCOUNT MENU
   -------------------------------------------------- */
function elrancho_account_redirect_dashboard_to_orders() {
    if ( ! function_exists('is_account_page') || ! is_account_page() || ! is_user_logged_in() ) { return; }
    if ( is_admin() || wp_doing_ajax() ) { return; }
    if ( ! function_exists('is_wc_endpoint_url') || (! is_wc_endpoint_url() || is_wc_endpoint_url('dashboard')) ) {
        wp_safe_redirect( wc_get_account_endpoint_url('orders') ); exit;
    }
}
add_action( 'template_redirect', 'elrancho_account_redirect_dashboard_to_orders' );

function elrancho_account_menu_items( $items ) {
    if ( isset($items['dashboard']) ) { unset($items['dashboard']); }
    $labels  = [ 'orders'=>'Mis pedidos','downloads'=>'Descargas','edit-address'=>'Direcciones','payment-methods'=>'Métodos de pago','edit-account'=>'Detalles de cuenta','customer-logout'=>'Cerrar sesión' ];
    $ordered = [];
    foreach ( ['orders','edit-address','payment-methods','edit-account','downloads','customer-logout'] as $key ) {
        if ( isset($items[$key]) ) { $ordered[$key] = $labels[$key] ?? $items[$key]; unset($items[$key]); }
    }
    foreach ( $items as $key => $label ) { $ordered[$key] = $labels[$key] ?? $label; }
    return $ordered;
}
add_filter( 'woocommerce_account_menu_items', 'elrancho_account_menu_items', 20 );


/* --------------------------------------------------
   17. MI CUENTA — Página dedicada "Mis Puntos"
   -------------------------------------------------- */
function erbl_register_account_endpoint() {
    add_rewrite_endpoint( 'my-points', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'erbl_register_account_endpoint' );

add_filter( 'woocommerce_get_query_vars', function( $vars ) {
    $vars['my-points'] = '';
    return $vars;
} );

// Flush automático una sola vez cuando el endpoint cambia de slug
add_action( 'init', function() {
    if ( get_option('erbl_endpoint_slug') !== 'my-points' ) {
        flush_rewrite_rules();
        update_option( 'erbl_endpoint_slug', 'my-points' );
    }
} );

add_filter( 'woocommerce_account_menu_items', function( $items ) {
    $settings = elrancho_loyalty_get_settings();
    if ( $settings['enabled'] !== 'yes' ) { return $items; }
    $new = [];
    foreach ( $items as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'orders' ) { $new['my-points'] = '🎁 Mis Puntos'; }
    }
    return $new;
}, 30 );

add_action( 'woocommerce_account_my-points_endpoint', 'erbl_account_mis_puntos_page' );
function erbl_account_mis_puntos_page() {
    if ( ! is_user_logged_in() ) { return; }
    global $wpdb;
    $uid      = get_current_user_id();
    $settings = elrancho_loyalty_get_settings();
    $pv       = floatval( $settings['point_value'] );
    $points   = erbl_get_user_points( $uid );
    $spend    = erbl_get_user_total_spend( $uid );
    $tier     = erbl_get_user_tier( $uid );
    $ref_code = get_user_meta( $uid, '_erbl_referral_code', true );
    $ref_link = $ref_code ? add_query_arg( 'ref', $ref_code, home_url('/') ) : '';

    if ( $tier === 'bronze' )      { $t_spend = floatval($settings['tier_silver_spend']); $t_next = 'Plata 🥈'; }
    elseif ( $tier === 'silver' )  { $t_spend = floatval($settings['tier_gold_spend']);   $t_next = 'Oro 🥇'; }
    else                           { $t_spend = 0; $t_next = ''; }
    $t_pct    = $t_spend > 0 ? min(100, round(($spend / $t_spend) * 100)) : 100;
    $t_remain = $t_spend > 0 ? max(0, $t_spend - $spend) : 0;

    $page       = max(1, intval($_GET['paged'] ?? 1));
    $per        = 15;
    $f_type     = sanitize_key($_GET['tipo'] ?? '');
    $t_tx       = $wpdb->prefix . 'erbl_transactions';
    $params_base = [$uid];
    if ($f_type) { $params_base[] = $f_type; }
    $where_sql   = $f_type ? ' AND type=%s' : '';
    $total       = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_tx WHERE user_id=%d" . $where_sql, ...$params_base));
    $txs         = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t_tx WHERE user_id=%d" . $where_sql . " ORDER BY id DESC LIMIT %d OFFSET %d", ...array_merge($params_base, [$per, ($page-1)*$per])));
    $pages      = (int)ceil($total / $per);

    $t_ch = $wpdb->prefix . 'erbl_challenges';
    $t_cp = $wpdb->prefix . 'erbl_challenge_progress';
    $tier_rank  = ['bronze'=>1,'silver'=>2,'gold'=>3];
    $user_rank  = $tier_rank[$tier] ?? 1;
    $challenges = $wpdb->get_results("SELECT ch.*, cp.progress, cp.completed FROM $t_ch ch LEFT JOIN $t_cp cp ON cp.challenge_id=ch.id AND cp.user_id={$uid} WHERE ch.active=1 ORDER BY ch.bonus_pts DESC");

    $tx_labels  = ['order'=>'🛒 Compra','registration'=>'🎉 Bienvenida','referral_bonus'=>'👥 Referido','redemption'=>'🎁 Redención','reversal'=>'↩ Reversa','challenge'=>'🎯 Reto','expiry'=>'⏰ Expiración','manual'=>'✏️ Ajuste'];
    $base_url   = wc_get_account_endpoint_url('my-points');
    ?>
    <div style="max-width:820px;">

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:24px;">
            <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#b81417;"><?php echo number_format($points); ?></div>
                <div style="font-size:12px;color:#7D6B60;margin-top:2px;">puntos disponibles</div>
                <div style="font-size:11px;color:#4A3B32;font-weight:500;margin-top:4px;">≈ $<?php echo number_format($points * $pv, 2); ?> USD</div>
            </div>
            <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#b81417;"><?php echo erbl_tier_label($tier); ?></div>
                <div style="font-size:12px;color:#7D6B60;margin-top:2px;">tu nivel actual</div>
                <div style="font-size:11px;color:#4A3B32;font-weight:500;margin-top:4px;">$<?php echo number_format($spend, 2); ?> gastados</div>
            </div>
            <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#b81417;"><?php echo number_format($total); ?></div>
                <div style="font-size:12px;color:#7D6B60;margin-top:2px;">movimientos totales</div>
            </div>
        </div>

        <?php if ( $t_spend > 0 ) : ?>
        <div style="background:#fff;border:1px solid #e0d8cf;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="font-size:14px;font-weight:600;color:#4A3B32;">Progreso hacia <?php echo esc_html($t_next); ?></span>
                <span style="font-size:12px;color:#7D6B60;">$<?php echo number_format($t_remain, 2); ?> USD más</span>
            </div>
            <div style="background:#f0e8de;border-radius:99px;height:10px;overflow:hidden;">
                <div style="background:#b81417;height:10px;border-radius:99px;width:<?php echo esc_attr($t_pct); ?>%;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:11px;color:#7D6B60;"><?php echo erbl_tier_label($tier); ?></span>
                <span style="font-size:11px;color:#7D6B60;"><?php echo $t_pct; ?>% completado</span>
                <span style="font-size:11px;color:#7D6B60;"><?php echo esc_html($t_next); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($challenges) : ?>
        <div style="background:#fff;border:1px solid #e0d8cf;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
            <h3 style="font-size:15px;font-weight:600;margin:0 0 14px;color:#4A3B32;">🎯 Retos activos</h3>
            <?php foreach ($challenges as $ch) :
                $cp    = intval($ch->progress ?? 0);
                $ct    = intval($ch->target);
                $done  = !empty($ch->completed);
                $cpct  = $ct > 0 ? min(100, round(($cp/$ct)*100)) : 0;
                $lock  = $user_rank < ($tier_rank[$ch->tier_req] ?? 1);
                $col   = $done ? '#0a7c42' : ($lock ? '#aaa' : '#4A3B32'); ?>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0e8de;gap:12px;">
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;color:<?php echo esc_attr($col);?>;"><?php echo $done?'✅ ':($lock?'🔒 ':''); echo esc_html($ch->title); ?></div>
                        <div style="font-size:12px;color:#7D6B60;margin:2px 0 6px;"><?php echo esc_html($ch->description); ?><?php if($lock) echo ' — requiere '.esc_html(erbl_tier_label($ch->tier_req)); ?></div>
                        <?php if (!$done && !$lock) : ?>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="background:#f0e8de;border-radius:99px;height:6px;width:160px;">
                                <div style="background:#b81417;height:6px;border-radius:99px;width:<?php echo esc_attr($cpct); ?>%;"></div>
                            </div>
                            <span style="font-size:11px;color:#7D6B60;"><?php echo $cp; ?>/<?php echo $ct; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <span style="font-size:13px;font-weight:700;color:<?php echo $done?'#0a7c42':'#b81417'; ?>;">+<?php echo number_format($ch->bonus_pts); ?> pts</span>
                        <?php if($done): ?><div style="font-size:10px;color:#0a7c42;">¡Completado!</div><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="background:#fff;border:1px solid #e0d8cf;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                <h3 style="font-size:15px;font-weight:600;margin:0;color:#4A3B32;">📋 Historial de puntos</h3>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php foreach ([''=>'Todos','order'=>'Compras','referral_bonus'=>'Referidos','redemption'=>'Redenciones','challenge'=>'Retos'] as $v=>$l) :
                        $active = ($f_type === $v);
                        $url    = $v ? add_query_arg('tipo', $v, $base_url) : $base_url; ?>
                        <a href="<?php echo esc_url($url); ?>" style="font-size:11px;padding:4px 10px;border-radius:99px;text-decoration:none;border:1px solid <?php echo $active?'#b81417':'#e0d8cf'; ?>;background:<?php echo $active?'#b81417':'#fdf8f1'; ?>;color:<?php echo $active?'#fff':'#7D6B60'; ?>;"><?php echo esc_html($l); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($txs) : ?>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead><tr style="border-bottom:2px solid #f0e8de;">
                    <th style="text-align:left;padding:8px 6px;color:#7D6B60;font-weight:600;font-size:12px;">Movimiento</th>
                    <th style="text-align:left;padding:8px 6px;color:#7D6B60;font-weight:600;font-size:12px;">Detalle</th>
                    <th style="text-align:right;padding:8px 6px;color:#7D6B60;font-weight:600;font-size:12px;">Puntos</th>
                    <th style="text-align:right;padding:8px 6px;color:#7D6B60;font-weight:600;font-size:12px;">Balance</th>
                    <th style="text-align:right;padding:8px 6px;color:#7D6B60;font-weight:600;font-size:12px;">Fecha</th>
                </tr></thead>
                <tbody>
                <?php foreach ($txs as $tx) :
                    $pos   = intval($tx->delta) > 0;
                    $color = $pos ? '#0a7c42' : '#c0392b';
                    $sign  = $pos ? '+' : ''; ?>
                    <tr style="border-bottom:1px solid #f9f4ef;">
                        <td style="padding:10px 6px;color:#4A3B32;font-weight:500;"><?php echo esc_html($tx_labels[$tx->type] ?? ucfirst($tx->type)); ?></td>
                        <td style="padding:10px 6px;color:#7D6B60;font-size:12px;"><?php echo esc_html($tx->note ?: '—'); ?></td>
                        <td style="padding:10px 6px;text-align:right;font-weight:700;color:<?php echo esc_attr($color); ?>;"><?php echo esc_html($sign.number_format($tx->delta)); ?></td>
                        <td style="padding:10px 6px;text-align:right;color:#4A3B32;"><?php echo number_format($tx->balance); ?></td>
                        <td style="padding:10px 6px;text-align:right;color:#7D6B60;font-size:12px;white-space:nowrap;"><?php echo esc_html(date_i18n('d M Y', strtotime($tx->created_at))); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($pages > 1) : ?>
            <div style="display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap;">
                <?php for ($i=1; $i<=$pages; $i++) :
                    $url_p = add_query_arg(array_filter(['tipo'=>$f_type,'paged'=>$i>1?$i:false]), $base_url);
                    $act   = ($i === $page); ?>
                    <a href="<?php echo esc_url($url_p); ?>" style="font-size:12px;padding:5px 12px;border-radius:6px;text-decoration:none;border:1px solid <?php echo $act?'#b81417':'#e0d8cf'; ?>;background:<?php echo $act?'#b81417':'#fff'; ?>;color:<?php echo $act?'#fff':'#4A3B32'; ?>;"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php else : ?>
            <div style="text-align:center;padding:32px;color:#7D6B60;">
                <div style="font-size:32px;margin-bottom:8px;">🍞</div>
                <p style="margin:0;font-size:14px;">Aún no tienes movimientos<?php echo $f_type?' de este tipo':''; ?>.</p>
                <p style="margin:6px 0 0;font-size:12px;">¡Haz tu primera compra para empezar!</p>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($ref_link) : ?>
        <div style="background:#fdf8f1;border:1px solid #e8d5b0;border-radius:10px;padding:16px 20px;text-align:center;">
            <p style="font-size:14px;font-weight:600;color:#4A3B32;margin:0 0 4px;">Invita a un amigo y gana <?php echo number_format(intval($settings['bonus_referrer'])); ?> pts 🎁</p>
            <p style="font-size:12px;color:#7D6B60;margin:0 0 12px;">Tu amigo también recibe <?php echo number_format(intval($settings['bonus_referred'])); ?> pts en su primera compra.</p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;max-width:480px;margin:0 auto;">
                <input type="text" value="<?php echo esc_url($ref_link); ?>" readonly onclick="this.select()" style="flex:1;min-width:200px;border:1px solid #e0d8cf;border-radius:8px;padding:8px 12px;font-size:12px;background:#fff;color:#4A3B32;">
                <button onclick="navigator.clipboard.writeText('<?php echo esc_js($ref_link); ?>').then(()=>{this.textContent='¡Copiado! ✓';setTimeout(()=>this.textContent='Copiar link',2000)})" style="background:#b81417;color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:13px;cursor:pointer;white-space:nowrap;font-weight:500;">Copiar link</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/* --------------------------------------------------
   18. ADMIN — Tab "Transacciones" con filtros
   -------------------------------------------------- */
add_action( 'admin_menu', function() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
    add_submenu_page( 'woocommerce', 'Rancho Rewards', 'Rancho Rewards', 'manage_woocommerce', 'elrancho-loyalty', 'erbl_admin_page_full' );
} );

function erbl_admin_page_full() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die('Sin permisos.'); }
    global $wpdb;
    $s    = elrancho_loyalty_get_settings();
    $tab  = sanitize_key( $_GET['tab'] ?? 'dashboard' );
    $t_tx = $wpdb->prefix . 'erbl_transactions';
    $t_ch = $wpdb->prefix . 'erbl_challenges';
    $total_pts   = (int)$wpdb->get_var("SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)),0) FROM {$wpdb->usermeta} WHERE meta_key='_erbl_points'");
    $members     = (int)$wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $t_tx");
    $pts_month   = (int)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(delta),0) FROM $t_tx WHERE delta>0 AND created_at>=%s", date('Y-m-01')));
    $red_month   = (int)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ABS(delta)),0) FROM $t_tx WHERE type='redemption' AND created_at>=%s", date('Y-m-01')));
    $tabs_nav = ['dashboard'=>'Dashboard','transactions'=>'Transacciones','settings'=>'Configuración','tiers'=>'Tiers','challenges'=>'Retos','members'=>'Miembros'];
    ?>
    <div class="wrap">
    <h1 style="display:flex;align-items:center;gap:8px;"><span style="font-size:22px;">🥐</span> Rancho Rewards</h1>
    <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
        <?php foreach ($tabs_nav as $t=>$l) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab='.$t)); ?>" class="nav-tab <?php echo $tab===$t?'nav-tab-active':''; ?>"><?php echo esc_html($l); ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($tab === 'dashboard') :
        $cards=[['Miembros activos',$members,''],['Puntos activos',number_format($total_pts),'pts'],['Puntos ganados mes',number_format($pts_month),'pts'],['Redimidos mes',number_format($red_month),'pts']]; ?>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
            <?php foreach ($cards as $c) : ?>
                <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px 20px;">
                    <div style="font-size:12px;color:#666;margin-bottom:4px;"><?php echo esc_html($c[0]); ?></div>
                    <div style="font-size:22px;font-weight:600;"><?php echo esc_html($c[1]); ?> <small style="font-size:12px;color:#888;"><?php echo esc_html($c[2]); ?></small></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;">
                <h3 style="margin-top:0;">Top 10 clientes <a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions')); ?>" style="font-size:12px;font-weight:400;margin-left:8px;">Ver todas →</a></h3>
                <?php $top=$wpdb->get_results("SELECT u.ID,u.display_name,CAST(um.meta_value AS UNSIGNED) AS pts FROM {$wpdb->users} u INNER JOIN {$wpdb->usermeta} um ON um.user_id=u.ID AND um.meta_key='_erbl_points' WHERE CAST(um.meta_value AS UNSIGNED)>0 ORDER BY pts DESC LIMIT 10");
                if ($top) : ?>
                <table class="widefat" style="border:none;"><thead><tr><th>Cliente</th><th>Tier</th><th>Puntos</th></tr></thead><tbody>
                <?php foreach ($top as $r):?>
                    <tr><td><a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions&uid='.$r->ID));?>" style="font-size:12px;"><?php echo esc_html($r->display_name);?></a></td><td><?php echo erbl_tier_label(erbl_get_user_tier($r->ID));?></td><td><?php echo number_format($r->pts);?></td></tr>
                <?php endforeach;?>
                </tbody></table>
                <?php else: ?><p>Sin miembros aún.</p><?php endif; ?>
            </div>
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;">
                <h3 style="margin-top:0;">Últimas transacciones <a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions')); ?>" style="font-size:12px;font-weight:400;margin-left:8px;">Ver todas →</a></h3>
                <?php $recent=$wpdb->get_results("SELECT t.*,u.display_name FROM $t_tx t LEFT JOIN {$wpdb->users} u ON u.ID=t.user_id ORDER BY t.id DESC LIMIT 10");
                if ($recent): ?>
                <table class="widefat" style="border:none;"><thead><tr><th>Cliente</th><th>Tipo</th><th>Delta</th><th>Fecha</th></tr></thead><tbody>
                <?php foreach ($recent as $r): $pos=intval($r->delta)>0; ?>
                    <tr><td><a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions&uid='.$r->user_id));?>" style="font-size:12px;"><?php echo esc_html($r->display_name);?></a></td><td style="font-size:11px;color:#646970;"><?php echo esc_html($r->type);?></td><td style="font-weight:600;color:<?php echo $pos?'#0a7c42':'#c0392b';?>;"><?php echo ($pos?'+':'').number_format($r->delta);?></td><td style="font-size:11px;color:#646970;"><?php echo esc_html(date_i18n('d M y',strtotime($r->created_at)));?></td></tr>
                <?php endforeach;?>
                </tbody></table>
                <?php else: ?><p>Sin transacciones aún.</p><?php endif; ?>
            </div>
        </div>
        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;margin-top:20px;">
            <h3 style="margin-top:0;">Migración de datos v1 → v2</h3>
            <p style="color:#646970;font-size:13px;">Migra usuarios del sistema antiguo (<code>_elrancho_loyalty_points</code>) al sistema v2. También genera códigos de referido y calcula el gasto total para usuarios sin estos datos.</p>
            <button id="erbl-migrate-btn" class="button button-primary">Ejecutar migración</button>
            <span id="erbl-migrate-status" style="margin-left:12px;font-size:13px;"></span>
        </div>
        <script>
        document.getElementById('erbl-migrate-btn').addEventListener('click', function() {
            var btn = this;
            var status = document.getElementById('erbl-migrate-status');
            btn.disabled = true;
            status.textContent = 'Ejecutando...';
            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=erbl_run_migration&_wpnonce=' + '<?php echo wp_create_nonce("erbl_migrate"); ?>'
            }).then(r => r.json()).then(data => {
                status.textContent = data.data || data.message || 'Completado';
                btn.disabled = false;
            }).catch(() => {
                status.textContent = 'Error. Intenta de nuevo.';
                btn.disabled = false;
            });
        });
        </script>

    <?php elseif ($tab === 'transactions'):
        $csv_url = wp_nonce_url(add_query_arg(array_filter(['erbl_export_csv'=>1,'uid'=>$_GET['uid']??'','tipo'=>$_GET['tipo']??'','desde'=>$_GET['desde']??'','hasta'=>$_GET['hasta']??'']), admin_url('admin.php?page=elrancho-loyalty&tab=transactions')), 'erbl_export_csv');
        ?>
        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
            <a href="<?php echo esc_url($csv_url); ?>" class="button">⬇ Exportar CSV</a>
        </div>
        <?php
        $f_uid  = intval($_GET['uid'] ?? 0);
        $f_type = sanitize_key($_GET['tipo'] ?? '');
        $f_from = sanitize_text_field($_GET['desde'] ?? '');
        $f_to   = sanitize_text_field($_GET['hasta'] ?? '');
        $pg     = max(1, intval($_GET['paged'] ?? 1));
        $per_pg = 30;

        $where  = 'WHERE 1=1'; $params = [];
        if ($f_uid)  { $where .= ' AND t.user_id=%d'; $params[] = $f_uid; }
        if ($f_type) { $where .= ' AND t.type=%s';    $params[] = $f_type; }
        if ($f_from) { $where .= ' AND t.created_at>=%s'; $params[] = $f_from.' 00:00:00'; }
        if ($f_to)   { $where .= ' AND t.created_at<=%s'; $params[] = $f_to.' 23:59:59'; }

        $csql  = "SELECT COUNT(*) FROM $t_tx t $where";
        $lsql  = "SELECT t.*,u.display_name FROM $t_tx t LEFT JOIN {$wpdb->users} u ON u.ID=t.user_id $where ORDER BY t.id DESC LIMIT %d OFFSET %d";
        $total_rows = $params ? (int)$wpdb->get_var($wpdb->prepare($csql,...$params)) : (int)$wpdb->get_var($csql);
        $lparams    = array_merge($params,[$per_pg,($pg-1)*$per_pg]);
        $rows       = $params ? $wpdb->get_results($wpdb->prepare($lsql,...$lparams)) : $wpdb->get_results($wpdb->prepare($lsql,$per_pg,($pg-1)*$per_pg));
        $total_pages = (int)ceil($total_rows/$per_pg);

        if ($f_uid && ($su = get_userdata($f_uid))) : ?>
            <div style="background:#fff;border:1px solid #2271b1;border-radius:8px;padding:14px 18px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div>
                    <strong style="font-size:15px;"><?php echo esc_html($su->display_name); ?></strong>
                    <span style="color:#646970;font-size:13px;margin-left:8px;"><?php echo esc_html($su->user_email); ?></span>
                    <div style="margin-top:4px;font-size:13px;">
                        <?php echo erbl_tier_label(erbl_get_user_tier($f_uid)); ?> &nbsp;·&nbsp;
                        <strong><?php echo number_format(erbl_get_user_points($f_uid)); ?> pts</strong> disponibles &nbsp;·&nbsp;
                        $<?php echo number_format(erbl_get_user_total_spend($f_uid), 2); ?> USD gastados
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="<?php echo esc_url(get_edit_user_link($f_uid)); ?>" class="button">Editar usuario</a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions')); ?>" class="button">Ver todos</a>
                </div>
            </div>
        <?php endif; ?>

        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
            <form method="get" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <input type="hidden" name="page" value="elrancho-loyalty">
                <input type="hidden" name="tab"  value="transactions">
                <div><label style="display:block;font-size:12px;color:#646970;margin-bottom:3px;">ID de usuario</label>
                    <input type="number" name="uid" value="<?php echo esc_attr($f_uid?:''); ?>" placeholder="ID" style="width:100px;padding:5px 8px;border:1px solid #8c8f94;border-radius:3px;font-size:13px;"></div>
                <div><label style="display:block;font-size:12px;color:#646970;margin-bottom:3px;">Tipo</label>
                    <select name="tipo" style="padding:5px 8px;border:1px solid #8c8f94;border-radius:3px;font-size:13px;">
                        <option value="">Todos</option>
                        <?php foreach (['order'=>'Compra','registration'=>'Registro','referral_bonus'=>'Referido','redemption'=>'Redención','reversal'=>'Reversa','challenge'=>'Reto','expiry'=>'Expiración','manual'=>'Ajuste'] as $v=>$l): ?>
                            <option value="<?php echo $v;?>" <?php selected($f_type,$v);?>><?php echo $l;?></option>
                        <?php endforeach;?>
                    </select></div>
                <div><label style="display:block;font-size:12px;color:#646970;margin-bottom:3px;">Desde</label>
                    <input type="date" name="desde" value="<?php echo esc_attr($f_from);?>" style="padding:5px 8px;border:1px solid #8c8f94;border-radius:3px;font-size:13px;"></div>
                <div><label style="display:block;font-size:12px;color:#646970;margin-bottom:3px;">Hasta</label>
                    <input type="date" name="hasta" value="<?php echo esc_attr($f_to);?>" style="padding:5px 8px;border:1px solid #8c8f94;border-radius:3px;font-size:13px;"></div>
                <button type="submit" class="button button-primary">Filtrar</button>
                <?php if ($f_uid||$f_type||$f_from||$f_to): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions')); ?>" class="button">Limpiar</a>
                <?php endif;?>
            </form>
        </div>

        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden;">
            <div style="padding:10px 16px;border-bottom:1px solid #f0f0f1;display:flex;justify-content:space-between;">
                <span style="font-size:13px;color:#646970;"><?php echo number_format($total_rows); ?> transacciones</span>
                <?php if ($total_pages>1): ?><span style="font-size:12px;color:#646970;">Página <?php echo $pg; ?> de <?php echo $total_pages;?></span><?php endif;?>
            </div>
            <?php if ($rows):
                $tlabels=['order'=>'🛒 Compra','registration'=>'🎉 Bienvenida','referral_bonus'=>'👥 Referido','redemption'=>'🎁 Redención','reversal'=>'↩ Reversa','challenge'=>'🎯 Reto','expiry'=>'⏰ Expiración','manual'=>'✏️ Ajuste']; ?>
            <table class="widefat" style="border:none;border-radius:0;">
                <thead><tr><th style="width:36px;">#</th><th>Cliente</th><th>Tipo</th><th>Nota</th><th style="text-align:right;">Puntos</th><th style="text-align:right;">Balance</th><th style="text-align:right;">Fecha</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): $pos=intval($r->delta)>0; ?>
                    <tr>
                        <td style="color:#aaa;font-size:11px;"><?php echo $r->id;?></td>
                        <td><a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions&uid='.$r->user_id));?>" style="font-size:13px;"><?php echo esc_html($r->display_name?:'#'.$r->user_id);?></a></td>
                        <td style="font-size:12px;"><?php echo esc_html($tlabels[$r->type]??ucfirst($r->type));?></td>
                        <td style="font-size:12px;color:#646970;max-width:180px;"><?php echo esc_html($r->note?:'—');?></td>
                        <td style="text-align:right;font-weight:700;font-size:14px;color:<?php echo $pos?'#0a7c42':'#c0392b';?>;"><?php echo ($pos?'+':'').number_format($r->delta);?></td>
                        <td style="text-align:right;font-size:13px;"><?php echo number_format($r->balance);?></td>
                        <td style="text-align:right;font-size:12px;color:#646970;white-space:nowrap;"><?php echo esc_html(date_i18n('d M Y H:i',strtotime($r->created_at)));?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
            <?php if ($total_pages>1):
                $burl=admin_url('admin.php?page=elrancho-loyalty&tab=transactions'.($f_uid?"&uid=$f_uid":'').($f_type?"&tipo=$f_type":'').($f_from?"&desde=$f_from":'').($f_to?"&hasta=$f_to":'')); ?>
                <div style="padding:10px 16px;border-top:1px solid #f0f0f1;display:flex;gap:4px;justify-content:flex-end;">
                <?php for ($i=1;$i<=$total_pages;$i++): $act=($i===$pg); ?>
                    <a href="<?php echo esc_url(add_query_arg('paged',$i,$burl));?>" style="padding:4px 10px;border:1px solid <?php echo $act?'#2271b1':'#ddd';?>;border-radius:3px;font-size:12px;text-decoration:none;background:<?php echo $act?'#2271b1':'#fff';?>;color:<?php echo $act?'#fff':'#2c3338';?>;"><?php echo $i;?></a>
                <?php endfor;?>
                </div>
            <?php endif;?>
            <?php else: ?>
                <div style="padding:32px;text-align:center;color:#646970;"><p>No se encontraron transacciones con estos filtros.</p></div>
            <?php endif;?>
        </div>

    <?php elseif ($tab === 'settings'): ?>
        <form method="post" action="options.php">
            <?php settings_fields('elrancho_loyalty_group');?>
            <h2>Acumulación de puntos</h2>
            <table class="form-table">
                <tr><th>Habilitar programa</th><td><label><input type="checkbox" name="elrancho_loyalty_settings[enabled]" value="1" <?php checked($s['enabled'],'yes');?>> Activo</label></td></tr>
                <tr><th>Puntos por $1 USD</th><td><input type="number" min="0" step="1" class="small-text" name="elrancho_loyalty_settings[points_rate]" value="<?php echo esc_attr($s['points_rate']);?>"><p class="description">10 pts por cada dólar.</p></td></tr>
                <tr><th>Valor del punto (USD)</th><td><input type="number" min="0" step="0.001" class="small-text" name="elrancho_loyalty_settings[point_value]" value="<?php echo esc_attr($s['point_value']);?>"><p class="description">0.01 = 100 pts = $1 USD.</p></td></tr>
                <tr><th>Base de cálculo</th><td><select name="elrancho_loyalty_settings[points_base]"><option value="items" <?php selected($s['points_base'],'items');?>>Sin envío</option><option value="total" <?php selected($s['points_base'],'total');?>>Total pedido</option></select></td></tr>
                <tr><th>Acreditar cuando</th><td><select name="elrancho_loyalty_settings[award_status]"><option value="processing" <?php selected($s['award_status'],'processing');?>>Procesando</option><option value="completed" <?php selected($s['award_status'],'completed');?>>Completado</option></select></td></tr>
                <tr><th>Monto mínimo (USD)</th><td><input type="number" min="0" step="0.01" class="small-text" name="elrancho_loyalty_settings[min_order_total]" value="<?php echo esc_attr($s['min_order_total']);?>"></td></tr>
                <tr><th>Máx. pts por orden</th><td><input type="number" min="0" step="1" class="small-text" name="elrancho_loyalty_settings[max_points_per_order]" value="<?php echo esc_attr($s['max_points_per_order']);?>"><p class="description">0 = sin límite.</p></td></tr>
            </table>
            <h2>Redención</h2>
            <table class="form-table">
                <tr><th>Habilitar redención</th><td><label><input type="checkbox" name="elrancho_loyalty_settings[redeem_enabled]" value="1" <?php checked($s['redeem_enabled'],'yes');?>> Activo</label></td></tr>
                <tr><th>Mínimo para redimir (pts)</th><td><input type="number" min="0" class="small-text" name="elrancho_loyalty_settings[redeem_minimum]" value="<?php echo esc_attr($s['redeem_minimum']);?>"></td></tr>
                <tr><th>Incremento (pts)</th><td><input type="number" min="1" class="small-text" name="elrancho_loyalty_settings[redeem_step]" value="<?php echo esc_attr($s['redeem_step']);?>"></td></tr>
                <tr><th>Descuento máximo (%)</th><td><input type="number" min="0" max="100" class="small-text" name="elrancho_loyalty_settings[redeem_max_pct]" value="<?php echo esc_attr($s['redeem_max_pct']);?>"> %</td></tr>
            </table>
            <h2>Bonos</h2>
            <table class="form-table">
                <tr><th>Bono de registro (pts)</th><td><input type="number" min="0" class="small-text" name="elrancho_loyalty_settings[bonus_registration]" value="<?php echo esc_attr($s['bonus_registration']);?>"></td></tr>
                <tr><th>Multiplicador cumpleaños</th><td><input type="number" min="1" step="0.1" class="small-text" name="elrancho_loyalty_settings[bonus_birthday_mult]" value="<?php echo esc_attr($s['bonus_birthday_mult']);?>">x</td></tr>
                <tr><th>Pts al referidor</th><td><input type="number" min="0" class="small-text" name="elrancho_loyalty_settings[bonus_referrer]" value="<?php echo esc_attr($s['bonus_referrer']);?>"></td></tr>
                <tr><th>Pts al referido</th><td><input type="number" min="0" class="small-text" name="elrancho_loyalty_settings[bonus_referred]" value="<?php echo esc_attr($s['bonus_referred']);?>"></td></tr>
            </table>
            <h2>Tiers</h2>
            <table class="form-table">
                <tr><th>Gasto mínimo Plata (USD)</th><td><input type="number" min="0" class="small-text" name="elrancho_loyalty_settings[tier_silver_spend]" value="<?php echo esc_attr($s['tier_silver_spend']);?>"></td></tr>
                <tr><th>Multiplicador Plata</th><td><input type="number" min="1" step="0.01" class="small-text" name="elrancho_loyalty_settings[tier_silver_mult]" value="<?php echo esc_attr($s['tier_silver_mult']);?>">x</td></tr>
                <tr><th>Gasto mínimo Oro (USD)</th><td><input type="number" min="0" class="small-text" name="elrancho_loyalty_settings[tier_gold_spend]" value="<?php echo esc_attr($s['tier_gold_spend']);?>"></td></tr>
                <tr><th>Multiplicador Oro</th><td><input type="number" min="1" step="0.01" class="small-text" name="elrancho_loyalty_settings[tier_gold_mult]" value="<?php echo esc_attr($s['tier_gold_mult']);?>">x</td></tr>
            </table>
            <h2>Categoría especial</h2>
            <table class="form-table">
                <tr><th>Slug de categoría</th><td><input type="text" class="regular-text" name="elrancho_loyalty_settings[cat_mult_cakes_slug]" value="<?php echo esc_attr($s['cat_mult_cakes_slug']);?>"></td></tr>
                <tr><th>Multiplicador</th><td><input type="number" min="1" step="0.1" class="small-text" name="elrancho_loyalty_settings[cat_mult_cakes]" value="<?php echo esc_attr($s['cat_mult_cakes']);?>">x</td></tr>
            </table>
            <h2>Caducidad</h2>
            <table class="form-table">
                <tr><th>Meses sin actividad</th><td><input type="number" min="0" class="small-text" name="elrancho_loyalty_settings[expiry_months]" value="<?php echo esc_attr($s['expiry_months']);?>"><p class="description">0 = nunca caducan.</p></td></tr>
            </table>
            <?php submit_button('Guardar configuración');?>
        </form>

    <?php elseif ($tab === 'tiers'): ?>
        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:24px;max-width:700px;">
            <h2 style="margin-top:0;">Estructura de tiers</h2>
            <?php foreach ([['🥉','Bronce','Desde el registro','1x','Bono bienvenida + cumpleaños'],['🥈','Plata','$'.number_format($s['tier_silver_spend']).' USD acumulados',$s['tier_silver_mult'].'x','Envío gratis + acceso anticipado'],['🥇','Oro','$'.number_format($s['tier_gold_spend']).' USD acumulados',$s['tier_gold_mult'].'x','Todo lo de Plata + retos exclusivos']] as $t): ?>
                <div style="border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-bottom:12px;display:flex;gap:16px;">
                    <div style="font-size:32px;"><?php echo $t[0];?></div>
                    <div><strong style="font-size:16px;"><?php echo esc_html($t[1]);?></strong>
                    <div style="color:#666;font-size:13px;"><?php echo esc_html($t[2]);?></div>
                    <div style="color:#666;font-size:13px;">Multiplicador: <strong><?php echo esc_html($t[3]);?></strong></div>
                    <div style="color:#666;font-size:13px;"><?php echo esc_html($t[4]);?></div></div>
                </div>
            <?php endforeach;?>
        </div>

    <?php elseif ($tab === 'challenges'):
        $t_ch=$wpdb->prefix.'erbl_challenges';
        if (isset($_POST['erbl_save_challenge'])&&wp_verify_nonce($_POST['_wpnonce']??'','erbl_challenges')):
            $wpdb->insert($t_ch,['title'=>sanitize_text_field($_POST['ch_title']??''),'description'=>sanitize_textarea_field($_POST['ch_desc']??''),'type'=>sanitize_key($_POST['ch_type']??'orders_count'),'target'=>max(1,intval($_POST['ch_target']??1)),'bonus_pts'=>max(0,intval($_POST['ch_pts']??0)),'tier_req'=>sanitize_key($_POST['ch_tier']??'bronze'),'active'=>1,'created_at'=>current_time('mysql')]);
            echo '<div class="notice notice-success"><p>Reto creado.</p></div>';
        endif;
        if (isset($_GET['toggle_ch'])&&wp_verify_nonce($_GET['_wpnonce']??'','erbl_toggle_ch')):
            $cid=(int)$_GET['toggle_ch'];
            $wpdb->update($t_ch,['active'=>(int)!$wpdb->get_var($wpdb->prepare("SELECT active FROM $t_ch WHERE id=%d",$cid))],['id'=>$cid]);
        endif;
        $challenges=$wpdb->get_results("SELECT * FROM $t_ch ORDER BY id DESC");?>
        <table class="widefat striped"><thead><tr><th>Título</th><th>Tipo</th><th>Meta</th><th>Bonus</th><th>Tier</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
        <?php foreach($challenges as $ch):$tog=wp_nonce_url(admin_url('admin.php?page=elrancho-loyalty&tab=challenges&toggle_ch='.$ch->id),'erbl_toggle_ch');?>
            <tr><td><strong><?php echo esc_html($ch->title);?></strong><br><span style="color:#888;font-size:12px;"><?php echo esc_html($ch->description);?></span></td><td><?php echo esc_html($ch->type);?></td><td><?php echo esc_html($ch->target);?></td><td><?php echo number_format($ch->bonus_pts);?> pts</td><td><?php echo esc_html(ucfirst($ch->tier_req));?></td><td><?php echo $ch->active?'<span style="color:#0a7c42;">Activo</span>':'<span style="color:#c0392b;">Inactivo</span>';?></td><td><a href="<?php echo esc_url($tog);?>"><?php echo $ch->active?'Pausar':'Activar';?></a></td></tr>
        <?php endforeach;?>
        </tbody></table>
        <h2 style="margin-top:24px;">Crear reto</h2>
        <form method="post"><?php wp_nonce_field('erbl_challenges');?>
            <table class="form-table">
                <tr><th>Título</th><td><input type="text" name="ch_title" class="regular-text" required></td></tr>
                <tr><th>Descripción</th><td><input type="text" name="ch_desc" class="regular-text"></td></tr>
                <tr><th>Tipo</th><td><select name="ch_type"><option value="orders_count">N° de órdenes</option><option value="streak_weeks">Racha semanal</option><option value="single_order_min">Orden mínima ($)</option><option value="categories_month">Categorías distintas</option><option value="mondays_month">Lunes del mes</option></select></td></tr>
                <tr><th>Meta</th><td><input type="number" name="ch_target" class="small-text" min="1" value="1"></td></tr>
                <tr><th>Bonus (pts)</th><td><input type="number" name="ch_pts" class="small-text" min="0" value="100"></td></tr>
                <tr><th>Tier requerido</th><td><select name="ch_tier"><option value="bronze">Bronce</option><option value="silver">Plata</option><option value="gold">Oro</option></select></td></tr>
            </table>
            <input type="submit" name="erbl_save_challenge" class="button button-primary" value="Crear reto">
        </form>

    <?php elseif ($tab === 'members'): ?>
        <h2>Buscar miembro</h2>
        <form method="get"><input type="hidden" name="page" value="elrancho-loyalty"><input type="hidden" name="tab" value="members">
            <input type="text" name="erbl_search" value="<?php echo esc_attr($_GET['erbl_search']??'');?>" placeholder="Nombre o email...">
            <input type="submit" class="button" value="Buscar">
        </form>
        <?php $search=sanitize_text_field($_GET['erbl_search']??'');
        if ($search):
            $users=get_users(['search'=>'*'.$search.'*','search_columns'=>['user_login','user_email','display_name'],'number'=>30]);
            if ($users): ?>
            <table class="widefat striped" style="margin-top:16px;"><thead><tr><th>Cliente</th><th>Email</th><th>Tier</th><th>Puntos</th><th>Gasto</th><th>Acciones</th></tr></thead><tbody>
            <?php foreach($users as $u): ?>
                <tr><td><?php echo esc_html($u->display_name);?></td><td><?php echo esc_html($u->user_email);?></td><td><?php echo erbl_tier_label(erbl_get_user_tier($u->ID));?></td><td><?php echo number_format(erbl_get_user_points($u->ID));?></td><td>$<?php echo number_format((float)get_user_meta($u->ID,'_erbl_total_spend',true),2);?></td>
                <td><a href="<?php echo esc_url(admin_url('admin.php?page=elrancho-loyalty&tab=transactions&uid='.$u->ID));?>">Ver transacciones</a> | <a href="<?php echo esc_url(get_edit_user_link($u->ID));?>">Editar</a></td></tr>
            <?php endforeach;?>
            </tbody></table>
            <?php else: ?><p>Sin resultados.</p><?php endif;
        endif;?>
    <?php endif;?>
    </div>
    <?php
}



/* --------------------------------------------------
   19. MIGRACIÓN v1 → v2
   -------------------------------------------------- */
add_action( 'wp_ajax_erbl_run_migration', function() {
    if ( ! current_user_can('manage_woocommerce') || ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'erbl_migrate' ) ) {
        wp_send_json_error('Sin permisos.');
    }
    global $wpdb;
    $settings   = elrancho_loyalty_get_settings();
    $migrated   = 0;
    $ref_gen    = 0;
    $spend_calc = 0;
    $users = get_users(['role' => 'customer', 'number' => -1, 'fields' => 'ID']);
    foreach ($users as $uid) {
        // Migrar puntos v1
        $old = get_user_meta($uid, '_elrancho_loyalty_points', true);
        if ($old !== '' && $old !== false) {
            $current = erbl_get_user_points($uid);
            if ($current === 0) {
                update_user_meta($uid, '_erbl_points', max(0, intval($old)));
            }
            delete_user_meta($uid, '_elrancho_loyalty_points');
            $migrated++;
        }
        // Generar código de referido si no tiene
        $code = get_user_meta($uid, '_erbl_referral_code', true);
        if (!$code) {
            $new_code = strtoupper(substr(md5($uid . wp_generate_password(8, false)), 0, 8));
            update_user_meta($uid, '_erbl_referral_code', $new_code);
            $ref_gen++;
        }
        // Calcular total_spend si no tiene
        $spend = get_user_meta($uid, '_erbl_total_spend', true);
        if ($spend === '' || $spend === false) {
            $orders = wc_get_orders(['customer' => $uid, 'status' => 'completed', 'limit' => -1]);
            $total  = 0.0;
            foreach ($orders as $order) {
                $total += floatval($order->get_total()) - floatval($order->get_shipping_total());
            }
            update_user_meta($uid, '_erbl_total_spend', max(0.0, $total));
            $spend_calc++;
        }
    }
    // Actualizar índice de DB si no existe
    $t_tx = $wpdb->prefix . 'erbl_transactions';
    $indexes = $wpdb->get_results("SHOW INDEX FROM $t_tx WHERE Key_name='created_at'");
    if (empty($indexes)) {
        $wpdb->query("ALTER TABLE $t_tx ADD INDEX created_at (created_at)");
    }
    wp_send_json_success(sprintf(
        'Migración completada: %d puntos migrados, %d códigos generados, %d gastos calculados.',
        $migrated, $ref_gen, $spend_calc
    ));
} );

/* --------------------------------------------------
   20. EXPORTAR TRANSACCIONES CSV
   -------------------------------------------------- */
add_action( 'admin_init', function() {
    if ( ! isset($_GET['erbl_export_csv']) || ! current_user_can('manage_woocommerce') ) { return; }
    if ( ! wp_verify_nonce($_GET['_wpnonce'] ?? '', 'erbl_export_csv') ) { wp_die('Nonce inválido.'); }
    global $wpdb;
    $t_tx   = $wpdb->prefix . 'erbl_transactions';
    $f_uid  = intval($_GET['uid'] ?? 0);
    $f_type = sanitize_key($_GET['tipo'] ?? '');
    $f_from = sanitize_text_field($_GET['desde'] ?? '');
    $f_to   = sanitize_text_field($_GET['hasta'] ?? '');
    $where  = ['1=1'];
    $params = [];
    if ($f_uid)  { $where[] = 'user_id=%d'; $params[] = $f_uid; }
    if ($f_type) { $where[] = 'type=%s';    $params[] = $f_type; }
    if ($f_from) { $where[] = 'created_at>=%s'; $params[] = $f_from . ' 00:00:00'; }
    if ($f_to)   { $where[] = 'created_at<=%s'; $params[] = $f_to   . ' 23:59:59'; }
    $sql  = "SELECT t.*,u.display_name,u.user_email FROM $t_tx t LEFT JOIN {$wpdb->users} u ON u.ID=t.user_id WHERE " . implode(' AND ', $where) . " ORDER BY t.id DESC";
    $rows = $params ? $wpdb->get_results($wpdb->prepare($sql, ...$params)) : $wpdb->get_results($sql);
    $labels = ['order'=>'Compra','registration'=>'Bienvenida','referral_bonus'=>'Referido','redemption'=>'Redención','reversal'=>'Reversa','challenge'=>'Reto','expiry'=>'Expiración','manual'=>'Ajuste'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rancho-rewards-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($out, ['ID', 'Cliente', 'Email', 'Tipo', 'Puntos', 'Balance', 'Nota', 'Fecha']);
    foreach ($rows as $r) {
        fputcsv($out, [$r->id, $r->display_name, $r->user_email, $labels[$r->type] ?? $r->type, $r->delta, $r->balance, $r->note, $r->created_at]);
    }
    fclose($out);
    exit;
} );

/* =============================================
   OPCIONES DEL CUSTOMIZER
   ============================================= */
function elrancho_customize_register($wp_customize) {
    // Sección: Panadería
    $wp_customize->add_section('elrancho_bakery', [
        'title'    => __('El Rancho Bakery', 'elrancho'),
        'priority' => 30,
    ]);

    // Hero Background
    $wp_customize->add_setting('hero_background', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_background', [
        'label'   => __('Imagen Hero', 'elrancho'),
        'section' => 'elrancho_bakery',
    ]));

    // Hero Badge
    $wp_customize->add_setting('hero_badge', ['default' => 'Horneado Fresco Diario', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_badge', [
        'label'   => __('Texto del Badge del Hero', 'elrancho'),
        'section' => 'elrancho_bakery',
        'type'    => 'text',
    ]);

    // Hero Title
    $wp_customize->add_setting('hero_title', ['default' => 'Sabores Auténticos, Calidad Artesanal', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_title', [
        'label'   => __('Título del Hero', 'elrancho'),
        'section' => 'elrancho_bakery',
        'type'    => 'text',
    ]);

    // Hero Subtitle
    $wp_customize->add_setting('hero_subtitle', ['default' => 'Experimenta la calidez de la panadería tradicional con nuestro pan artesanal, pan dulce y pasteles personalizados.', 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('hero_subtitle', [
        'label'   => __('Subtítulo del Hero', 'elrancho'),
        'section' => 'elrancho_bakery',
        'type'    => 'textarea',
    ]);

    // Tagline del footer
    $wp_customize->add_setting('footer_tagline', ['default' => 'Trayendo el auténtico sabor de la panadería artesanal a tu vecindario desde 1995.', 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('footer_tagline', [
        'label'   => __('Tagline del Footer', 'elrancho'),
        'section' => 'elrancho_bakery',
        'type'    => 'textarea',
    ]);

    // Logo del footer (independiente del logo principal)
    $wp_customize->add_setting('footer_logo', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_logo', [
        'label'   => __('Logo del Footer', 'elrancho'),
        'section' => 'elrancho_bakery',
    ]));

    // Colores del layout global
    $wp_customize->add_setting('header_bg_color', [
        'default'           => '#5c260f',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bg_color', [
        'label'   => __('Color de fondo del Header', 'elrancho'),
        'section' => 'elrancho_bakery',
    ]));

    $wp_customize->add_setting('footer_bg_color', [
        'default'           => '#5c260f',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_bg_color', [
        'label'   => __('Color de fondo del Footer', 'elrancho'),
        'section' => 'elrancho_bakery',
    ]));

    // Loyalty Section
    $wp_customize->add_setting('loyalty_title', ['default' => 'Gana Pan con Cada Compra', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('loyalty_title', [
        'label'   => __('Título del Programa de Lealtad', 'elrancho'),
        'section' => 'elrancho_bakery',
        'type'    => 'text',
    ]);

    $wp_customize->add_setting('loyalty_description', ['default' => 'Únete a nuestro programa de recompensas. Obtén descuentos exclusivos, antojos de cumpleaños y puntos por cada peso que gastes.', 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('loyalty_description', [
        'label'   => __('Descripción del Programa de Lealtad', 'elrancho'),
        'section' => 'elrancho_bakery',
        'type'    => 'textarea',
    ]);

    $wp_customize->add_setting('loyalty_image', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'loyalty_image', [
        'label'   => __('Imagen del Programa de Lealtad', 'elrancho'),
        'section' => 'elrancho_bakery',
    ]));

    // About section
    $wp_customize->add_setting('about_image', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'about_image', [
        'label'   => __('Imagen de la Sección "Nosotros"', 'elrancho'),
        'section' => 'elrancho_bakery',
    ]));

    $wp_customize->add_setting('about_year', ['default' => '1995', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('about_year', [
        'label'   => __('Año de fundación', 'elrancho'),
        'section' => 'elrancho_bakery',
        'type'    => 'text',
    ]);

    // Redes sociales
    foreach (['facebook' => 'Facebook URL', 'instagram' => 'Instagram URL', 'tiktok' => 'TikTok URL'] as $key => $label) {
        $wp_customize->add_setting("social_{$key}", ['default' => '#', 'sanitize_callback' => 'esc_url_raw']);
        $wp_customize->add_control("social_{$key}", [
            'label'   => __($label, 'elrancho'),
            'section' => 'elrancho_bakery',
            'type'    => 'url',
        ]);
    }
}
add_action('customize_register', 'elrancho_customize_register');

function elrancho_output_customizer_colors() {
    $header_bg = sanitize_hex_color(get_theme_mod('header_bg_color', '#5c260f'));
    $footer_bg = sanitize_hex_color(get_theme_mod('footer_bg_color', '#5c260f'));

    if (!$header_bg) {
        $header_bg = '#5c260f';
    }
    if (!$footer_bg) {
        $footer_bg = '#5c260f';
    }

    echo '<style id="elrancho-customizer-colors">:root{--header-bg-color:' . esc_html($header_bg) . ';--footer-bg-color:' . esc_html($footer_bg) . ';}</style>';
}
add_action('wp_head', 'elrancho_output_customizer_colors', 20);

/* =============================================
   HELPERS
   ============================================= */
function elrancho_get_mod($key, $default = '') {
    return get_theme_mod($key, $default);
}

function elrancho_svg_icon($name) {
    $icons = [
        'bread'    => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/></svg>',
        'cart'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
        'heart'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>',
        'user'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'search'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        'menu'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
        'star'     => '<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'arrow'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
        'gift'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>',
        'truck'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
        'bulb'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="18" x2="15" y2="18"/><line x1="10" y1="22" x2="14" y2="22"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0018 8 6 6 0 006 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 018.91 14"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>',
        'instagram'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
        'tiktok'   => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.67a8.21 8.21 0 004.84 1.56V6.78a4.85 4.85 0 01-1.07-.09z"/></svg>',
        'checkmark'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
        'lock'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
        'store'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    ];
    return isset($icons[$name]) ? '<span class="elrancho-icon">' . $icons[$name] . '</span>' : '';
}

/* =============================================
   AJAX: Newsletter Signup
   ============================================= */
function elrancho_newsletter_signup() {
    check_ajax_referer('elrancho_nonce', 'nonce');
    $email = sanitize_email($_POST['email'] ?? '');

    if (!is_email($email)) {
        wp_send_json_error(['message' => __('Por favor ingresa un email válido.', 'elrancho')]);
    }

    // Aquí puedes integrar con Mailchimp, ConvertKit, etc.
    // Por ahora guardamos en opciones
    $subscribers = get_option('elrancho_newsletter', []);
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        update_option('elrancho_newsletter', $subscribers);
    }

    wp_send_json_success(['message' => __('¡Gracias por suscribirte!', 'elrancho')]);
}
add_action('wp_ajax_elrancho_newsletter', 'elrancho_newsletter_signup');
add_action('wp_ajax_nopriv_elrancho_newsletter', 'elrancho_newsletter_signup');

/* =============================================
   SHORTCODES
   ============================================= */
// Muestra productos destacados en homepage
function elrancho_featured_products($atts) {
    $atts = shortcode_atts(['limit' => 4, 'category' => ''], $atts);
    ob_start();
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => intval($atts['limit']),
        'meta_query'     => [['key' => '_featured', 'value' => 'yes']],
    ];
    if ($atts['category']) {
        $args['tax_query'] = [['taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => explode(',', $atts['category'])]];
    }
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        echo '<div class="products-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            get_template_part('template-parts/product-card');
        }
        echo '</div>';
        wp_reset_postdata();
    }
    return ob_get_clean();
}
add_shortcode('elrancho_featured', 'elrancho_featured_products');

/* =============================================
   ADMIN: Opciones extra
   ============================================= */
function elrancho_admin_styles() {
    echo '<style>
        #adminmenu .current-menu-item a { border-left: 3px solid #b81417; }
    </style>';
}
add_action('admin_head', 'elrancho_admin_styles');

// Columna "Baker's Note" en admin de productos
function elrancho_product_columns($columns) {
    $columns['bakers_note'] = __('Nota del Panadero', 'elrancho');
    return $columns;
}
add_filter('manage_product_posts_columns', 'elrancho_product_columns');

function elrancho_product_columns_data($column, $post_id) {
    if ($column === 'bakers_note') {
        $note = get_post_meta($post_id, '_bakers_note', true);
        echo $note ? esc_html(wp_trim_words($note, 10)) : '—';
    }
}
add_action('manage_product_posts_custom_column', 'elrancho_product_columns_data', 10, 2);

// Meta box para Baker's Note
function elrancho_bakers_note_metabox() {
    add_meta_box('bakers_note', __("Nota del Panadero", 'elrancho'), function($post) {
        $note = get_post_meta($post->ID, '_bakers_note', true);
        wp_nonce_field('elrancho_bakers_note', 'bakers_note_nonce');
        echo '<textarea name="bakers_note" rows="3" style="width:100%;font-family:inherit;padding:8px;border-radius:6px;">' . esc_textarea($note) . '</textarea>';
        echo '<p class="description">' . esc_html__('Tip especial del panadero para este producto. Se muestra en la página del producto.', 'elrancho') . '</p>';
    }, 'product', 'normal', 'default');
}
add_action('add_meta_boxes', 'elrancho_bakers_note_metabox');

function elrancho_save_bakers_note($post_id) {
    if (!isset($_POST['bakers_note_nonce']) || !wp_verify_nonce($_POST['bakers_note_nonce'], 'elrancho_bakers_note')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $note = sanitize_textarea_field($_POST['bakers_note'] ?? '');
    update_post_meta($post_id, '_bakers_note', $note);
}
add_action('save_post', 'elrancho_save_bakers_note');
