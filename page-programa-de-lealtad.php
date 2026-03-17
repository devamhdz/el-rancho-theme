<?php
/**
 * Template Name: Programa de Lealtad
 *
 * Página pública del programa Rancho Rewards.
 * Asignar en WP Admin → Páginas → Atributos → Template: "Programa de Lealtad"
 */

defined('ABSPATH') || exit;

$settings     = function_exists('elrancho_loyalty_get_settings') ? elrancho_loyalty_get_settings() : [];
$pts_rate     = intval($settings['points_rate']       ?? 10);
$pts_value    = floatval($settings['point_value']     ?? 0.01);
$tier_s_mult  = floatval($settings['tier_silver_mult']?? 1.25);
$tier_g_mult  = floatval($settings['tier_gold_mult']  ?? 1.5);
$bday_mult    = floatval($settings['bonus_birthday_mult'] ?? 2.0);
$cake_mult    = floatval($settings['cat_mult_cakes']  ?? 1.5);
$reg_bonus    = intval($settings['bonus_registration']?? 200);
$ref_bonus    = intval($settings['bonus_referrer']    ?? 500);
$ref_referred = intval($settings['bonus_referred']    ?? 300);
$tier_s_spend = intval($settings['tier_silver_spend'] ?? 500);
$tier_g_spend = intval($settings['tier_gold_spend']   ?? 1200);
$redeem_min   = intval($settings['redeem_minimum']    ?? 500);

get_header();
?>

