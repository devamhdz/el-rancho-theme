# Rancho Rewards — REST API

**Base URL:** `https://elranchobakery.com/wp-json/erbl/v1/`

**Autenticación:** WordPress Application Passwords (Basic Auth)

Para obtener un Application Password: WordPress Admin → Usuarios → Tu perfil → Application Passwords.

Incluye en cada request:
```
Authorization: Basic base64(username:application_password)
```

---

## Endpoints

### GET /wallet
Retorna el saldo, tier y datos del programa del usuario autenticado.

**Response:**
```json
{
  "points": 1500,
  "value_usd": 15.00,
  "tier": "silver",
  "tier_label": "🥈 Plata",
  "tier_multiplier": 1.25,
  "next_tier": "gold",
  "next_tier_pct": 62,
  "next_tier_remain": 456.50,
  "total_spend_usd": 743.50,
  "referral_code": "AB12CD34",
  "referral_link": "https://elranchobakery.com/?ref=AB12CD34",
  "redeem_minimum": 500,
  "point_value": 0.01
}
```

**Ejemplo:**
```bash
curl -u "usuario:app_password" https://elranchobakery.com/wp-json/erbl/v1/wallet
```

---

### GET /transactions
Historial de transacciones paginado del usuario autenticado.

**Query params:**
| Param | Tipo | Descripción |
|-------|------|-------------|
| page  | int  | Página (default: 1) |

**Response:**
```json
{
  "transactions": [
    {
      "id": 42,
      "delta": 150,
      "balance": 1500,
      "type": "order",
      "type_label": "Compra",
      "note": "Pedido #1234",
      "date": "2026-03-15 14:30:00"
    }
  ],
  "total": 18,
  "pages": 1,
  "page": 1
}
```

**Ejemplo:**
```bash
curl -u "usuario:app_password" "https://elranchobakery.com/wp-json/erbl/v1/transactions?page=1"
```

---

### GET /challenges
Lista de retos activos con progreso del usuario autenticado.

**Response:**
```json
{
  "challenges": [
    {
      "id": 1,
      "title": "Racha semanal",
      "description": "Compra 4 semanas seguidas",
      "bonus_pts": 300,
      "tier_req": "bronze",
      "locked": false,
      "progress": 2,
      "target": 4,
      "pct": 50,
      "completed": false
    }
  ]
}
```

**Ejemplo:**
```bash
curl -u "usuario:app_password" https://elranchobakery.com/wp-json/erbl/v1/challenges
```

---

### POST /referral/apply
Aplica un código de referido al usuario autenticado. Solo funciona antes de la primera compra.

**Body:**
```json
{ "code": "AB12CD34" }
```

**Response (éxito):**
```json
{ "success": true, "message": "Código guardado. Recibirás tus puntos en tu primera compra." }
```

**Errores:**
| Código | Descripción |
|--------|-------------|
| 400    | Ya usaste un código / auto-referido |
| 404    | Código inválido |

**Ejemplo:**
```bash
curl -u "usuario:app_password" -X POST \
  -H "Content-Type: application/json" \
  -d '{"code":"AB12CD34"}' \
  https://elranchobakery.com/wp-json/erbl/v1/referral/apply
```

---

### POST /redeem-token
Genera un token de redención para usar en tienda física (válido 30 min). Genera datos para QR.

**Body:**
```json
{ "points": 500 }
```

**Response:**
```json
{
  "token": "abc123xyz...",
  "points": 500,
  "value_usd": 5.00,
  "expires_in": 1800,
  "qr_data": "{\"token\":\"abc123xyz...\",\"pts\":500}"
}
```

**Errores:**
| Código | Descripción |
|--------|-------------|
| 400    | Puntos por debajo del mínimo o saldo insuficiente |

---

### POST /redeem-token/consume
Valida y consume un token de redención. **Solo para staff/cajeros** (requiere `manage_woocommerce`).

**Body:**
```json
{ "token": "abc123xyz..." }
```

**Response:**
```json
{
  "success": true,
  "user": "María García",
  "points_used": 500,
  "new_balance": 1000
}
```

**Errores:**
| Código | Descripción |
|--------|-------------|
| 404    | Token inválido o expirado |

**Ejemplo:**
```bash
curl -u "admin:app_password" -X POST \
  -H "Content-Type: application/json" \
  -d '{"token":"abc123xyz..."}' \
  https://elranchobakery.com/wp-json/erbl/v1/redeem-token/consume
```

---

### PUT /profile
Actualiza datos del perfil del usuario autenticado.

**Body:**
```json
{ "birthday": "1990-05-15" }
```

**Response:**
```json
{ "success": true, "updated": { "birthday": "1990-05-15" } }
```

**Errores:**
| Código | Descripción |
|--------|-------------|
| 400    | Formato de fecha inválido (debe ser YYYY-MM-DD) o sin datos |

**Ejemplo:**
```bash
curl -u "usuario:app_password" -X PUT \
  -H "Content-Type: application/json" \
  -d '{"birthday":"1990-05-15"}' \
  https://elranchobakery.com/wp-json/erbl/v1/profile
```

---

## Códigos de error comunes

| HTTP | WP Error Code | Descripción |
|------|--------------|-------------|
| 401  | rest_forbidden | No autenticado |
| 403  | rest_forbidden | Sin permisos |
| 400  | (varies) | Datos inválidos |
| 404  | (varies) | Recurso no encontrado |

---

*Generado: 2026-03-16 — El Rancho Bakery*
