# Rancho Rewards — Pendientes para Claude Code
**Proyecto:** El Rancho Bakery — Sistema de Lealtad  
**Tema:** `elrancho-theme` (WooCommerce custom theme)  
**Stack:** PHP 8+, WordPress, WooCommerce, MySQL  
**Archivo principal:** `functions.php` — el módulo de lealtad empieza en la sección `LOYALTY MODULE — RANCHO REWARDS`

---

## 🔴 Crítico — Sin esto el sistema no funciona en producción

### 1. Migrar usuarios existentes al sistema v2
Los usuarios creados antes de instalar el tema v2 no tienen:
- `_erbl_referral_code` en user_meta
- `_erbl_points` (tienen `_elrancho_loyalty_points` del sistema viejo)
- `_erbl_total_spend` calculado

**Tarea:** Crear un script de migración one-shot. Puede ser:
- Un WP-CLI command: `wp erbl migrate`
- O un botón en WooCommerce → Rancho Rewards → Dashboard que ejecute la migración vía AJAX

La lógica debe:
1. Iterar todos los usuarios con rol `customer`
2. Si tiene `_elrancho_loyalty_points` → migrar a `_erbl_points` y borrar el meta viejo
3. Si no tiene `_erbl_referral_code` → generar uno único
4. Si no tiene `_erbl_total_spend` → calcularlo sumando el total de todas sus órdenes completadas con `wc_get_orders()`
5. Loggear cuántos usuarios se migraron

---

### 2. Flush de rewrite rules al activar el tema
El endpoint `/my-account/mis-puntos/` requiere que se haga flush de permalinks después de registrarlo. Actualmente el usuario tiene que ir manualmente a Ajustes → Enlaces permanentes.

**Tarea:** En `functions.php`, agregar en `after_switch_theme`:
```php
add_action('after_switch_theme', function() {
    erbl_register_account_endpoint();
    flush_rewrite_rules();
});
```

---

### 3. Autenticación REST API para la app móvil
Los endpoints `/wp-json/erbl/v1/*` usan `is_user_logged_in()` que funciona con cookies de sesión web, pero **no funciona con JWT tokens** que necesitará la app móvil.

**Tarea:** Agregar soporte para Application Passwords de WordPress (nativo desde WP 5.6) en los endpoints REST. Cambiar el `permission_callback` para que acepte también autenticación Basic Auth con Application Password:
```php
'permission_callback' => function() {
    return is_user_logged_in() || ( defined('REST_REQUEST') && REST_REQUEST && get_current_user_id() > 0 );
}
```
Y documentar en un archivo `REST-API.md` los endpoints disponibles con ejemplos de curl.

---

## 🟡 Importante — Funcionalidad comprometida sin esto

### 4. La función `erbl_admin_page_full()` duplica código de `elrancho_loyalty_admin_page()`
En `functions.php` existen dos funciones de admin page. La original `elrancho_loyalty_admin_page()` quedó huérfana porque `erbl_admin_page_full()` la reemplazó con `remove_action` + `add_action`. Esto funciona pero es frágil.

**Tarea:** 
1. Eliminar la función `elrancho_loyalty_admin_page()` completa (ya no se usa)
2. Verificar que `erbl_admin_page_full()` tenga todas las tabs: dashboard, transactions, settings, tiers, challenges, members
3. Limpiar el `remove_action('admin_menu', 'elrancho_loyalty_admin_menu')` — dejar solo `erbl_admin_page_full`

---

### 5. Widget de puntos en `navigation.php` usa función del sistema viejo
El archivo `woocommerce/myaccount/navigation.php` línea 12 llama a `elrancho_loyalty_get_user_points()` (sistema v1).

**Tarea:** Cambiar a `erbl_get_user_points()` directamente:
```php
// Cambiar esto:
$loyalty_points = function_exists('elrancho_loyalty_get_user_points') && is_user_logged_in()
    ? (int) elrancho_loyalty_get_user_points(get_current_user_id())
    : 0;

// Por esto:
$loyalty_points = function_exists('erbl_get_user_points') && is_user_logged_in()
    ? erbl_get_user_points(get_current_user_id())
    : 0;
```

---

### 6. La barra de progreso en `navigation.php` muestra puntos, debería mostrar tier
El nav card muestra "X puntos" debajo del nombre del usuario. Mejorar para mostrar también el tier con emoji.

**Tarea:** En `navigation.php`, agregar debajo del saldo de puntos:
```php
$user_tier = function_exists('erbl_get_user_tier') ? erbl_get_user_tier(get_current_user_id()) : 'bronze';
$tier_label = function_exists('erbl_tier_label') ? erbl_tier_label($user_tier) : '';
```
Y renderizarlo en el HTML del avatar card.

---

### 7. No hay validación de `$where_sql` con parámetros separados en `erbl_account_mis_puntos_page()`
En la página de Mi Cuenta, el filtro por tipo construye el WHERE así:
```php
$where_sql = $f_type ? $wpdb->prepare(' AND type=%s', $f_type) : '';
// Luego:
$total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_tx WHERE user_id=%d" . $where_sql, $uid));
```
Esto mezcla un `prepare()` dentro de otro `prepare()`, lo cual puede dar warnings en WP_DEBUG.

**Tarea:** Refactorizar usando un array de params consistente igual que en `erbl_admin_page_full()` (que ya lo hace correctamente).

---

## 🟢 Mejoras — El sistema funciona pero estas cosas lo hacen mejor