<main id="site-main" class="site-main erbl-public-page">

    <!-- ================================================
         HERO
         ================================================ -->
    <section class="erbl-hero">
        <div class="container">
            <div class="erbl-hero__badge">🥐 <?php bloginfo('name'); ?> Rewards</div>
            <h1 class="erbl-hero__title">
                <?php esc_html_e('Gana Pan con Cada Compra', 'elrancho'); ?>
            </h1>
            <p class="erbl-hero__subtitle">
                <?php esc_html_e('Únete gratis, acumula puntos en cada pedido y canjéalos por descuentos reales. Más compras, más beneficios.', 'elrancho'); ?>
            </p>
            <div class="erbl-hero__actions">
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('my-points')); ?>" class="btn btn-primary">
                        <?php esc_html_e('Ver mis puntos', 'elrancho'); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_registration_url()); ?>" class="btn btn-primary">
                        <?php esc_html_e('Unirse gratis', 'elrancho'); ?>
                    </a>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline">
                        <?php esc_html_e('Iniciar sesión', 'elrancho'); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="erbl-hero__stats">
                <div class="erbl-hero__stat">
                    <span class="erbl-hero__stat-value"><?php echo number_format($pts_rate); ?></span>
                    <span class="erbl-hero__stat-label"><?php esc_html_e('puntos por $1 USD', 'elrancho'); ?></span>
                </div>
                <div class="erbl-hero__stat">
                    <span class="erbl-hero__stat-value"><?php echo number_format($reg_bonus); ?></span>
                    <span class="erbl-hero__stat-label"><?php esc_html_e('puntos de bienvenida', 'elrancho'); ?></span>
                </div>
                <div class="erbl-hero__stat">
                    <span class="erbl-hero__stat-value"><?php echo number_format($ref_bonus); ?></span>
                    <span class="erbl-hero__stat-label"><?php esc_html_e('puntos por referido', 'elrancho'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         CÓMO FUNCIONA
         ================================================ -->
    <section class="erbl-how section-sm">
        <div class="container">
            <h2 class="erbl-section-title"><?php esc_html_e('¿Cómo funciona?', 'elrancho'); ?></h2>
            <div class="erbl-steps">
                <div class="erbl-step">
                    <div class="erbl-step__icon">1</div>
                    <h3><?php esc_html_e('Regístrate gratis', 'elrancho'); ?></h3>
                    <p><?php printf(esc_html__('Crea tu cuenta y recibe %d puntos de bienvenida al instante.', 'elrancho'), $reg_bonus); ?></p>
                </div>
                <div class="erbl-step__connector" aria-hidden="true">→</div>
                <div class="erbl-step">
                    <div class="erbl-step__icon">2</div>
                    <h3><?php esc_html_e('Compra y acumula', 'elrancho'); ?></h3>
                    <p><?php printf(esc_html__('Gana %d puntos por cada $1 USD en tus pedidos. Más nivel = más puntos.', 'elrancho'), $pts_rate); ?></p>
                </div>
                <div class="erbl-step__connector" aria-hidden="true">→</div>
                <div class="erbl-step">
                    <div class="erbl-step__icon">3</div>
                    <h3><?php esc_html_e('Canjea descuentos', 'elrancho'); ?></h3>
                    <p><?php printf(esc_html__('Desde %d puntos puedes usarlos como descuento en tu siguiente compra.', 'elrancho'), $redeem_min); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         TIERS
         ================================================ -->
    <section class="erbl-tiers section-sm">
        <div class="container">
            <h2 class="erbl-section-title"><?php esc_html_e('Niveles de membresía', 'elrancho'); ?></h2>
            <p class="erbl-section-subtitle"><?php esc_html_e('Tu nivel sube automáticamente según tu gasto acumulado. Más nivel, más puntos en cada compra.', 'elrancho'); ?></p>
            <div class="erbl-tiers__grid">

                <div class="erbl-tier-card erbl-tier-card--bronze">
                    <div class="erbl-tier-card__emoji">🥉</div>
                    <h3><?php esc_html_e('Bronce', 'elrancho'); ?></h3>
                    <div class="erbl-tier-card__req"><?php esc_html_e('Desde el registro', 'elrancho'); ?></div>
                    <div class="erbl-tier-card__mult">1x <?php esc_html_e('multiplicador', 'elrancho'); ?></div>
                    <ul class="erbl-tier-card__perks">
                        <li><?php printf(esc_html__('%d pts de bienvenida', 'elrancho'), $reg_bonus); ?></li>
                        <li><?php printf(esc_html__('%d pts por cada $1', 'elrancho'), $pts_rate); ?></li>
                        <li><?php esc_html_e('2x puntos en tu semana de cumpleaños', 'elrancho'); ?></li>
                        <li><?php printf(esc_html__('%d pts por cada amigo referido', 'elrancho'), $ref_bonus); ?></li>
                    </ul>
                </div>

                <div class="erbl-tier-card erbl-tier-card--silver">
                    <div class="erbl-tier-card__emoji">🥈</div>
                    <h3><?php esc_html_e('Plata', 'elrancho'); ?></h3>
                    <div class="erbl-tier-card__req"><?php printf(esc_html__('Desde $%s USD acumulados', 'elrancho'), number_format($tier_s_spend)); ?></div>
                    <div class="erbl-tier-card__mult"><?php echo esc_html($tier_s_mult); ?>x <?php esc_html_e('multiplicador', 'elrancho'); ?></div>
                    <ul class="erbl-tier-card__perks">
                        <li><?php esc_html_e('Todo lo de Bronce', 'elrancho'); ?></li>
                        <li><?php printf(esc_html__('%sx puntos en cada compra', 'elrancho'), $tier_s_mult); ?></li>
                        <li><?php esc_html_e('Acceso anticipado a ofertas', 'elrancho'); ?></li>
                        <li><?php esc_html_e('Envío gratis en pedidos especiales', 'elrancho'); ?></li>
                    </ul>
                </div>

                <div class="erbl-tier-card erbl-tier-card--gold">
                    <div class="erbl-tier-card__emoji">🥇</div>
                    <h3><?php esc_html_e('Oro', 'elrancho'); ?></h3>
                    <div class="erbl-tier-card__req"><?php printf(esc_html__('Desde $%s USD acumulados', 'elrancho'), number_format($tier_g_spend)); ?></div>
                    <div class="erbl-tier-card__mult"><?php echo esc_html($tier_g_mult); ?>x <?php esc_html_e('multiplicador', 'elrancho'); ?></div>
                    <ul class="erbl-tier-card__perks">
                        <li><?php esc_html_e('Todo lo de Plata', 'elrancho'); ?></li>
                        <li><?php printf(esc_html__('%sx puntos en cada compra', 'elrancho'), $tier_g_mult); ?></li>
                        <li><?php esc_html_e('Retos exclusivos de Oro', 'elrancho'); ?></li>
                        <li><?php esc_html_e('Atención prioritaria', 'elrancho'); ?></li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- ================================================
         CALCULADORA DE PUNTOS
         ================================================ -->
    <section class="erbl-calculator section-sm">
        <div class="container">
            <h2 class="erbl-section-title"><?php esc_html_e('Calcula tus puntos', 'elrancho'); ?></h2>
            <p class="erbl-section-subtitle"><?php esc_html_e('Simula cuántos puntos ganarías en una compra.', 'elrancho'); ?></p>

            <div class="erbl-calc-card">
                <div class="erbl-calc-fields">

                    <div class="erbl-calc-field">
                        <label for="erbl-calc-amount"><?php esc_html_e('Monto de la compra (USD)', 'elrancho'); ?></label>
                        <div class="erbl-calc-input-wrap">
                            <span class="erbl-calc-prefix">$</span>
                            <input type="number" id="erbl-calc-amount" min="0" step="0.01" value="20" placeholder="0.00">
                        </div>
                    </div>

                    <div class="erbl-calc-field">
                        <label for="erbl-calc-tier"><?php esc_html_e('Tu nivel', 'elrancho'); ?></label>
                        <select id="erbl-calc-tier">
                            <option value="1">🥉 <?php esc_html_e('Bronce (1x)', 'elrancho'); ?></option>
                            <option value="<?php echo esc_attr($tier_s_mult); ?>">🥈 <?php printf(esc_html__('Plata (%sx)', 'elrancho'), $tier_s_mult); ?></option>
                            <option value="<?php echo esc_attr($tier_g_mult); ?>">🥇 <?php printf(esc_html__('Oro (%sx)', 'elrancho'), $tier_g_mult); ?></option>
                        </select>
                    </div>

                    <div class="erbl-calc-field erbl-calc-field--checkbox">
                        <label>
                            <input type="checkbox" id="erbl-calc-cake">
                            <?php printf(esc_html__('¿Es pastel personalizado? (+%sx)', 'elrancho'), $cake_mult); ?>
                        </label>
                        <small><?php esc_html_e('Los pasteles de la categoría especial tienen multiplicador adicional.', 'elrancho'); ?></small>
                    </div>

                </div>

                <div class="erbl-calc-result" id="erbl-calc-result" aria-live="polite">
                    <div class="erbl-calc-result__pts" id="erbl-calc-pts">—</div>
                    <div class="erbl-calc-result__label"><?php esc_html_e('puntos', 'elrancho'); ?></div>
                    <div class="erbl-calc-result__value" id="erbl-calc-value"></div>
                </div>

                <div class="erbl-calc-note">
                    <span class="erbl-calc-note__icon">🎂</span>
                    <span><?php printf(
                        esc_html__('En tu semana de cumpleaños aplica %sx adicional sobre todos tus puntos.', 'elrancho'),
                        $bday_mult
                    ); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         REFERIDOS
         ================================================ -->
    <section class="erbl-referral section-sm">
        <div class="container">
            <div class="erbl-referral__card">
                <div class="erbl-referral__text">
                    <h2><?php esc_html_e('Invita a un amigo', 'elrancho'); ?></h2>
                    <p><?php printf(
                        esc_html__('Comparte tu código único y gana %d puntos cada vez que un amigo haga su primera compra. Tu amigo también recibe %d puntos de bienvenida extra.', 'elrancho'),
                        $ref_bonus,
                        $ref_referred
                    ); ?></p>
                    <?php if (is_user_logged_in()) :
                        $uid      = get_current_user_id();
                        $ref_code = get_user_meta($uid, '_erbl_referral_code', true);
                        if ($ref_code) : ?>
                        <div class="erbl-referral__code-wrap">
                            <span class="erbl-referral__code"><?php echo esc_html($ref_code); ?></span>
                            <button
                                onclick="navigator.clipboard.writeText('<?php echo esc_js(add_query_arg('ref', $ref_code, home_url('/'))); ?>').then(()=>{this.textContent='¡Copiado! ✓';setTimeout(()=>this.textContent='<?php echo esc_js(__('Copiar link', 'elrancho')); ?>',2000)})"
                                class="btn btn-sm">
                                <?php esc_html_e('Copiar link', 'elrancho'); ?>
                            </button>
                        </div>
                        <?php endif;
                    else : ?>
                        <a href="<?php echo esc_url(wp_registration_url()); ?>" class="btn btn-primary">
                            <?php esc_html_e('Regístrate para obtener tu código', 'elrancho'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="erbl-referral__visual" aria-hidden="true">
                    <div class="erbl-referral__bubble erbl-referral__bubble--you">
                        <span>+<?php echo number_format($ref_bonus); ?></span>
                        <small><?php esc_html_e('tú', 'elrancho'); ?></small>
                    </div>
                    <div class="erbl-referral__arrow">↔</div>
                    <div class="erbl-referral__bubble erbl-referral__bubble--friend">
                        <span>+<?php echo number_format($ref_referred); ?></span>
                        <small><?php esc_html_e('tu amigo', 'elrancho'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         FAQ
         ================================================ -->
    <section class="erbl-faq section-sm">
        <div class="container">
            <h2 class="erbl-section-title"><?php esc_html_e('Preguntas frecuentes', 'elrancho'); ?></h2>
            <div class="erbl-faq__list">

                <?php
                $faqs = [
                    [
                        __('¿Cuándo se acreditan mis puntos?', 'elrancho'),
                        __('Los puntos se acreditan automáticamente cuando tu pedido pasa a estado "Procesando". Si tu pedido es cancelado o reembolsado, los puntos se revierten.', 'elrancho'),
                    ],
                    [
                        __('¿Cómo canjeo mis puntos?', 'elrancho'),
                        sprintf(__('En el checkout verás una sección para ingresar cuántos puntos quieres usar. El mínimo para canjear es %d puntos. También puedes usar un QR en tienda física.', 'elrancho'), $redeem_min),
                    ],
                    [
                        __('¿Los puntos caducan?', 'elrancho'),
                        __('Los puntos se mantienen activos mientras hagas al menos una compra cada cierto tiempo. Si tu cuenta queda inactiva por el período configurado, los puntos expiran. Revisa tus movimientos en "Mis Puntos".', 'elrancho'),
                    ],
                    [
                        __('¿Cómo funciona el bono de cumpleaños?', 'elrancho'),
                        sprintf(__('Durante la semana de tu cumpleaños recibes %.0fx los puntos habituales en todas tus compras. Para activarlo debes tener tu fecha de cumpleaños registrada en tu perfil.', 'elrancho'), $bday_mult),
                    ],
                    [
                        __('¿Cómo subo de nivel?', 'elrancho'),
                        sprintf(__('Los niveles se calculan por tu gasto acumulado histórico. Llegas a Plata con $%s USD y a Oro con $%s USD en compras totales.', 'elrancho'), number_format($tier_s_spend), number_format($tier_g_spend)),
                    ],
                    [
                        __('¿Puedo usar puntos en tienda física?', 'elrancho'),
                        __('Sí. Desde "Mis Puntos" en tu cuenta puedes generar un código QR de redención válido por 30 minutos. Muéstraselo al cajero y se descontarán automáticamente.', 'elrancho'),
                    ],
                ];
                foreach ($faqs as $i => $faq) : ?>
                <div class="erbl-faq__item">
                    <button
                        class="erbl-faq__question"
                        aria-expanded="false"
                        aria-controls="erbl-faq-<?php echo $i; ?>"
                        id="erbl-faq-btn-<?php echo $i; ?>">
                        <?php echo esc_html($faq[0]); ?>
                        <span class="erbl-faq__chevron" aria-hidden="true">+</span>
                    </button>
                    <div
                        class="erbl-faq__answer"
                        id="erbl-faq-<?php echo $i; ?>"
                        role="region"
                        aria-labelledby="erbl-faq-btn-<?php echo $i; ?>"
                        hidden>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <!-- ================================================
         CTA FINAL
         ================================================ -->
    <section class="erbl-cta section-sm">
        <div class="container">
            <div class="erbl-cta__card">
                <h2><?php esc_html_e('¿Listo para empezar a ganar?', 'elrancho'); ?></h2>
                <p><?php printf(
                    esc_html__('Regístrate hoy y recibe %d puntos gratis. Sin tarjeta, sin compromiso.', 'elrancho'),
                    $reg_bonus
                ); ?></p>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('my-points')); ?>" class="btn btn-light">
                        <?php esc_html_e('Ver mis puntos', 'elrancho'); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_registration_url()); ?>" class="btn btn-light">
                        <?php esc_html_e('Crear cuenta gratis', 'elrancho'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<style>
/* ================================================
   ESTILOS — Página pública Rancho Rewards
   ================================================ */

/* Hero */
.erbl-hero {
    background: var(--color-background-dark);
    color: #fff;
    padding: 4rem 0 3rem;
    text-align: center;
}
.erbl-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: var(--radius-full);
    padding: 4px 16px;
    font-size: 0.8125rem;
    font-weight: 500;
    letter-spacing: 0.03em;
    margin-bottom: 1.25rem;
    color: var(--color-accent-gold);
}
.erbl-hero__title {
    font-size: clamp(2rem, 5vw, 3.25rem);
    font-weight: 700;
    line-height: 1.15;
    margin: 0 0 1rem;
    color: #fff;
}
.erbl-hero__subtitle {
    font-size: 1.0625rem;
    color: rgba(255,255,255,0.72);
    max-width: 560px;
    margin: 0 auto 2rem;
    line-height: 1.6;
}
.erbl-hero__actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 2.5rem;
}
.erbl-hero__actions .btn-outline {
    background: transparent;
    border: 1.5px solid rgba(255,255,255,0.35);
    color: #fff;
}
.erbl-hero__actions .btn-outline:hover {
    background: rgba(255,255,255,0.08);
}
.erbl-hero__stats {
    display: flex;
    justify-content: center;
    gap: 2.5rem;
    flex-wrap: wrap;
    border-top: 1px solid rgba(255,255,255,0.12);
    padding-top: 2rem;
}
.erbl-hero__stat { text-align: center; }
.erbl-hero__stat-value {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--color-accent-gold);
    line-height: 1;
}
.erbl-hero__stat-label {
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.6);
    margin-top: 4px;
    display: block;
}

