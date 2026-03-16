# El Rancho Bakery - Tema WooCommerce

Tema artesanal de WordPress/WooCommerce diseñado para panaderías. Basado en el diseño visual de "El Rancho Bakery".

## Características

- ✅ **Soporte completo de WooCommerce** (tienda, carrito, checkout, mi cuenta)
- ✅ **Homepage personalizada** con Hero, Categorías, Productos Destacados, Lealtad y Nosotros
- ✅ **Sidebar de filtros** (categorías, precio, ingredientes)
- ✅ **Tarjetas de producto** con badges (Bestseller, Nuevo, Oferta), wishlist y AJAX add to cart
- ✅ **Página de producto** con galería, nota del panadero, tabs
- ✅ **Customizer** para editar textos, imágenes y redes sociales sin código
- ✅ **100% responsive** (móvil, tablet, escritorio)
- ✅ **Performance** optimizado (fuentes, lazy load, animaciones CSS)
- ✅ **Accesible** (ARIA labels, skip link, focus visible)

## Paleta de colores

| Variable              | Valor     | Uso                    |
|-----------------------|-----------|------------------------|
| `--color-primary`     | `#b81417` | Rojo principal, CTAs   |
| `--color-background`  | `#fdfbf7` | Fondo crema cálido     |
| `--color-text-main`   | `#4A3B32` | Texto principal marrón |
| `--color-text-light`  | `#7D6B60` | Texto secundario       |

## Instalación

1. Comprime la carpeta `elrancho-theme` como `.zip`
2. En WordPress: **Apariencia > Temas > Añadir nuevo > Subir tema**
3. Activa el tema
4. Activa el plugin **WooCommerce** si no está instalado
5. Ve a **Apariencia > Personalizar > El Rancho Bakery** para configurar imágenes y textos
6. Asigna la **Página de inicio estática** en Personalizar > Página de inicio

## Páginas requeridas

- **Inicio** → usar la plantilla "Página de Inicio" (se asigna sola si usas front-page.php)
- **Tienda** → creada por WooCommerce automáticamente
- **Carrito** → creada por WooCommerce automáticamente
- **Checkout** → creada por WooCommerce automáticamente
- **Mi Cuenta** → creada por WooCommerce automáticamente

## Menus de navegación

En **Apariencia > Menús** crea un menú y asígnalo a la ubicación "Menú Principal".

## Meta fields especiales para productos

En cada producto, encontrarás una caja de "Nota del Panadero" que se muestra en la página del producto.

Puedes agregar también:
- `_allergens` → Información de ingredientes y alérgenos
- `_nutrition` → Información nutricional

## Shortcodes

```
[elrancho_featured limit="4" category="pan-dulce"]
```
Muestra productos destacados de una categoría específica.

## Personalización

El tema usa variables CSS en `:root`, por lo que puedes cambiar colores fácilmente en `style.css` o con CSS personalizado desde el Customizer.

## Estructura de archivos

```
elrancho-theme/
├── style.css              ← Estilos principales + metadatos del tema
├── functions.php          ← Configuración, hooks, AJAX
├── header.php             ← Header con nav sticky
├── footer.php             ← Footer con newsletter
├── index.php              ← Template fallback
├── front-page.php         ← Homepage
├── searchform.php         ← Formulario de búsqueda personalizado
├── assets/
│   ├── css/               ← CSS adicionales (si necesitas)
│   ├── js/
│   │   └── main.js        ← JavaScript (mobile menu, AJAX cart, wishlist, animaciones)
│   └── images/
│       └── hero-bg.jpg    ← Imagen del hero (reemplaza con la tuya)
└── woocommerce/
    ├── archive-product.php  ← Página de tienda/catálogo
    └── single-product.php   ← Página de producto individual
```

## Dependencias

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 8.0+
