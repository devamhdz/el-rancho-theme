<?php
/**
 * Template para páginas (incluye Cart / Checkout / My Account).
 */

get_header();
?>

<main id="site-main" class="site-main <?php echo function_exists('is_woocommerce') && (is_cart() || is_checkout() || is_account_page()) ? 'woocommerce-page' : ''; ?>">
<div class="container" style="padding-top:2.5rem;padding-bottom:4rem;">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if (!function_exists('is_woocommerce') || (!is_cart() && !is_checkout() && !is_account_page())) : ?>
				<header style="margin-bottom:1.25rem;">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header>
			<?php endif; ?>

			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; endif; ?>

</div>
</main>

<?php get_footer(); ?>
