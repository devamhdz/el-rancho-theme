# Rancho Rewards — Pendientes para Claude Code
**Proyecto:** El Rancho Bakery — Sistema de Lealtad
**Tema:** `elrancho-theme` (WooCommerce custom theme)
**Stack:** PHP 8+, WordPress, WooCommerce, MySQL
**Archivo principal:** `functions.php` — el módulo de lealtad empieza en la sección `LOYALTY MODULE — RANCHO REWARDS`

---

## ✅ Ya completado (referencia)

- [x] Motor de puntos con acumulación automática por órdenes
- [x] Reversas automáticas en refunded/cancelled/failed
- [x] Sistema de tiers Bronce/Plata/Oro con multiplicadores dinámicos
- [x] Multiplicador de cumpleaños (semana)
- [x] Multiplicador por categoría (custom-cakes = 1.5x)
- [x] Bono de registro (200 pts)
- [x] Sistema de referidos con cookie 30 días + doble bono
- [x] Redención de puntos en checkout (UI + fee + deducción)
- [x] Motor de retos con 5 tipos de condición
- [x] Cron mensual de caducidad de puntos
- [x] 5 endpoints REST: /wallet, /transactions, /challenges, /referral/apply, /redeem-token
- [x] Admin Dashboard con tabs: Dashboard, Transacciones (con filtros), Configuración, Tiers, Retos, Miembros
- [x] Página "🎁 Mis Puntos" en Mi Cuenta con historial paginado y filtros
- [x] Perfil de usuario en WP Admin con Rancho Rewards section
- [x] Columnas de puntos y tier en lista de usuarios
- [x] Shortcodes: `[elrancho_loyalty_points]` y `[erbl_tier]`
- [x] Widget de progreso en sidebar de "Mis Pedidos"
- [x] Migración backward-compatible del sistema v1
- [x] URLs del endpoint en inglés (`/my-account/my-points/`)
- [x] Fix: link "Ver mis puntos" en perfil apuntaba al endpoint equivocado
- [x] Fix: botón "Ver mis puntos" en landing usaba `?loyalty=1` inválido
- [x] **#1** Script de migración v1→v2 — botón en admin Dashboard + handler AJAX (`wp_ajax_erbl_run_migration`)
- [x] **#2** Flush rewrite rules automático en `after_switch_theme`
- [x] **#3** REST API: soporte Application Passwords para app móvil
- [x] **#4** Eliminar `elrancho_loyalty_admin_page()` duplicada y limpiar `remove_action`
- [x] **#5** `navigation.php`: migrar de `elrancho_loyalty_get_user_points` a `erbl_get_user_points` (v2)
- [x] **#6** `navigation.php`: mostrar tier con emoji debajo de los puntos
- [x] **#7** Fix double-prepare en `$where_sql` de `erbl_account_mis_puntos_page()`
- [x] **#8** Exportar transacciones a CSV desde tab Transacciones (botón + `admin_init` handler)
- [x] **#9** Email HTML al cliente al ganar puntos por una orden
- [x] **#10** Email HTML al referidor cuando su referido hace primera compra
- [x] **#11** CSS variables — ya estaban todas definidas en `:root` de `style.css`
- [x] **#12** Calculadora en página pública: checkbox pasteles (+1.5x) y nota de cumpleaños (2x)
- [x] **#13** Cron mensual: reset de progreso de retos `categories_month`, `mondays_month` y `streak_weeks`
- [x] **#14** Endpoint `POST /wp-json/erbl/v1/redeem-token/consume` para cajeros en tienda física
- [x] **#15** Índice `created_at` en `wp_erbl_transactions` (DDL + ALTER en migración)
- [x] **#16** `REST-API.md` con documentación completa de todos los endpoints y ejemplos curl
- [x] **#17** Endpoint `PUT /wp-json/erbl/v1/profile` para actualizar cumpleaños desde la app
- [x] **#12b** `page-programa-de-lealtad.php` — template completo: hero, cómo funciona, tiers, calculadora, referidos, FAQ, CTA
- [x] **Staff Redención** `page-rancho-staff.php` — flujo cajero: PIN → QR scanner → confirmación → resultado (monto a descontar en POS)
- [x] **Staff Redención** REST `/redeem-token/preview` — valida token sin consumirlo, devuelve cliente + puntos + valor USD
- [x] **Staff Redención** REST `/staff/create-user` — crea usuarios con rol `erbl_staff` desde app móvil
- [x] **Staff Redención** Rol `erbl_staff` con capacidad `erbl_staff_consume` creado en `erbl_install()`
- [x] **Staff Redención** `$staff_auth` — autorización unificada: `manage_woocommerce` OR `erbl_staff` role OR `staff_pin` param
- [x] **Staff Redención** Settings: `staff_pin` y `redeem_min_store` en panel admin → Staff & Tienda
- [x] **Fix** `elrancho_loyalty_sanitize_settings()` no incluía `staff_pin` ni `redeem_min_store` — se descartaban al guardar
- [x] **Fix** `page-rancho-staff.php`: cámara negra en iOS Safari — `BarcodeDetector` no disponible, ocultar video con `querySelector('.scanner-video-wrap')`
- [x] **Fix** PHP 8: `validate_callback => 'is_numeric'` en `/redeem-token` causaba `ArgumentCountError` — envuelto en lambda
- [x] **Admin** Botón "🧪 Generar token de prueba" en Dashboard admin (selecciona usuario + puntos, crea transient)
- [x] **Cliente** Tarjeta "🏪 Redimir en tienda física" en Mis Puntos — slider de puntos + botón Generar QR (QRCode.js CDN)
- [x] **Cliente** Slider de redención respeta el mínimo configurado (`redeem_minimum`) como valor inicial y mínimo del range
- [x] **Cliente** Historial de puntos rediseñado como tarjetas para móvil (antes era tabla de 5 columnas)
- [x] **Staff** REST `GET /wp-json/erbl/v1/staff/redemptions` — últimas redenciones (requiere `$staff_auth`)
- [x] **Staff** Sección "Redenciones recientes" en `page-rancho-staff.php` — se carga al entrar y se actualiza tras cada canje

---

*Actualizado el 17 Mar 2026*
