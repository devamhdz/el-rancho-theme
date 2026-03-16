<form role="search" method="get" class="header-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <span class="header-search-icon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
    </span>
    <input type="search"
           name="s"
           value="<?php echo get_search_query(); ?>"
           placeholder="<?php esc_attr_e('Buscar pasteles...', 'elrancho'); ?>"
           aria-label="<?php esc_attr_e('Buscar en la tienda', 'elrancho'); ?>">
    <input type="hidden" name="post_type" value="product">
    <button type="submit" class="sr-only"><?php esc_html_e('Buscar', 'elrancho'); ?></button>
</form>
