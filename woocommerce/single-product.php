<?php
/**
 * Template: Página de Producto Individual
 * Basado fielmente en el diseño bakery_product_detail_screen
 */

get_header();
the_post();
global $product;
if (!$product) { get_footer(); exit; }

$avg          = $product->get_average_rating();
$count        = $product->get_review_count();
$stock        = $product->get_stock_quantity();
$in_stock     = $product->is_in_stock();
$is_simple    = $product->is_type('simple');
$supports_qty = !$product->is_sold_individually();
$short_desc   = $product->get_short_description();
$bakers_note  = get_post_meta(get_the_ID(), '_bakers_note', true);
$allergens    = get_post_meta(get_the_ID(), '_allergens', true);
$nutrition    = get_post_meta(get_the_ID(), '_nutrition', true);

// Badge
$badge = '';
if (!$in_stock)               $badge = __('Agotado', 'elrancho');
elseif ($product->is_on_sale())   $badge = __('Oferta', 'elrancho');
elseif ($product->is_featured())  $badge = __('Bestseller', 'elrancho');

// Imágenes
$main_id   = $product->get_image_id();
$gallery   = $product->get_gallery_image_ids();
$all_imgs  = array_values(array_filter(array_merge([$main_id], $gallery)));
?>
<!-- Estilos de la página de producto -->
<style id="pd-styles">
/* ── Product Detail Layout ── */
.pd-wrap{display:grid;grid-template-columns:1fr 1fr;gap:3.5rem;align-items:start;padding:2rem 0 4rem}
@media(max-width:900px){.pd-wrap{grid-template-columns:1fr;gap:2rem}}

/* Breadcrumb */
.pd-bc{display:flex;align-items:center;gap:.5rem;font-size:.875rem;padding:1.25rem 0 .25rem;color:var(--color-text-muted)}
.pd-bc a{color:var(--color-text-muted);transition:color .2s}.pd-bc a:hover{color:var(--color-primary)}
.pd-bc .sep{opacity:.4}.pd-bc .cur{color:var(--color-text-main);font-weight:600}

/* Gallery */
.pd-gallery{display:flex;flex-direction:column;gap:.875rem}
.pd-main{position:relative;aspect-ratio:1;border-radius:1rem;overflow:hidden;background:var(--color-background-warm);box-shadow:0 4px 24px rgba(74,59,50,.09)}
.pd-main img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease}
.pd-main:hover img{transform:scale(1.04)}
.pd-badge{position:absolute;top:1rem;left:1rem;z-index:2;background:rgba(255,255,255,.92);backdrop-filter:blur(6px);color:var(--color-primary);font-size:.7rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;padding:.3rem .85rem;border-radius:9999px;box-shadow:0 1px 6px rgba(0,0,0,.08)}
.pd-thumbs{display:grid;grid-template-columns:repeat(4,1fr);gap:.625rem}
.pd-thumb{aspect-ratio:1;border-radius:.625rem;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:border-color .2s,box-shadow .2s;background:var(--color-background-warm)}
.pd-thumb img{width:100%;height:100%;object-fit:cover}
.pd-thumb.active{border-color:var(--color-primary);box-shadow:0 0 0 3px rgba(184,20,23,.13)}
.pd-thumb:hover:not(.active){border-color:rgba(184,20,23,.35)}