/* Secciones */
.erbl-section-title {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 700;
    color: var(--color-text-main);
    text-align: center;
    margin: 0 0 0.5rem;
}
.erbl-section-subtitle {
    text-align: center;
    color: var(--color-text-light);
    font-size: 0.9375rem;
    margin: 0 0 2.5rem;
}

/* Cómo funciona */
.erbl-steps {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 0;
    flex-wrap: wrap;
}
.erbl-step {
    flex: 1;
    min-width: 200px;
    max-width: 280px;
    text-align: center;
    padding: 0 1rem;
}
.erbl-step__icon {
    width: 48px;
    height: 48px;
    background: var(--color-primary);
    color: #fff;
    border-radius: var(--radius-full);
    font-size: 1.125rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}
.erbl-step h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-text-main);
    margin: 0 0 0.5rem;
}
.erbl-step p {
    font-size: 0.875rem;
    color: var(--color-text-light);
    line-height: 1.6;
    margin: 0;
}
.erbl-step__connector {
    font-size: 1.5rem;
    color: var(--color-border-warm);
    padding: 0 0.5rem;
    margin-top: 12px;
    flex-shrink: 0;
}
@media (max-width: 640px) { .erbl-step__connector { display: none; } }

/* Tiers */
.erbl-tiers__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
}
.erbl-tier-card {
    background: var(--color-surface);
    border: 2px solid var(--color-border);
    border-radius: var(--radius-2xl);
    padding: 1.75rem 1.5rem;
    position: relative;
    transition: transform var(--transition), box-shadow var(--transition);
}
.erbl-tier-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}
.erbl-tier-card--silver { border-color: #c0c0c0; }
.erbl-tier-card--gold   { border-color: var(--color-accent-gold); background: #fffcf0; }
.erbl-tier-card__emoji  { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }
.erbl-tier-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-text-main);
    margin: 0 0 0.25rem;
}
.erbl-tier-card__req {
    font-size: 0.8125rem;
    color: var(--color-text-light);
    margin-bottom: 0.5rem;
}
.erbl-tier-card__mult {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-primary);
    margin-bottom: 1rem;
}
.erbl-tier-card__perks {
    list-style: none;
    padding: 0;
    margin: 0;
}
.erbl-tier-card__perks li {
    font-size: 0.875rem;
    color: var(--color-text-light);
    padding: 5px 0;
    border-bottom: 1px solid var(--color-border);
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.erbl-tier-card__perks li::before {
    content: '✓';
    color: var(--color-success);
    font-weight: 600;
    flex-shrink: 0;
}
.erbl-tier-card__perks li:last-child { border-bottom: none; }

/* Calculadora */
.erbl-calculator { background: var(--color-background-warm); }
.erbl-calc-card {
    background: var(--color-surface);
    border: 1.5px solid var(--color-border-warm);
    border-radius: var(--radius-2xl);
    padding: 2rem;
    max-width: 640px;
    margin: 0 auto;
}
.erbl-calc-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 520px) { .erbl-calc-fields { grid-template-columns: 1fr; } }
.erbl-calc-field--checkbox { grid-column: 1 / -1; }
.erbl-calc-field label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--color-text-main);
    margin-bottom: 6px;
}
.erbl-calc-field--checkbox label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    cursor: pointer;
}
.erbl-calc-field--checkbox small {
    display: block;
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin-top: 4px;
    margin-left: 24px;
}
.erbl-calc-input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid var(--color-border-warm);
    border-radius: var(--radius-lg);
    overflow: hidden;
}
.erbl-calc-prefix {
    padding: 0 10px;
    color: var(--color-text-muted);
    font-size: 0.9375rem;
    background: var(--color-background-warm);
    align-self: stretch;
    display: flex;
    align-items: center;
    border-right: 1.5px solid var(--color-border-warm);
}
.erbl-calc-input-wrap input {
    border: none;
    outline: none;
    padding: 0.625rem 0.75rem;
    font-size: 0.9375rem;
    color: var(--color-text-main);
    width: 100%;
    background: var(--color-surface);
}
.erbl-calc-field select {
    width: 100%;
    border: 1.5px solid var(--color-border-warm);
    border-radius: var(--radius-lg);
    padding: 0.625rem 0.75rem;
    font-size: 0.9375rem;
    color: var(--color-text-main);
    background: var(--color-surface);
    outline: none;
}
.erbl-calc-result {
    background: var(--color-background-warm);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 1rem;
}
.erbl-calc-result__pts {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--color-primary);
    line-height: 1;
}
.erbl-calc-result__label {
    font-size: 0.875rem;
    color: var(--color-text-light);
    margin-top: 4px;
}
.erbl-calc-result__value {
    font-size: 0.8125rem;
    color: var(--color-text-muted);
    margin-top: 6px;
}
.erbl-calc-note {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #fff8e6;
    border: 1px solid #f0d080;
    border-radius: var(--radius-lg);
    padding: 10px 14px;
    font-size: 0.8125rem;
    color: #7a5c00;
    line-height: 1.5;
}
.erbl-calc-note__icon { flex-shrink: 0; font-size: 1rem; }

