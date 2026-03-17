<?php
/**
 * My Orders
 *
 * Override de: woocommerce/templates/myaccount/orders.php
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders);

$first_name = '';
if (is_user_logged_in()) {
	$current_user = wp_get_current_user();
	$first_name = !empty($current_user->first_name) ? $current_user->first_name : $current_user->display_name;
}

$latest_order = null;
$pending_count = 0;
if ($has_orders && !empty($customer_orders->orders)) {
	$latest_order = wc_get_order($customer_orders->orders[0]);
	foreach ($customer_orders->orders as $order_id) {
		$o = wc_get_order($order_id);
		if (!$o) {
			continue;
		}
		if (in_array($o->get_status(), ['pending', 'on-hold', 'failed'], true)) {
			$pending_count++;
		}
	}
}

// Rancho Rewards — datos dinámicos del sistema v2.0
$loyalty_points = function_exists('erbl_get_user_points') && is_user_logged_in()
	? erbl_get_user_points( get_current_user_id() )
	: 0;

// Tier y progreso al siguiente nivel desde el sistema dinámico
$erbl_tier        = function_exists('erbl_get_user_tier') && is_user_logged_in() ? erbl_get_user_tier( get_current_user_id() ) : 'bronze';
$erbl_tier_label  = function_exists('erbl_tier_label') ? erbl_tier_label( $erbl_tier ) : '🥉 Bronce';
$erbl_settings    = function_exists('elrancho_loyalty_get_settings') ? elrancho_loyalty_get_settings() : [];
$erbl_spend       = function_exists('erbl_get_user_total_spend') && is_user_logged_in() ? erbl_get_user_total_spend( get_current_user_id() ) : 0;

// Calcular progreso al siguiente tier
if ( $erbl_tier === 'bronze' ) {
	$target_spend = floatval( $erbl_settings['tier_silver_spend'] ?? 500 );
	$next_tier_label = 'Plata 🥈';
} elseif ( $erbl_tier === 'silver' ) {
	$target_spend = floatval( $erbl_settings['tier_gold_spend'] ?? 1200 );
	$next_tier_label = 'Oro 🥇';
} else {
	$target_spend    = 0;
	$next_tier_label = '';
}
$progress_pct = ( $target_spend > 0 ) ? min( 100, intval( round( ( $erbl_spend / $target_spend ) * 100 ) ) ) : 100;
$points_left  = ( $target_spend > 0 ) ? max( 0, $target_spend - $erbl_spend ) : 0;
?>

<section class="elr-orders-page" aria-label="<?php esc_attr_e('Historial de pedidos', 'elrancho'); ?>">
	<header class="elr-orders-hero">
		<div class="elr-orders-hero-copy">
			<?php if (!empty($first_name)) : ?>
				<div class="elr-orders-welcome"><?php echo esc_html(sprintf(__('Bienvenido de nuevo, %s', 'elrancho'), strtoupper($first_name))); ?></div>
			<?php endif; ?>
			<h2><?php esc_html_e('Mis Pedidos', 'elrancho'); ?></h2>
			<p><?php esc_html_e('Gestiona tus entregas y revisa el estado de tus compras recientes.', 'elrancho'); ?></p>
		</div>
	</header>

	<div class="elr-orders-layout">
		<div class="elr-orders-main">
			<div class="elr-orders-card">
				<div class="elr-orders-card-head">
					<h3><?php esc_html_e('Compras Recientes', 'elrancho'); ?></h3>
					<div class="elr-orders-filters" aria-hidden="true">
						<span class="active"><?php esc_html_e('Todos', 'elrancho'); ?></span>
						<span><?php echo esc_html(sprintf(__('Pendientes (%d)', 'elrancho'), $pending_count)); ?></span>
					</div>
				</div>

				<?php if ($has_orders) : ?>
					<div class="elr-orders-table-wrap">
						<table class="elr-orders-table">
							<thead>
								<tr>
									<th><?php esc_html_e('Pedido', 'elrancho'); ?></th>
									<th><?php esc_html_e('Fecha', 'elrancho'); ?></th>
									<th><?php esc_html_e('Estado', 'elrancho'); ?></th>
									<th><?php esc_html_e('Total', 'elrancho'); ?></th>
									<th class="txt-right"><?php esc_html_e('Acción', 'elrancho'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($customer_orders->orders as $customer_order) {
									$order = wc_get_order($customer_order);
									if (!$order) {
										continue;
									}

									$status = $order->get_status();
									$status_label = wc_get_order_status_name($status);
									$status_class = 'is-default';
									if (in_array($status, ['processing', 'on-hold', 'pending'], true)) {
										$status_class = 'is-processing';
									} elseif ($status === 'completed') {
										$status_class = 'is-completed';
									} elseif (in_array($status, ['shipped'], true)) {
										$status_class = 'is-shipped';
									} elseif (in_array($status, ['failed', 'cancelled', 'refunded'], true)) {
										$status_class = 'is-cancelled';
									}

									$actions = wc_get_account_orders_actions($order);
									$primary_action = null;
									if (isset($actions['view'])) {
										$primary_action = $actions['view'];
									} elseif (!empty($actions)) {
										$primary_action = reset($actions);
									}
									?>
									<tr>
										<td data-title="<?php esc_attr_e('Pedido', 'elrancho'); ?>" class="col-order"><strong>#<?php echo esc_html($order->get_order_number()); ?></strong></td>
										<td data-title="<?php esc_attr_e('Fecha', 'elrancho'); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></td>
										<td data-title="<?php esc_attr_e('Estado', 'elrancho'); ?>">
											<span class="elr-order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
										</td>
										<td data-title="<?php esc_attr_e('Total', 'elrancho'); ?>" class="col-total"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></td>
										<td data-title="<?php esc_attr_e('Acción', 'elrancho'); ?>" class="txt-right">
											<?php if (!empty($primary_action)) : ?>
												<a class="elr-order-action" href="<?php echo esc_url($primary_action['url']); ?>">
													<?php echo esc_html($primary_action['name']); ?>
												</a>
											<?php else : ?>
												<span class="elr-order-action muted">-</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>

					<?php if (1 < $customer_orders->max_num_pages) : ?>
						<div class="elr-orders-more">
							<?php if (1 !== $current_page) : ?>
								<a class="button" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>"><?php esc_html_e('Anteriores', 'woocommerce'); ?></a>
							<?php endif; ?>
							<?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
								<a class="button" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>"><?php esc_html_e('Mostrar más pedidos', 'elrancho'); ?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="elr-orders-empty">
						<p><?php esc_html_e('Aún no has realizado pedidos.', 'elrancho'); ?></p>
						<a class="button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Ir a la tienda', 'elrancho'); ?></a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<aside class="elr-orders-side">
			<?php if ($latest_order instanceof WC_Order) : ?>
				<article class="elr-last-order">
					<h4><?php esc_html_e('Último pedido', 'elrancho'); ?></h4>
					<div class="row">
						<div class="icon" aria-hidden="true">🚚</div>
						<div>
							<p class="title"><?php echo esc_html(wc_get_order_status_name($latest_order->get_status())); ?></p>
							<p class="meta"><?php echo esc_html(sprintf(__('Pedido #%s • %s', 'elrancho'), $latest_order->get_order_number(), wc_format_datetime($latest_order->get_date_created()))); ?></p>
						</div>
					</div>
					<a class="track-btn" href="<?php echo esc_url($latest_order->get_view_order_url()); ?>"><?php esc_html_e('Ver pedido', 'elrancho'); ?></a>
				</article>
			<?php endif; ?>

			<article class="elr-rewards-panel">
				<div class="head">
					<h4><?php esc_html_e('Rancho Rewards', 'elrancho'); ?></h4>
					<span aria-hidden="true"><?php echo $erbl_tier === 'gold' ? '🥇' : ( $erbl_tier === 'silver' ? '🥈' : '🥉' ); ?></span>
				</div>
				<div style="font-size:11px;color:#7D6B60;margin-bottom:6px;"><?php echo esc_html($erbl_tier_label); ?></div>
				<div class="points-row">
					<strong><?php echo esc_html(number_format($loyalty_points)); ?></strong>
					<span><?php esc_html_e('pts', 'elrancho'); ?></span>
				</div>
				<?php if ( $target_spend > 0 ) : ?>
				<div class="progress" title="<?php echo esc_attr($progress_pct); ?>% hacia <?php echo esc_attr($next_tier_label); ?>">
					<span style="width:<?php echo esc_attr($progress_pct); ?>%"></span>
				</div>
				<p class="hint">
					<?php if ( $points_left > 0 ) :
						echo esc_html( sprintf( '$%.0f USD más para %s', $points_left, $next_tier_label ) );
					else :
						esc_html_e('¡Nivel máximo alcanzado!', 'elrancho');
					endif; ?>
				</p>
				<?php endif; ?>
				<a class="redeem-link" href="<?php echo esc_url(wc_get_account_endpoint_url('my-points')); ?>"><?php esc_html_e('Ver mis puntos', 'elrancho'); ?></a>
			</article>

			<article class="elr-support-panel">
				<h4><?php esc_html_e('¿Necesitas ayuda?', 'elrancho'); ?></h4>
				<p><?php esc_html_e('¿Dudas sobre un pedido o entrega? Nuestro equipo está para ayudarte.', 'elrancho'); ?></p>
				<a href="#" class="support-link"><?php esc_html_e('Soporte en vivo', 'elrancho'); ?></a>
			</article>
		</aside>
	</div>
</section>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>
