<?php
/**
 * My Account
 *
 * Override de: woocommerce/templates/myaccount/my-account.php
 */

defined('ABSPATH') || exit;
?>

<section class="elr-account-layout-wrap">
	<div class="elr-account-layout">
		<?php do_action('woocommerce_account_navigation'); ?>

		<div class="woocommerce-MyAccount-content">
			<?php do_action('woocommerce_account_content'); ?>
		</div>
	</div>
</section>