### 8. Exportar transacciones a CSV desde el admin
En WooCommerce → Rancho Rewards → Transacciones, agregar un botón "Exportar CSV" que descargue las transacciones filtradas actualmente visibles.

**Tarea:** Agregar endpoint AJAX o action GET que genere el CSV con headers correctos:
```
ID, Cliente, Email, Tipo, Puntos, Balance, Fecha
```

---

### 9. Email de notificación al ganar puntos
Cuando se acreditan puntos por una orden, el cliente no recibe ninguna notificación.

**Tarea:** En `elrancho_loyalty_maybe_award_points()`, después de `erbl_adjust_points()`, enviar un email usando `wp_mail()` con:
- Asunto: "🎉 Ganaste X puntos en El Rancho Bakery"
- Body HTML con saldo nuevo, tier actual, y link a `/my-account/mis-puntos/`
- Usar el template de emails de WooCommerce (`WC_Email`) para que respete el diseño del sitio

---

### 10. Email de bono de referido
Cuando el referidor recibe sus 500 pts por un referido exitoso, tampoco recibe notificación.

**Tarea:** En `erbl_maybe_award_referral_bonus()`, enviar email al referidor con:
- "¡Tu amigo [nombre] hizo su primera compra y tú ganaste 500 puntos!"
- Saldo actualizado y link a mis puntos

---

### 11. Página pública del programa (`page-programa-de-lealtad.php`) — CSS variables faltantes
El template usa variables CSS como `--color-background-dark`, `--color-accent-gold`, `--color-success`, `--color-surface`, `--color-border`, `--color-background-warm` que pueden no estar definidas en `style.css`.

**Tarea:** 
1. Revisar `style.css` y verificar qué variables están definidas en `:root`
2. Agregar las que falten. Valores sugeridos:
```css
:root {
  --color-background-dark: #1a0a0a;
  --color-accent-gold:     #f0c040;
  --color-success:         #0a7c42;
  --color-surface:         #ffffff;
  --color-border:          #e0d8cf;
  --color-background-warm: #fdf8f1;
  --color-border-warm:     #e8d5b0;
  --color-text-muted:      #7D6B60;
}
```

---

### 12. El simulador de la página pública no refleja cumpleaños ni categoría de pasteles
El calculador en `page-programa-de-lealtad.php` solo muestra el multiplicador de tier pero no el 1.5x de pasteles ni el 2x de cumpleaños.

**Tarea:** Agregar en el widget JS de la calculadora:
- Un checkbox "¿Es pastel personalizado?" que multiplique por `cat_mult_cakes`
- Una nota "En tu semana de cumpleaños aplica 2x adicional"

---

### 13. Retos — el progreso no se resetea entre períodos
Los retos de tipo `streak_weeks`, `categories_month`, y `mondays_month` deberían resetearse al inicio de cada mes/semana, pero actualmente el progreso solo sube, nunca baja.

**Tarea:** En el cron mensual `erbl_expire_points_event` (o crear uno semanal separado), agregar lógica que:
1. Para retos `categories_month` y `mondays_month`: reset de `progress = 0` y `completed = 0` el día 1 de cada mes
2. Para retos `streak_weeks`: verificar que hubo una orden en los últimos 7 días; si no, reset el progreso de racha

---

### 14. QR de redención para tienda física — falta el endpoint de validación
El endpoint `POST /wp-json/erbl/v1/redeem-token` genera el token y lo guarda en un transient, pero **no hay endpoint para que el cajero valide y consuma el token**.

**Tarea:** Crear `POST /wp-json/erbl/v1/redeem-token/consume`:
```
Body: { "token": "abc123..." }
```
- Verificar que el transient existe
- Descontar los puntos del usuario: `erbl_adjust_points($uid, -$pts, 'redemption', 0, 'Redención en tienda física')`
- Borrar el transient
- Devolver `{ success: true, user: "Nombre", points_used: N, new_balance: X }`
- Este endpoint debe poder autenticarse con un API key de staff (no requiere ser el usuario)

---

### 15. Índice de base de datos faltante
La tabla `wp_erbl_transactions` tiene índice en `user_id` y `type`, pero las queries más frecuentes filtran por `created_at` (para el dashboard del mes).

**Tarea:** En `erbl_install()`, agregar al CREATE TABLE:
```sql
KEY created_at (created_at)
```
Y para instancias ya instaladas, ejecutar:
```sql
ALTER TABLE wp_erbl_transactions ADD INDEX created_at (created_at);
```
Agregar esto como parte de la migración del punto #1.

---

## 📱 App Móvil — Pendientes específicos

### 16. Documentar todos los endpoints REST
Crear archivo `REST-API.md` en la raíz del tema con:
- URL base: `https://elranchobakery.com/wp-json/erbl/v1/`
- Autenticación: Application Passwords (Basic Auth)
- Todos los endpoints con método, params, response de ejemplo
- Códigos de error posibles

### 17. Endpoint de perfil del usuario
La app necesita poder actualizar el cumpleaños del usuario (para el bono 2x).

**Tarea:** Crear `PUT /wp-json/erbl/v1/profile`:
```
Body: { "birthday": "1990-05-15" }
```
Actualiza `_erbl_birthday` en user_meta. Validar formato de fecha.

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
- [x] Página pública del programa (`/programa-de-lealtad/`) con hero, tiers, calculadora, FAQ
- [x] Widget de progreso en sidebar de "Mis Pedidos"
- [x] Migración backward-compatible del sistema v1

---

*Generado el 15 Mar 2026 — basado en revisión del código actual de `elrancho-theme`*
