<?php
/**
 * Template Name: Rancho Staff
 *
 * Página de redención para cajeros. Acceso por PIN.
 * Asignar en WP Admin → Páginas → crear página con slug "rancho-staff" y este template.
 */
defined('ABSPATH') || exit;

$settings    = function_exists('elrancho_loyalty_get_settings') ? elrancho_loyalty_get_settings() : [];
$pin_enabled = ! empty($settings['staff_pin']);
$api_base    = esc_url(rest_url('erbl/v1'));
$site_name   = get_bloginfo('name');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="theme-color" content="#b81417">
<title><?php echo esc_html($site_name); ?> — Staff</title>
<?php wp_head(); ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#f5f0eb;min-height:100vh;display:flex;flex-direction:column;-webkit-tap-highlight-color:transparent}
.staff-wrap{flex:1;display:flex;flex-direction:column;max-width:420px;margin:0 auto;width:100%;padding:1.5rem 1rem 2rem}

/* Header */
.staff-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;padding-top:env(safe-area-inset-top)}
.staff-logo{font-size:1rem;font-weight:700;color:#b81417;display:flex;align-items:center;gap:6px}
.staff-badge{font-size:0.6875rem;font-weight:600;background:#b81417;color:#fff;padding:2px 8px;border-radius:99px;letter-spacing:.03em}
.staff-logout{font-size:0.8125rem;color:#7D6B60;background:none;border:none;cursor:pointer;padding:4px 8px}

/* Screens */
.screen{display:none;flex-direction:column;flex:1}
.screen.active{display:flex}

/* PIN screen */
.pin-card{background:#fff;border-radius:20px;padding:2rem 1.5rem;text-align:center;box-shadow:0 2px 16px rgba(74,59,50,.08)}
.pin-icon{font-size:2.5rem;margin-bottom:1rem}
.pin-title{font-size:1.25rem;font-weight:700;color:#4A3B32;margin-bottom:.25rem}
.pin-sub{font-size:.875rem;color:#7D6B60;margin-bottom:1.5rem}
.pin-dots{display:flex;justify-content:center;gap:12px;margin-bottom:1.5rem}
.pin-dot{width:14px;height:14px;border-radius:50%;border:2px solid #e0d8cf;background:#fff;transition:all .15s}
.pin-dot.filled{background:#b81417;border-color:#b81417}
.pin-keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;max-width:260px;margin:0 auto}
.pin-key{aspect-ratio:1;background:#f8f2ec;border:none;border-radius:14px;font-size:1.25rem;font-weight:600;color:#4A3B32;cursor:pointer;transition:background .1s;display:flex;align-items:center;justify-content:center}
.pin-key:active{background:#e8d5c4}
.pin-key.delete{font-size:1rem;color:#7D6B60}
.pin-key.empty{background:transparent;pointer-events:none}
.pin-error{color:#b81417;font-size:.875rem;margin-top:1rem;min-height:1.25rem}

/* Scanner screen */
.scanner-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 2px 16px rgba(74,59,50,.08);flex:1;display:flex;flex-direction:column}
.scanner-video-wrap{position:relative;background:#000;aspect-ratio:1;width:100%}
#staff-video{width:100%;height:100%;object-fit:cover}
.scanner-overlay{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none}
.scanner-frame{width:65%;aspect-ratio:1;border:3px solid #b81417;border-radius:16px;box-shadow:0 0 0 9999px rgba(0,0,0,.45)}
.scanner-corner{position:absolute;width:20px;height:20px;border-color:#fff;border-style:solid;border-width:0}
.scanner-corner.tl{top:0;left:0;border-top-width:3px;border-left-width:3px;border-radius:4px 0 0 0}
.scanner-corner.tr{top:0;right:0;border-top-width:3px;border-right-width:3px;border-radius:0 4px 0 0}
.scanner-corner.bl{bottom:0;left:0;border-bottom-width:3px;border-left-width:3px;border-radius:0 0 0 4px}
.scanner-corner.br{bottom:0;right:0;border-bottom-width:3px;border-right-width:3px;border-radius:0 0 4px 0}
.scanner-hint{background:#fff;padding:1rem 1.25rem;text-align:center}
.scanner-hint p{font-size:.8125rem;color:#7D6B60;margin-bottom:.75rem}
.manual-input-wrap{display:flex;gap:8px}
.manual-input-wrap input{flex:1;border:1.5px solid #e0d8cf;border-radius:10px;padding:.625rem .875rem;font-size:.9375rem;color:#4A3B32;outline:none;font-family:monospace;text-transform:uppercase;letter-spacing:.05em}
.manual-input-wrap input:focus{border-color:#b81417}
.btn-scan{background:#b81417;color:#fff;border:none;border-radius:10px;padding:.625rem 1rem;font-size:.875rem;font-weight:600;cursor:pointer;white-space:nowrap}
.btn-scan:active{background:#8a0f11}
.scanner-status{font-size:.8125rem;color:#b81417;text-align:center;padding:.5rem 1.25rem;min-height:2rem}

/* Confirm screen */
.confirm-card{background:#fff;border-radius:20px;padding:1.75rem 1.5rem;box-shadow:0 2px 16px rgba(74,59,50,.08)}
.confirm-header{text-align:center;margin-bottom:1.5rem}
.confirm-avatar{width:56px;height:56px;background:#b81417;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.375rem;font-weight:700;color:#fff;margin:0 auto .75rem}
.confirm-name{font-size:1.125rem;font-weight:700;color:#4A3B32}
.confirm-tier{font-size:.875rem;color:#7D6B60;margin-top:2px}
.confirm-amount{background:#fdf8f1;border:1.5px solid #e8d5b0;border-radius:14px;padding:1.25rem;text-align:center;margin-bottom:1.25rem}
.confirm-pts{font-size:.8125rem;color:#7D6B60;margin-bottom:4px}
.confirm-discount{font-size:2.25rem;font-weight:700;color:#b81417;line-height:1}
.confirm-discount-label{font-size:.8125rem;color:#7D6B60;margin-top:4px}
.confirm-balance{font-size:.8125rem;color:#a89a92;margin-top:8px}
.confirm-actions{display:grid;grid-template-columns:1fr 2fr;gap:10px}
.btn-cancel{background:#f5f0eb;color:#7D6B60;border:none;border-radius:12px;padding:.875rem;font-size:.9375rem;font-weight:600;cursor:pointer}
.btn-confirm{background:#b81417;color:#fff;border:none;border-radius:12px;padding:.875rem;font-size:.9375rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px}
.btn-confirm:active{background:#8a0f11}
.btn-confirm:disabled{background:#e0d8cf;cursor:default}

/* Result screen */
.result-card{background:#fff;border-radius:20px;padding:2.5rem 1.5rem;text-align:center;box-shadow:0 2px 16px rgba(74,59,50,.08)}
.result-icon{font-size:3.5rem;margin-bottom:1rem}
.result-title{font-size:1.375rem;font-weight:700;margin-bottom:.5rem}
.result-title.success{color:#2d7a3e}
.result-title.error{color:#b81417}
.result-msg{font-size:.9375rem;color:#7D6B60;line-height:1.5;margin-bottom:.5rem}
.result-discount-box{background:#f0faf3;border:1.5px solid #b8dfc4;border-radius:14px;padding:1.25rem;margin:1.25rem 0}
.result-discount-label{font-size:.8125rem;color:#2d7a3e;font-weight:600;margin-bottom:4px}
.result-discount-val{font-size:2rem;font-weight:700;color:#2d7a3e}
.result-discount-hint{font-size:.75rem;color:#4A3B32;margin-top:6px;opacity:.7}
.btn-new{width:100%;background:#b81417;color:#fff;border:none;border-radius:12px;padding:1rem;font-size:1rem;font-weight:600;cursor:pointer;margin-top:1.25rem}
.btn-new:active{background:#8a0f11}

/* Spinner */
@keyframes spin{to{transform:rotate(360deg)}}
.spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
</style>
</head>
<body>
<div class="staff-wrap">

    <!-- Header -->
    <div class="staff-header">
        <div class="staff-logo">🥐 <?php echo esc_html($site_name); ?> <span class="staff-badge">STAFF</span></div>
        <button class="staff-logout" id="btn-logout" style="display:none" onclick="staffLogout()">Salir</button>
    </div>

    <?php if ( ! $pin_enabled ) : ?>
    <div style="background:#fff;border-radius:16px;padding:2rem;text-align:center;color:#7D6B60;">
        <div style="font-size:2rem;margin-bottom:.75rem">⚙️</div>
        <p style="font-weight:600;color:#4A3B32;margin-bottom:.5rem">Staff no configurado</p>
        <p style="font-size:.875rem">Configura el PIN en WooCommerce → Rancho Rewards → Configuración → Staff & Tienda.</p>
    </div>
    <?php else : ?>

    <!-- ── PANTALLA: PIN ── -->
    <div class="screen active" id="screen-pin">
        <div class="pin-card">
            <div class="pin-icon">🔐</div>
            <div class="pin-title">Acceso Staff</div>
            <div class="pin-sub">Ingresa tu PIN para continuar</div>
            <div class="pin-dots" id="pin-dots">
                <div class="pin-dot" id="dot-0"></div>
                <div class="pin-dot" id="dot-1"></div>
                <div class="pin-dot" id="dot-2"></div>
                <div class="pin-dot" id="dot-3"></div>
            </div>
            <div class="pin-keypad" id="pin-keypad">
                <?php foreach([1,2,3,4,5,6,7,8,9,'','0','⌫'] as $k): ?>
                <button class="pin-key <?php echo $k===''?'empty':''; echo $k==='⌫'?'delete':''; ?>"
                    <?php if($k!==''):?> onclick="pinKey('<?php echo $k; ?>')"<?php endif;?>>
                    <?php echo $k; ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="pin-error" id="pin-error"></div>
        </div>
    </div>

    <!-- ── PANTALLA: SCANNER ── -->
    <div class="screen" id="screen-scanner" style="gap:12px">
        <div class="scanner-card">
            <div class="scanner-video-wrap">
                <video id="staff-video" autoplay playsinline muted></video>
                <div class="scanner-overlay">
                    <div style="position:relative;width:65%;aspect-ratio:1">
                        <div class="scanner-frame"></div>
                        <div class="scanner-corner tl"></div>
                        <div class="scanner-corner tr"></div>
                        <div class="scanner-corner bl"></div>
                        <div class="scanner-corner br"></div>
                    </div>
                </div>
            </div>
            <div class="scanner-hint">
                <p>Apunta la cámara al QR del cliente, o ingresa el código manualmente</p>
                <div class="manual-input-wrap">
                    <input type="text" id="manual-code" placeholder="Código del cliente" maxlength="32" autocomplete="off" autocapitalize="characters">
                    <button class="btn-scan" onclick="submitCode()">Buscar</button>
                </div>
            </div>
            <div class="scanner-status" id="scanner-status"></div>
        </div>
    </div>

    <!-- ── PANTALLA: CONFIRMACIÓN ── -->
    <div class="screen" id="screen-confirm">
        <div class="confirm-card">
            <div class="confirm-header">
                <div class="confirm-avatar" id="confirm-avatar">?</div>
                <div class="confirm-name" id="confirm-name">—</div>
                <div class="confirm-tier" id="confirm-tier">—</div>
            </div>
            <div class="confirm-amount">
                <div class="confirm-pts" id="confirm-pts">— puntos</div>
                <div class="confirm-discount" id="confirm-discount">$0.00</div>
                <div class="confirm-discount-label">USD a descontar</div>
                <div class="confirm-balance" id="confirm-balance"></div>
            </div>
            <div class="confirm-actions">
                <button class="btn-cancel" onclick="showScreen('screen-scanner')">Cancelar</button>
                <button class="btn-confirm" id="btn-confirm" onclick="confirmRedemption()">
                    <span id="confirm-btn-text">✓ Confirmar</span>
                    <div class="spinner" id="confirm-spinner" style="display:none"></div>
                </button>
            </div>
        </div>
    </div>

    <!-- ── PANTALLA: RESULTADO ── -->
    <div class="screen" id="screen-result">
        <div class="result-card">
            <div class="result-icon" id="result-icon">✅</div>
            <div class="result-title" id="result-title">¡Listo!</div>
            <div class="result-msg" id="result-msg"></div>
            <div class="result-discount-box" id="result-discount-box">
                <div class="result-discount-label">APLICA ESTE DESCUENTO EN EL POS</div>
                <div class="result-discount-val" id="result-discount-val">$0.00</div>
                <div class="result-discount-hint">Ingresa manualmente en tu sistema de cobro</div>
            </div>
            <button class="btn-new" onclick="newScan()">Siguiente cliente</button>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
(function() {
    var API   = <?php echo json_encode(rest_url('erbl/v1')); ?>;
    var pin   = '';
    var currentToken = '';
    var stream = null;
    var scanning = false;
    var detector = null;
    var scanLoop = null;

    // ── PIN ──────────────────────────────────────────
    function pinKey(k) {
        var err = document.getElementById('pin-error');
        err.textContent = '';
        if (k === '⌫') { pin = pin.slice(0, -1); }
        else if (pin.length < 8) { pin += k; }
        updateDots();
        if (pin.length >= 4) {
            setTimeout(submitPin, 150);
        }
    }
    window.pinKey = pinKey;

    function updateDots() {
        for (var i = 0; i < 4; i++) {
            var d = document.getElementById('dot-' + i);
            if (d) d.classList.toggle('filled', i < pin.length);
        }
    }

    function submitPin() {
        // Validar PIN contra el servidor haciendo un preview con token vacío
        // Si el PIN es incorrecto, el servidor devuelve 401/403
        // Usamos un token dummy para verificar solo el PIN
        fetch(API + '/redeem-token/preview', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({token: '__pin_check__', staff_pin: pin})
        }).then(function(r) {
            if (r.status === 401 || r.status === 403) {
                // PIN incorrecto
                showPinError('PIN incorrecto');
            } else {
                // PIN correcto (puede ser 404 por token inválido, pero eso está bien)
                sessionStorage.setItem('erbl_staff_pin', pin);
                startScanner();
            }
        }).catch(function() {
            // Si hay error de red, asumimos que el PIN puede ser correcto e intentamos igual
            sessionStorage.setItem('erbl_staff_pin', pin);
            startScanner();
        });
    }

    function showPinError(msg) {
        document.getElementById('pin-error').textContent = msg;
        pin = '';
        updateDots();
    }

    // ── SCANNER ──────────────────────────────────────
    function startScanner() {
        showScreen('screen-scanner');
        document.getElementById('btn-logout').style.display = '';

        if (typeof BarcodeDetector !== 'undefined') {
            detector = new BarcodeDetector({formats: ['qr_code']});
            var video = document.getElementById('staff-video');
            navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}, audio: false})
                .then(function(s) {
                    stream = s;
                    video.srcObject = s;
                    video.play();
                    scanning = true;
                    scanLoop = requestAnimationFrame(scanFrame);
                })
                .catch(function() {
                    document.getElementById('scanner-status').textContent = 'Cámara no disponible. Usa el código manual.';
                });
        } else {
            document.getElementById('scanner-status').textContent = 'Usa el código manual (cámara no compatible con este browser).';
        }
    }

    function scanFrame() {
        if (!scanning) return;
        var video = document.getElementById('staff-video');
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            detector.detect(video).then(function(codes) {
                if (codes.length > 0) {
                    var raw = codes[0].rawValue;
                    var token = extractToken(raw);
                    if (token) {
                        scanning = false;
                        previewToken(token);
                        return;
                    }
                }
                scanLoop = requestAnimationFrame(scanFrame);
            }).catch(function() {
                scanLoop = requestAnimationFrame(scanFrame);
            });
        } else {
            scanLoop = requestAnimationFrame(scanFrame);
        }
    }

    function extractToken(raw) {
        try {
            var obj = JSON.parse(raw);
            return obj.token || null;
        } catch(e) {
            // Si no es JSON, puede ser el token directo
            if (/^[a-zA-Z0-9]{12,}$/.test(raw)) return raw;
            return null;
        }
    }

    window.submitCode = function() {
        var code = document.getElementById('manual-code').value.trim();
        if (!code) return;
        var token = extractToken(code) || code;
        previewToken(token);
    };

    // ── PREVIEW ──────────────────────────────────────
    function previewToken(token) {
        document.getElementById('scanner-status').textContent = 'Verificando...';
        currentToken = token;
        var storedPin = sessionStorage.getItem('erbl_staff_pin') || '';
        fetch(API + '/redeem-token/preview', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({token: token, staff_pin: storedPin})
        }).then(function(r) { return r.json(); }).then(function(data) {
            document.getElementById('scanner-status').textContent = '';
            if (data.valid) {
                showConfirm(data);
            } else {
                document.getElementById('scanner-status').textContent = data.message || 'Token inválido.';
                resumeScan();
            }
        }).catch(function() {
            document.getElementById('scanner-status').textContent = 'Error de conexión.';
            resumeScan();
        });
    }

    function resumeScan() {
        if (detector && stream) {
            scanning = true;
            scanLoop = requestAnimationFrame(scanFrame);
        }
    }

    // ── CONFIRM ──────────────────────────────────────
    function showConfirm(data) {
        stopCamera();
        var name = data.user || '—';
        document.getElementById('confirm-avatar').textContent = name.charAt(0).toUpperCase();
        document.getElementById('confirm-name').textContent = name;
        document.getElementById('confirm-tier').textContent = data.tier_label || '';
        document.getElementById('confirm-pts').textContent = data.points.toLocaleString() + ' puntos';
        document.getElementById('confirm-discount').textContent = '$' + data.value_usd.toFixed(2);
        document.getElementById('confirm-balance').textContent = 'Saldo restante: ' + data.balance_after.toLocaleString() + ' pts';
        showScreen('screen-confirm');
    }

    window.confirmRedemption = function() {
        var btn     = document.getElementById('btn-confirm');
        var btnTxt  = document.getElementById('confirm-btn-text');
        var spinner = document.getElementById('confirm-spinner');
        btn.disabled = true;
        btnTxt.style.display = 'none';
        spinner.style.display = '';
        var storedPin = sessionStorage.getItem('erbl_staff_pin') || '';
        fetch(API + '/redeem-token/consume', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({token: currentToken, staff_pin: storedPin})
        }).then(function(r) { return r.json(); }).then(function(data) {
            btn.disabled = false;
            btnTxt.style.display = '';
            spinner.style.display = 'none';
            if (data.success) {
                showResult(true, data);
            } else {
                showResult(false, {message: data.message || 'Error al procesar.'});
            }
        }).catch(function() {
            btn.disabled = false;
            btnTxt.style.display = '';
            spinner.style.display = 'none';
            showResult(false, {message: 'Error de conexión. Intenta de nuevo.'});
        });
    };

    // ── RESULT ───────────────────────────────────────
    function showResult(success, data) {
        var box = document.getElementById('result-discount-box');
        if (success) {
            document.getElementById('result-icon').textContent = '✅';
            document.getElementById('result-title').className = 'result-title success';
            document.getElementById('result-title').textContent = '¡Redención exitosa!';
            document.getElementById('result-msg').textContent = data.user + ' ha usado sus puntos.';
            document.getElementById('result-discount-val').textContent = '$' + data.value_usd.toFixed(2) + ' USD';
            box.style.display = '';
        } else {
            document.getElementById('result-icon').textContent = '❌';
            document.getElementById('result-title').className = 'result-title error';
            document.getElementById('result-title').textContent = 'Error';
            document.getElementById('result-msg').textContent = data.message || 'Algo salió mal.';
            box.style.display = 'none';
        }
        showScreen('screen-result');
    }

    window.newScan = function() {
        currentToken = '';
        document.getElementById('manual-code').value = '';
        document.getElementById('scanner-status').textContent = '';
        startScanner();
    };

    // ── UTILS ────────────────────────────────────────
    function showScreen(id) {
        document.querySelectorAll('.screen').forEach(function(s) { s.classList.remove('active'); });
        document.getElementById(id).classList.add('active');
    }
    window.showScreen = showScreen;

    function stopCamera() {
        scanning = false;
        if (scanLoop) cancelAnimationFrame(scanLoop);
        if (stream) { stream.getTracks().forEach(function(t){t.stop();}); stream = null; }
    }

    window.staffLogout = function() {
        stopCamera();
        sessionStorage.removeItem('erbl_staff_pin');
        pin = '';
        updateDots();
        document.getElementById('btn-logout').style.display = 'none';
        document.getElementById('pin-error').textContent = '';
        showScreen('screen-pin');
    };

    // Auto-login si hay PIN en sesión
    var savedPin = sessionStorage.getItem('erbl_staff_pin');
    if (savedPin) {
        pin = savedPin;
        startScanner();
    }
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