/* Info col */
.pd-info{display:flex;flex-direction:column}
.pd-title{font-size:clamp(1.75rem,3.5vw,2.5rem);font-weight:800;line-height:1.1;color:var(--color-text-main);margin:0 0 .75rem}
.pd-meta{display:flex;align-items:center;gap:.875rem;flex-wrap:wrap;margin-bottom:1.25rem}
.pd-stars{display:flex;align-items:center;gap:.15rem}
.pd-stars span{font-size:1rem}
.pd-review-txt{font-size:.875rem;color:var(--color-text-muted)}
.pd-review-txt:hover{text-decoration:underline;cursor:pointer}
.pd-stock{font-size:.8rem;font-weight:700;padding:.2rem .75rem;border-radius:9999px}
.pd-stock.in{background:rgba(45,122,62,.10);color:#2d7a3e}
.pd-stock.out{background:rgba(184,20,23,.08);color:var(--color-primary)}
.pd-price-row{display:flex;align-items:baseline;gap:.5rem;margin-bottom:1.25rem}
.pd-price-num{font-size:2rem;font-weight:800;color:var(--color-primary)}
.pd-price-unit{font-size:1.05rem;color:var(--color-text-muted)}
.pd-desc{font-size:1rem;line-height:1.75;color:var(--color-text-light);margin:0}

/* Purchase area */
.pd-purchase{border-top:1px solid var(--color-border);border-bottom:1px solid var(--color-border);padding:1.5rem 0;margin:1.5rem 0;display:flex;flex-direction:column;gap:1.25rem}
.pd-qty-row{display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap}
.pd-qty-label{font-weight:700;color:var(--color-text-main);font-size:.9375rem}
.pd-qty-ctrl{display:flex;align-items:center;border:1.5px solid var(--color-border-warm);border-radius:.625rem;overflow:hidden;background:#fff}
.pd-qbtn{width:2.75rem;height:2.75rem;display:flex;align-items:center;justify-content:center;background:transparent;border:none;cursor:pointer;color:var(--color-text-main);transition:all .2s}
.pd-qbtn:hover{color:var(--color-primary);background:var(--color-background-warm)}
.pd-qinput{width:3rem;text-align:center;border:none;border-left:1px solid var(--color-border-warm);border-right:1px solid var(--color-border-warm);padding:.5rem 0;font-size:1rem;font-weight:700;color:var(--color-text-main);background:transparent;-moz-appearance:textfield;outline:none;font-family:var(--font-family-main)}
.pd-qinput::-webkit-inner-spin-button{display:none}
.pd-avail{font-size:.875rem;color:var(--color-text-muted)}
.pd-cart-row{display:flex;align-items:stretch;gap:.75rem}
.pd-atc{flex:1;display:flex;align-items:center;justify-content:center;gap:.625rem;background:var(--color-primary);color:#fff;border:none;border-radius:.875rem;padding:1rem 1.5rem;font-size:1.0625rem;font-weight:700;cursor:pointer;font-family:var(--font-family-main);box-shadow:0 4px 16px rgba(184,20,23,.22);transition:all .2s;min-height:3.5rem;text-decoration:none}
.pd-atc:hover{background:var(--color-primary-dark);color:#fff;transform:translateY(-1px);box-shadow:0 6px 20px rgba(184,20,23,.3)}
.pd-atc:disabled{opacity:.65;pointer-events:none}
.pd-native-atc{flex:1}
.pd-native-atc form.cart{margin:0}
.pd-native-atc .single_add_to_cart_button{width:100%;min-height:3.5rem;border:none;border-radius:.875rem;background:var(--color-primary);color:#fff;font-size:1.0625rem;font-weight:700;font-family:var(--font-family-main);padding:1rem 1.5rem;box-shadow:0 4px 16px rgba(184,20,23,.22);transition:all .2s}
.pd-native-atc .single_add_to_cart_button:hover{background:var(--color-primary-dark);color:#fff;transform:translateY(-1px);box-shadow:0 6px 20px rgba(184,20,23,.3)}
.pd-wl{width:3.5rem;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--color-border-warm);border-radius:.875rem;background:#fff;color:var(--color-text-muted);cursor:pointer;transition:all .2s;flex-shrink:0}
.pd-wl:hover,.pd-wl.active{border-color:var(--color-primary);color:var(--color-primary)}
.pd-wl.active svg{fill:var(--color-primary)}

/* Accordions */
.pd-accordions{display:flex;flex-direction:column;gap:.75rem}
.pd-acc{border:1.5px solid var(--color-border);border-radius:.875rem;overflow:hidden;background:#fff;transition:border-color .2s,box-shadow .2s}
.pd-acc[open]{border-color:rgba(184,20,23,.2);box-shadow:0 0 0 3px rgba(184,20,23,.06)}
.pd-acc>summary{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;font-size:.9375rem;font-weight:700;color:var(--color-text-main);cursor:pointer;list-style:none;user-select:none}
.pd-acc>summary::-webkit-details-marker{display:none}
.pd-acc-icon{color:var(--color-text-muted);transition:transform .25s}
.pd-acc[open] .pd-acc-icon{transform:rotate(180deg)}
.pd-acc-body{padding:1rem 1.25rem 1.25rem;font-size:.9rem;color:var(--color-text-light);line-height:1.7;border-top:1px solid var(--color-border)}

/* Baker's note */
.pd-note{background:var(--color-background-warm);border:1px solid var(--color-border-warm);border-radius:.875rem;padding:1.125rem 1.25rem;margin-top:.875rem}
.pd-note-title{display:flex;align-items:center;gap:.4rem;font-size:.875rem;font-weight:800;color:var(--color-primary);margin-bottom:.5rem}
.pd-note p{font-size:.875rem;font-style:italic;color:var(--color-text-light);margin:0;line-height:1.65}

/* Related */
.pd-related{padding:2.5rem 0 4rem}
.pd-related-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.pd-related-hd h2{font-size:1.5rem;font-weight:800;margin:0}
.pd-rel-nav{display:flex;gap:.5rem}
.pd-rnbtn{width:2.25rem;height:2.25rem;border:1.5px solid var(--color-border-warm);border-radius:.5rem;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--color-text-main);transition:all .2s}
.pd-rnbtn:hover{border-color:var(--color-primary);color:var(--color-primary)}
.pd-rel-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem}
@media(max-width:900px){.pd-rel-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.pd-rel-grid{gap:.75rem}}
.pd-rel-card{text-decoration:none;display:block}
.pd-rel-img{width:100%;aspect-ratio:1;border-radius:.875rem;overflow:hidden;background:var(--color-background-warm);margin-bottom:.75rem}
.pd-rel-img img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
.pd-rel-card:hover .pd-rel-img img{transform:scale(1.04)}
.pd-rel-cat{font-size:.8125rem;color:var(--color-text-muted);margin-bottom:.2rem}
.pd-rel-name{font-size:.9375rem;font-weight:700;color:var(--color-text-main);margin-bottom:.25rem}
.pd-rel-price{font-size:1rem;font-weight:800;color:var(--color-primary)}

@keyframes pd-spin{to{transform:rotate(360deg)}}
</style>

<main id="site-main" class="site-main">
<div class="container">

  <!-- Breadcrumb -->
  <nav class="pd-bc" aria-label="Breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'elrancho'); ?></a>
    <span class="sep">/</span>
    <?php
    $cats = wc_get_product_terms(get_the_ID(), 'product_cat', ['orderby' => 'parent', 'order' => 'ASC']);
    if ($cats && !is_wp_error($cats)) {
        $cat = end($cats);
        echo '<a href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->name) . '</a>';
        echo '<span class="sep">/</span>';
    }
    ?>
    <span class="cur"><?php the_title(); ?></span>
  </nav>

  <div class="pd-wrap" itemscope itemtype="https://schema.org/Product">

    <!-- ─── GALERÍA ─── -->
    <div class="pd-gallery">
      <div class="pd-main">
        <?php if ($badge) : ?>
          <div class="pd-badge"><?php echo esc_html($badge); ?></div>
        <?php endif; ?>
        <?php if ($main_id) :
          $src = wp_get_attachment_image_url($main_id, 'large');
          $alt = get_post_meta($main_id, '_wp_attachment_image_alt', true) ?: get_the_title();
        ?>
          <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr($alt); ?>" id="pd-main-img" itemprop="image">
        <?php else : ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><?php echo wc_placeholder_img('large'); ?></div>
        <?php endif; ?>
      </div>

      <?php if (count($all_imgs) > 1) : ?>
        <div class="pd-thumbs">
          <?php foreach ($all_imgs as $i => $iid) :
            if (!$iid) continue;
            $tsrc = wp_get_attachment_image_url($iid, 'thumbnail');
            $fsrc = wp_get_attachment_image_url($iid, 'large');
            $talt = get_post_meta($iid, '_wp_attachment_image_alt', true) ?: get_the_title();
          ?>
            <div class="pd-thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                 data-full="<?php echo esc_url($fsrc); ?>"
                 data-alt="<?php echo esc_attr($talt); ?>"
                 onclick="pdSwitch(this)" role="button" tabindex="0"
                 onkeydown="if(event.key==='Enter')pdSwitch(this)">
              <img src="<?php echo esc_url($tsrc); ?>" alt="<?php echo esc_attr($talt); ?>" loading="lazy">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ─── INFO ─── -->
    <div class="pd-info">

      <h1 class="pd-title" itemprop="name"><?php the_title(); ?></h1>

      <div class="pd-meta">
        <?php if ($avg > 0) : ?>
          <div class="pd-stars" aria-label="<?php printf(esc_attr__('%s de 5', 'elrancho'), $avg); ?>">
            <?php for ($i = 1; $i <= 5; $i++) :
              echo '<span style="color:' . ($i <= round($avg) ? '#f59e0b' : '#e2d5c3') . ';" aria-hidden="true">★</span>';
            endfor; ?>
          </div>
          <span class="pd-review-txt"><?php echo esc_html($avg); ?> (<?php echo intval($count); ?> <?php esc_html_e('reseñas', 'elrancho'); ?>)</span>
        <?php endif; ?>
        <span class="pd-stock <?php echo $in_stock ? 'in' : 'out'; ?>">
          <?php echo $in_stock ? esc_html__('En Stock', 'elrancho') : esc_html__('Agotado', 'elrancho'); ?>
        </span>
      </div>

      <div class="pd-price-row" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
        <span class="pd-price-num"><?php echo wc_price($product->get_price()); ?></span>
        <span class="pd-price-unit"><?php esc_html_e('/ unidad', 'elrancho'); ?></span>
        <meta itemprop="price" content="<?php echo esc_attr($product->get_price()); ?>">
        <meta itemprop="priceCurrency" content="<?php echo esc_attr(get_woocommerce_currency()); ?>">
        <link itemprop="availability" href="<?php echo $in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>">
      </div>

      <?php if ($short_desc) : ?>
        <p class="pd-desc"><?php echo wp_kses_post($short_desc); ?></p>
      <?php endif; ?>

      <!-- Purchase area -->
      <div class="pd-purchase">
        <?php if ($is_simple && $supports_qty && $product->is_purchasable() && $in_stock) : ?>
          <div class="pd-qty-row">
            <span class="pd-qty-label"><?php esc_html_e('Cantidad', 'elrancho'); ?></span>
            <div class="pd-qty-ctrl">
              <button class="pd-qbtn" id="pd-minus" type="button" aria-label="Reducir">
                <svg width="14" height="2" viewBox="0 0 14 2"><line x1="0" y1="1" x2="14" y2="1" stroke="currentColor" stroke-width="2"/></svg>
              </button>
              <input class="pd-qinput" id="pd-qty" type="number" value="1" min="1" max="<?php echo $stock ? intval($stock) : 99; ?>" readonly>
              <button class="pd-qbtn" id="pd-plus" type="button" aria-label="Aumentar">
                <svg width="14" height="14" viewBox="0 0 14 14"><line x1="7" y1="0" x2="7" y2="14" stroke="currentColor" stroke-width="2"/><line x1="0" y1="7" x2="14" y2="7" stroke="currentColor" stroke-width="2"/></svg>
              </button>
            </div>
            <?php if ($stock) : ?>
              <span class="pd-avail"><?php printf(esc_html__('%d disponibles hoy', 'elrancho'), $stock); ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="pd-cart-row">
          <?php if ($is_simple && $product->is_purchasable() && $in_stock) : ?>
            <button
              class="pd-atc add-to-cart-btn add_to_cart_button ajax_add_to_cart"
              id="pd-atc"
              data-product-id="<?php echo esc_attr($product->get_id()); ?>"
              data-product_id="<?php echo esc_attr($product->get_id()); ?>"
              data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
              data-product-type="<?php echo esc_attr($product->get_type()); ?>"
              data-quantity="1"
              type="button">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
              </svg>
              <?php esc_html_e('Agregar al Carrito', 'elrancho'); ?>
            </button>
          <?php elseif ($product->is_purchasable() && $in_stock) : ?>
            <div class="pd-native-atc">
              <?php woocommerce_template_single_add_to_cart(); ?>
            </div>
          <?php else : ?>
            <span class="pd-atc" style="background:var(--color-text-muted);cursor:default;"><?php esc_html_e('No disponible', 'elrancho'); ?></span>
          <?php endif; ?>

          <button class="pd-wl" id="pd-wl" data-product-id="<?php echo esc_attr($product->get_id()); ?>" type="button" aria-label="Favoritos">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Accordions -->
      <div class="pd-accordions">
        <details class="pd-acc">
          <summary>
            <?php esc_html_e('Ingredientes & Alérgenos', 'elrancho'); ?>
            <svg class="pd-acc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
          </summary>
          <div class="pd-acc-body">
            <?php if ($allergens) : echo wp_kses_post($allergens);
            else : esc_html_e('Agrega esta información desde el editor del producto (campo: Ingredientes & Alérgenos).', 'elrancho'); endif; ?>
          </div>
        </details>

        <details class="pd-acc">
          <summary>
            <?php esc_html_e('Información Nutricional', 'elrancho'); ?>
            <svg class="pd-acc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
          </summary>
          <div class="pd-acc-body">
            <?php if ($nutrition) : echo wp_kses_post($nutrition);
            else : esc_html_e('Agrega esta información desde el editor del producto (campo: Información Nutricional).', 'elrancho'); endif; ?>
          </div>
        </details>
      </div>

      <!-- Baker's Note -->
      <?php if ($bakers_note) : ?>
        <div class="pd-note">
          <div class="pd-note-title">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="18" x2="15" y2="18"/><line x1="10" y1="22" x2="14" y2="22"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0018 8 6 6 0 006 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 018.91 14"/></svg>
            <?php esc_html_e('Nota del Panadero', 'elrancho'); ?>
          </div>
          <p>"<?php echo esc_html($bakers_note); ?>"</p>
        </div>
      <?php endif; ?>

    </div><!-- /.pd-info -->
  </div><!-- /.pd-wrap -->

  <!-- ─── RELACIONADOS ─── -->
  <?php
  $related_ids = wc_get_related_products($product->get_id(), 4);
  $related_ids = array_filter(array_map('intval', $related_ids));
  if (!empty($related_ids)) :
  ?>
  <section class="pd-related" aria-label="<?php esc_attr_e('Clientes también compraron', 'elrancho'); ?>">
    <div class="pd-related-hd">
      <h2><?php esc_html_e('Los Clientes También Compraron', 'elrancho'); ?></h2>
      <div class="pd-rel-nav" aria-hidden="true">
        <button class="pd-rnbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg></button>
        <button class="pd-rnbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></button>
      </div>
    </div>
    <div class="pd-rel-grid">
      <?php foreach ($related_ids as $rid) :
        $rp = wc_get_product($rid);
        if (!$rp) continue;
        $rimg = get_the_post_thumbnail_url($rid, 'woocommerce_thumbnail');
        $rcats = wc_get_product_terms($rid, 'product_cat', ['fields' => 'names']);
        $rcat = !empty($rcats) ? $rcats[0] : '';
      ?>
        <a href="<?php echo esc_url(get_permalink($rid)); ?>" class="pd-rel-card">
          <div class="pd-rel-img">
            <?php if ($rimg) : ?>
              <img src="<?php echo esc_url($rimg); ?>" alt="<?php echo esc_attr($rp->get_name()); ?>" loading="lazy">
            <?php else : ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--color-background-warm);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="var(--color-border-warm)"><path d="M17 3H7C4.24 3 2 5.24 2 8c0 2.24 1.45 4.13 3.45 4.77L5 20c0 .55.45 1 1 1h12c.55 0 1-.45 1-1l-.45-7.23C20.55 12.13 22 10.24 22 8c0-2.76-2.24-5-5-5z"/></svg>
              </div>
            <?php endif; ?>
          </div>
          <?php if ($rcat) : ?><div class="pd-rel-cat"><?php echo esc_html($rcat); ?></div><?php endif; ?>
          <div class="pd-rel-name"><?php echo esc_html($rp->get_name()); ?></div>
          <div class="pd-rel-price"><?php echo $rp->get_price_html(); ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</div><!-- /.container -->
</main>

<script>
// Switcher de imágenes
function pdSwitch(el) {
  var img = document.getElementById('pd-main-img');
  if (img) { img.src = el.dataset.full; img.alt = el.dataset.alt; }
  document.querySelectorAll('.pd-thumb').forEach(function(t){ t.classList.remove('active'); });
  el.classList.add('active');
}

(function($){
  // Quantity
  var qInput = document.getElementById('pd-qty');
  var btnM   = document.getElementById('pd-minus');
  var btnP   = document.getElementById('pd-plus');
  var atcBtn = document.getElementById('pd-atc');
  var syncQty = function() {
    if (!qInput || !atcBtn) return;
    atcBtn.setAttribute('data-quantity', parseInt(qInput.value, 10) || 1);
  };
  if (qInput && btnM && btnP) {
    btnM.addEventListener('click', function(){
      var v = parseInt(qInput.value), mn = parseInt(qInput.min)||1;
      if (v > mn) qInput.value = v - 1;
      syncQty();
    });
    btnP.addEventListener('click', function(){
      var v = parseInt(qInput.value), mx = parseInt(qInput.max)||99;
      if (v < mx) qInput.value = v + 1;
      syncQty();
    });
    syncQty();
  }

  // Wishlist
  var wlBtn = document.getElementById('pd-wl');
  if (wlBtn) {
    var pid = wlBtn.dataset.productId;
    var wl  = JSON.parse(localStorage.getItem('elrancho_wishlist')||'[]');
    if (wl.indexOf(pid) > -1) wlBtn.classList.add('active');
    wlBtn.addEventListener('click', function(){
      this.classList.toggle('active');
      var list = JSON.parse(localStorage.getItem('elrancho_wishlist')||'[]');
      var idx  = list.indexOf(pid);
      if (this.classList.contains('active')){ if(idx<0) list.push(pid); }
      else { if(idx>-1) list.splice(idx,1); }
      localStorage.setItem('elrancho_wishlist', JSON.stringify(list));
    });
  }
})(jQuery);
</script>

<?php get_footer(); ?>
