<?php
/**
 * My Account Navigation
 *
 * Override de: woocommerce/templates/myaccount/navigation.php
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$display_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
$loyalty_points = function_exists('erbl_get_user_points') && is_user_logged_in()
	? erbl_get_user_points(get_current_user_id())
	: 0;
$user_tier = function_exists('erbl_get_user_tier') && is_user_logged_in()
	? erbl_get_user_tier(get_current_user_id())
	: 'bronze';
$tier_label = function_exists('erbl_tier_label') ? erbl_tier_label($user_tier) : '';
?>

<?php do_action('woocommerce_before_account_navigation'); ?>

<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e('Navegación de cuenta', 'elrancho'); ?>">
	<div class="elr-account-user-card">
		<div class="avatar"><?php echo esc_html(strtoupper(substr($display_name, 0, 1))); ?></div>
		<div class="meta">
			<strong><?php echo esc_html($display_name); ?></strong>
			<small>
				<?php
				echo $loyalty_points > 0
					? esc_html(sprintf(__('%d puntos', 'elrancho'), $loyalty_points))
					: esc_html__('Cliente', 'elrancho');
				?>
			</small>
			<?php if ($tier_label) : ?>
			<small style="display:block;margin-top:2px;opacity:0.8;"><?php echo esc_html($tier_label); ?></small>
			<?php endif; ?>
		</div>
	</div>

	<ul>
		<?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
			<li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>">
				<a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" <?php echo wc_is_current_account_menu_item($endpoint) ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html($label); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action('woocommerce_after_account_navigation'); ?>
