/**
 * El Rancho Bakery - main.js
 * JavaScript principal del tema
 */

(function($) {
  'use strict';

  /* =============================================
     HEADER: Scroll shadow
     ============================================= */
  const header = document.getElementById('masthead');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });
  }

  /* =============================================
     SHOP SIDEBAR: remove empty widget boxes
     ============================================= */
  const shopSidebar = document.querySelector('.shop-sidebar');
  if (shopSidebar) {
    shopSidebar.querySelectorAll('.sidebar-widget').forEach((widget) => {
      const hasUsefulElement = !!widget.querySelector(
        'input, select, textarea, button, a, img, ul, ol, li, form, .js-price-range, .ingredient-tags'
      );
      const textContent = (widget.textContent || '').replace(/\s+/g, '').trim();
      if (!hasUsefulElement && !textContent) {
        widget.remove();
      }
    });
  }

  /* =============================================
     MOBILE MENU TOGGLE
     ============================================= */
  const mobileToggle = document.getElementById('mobile-menu-toggle');
  const mobileNav    = document.getElementById('mobile-nav');

  if (mobileToggle && mobileNav) {
    mobileToggle.addEventListener('click', () => {
      const isOpen = mobileNav.classList.toggle('open');
      mobileToggle.setAttribute('aria-expanded', isOpen);
      mobileToggle.setAttribute('aria-label', isOpen
        ? elRancho.i18n && elRancho.i18n.close || 'Cerrar menú'
        : 'Abrir menú'
      );
    });
    // Cerrar al hacer click fuera
    document.addEventListener('click', (e) => {
      if (!header.contains(e.target)) {
        mobileNav.classList.remove('open');
        mobileToggle.setAttribute('aria-expanded', false);
      }
    });
  }

  /* =============================================
     AJAX: Add to Cart
     Usa el endpoint nativo de WooCommerce: ?wc-ajax=add_to_cart
     ============================================= */
  function elrancho_updateCartCount() {
    // Reservado — WooCommerce actualiza fragmentos automáticamente vía added_to_cart
  }

  function elrancho_getWcAjaxUrl(endpoint) {
    const candidates = [
      window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url,
      window.elRancho && window.elRancho.wcAjaxUrl
    ].filter(Boolean);

    for (const candidate of candidates) {
      let url = String(candidate);
      if (url.includes('%%endpoint%%')) {
        url = url.replace('%%endpoint%%', endpoint);
      }

      try {
        const parsed = new URL(url, window.location.origin);
        if (parsed.searchParams.has('wc-ajax')) {
          parsed.searchParams.set('wc-ajax', endpoint);
          return parsed.toString();
        }
      } catch (_) {
        // Si falla parseo probamos siguiente candidato.
      }
    }

    const fallbackBase = (window.elRancho && window.elRancho.homeUrl) ? window.elRancho.homeUrl : '/';
    const fallback = new URL(fallbackBase, window.location.origin);
    fallback.searchParams.set('wc-ajax', endpoint);
    return fallback.toString();
  }

  // Delegación de eventos: captura botones aunque sean renderizados después
  $(document).on('click', '.add-to-cart-btn[data-product-id], .ajax_add_to_cart[data-product_id], .add_to_cart_button[data-product_id]', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    const $btn      = $(this);
    const productId = $btn.data('product-id') || $btn.data('productId') || $btn.attr('data-product_id');
    if (!productId) return;
    const productType = ($btn.data('product-type') || $btn.attr('data-product-type') || '').toString();
    const qtyFromData = parseInt($btn.attr('data-quantity') || $btn.data('quantity'), 10);
    const qtyInputEl  = document.getElementById('pd-qty');
    const qtyFromInput = qtyInputEl ? parseInt(qtyInputEl.value, 10) : NaN;
    const quantity = Number.isFinite(qtyFromInput) && qtyFromInput > 0
      ? qtyFromInput
      : (Number.isFinite(qtyFromData) && qtyFromData > 0 ? qtyFromData : 1);
    const i18n = (window.elRancho && window.elRancho.i18n) ? window.elRancho.i18n : {};

    // Para tipos no simples dejamos el flujo nativo de WC (ir a producto/opciones).
    if (productType && productType !== 'simple') {
      const href = $btn.attr('href');
      if (href) window.location = href;
      return;
    }

    const original = $btn.html();
    const spinIcon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 0.7s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>';

    $btn.addClass('loading').html(spinIcon).prop('disabled', true);
    $btn.attr('data-quantity', quantity);

    // Endpoint correcto de WooCommerce AJAX
    const wcAjaxUrl = elrancho_getWcAjaxUrl('add_to_cart');

    $.ajax({
      type: 'POST',
      url:  wcAjaxUrl,
      data: {
        product_id:   productId,
        quantity:     quantity,
        variation_id: $btn.data('variation_id') || 0,
      },
      success: function(response) {
        if (response && response.error) {
          if (response.product_url) {
            window.location = response.product_url;
          } else {
            elrancho_showToast('No se pudo agregar el producto.', 'error');
          }
          return;
        }
        if (response && response.fragments) {
          $.each(response.fragments, function(key, value) {
            $(key).replaceWith(value);
          });
        }
        // Evento nativo de WC — actualiza mini-cart y contadores automáticamente
        $(document.body).trigger('added_to_cart', [
          response ? response.fragments  : {},
          response ? response.cart_hash  : '',
          $btn
        ]);
        elrancho_showToast(i18n.addedToCart || '¡Agregado al carrito!', 'success');
      },
      error: function() {
        elrancho_showToast('Error al agregar el producto. Intenta de nuevo.', 'error');
      },
      complete: function() {
        $btn.removeClass('loading').html(original).prop('disabled', false);
      }
    });
  });

  // WooCommerce actualiza los fragmentos solo cuando se dispara added_to_cart
  // No necesitamos wc_fragment_refresh manual (evita llamadas 400 a admin-ajax.php)

  /* =============================================
     TOAST NOTIFICATIONS
     ============================================= */
  function elrancho_showToast(message, type = 'success') {
    const existing = document.querySelector('.elrancho-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'elrancho-toast';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');

    const colors = {
      success: { bg: '#2d7a3e', text: '#fff' },
      error:   { bg: '#b81417', text: '#fff' },
      info:    { bg: '#1d4ed8', text: '#fff' },
    };
    const color = colors[type] || colors.success;

    toast.style.cssText = `
      position: fixed;
      bottom: 1.5rem;
      right: 1.5rem;
      background: ${color.bg};
      color: ${color.text};
      padding: 0.875rem 1.25rem;
      border-radius: 0.75rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.9375rem;
      font-weight: 600;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      z-index: 9999;
      transform: translateY(1rem);
      opacity: 0;
      transition: all 0.25s ease;
      max-width: 320px;
      display: flex;
      align-items: center;
      gap: 0.625rem;
    `;

    toast.innerHTML = `
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        ${type === 'error'
          ? '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'
          : '<polyline points="20 6 9 17 4 12"/>'}
      </svg>
      ${message}
    `;

    document.body.appendChild(toast);
    requestAnimationFrame(() => {
      toast.style.opacity = '1';
      toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(1rem)';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }

  /* =============================================
     WISHLIST BUTTONS
     ============================================= */
  document.querySelectorAll('.product-wishlist-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      this.classList.toggle('active');
      const productId = this.dataset.productId;
      const isActive  = this.classList.contains('active');

      // Guardar en localStorage
      const wishlist = JSON.parse(localStorage.getItem('elrancho_wishlist') || '[]');
      if (isActive) {
        if (!wishlist.includes(productId)) wishlist.push(productId);
        elrancho_showToast('¡Agregado a favoritos!', 'success');
      } else {
        const idx = wishlist.indexOf(productId);
        if (idx > -1) wishlist.splice(idx, 1);
      }
      localStorage.setItem('elrancho_wishlist', JSON.stringify(wishlist));
    });

    // Restaurar estado desde localStorage
    const wishlist = JSON.parse(localStorage.getItem('elrancho_wishlist') || '[]');
    if (btn.dataset.productId && wishlist.includes(btn.dataset.productId)) {
      btn.classList.add('active');
    }
  });

  /* =============================================
     PRICE RANGE SLIDER
     ============================================= */
  const priceRanges = document.querySelectorAll('.js-price-range');
  if (priceRanges.length) {
    const isShopContext = document.body.classList.contains('post-type-archive-product')
      || document.body.classList.contains('tax-product_cat')
      || document.body.classList.contains('tax-product_tag');

    priceRanges.forEach((priceRange) => {
      const wrapper = priceRange.closest('.elrancho-price-filter-widget') || priceRange.parentElement;
      const priceDisplay = wrapper ? wrapper.querySelector('.js-price-range-display') : null;

      const updatePriceRangeUi = () => {
        if (priceDisplay) {
          priceDisplay.textContent = '$' + priceRange.value + '+';
        }
        const min = parseFloat(priceRange.min) || 0;
        const max = parseFloat(priceRange.max) || 1;
        const pct = max > min ? ((priceRange.value - min) / (max - min) * 100) : 100;
        priceRange.style.background = `linear-gradient(to right, var(--color-primary) ${pct}%, var(--color-border-warm) ${pct}%)`;
      };

      priceRange.addEventListener('input', updatePriceRangeUi);
      updatePriceRangeUi();

      if (isShopContext) {
        priceRange.addEventListener('change', function() {
          const nextMax = parseInt(this.value, 10);
          if (!Number.isFinite(nextMax) || nextMax <= 0) return;

          const url = new URL(window.location.href);
          const currentMax = parseInt(url.searchParams.get('max_price'), 10);
          if (currentMax === nextMax) return;

          url.searchParams.set('max_price', String(nextMax));
          url.searchParams.delete('paged');
          window.location.href = url.toString();
        });
      }
    });
  }

  /* =============================================
     FOOTER NEWSLETTER
     ============================================= */
  const newsletterBtn = document.getElementById('footer-newsletter-btn');
  if (newsletterBtn) {
    newsletterBtn.addEventListener('click', function() {
      const email   = document.getElementById('footer-email')?.value?.trim();
      const message = document.getElementById('newsletter-message');

      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        if (message) { message.style.display = 'block'; message.style.color = '#f59e0b'; message.textContent = 'Por favor ingresa un email válido.'; }
        return;
      }

      this.disabled = true;
      this.textContent = 'Enviando...';

      $.post(elRancho.ajaxUrl, { action: 'elrancho_newsletter', email, nonce: elRancho.nonce }, (res) => {
        if (message) {
          message.style.display = 'block';
          if (res.success) {
            message.style.color = '#2d7a3e';
            message.textContent = res.data?.message || '¡Gracias por suscribirte!';
            document.getElementById('footer-email').value = '';
          } else {
            message.style.color = '#f59e0b';
            message.textContent = res.data?.message || 'Error. Intenta de nuevo.';
          }
        }
      }).fail(() => {
        if (message) { message.style.display = 'block'; message.style.color = '#f59e0b'; message.textContent = 'Error de conexión.'; }
      }).always(() => {
        this.disabled = false;
        this.textContent = 'Suscribirme';
      });
    });
  }

  /* =============================================
     QUANTITY BUTTONS en producto individual
     ============================================= */
  function initQtyButtons() {
    document.querySelectorAll('.qty-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const input = this.closest('.quantity')?.querySelector('input[type="number"]');
        if (!input) return;
        const step = parseFloat(input.step) || 1;
        const min  = parseFloat(input.min)  || 1;
        const max  = parseFloat(input.max)  || Infinity;
        let val = parseFloat(input.value) || 1;
        if (this.classList.contains('qty-plus'))  val = Math.min(val + step, max);
        if (this.classList.contains('qty-minus')) val = Math.max(val - step, min);
        input.value = val;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
  }
  initQtyButtons();

  /* =============================================
     HERO CAROUSEL
     ============================================= */
  const heroSection = document.querySelector('.hero-section');
  const heroSlidesWrap = heroSection ? heroSection.querySelector('.hero-slides[data-hero-carousel="true"]') : null;
  if (heroSection && heroSlidesWrap) {
    const slides = Array.from(heroSection.querySelectorAll('.hero-slide'));
    const dots = Array.from(heroSection.querySelectorAll('.hero-dot[data-slide-to]'));
    let current = slides.findIndex(slide => slide.classList.contains('active'));
    if (current < 0) current = 0;
    let intervalId = null;

    const goToSlide = (nextIndex) => {
      if (!slides.length) return;
      const max = slides.length - 1;
      const normalized = nextIndex > max ? 0 : (nextIndex < 0 ? max : nextIndex);
      current = normalized;

      slides.forEach((slide, index) => {
        slide.classList.toggle('active', index === normalized);
      });

      dots.forEach((dot, index) => {
        const isActive = index === normalized;
        dot.classList.toggle('active', isActive);
        dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
    };

    const startAutoplay = () => {
      if (intervalId || slides.length < 2) return;
      intervalId = window.setInterval(() => goToSlide(current + 1), 5000);
    };

    const stopAutoplay = () => {
      if (!intervalId) return;
      window.clearInterval(intervalId);
      intervalId = null;
    };

    dots.forEach(dot => {
      dot.addEventListener('click', () => {
        const next = parseInt(dot.getAttribute('data-slide-to'), 10);
        if (!Number.isFinite(next)) return;
        goToSlide(next);
        stopAutoplay();
        startAutoplay();
      });
    });

    heroSection.addEventListener('mouseenter', stopAutoplay);
    heroSection.addEventListener('mouseleave', startAutoplay);

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAutoplay();
      } else {
        startAutoplay();
      }
    });

    startAutoplay();
  }

  /* =============================================
     ANIMACIONES DE ENTRADA (Intersection Observer)
     ============================================= */
  if ('IntersectionObserver' in window) {
    const cards = document.querySelectorAll('.product-card, .sidebar-widget, .loyalty-banner, .about-section > *');
    const style  = document.createElement('style');
    style.textContent = '.animate-enter { opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease, transform 0.5s ease; } .animate-enter.visible { opacity: 1; transform: translateY(0); }';
    document.head.appendChild(style);

    cards.forEach((el, i) => {
      el.classList.add('animate-enter');
      el.style.transitionDelay = (i * 0.06) + 's';
    });

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    cards.forEach(el => observer.observe(el));
  }

})(jQuery);
