<?php
/**
 * El template principal de fallback
 */

get_header();
?>

<main id="site-main" class="site-main">
<div class="container" style="padding-top:3rem;padding-bottom:4rem;">

    <?php if (have_posts()) : ?>

        <?php if (is_home() && !is_front_page()) : ?>
            <header style="margin-bottom:2rem;">
                <h1><?php single_post_title(); ?></h1>
            </header>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('product-card'); ?>>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="product-card-image" style="aspect-ratio:16/9;">
                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?></a>
                    </div>
                <?php endif; ?>
                <div class="product-card-body">
                    <h2 style="font-size:1.125rem;margin-bottom:0.5rem;"><a href="<?php the_permalink(); ?>" style="color:var(--color-text-main);"><?php the_title(); ?></a></h2>
                    <p style="font-size:0.875rem;color:var(--color-text-light);"><?php the_excerpt(); ?></p>
                </div>
            </article>
        <?php endwhile; ?>
        </div>

        <div style="margin-top:2rem;">
            <?php the_posts_pagination(); ?>
        </div>

    <?php else : ?>
        <div style="text-align:center;padding:4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--color-border-warm)" style="margin:0 auto 1.5rem;display:block;">
                <path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/>
            </svg>
            <h2><?php esc_html_e('No hay contenido disponible', 'elrancho'); ?></h2>
            <p style="color:var(--color-text-light);"><?php esc_html_e('Vuelve pronto, ¡hay cosas deliciosas en camino!', 'elrancho'); ?></p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary" style="margin-top:1.5rem;display:inline-flex;"><?php esc_html_e('Ir al inicio', 'elrancho'); ?></a>
        </div>
    <?php endif; ?>

</div>
</main>

<?php get_footer(); ?>