/* Referidos */
.erbl-referral__card {
    background: var(--color-surface);
    border: 1.5px solid var(--color-border-warm);
    border-radius: var(--radius-2xl);
    padding: 2.5rem;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    align-items: center;
}
@media (max-width: 640px) {
    .erbl-referral__card { grid-template-columns: 1fr; }
    .erbl-referral__visual { display: none; }
}
.erbl-referral__card h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-text-main);
    margin: 0 0 0.75rem;
}
.erbl-referral__card p {
    color: var(--color-text-light);
    font-size: 0.9375rem;
    line-height: 1.6;
    margin: 0 0 1.25rem;
}
.erbl-referral__code-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.erbl-referral__code {
    font-family: monospace;
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: var(--color-primary);
    background: var(--color-background-warm);
    padding: 6px 16px;
    border-radius: var(--radius-lg);
    border: 1.5px solid var(--color-border-warm);
}
.btn-sm {
    padding: 6px 14px;
    font-size: 0.8125rem;
    border-radius: var(--radius-lg);
    background: var(--color-primary);
    color: #fff;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: background var(--transition);
    text-decoration: none;
    display: inline-block;
}
.btn-sm:hover { background: var(--color-primary-dark); color: #fff; }
.erbl-referral__visual {
    display: flex;
    align-items: center;
    gap: 16px;
}
.erbl-referral__bubble {
    width: 80px;
    height: 80px;
    border-radius: var(--radius-full);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.erbl-referral__bubble--you {
    background: var(--color-primary);
    color: #fff;
}
.erbl-referral__bubble--friend {
    background: var(--color-accent-gold);
    color: #fff;
}
.erbl-referral__bubble span { font-size: 1rem; line-height: 1; }
.erbl-referral__bubble small { font-size: 0.6875rem; font-weight: 400; opacity: 0.85; }
.erbl-referral__arrow { font-size: 1.5rem; color: var(--color-border-warm); }

/* FAQ */
.erbl-faq__list {
    max-width: 720px;
    margin: 0 auto;
}
.erbl-faq__item {
    border-bottom: 1px solid var(--color-border);
}
.erbl-faq__question {
    width: 100%;
    background: none;
    border: none;
    padding: 1.1rem 0;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--color-text-main);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    text-align: left;
}
.erbl-faq__question:hover { color: var(--color-primary); }
.erbl-faq__chevron {
    font-size: 1.25rem;
    font-weight: 400;
    flex-shrink: 0;
    color: var(--color-text-muted);
    transition: transform var(--transition);
}
.erbl-faq__question[aria-expanded="true"] .erbl-faq__chevron {
    transform: rotate(45deg);
    color: var(--color-primary);
}
.erbl-faq__answer {
    padding: 0 0 1.1rem;
}
.erbl-faq__answer p {
    font-size: 0.9rem;
    color: var(--color-text-light);
    line-height: 1.65;
    margin: 0;
}

/* CTA final */
.erbl-cta { background: var(--color-background-warm); }
.erbl-cta__card {
    background: var(--color-background-dark);
    border-radius: var(--radius-2xl);
    padding: 3rem 2rem;
    text-align: center;
    color: #fff;
}
.erbl-cta__card h2 {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 700;
    color: #fff;
    margin: 0 0 0.75rem;
}
.erbl-cta__card p {
    color: rgba(255,255,255,0.72);
    font-size: 1rem;
    margin: 0 0 1.75rem;
}
.btn-light {
    background: #fff;
    color: var(--color-primary);
    border: none;
    padding: 0.75rem 2rem;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background var(--transition);
}
.btn-light:hover { background: #f5f5f5; color: var(--color-primary); }

/* section-sm spacing helper */
.erbl-public-page .section-sm { padding: 4rem 0; }
</style>

<script>
(function() {
    // Calculadora
    var ptsRate  = <?php echo json_encode($pts_rate); ?>;
    var ptsValue = <?php echo json_encode($pts_value); ?>;
    var cakeMult = <?php echo json_encode($cake_mult); ?>;

    function calcUpdate() {
        var amount   = parseFloat(document.getElementById('erbl-calc-amount').value) || 0;
        var tierMult = parseFloat(document.getElementById('erbl-calc-tier').value)   || 1;
        var isCake   = document.getElementById('erbl-calc-cake').checked;
        var mult     = tierMult * (isCake ? cakeMult : 1);
        var points   = Math.floor(amount * ptsRate * mult);
        var value    = (points * ptsValue).toFixed(2);

        document.getElementById('erbl-calc-pts').textContent   = points > 0 ? points.toLocaleString() : '—';
        document.getElementById('erbl-calc-value').textContent = points > 0 ? '≈ $' + value + ' USD' : '';
    }

    document.getElementById('erbl-calc-amount').addEventListener('input', calcUpdate);
    document.getElementById('erbl-calc-tier').addEventListener('change', calcUpdate);
    document.getElementById('erbl-calc-cake').addEventListener('change', calcUpdate);
    calcUpdate();

    // FAQ accordion
    document.querySelectorAll('.erbl-faq__question').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var expanded = this.getAttribute('aria-expanded') === 'true';
            var answerId = this.getAttribute('aria-controls');
            var answer   = document.getElementById(answerId);
            this.setAttribute('aria-expanded', !expanded);
            if (expanded) {
                answer.hidden = true;
            } else {
                answer.hidden = false;
            }
        });
    });
})();
</script>

<?php get_footer(); ?>
