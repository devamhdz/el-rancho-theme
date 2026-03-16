<?php
/**
 * Override: woocommerce/templates/loop/loop-start.php
 * Limpio — sin elementos vacíos al inicio del grid
 */
defined('ABSPATH') || exit;
?>
<ul class="products columns-<?php echo esc_attr(wc_get_loop_prop('columns')); ?>">
