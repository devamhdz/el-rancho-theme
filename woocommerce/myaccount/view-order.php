<?php
/**
 * View Order
 *
 * Override de: woocommerce/templates/myaccount/view-order.php
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id);
if (!$order instanceof WC_Order) {
	return;
}

$order_number = $order->get_order_number();
$order_status = $order->get_status();
$order_status_name = wc_get_order_status_name($order_status);
$created_date = $order->get_date_created();
$created_label = $created_date ? wc_format_datetime($created_date, wc_date_format() . ' \a \l\a\s g:i A') : '';

$is_pickup = false;
foreach ($order->get_shipping_methods() as $shipping_item) {
	$method_id = (string) $shipping_item->get_method_id();
	$method_name = (string) $shipping_item->get_name();
	if (strpos($method_id, 'local_pickup') !== false || stripos($method_name, 'pickup') !== false) {
		$is_pickup = true;
		break;
	}
}

$stage = 1;
if (in_array($order_status, ['processing', 'on-hold', 'completed', 'refunded', 'failed', 'cancelled'], true)) {
	$stage = 2;
}
if (in_array($order_status, ['completed'], true) || strpos($order_status, 'delivered') !== false || strpos($order_status, 'ready') !== false) {
	$stage = 3;
}

$items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$actions = wc_get_account_orders_actions($order);
$order_again_url = isset($actions['order-again']) ? $actions['order-again']['url'] : wc_get_page_permalink('shop');
$customer_notes = $order->get_customer_order_notes();

$shipping_total = (float) $order->get_shipping_total() + (float) $order->get_shipping_tax();
$subtotal = (float) $order->get_subtotal();
$tax_total = (float) $order->get_total_tax();
$total = (float) $order->get_total();

$loyalty_points = 0;
if (function_exists('elrancho_loyalty_get_settings') && function_exists('elrancho_loyalty_calculate_order_points')) {
	$settings = elrancho_loyalty_get_settings();
	if ($settings['enabled'] === 'yes') {
		$loyalty_points = (int) $order->get_meta('_elrancho_loyalty_points_awarded', true);
		if ($loyalty_points <= 0) {
			$loyalty_points = (int) elrancho_loyalty_calculate_order_points($order, $settings);
		}
	}
}

$current_points = function_exists('elrancho_loyalty_get_user_points') && is_user_logged_in()
	? (int) elrancho_loyalty_get_user_points(get_current_user_id())
	: 0;
$reward_target = 1500;
$points_left = max(0, $reward_target - $current_points);
$progress_pct = $reward_target > 0 ? min(100, (int) round(($current_points / $reward_target) * 100)) : 0;

$address_html = $is_pickup ? $order->get_formatted_billing_address() : $order->get_formatted_shipping_address();
if (empty($address_html)) {
	$address_html = $order->get_formatted_billing_address();
}
?>

<section class="elr-order-detail" aria-label="<?php esc_attr_e('Detalle de pedido', 'elrancho'); ?>">
	<div class="elr-order-detail-head">
		<nav class="elr-order-crumbs" aria-label="<?php esc_attr_e('Ruta de navegación', 'elrancho'); ?>">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'elrancho'); ?></a>
			<span>/</span>
			<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><?php esc_html_e('Cuenta', 'elrancho'); ?></a>
			<span>/</span>
			<span><?php echo esc_html(sprintf(__('Pedido #%s', 'elrancho'), $order_number)); ?></span>
		</nav>

		<div class="elr-order-title-row">
			<div>
				<h2><?php echo esc_html(sprintf(__('Pedido #%s', 'elrancho'), $order_number)); ?></h2>
				<p>
					<?php
					echo esc_html($created_label);
					echo ' ';
					?>
					<span class="elr-order-current-status"><?php echo esc_html($order_status_name); ?></span>
				</p>
			</div>
			<div class="elr-order-head-actions">
				<button type="button" class="btn-light" onclick="window.print()"><?php esc_html_e('Factura', 'elrancho'); ?></button>
				<a class="btn-solid" href="<?php echo esc_url($order_again_url); ?>"><?php esc_html_e('Ordenar de nuevo', 'elrancho'); ?></a>
			</div>
		</div>
	</div>

	<div class="elr-order-detail-layout">
		<div class="elr-order-detail-main">
			<article class="elr-detail-card">
				<h3><?php esc_html_e('Estado del pedido', 'elrancho'); ?></h3>
				<div class="elr-order-tracker stage-<?php echo esc_attr($stage); ?>">
					<div class="line-bg"></div>
					<div class="line-progress"></div>
					<div class="step is-done">
						<span>1</span>
						<p><?php esc_html_e('Realizado', 'elrancho'); ?></p>
						<small><?php echo $created_date ? esc_html(wc_format_datetime($created_date, 'g:i A')) : ''; ?></small>
					</div>
					<div class="step <?php echo $stage >= 2 ? 'is-done' : ''; ?>">
						<span>2</span>
						<p><?php esc_html_e('Preparando', 'elrancho'); ?></p>
						<small>
							<?php
							$modified_date = $order->get_date_modified();
							echo ($stage >= 2 && $modified_date) ? esc_html(wc_format_datetime($modified_date, 'g:i A')) : esc_html__('Pendiente', 'elrancho');
							?>
						</small>
					</div>
					<div class="step <?php echo $stage >= 3 ? 'is-done' : ''; ?>">
						<span>3</span>
						<p><?php echo $is_pickup ? esc_html__('Listo para pickup', 'elrancho') : esc_html__('Entregado', 'elrancho'); ?></p>
						<small><?php echo ($stage >= 3 && $order->get_date_completed()) ? esc_html(wc_format_datetime($order->get_date_completed(), 'g:i A')) : esc_html__('Pendiente', 'elrancho'); ?></small>
					</div>
				</div>
			</article>

			<article class="elr-detail-card elr-detail-items">
				<div class="card-head">
					<h3><?php esc_html_e('Productos comprados', 'elrancho'); ?></h3>
				</div>
				<div class="card-body">
					<?php foreach ($items as $item_id => $item) : ?>
						<?php
						$product = $item->get_product();
						$quantity = (int) $item->get_quantity();
						$line_total = $order->get_formatted_line_subtotal($item);
						$unit_price = $quantity > 0 ? wc_price(((float) $item->get_total()) / $quantity) : wc_price((float) $item->get_total());
						$image_html = '';
						if ($product && $product->get_image_id()) {
							$image_html = wp_get_attachment_image($product->get_image_id(), 'thumbnail');
						} else {
							$image_html = wc_placeholder_img('thumbnail');
						}
						?>
						<div class="elr-order-item-row">
							<div class="thumb"><?php echo wp_kses_post($image_html); ?></div>
							<div class="data">
								<h4><?php echo esc_html($item->get_name()); ?></h4>
								<div class="meta"><?php echo wp_kses_post(wc_display_item_meta($item, ['echo' => false])); ?></div>
								<div class="unit"><?php echo wp_kses_post($unit_price); ?></div>
							</div>
							<div class="qty-total">
								<p><?php echo esc_html(sprintf(__('Cant: %d', 'elrancho'), $quantity)); ?></p>
								<strong><?php echo wp_kses_post($line_total); ?></strong>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</article>

			<div class="elr-detail-grid-2">
				<article class="elr-detail-card">
					<h3><?php echo $is_pickup ? esc_html__('Datos de pickup', 'elrancho') : esc_html__('Dirección de entrega', 'elrancho'); ?></h3>
					<div class="address">
						<?php echo wp_kses_post($address_html ? $address_html : __('Sin dirección disponible.', 'elrancho')); ?>
					</div>
				</article>

				<article class="elr-detail-card">
					<h3><?php esc_html_e('Método de pago', 'elrancho'); ?></h3>
					<div class="pay-method">
						<p><?php echo esc_html($order->get_payment_method_title()); ?></p>
						<?php if ($order->get_billing_email()) : ?>
							<small><?php echo esc_html($order->get_billing_email()); ?></small>
						<?php endif; ?>
					</div>
				</article>
			</div>

			<?php if (!empty($customer_notes)) : ?>
				<article class="elr-detail-card">
					<h3><?php esc_html_e('Actualizaciones del pedido', 'elrancho'); ?></h3>
					<ul class="elr-order-note-list">
						<?php foreach ($customer_notes as $note) : ?>
							<li>
								<strong><?php echo esc_html(wc_format_datetime(wc_string_to_datetime($note->comment_date))); ?></strong>
								<div><?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?></div>
							</li>
						<?php endforeach; ?>
					</ul>
				</article>
			<?php endif; ?>
		</div>

		<aside class="elr-order-detail-side">
			<article class="elr-summary-card">
				<h3><?php esc_html_e('Resumen', 'elrancho'); ?></h3>
				<div class="row"><span><?php esc_html_e('Subtotal', 'elrancho'); ?></span><strong><?php echo wp_kses_post(wc_price($subtotal)); ?></strong></div>
				<div class="row"><span><?php esc_html_e('Envío', 'elrancho'); ?></span><strong><?php echo $shipping_total > 0 ? wp_kses_post(wc_price($shipping_total)) : esc_html__('Gratis', 'elrancho'); ?></strong></div>
				<div class="row"><span><?php esc_html_e('Impuestos', 'elrancho'); ?></span><strong><?php echo wp_kses_post(wc_price($tax_total)); ?></strong></div>
				<div class="divider"></div>
				<div class="row total"><span><?php esc_html_e('Total', 'elrancho'); ?></span><strong><?php echo wp_kses_post(wc_price($total)); ?></strong></div>

				<?php if ($loyalty_points > 0) : ?>
					<div class="loyalty-mini">
						<h4><?php esc_html_e('Lealtad', 'elrancho'); ?></h4>
						<p><?php echo esc_html(sprintf(__('Ganaste %d puntos con esta compra.', 'elrancho'), $loyalty_points)); ?></p>
						<div class="progress"><span style="width:<?php echo esc_attr($progress_pct); ?>%"></span></div>
						<small>
							<?php
							echo $points_left > 0
								? esc_html(sprintf(__('%d puntos para tu siguiente recompensa.', 'elrancho'), $points_left))
								: esc_html__('Ya puedes canjear recompensas.', 'elrancho');
							?>
						</small>
					</div>
				<?php endif; ?>

				<a class="track-btn" href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"><?php esc_html_e('Ver todos mis pedidos', 'elrancho'); ?></a>
				<p class="support"><?php esc_html_e('¿Problemas con tu pedido?', 'elrancho'); ?> <a href="#"><?php esc_html_e('Contactar soporte', 'elrancho'); ?></a></p>
			</article>
		</aside>
	</div>
</section>
