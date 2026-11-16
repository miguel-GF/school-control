# Guía de despliegue y solución de pantalla en blanco

**Versiones actuales**

| Componente | Versión |
|---|---|
| Laravel | 13.42.0 |
| PHP | 8.4.x |
| Frontend | 1.1.0 |
| API | 1.1.0 |

---

## 1. Síntoma: el frontend se ve en blanco

El API y el health responden bien, pero la página web queda en blanco y la consola del navegador muestra algo como:

```text
Uncaught (in promise) TypeError: Cannot read properties of null (reading 'component')
```

En el código fuente de la página aparece esto:

```html
<div id="app" data-page="{...}"></div>
```

Pero el frontend nuevo necesita esto:

```html
<script data-page="app" type="application/json">{...}</script>
<div id="app"></div>
```

---

## 2. Causa

Laravel guarda las vistas Blade ya compiladas en:

```text
storage/framework/views/
```

Si esa vista compilada es de la versión anterior de Inertia, sigue generando el `<div id="app" data-page="...">` viejo, aunque ya hayas subido el `vendor` y el `public/build` nuevos.

Como `resources/views/app.blade.php` no cambió, Laravel no vuelve a compilarla automáticamente.

---

## 3. Cómo ver el código fuente de la página

En Chrome o Edge:

- `Ctrl + U`
- o clic derecho → **Ver código fuente de la página**
- o escribe en la barra de direcciones:

```text
view-source:https://escuela.rtsys.mx/
```

> No uses la pestaña **Elements** de DevTools. Ahí ves el DOM después de que Vue lo modifica. Usa `Ctrl + U`, que muestra el HTML original que entrega el servidor.

---

## 4. Solución

### Opción A: sin SSH (cPanel / File Manager)

1. Entra al administrador de archivos del hosting.
2. Ve a la carpeta del proyecto Laravel.
3. Abre:

```text
storage/framework/views/
```

4. Elimina **todos los archivos `.php`** que estén dentro.
   - No borres la carpeta `views`, solo su contenido.
5. Recarga el navegador con:

```text
Ctrl + Shift + R
```

6. Abre de nuevo `Ctrl + U` y verifica que ahora aparezca:

```html
<script data-page="app" type="application/json">
```

### Opción B: con SSH

```bash
cd /ruta/del/proyecto
php artisan view:clear
```

Luego recarga con `Ctrl + Shift + R`.

---

## 5. Si sigue apareciendo el `<div id="app" data-page=...>`

### 5.1 Verifica la ruta real de las vistas compiladas

Revisa el `.env` del servidor. Si tiene una línea como:

```env
VIEW_COMPILED_PATH=/otra/ruta
```

Entonces borra los `.php` de **esa** ruta, no de `storage/framework/views`.

### 5.2 Limpia OPcache

Si el hosting tiene OPcache activo, puede seguir ejecutando el archivo compilado viejo aunque lo hayas borrado.

Crea temporalmente este archivo en `public/`:

```php
<?php
opcache_reset();
echo 'ok';
```

Guárdalo como:

```text
public/clear-opcache.php
```

Abre en el navegador:

```text
https://escuela.rtsys.mx/clear-opcache.php
```

Después **borra** `clear-opcache.php` del servidor y recarga la página con `Ctrl + Shift + R`.

### 5.3 Fuerza la recompilación de la vista

Vuelve a subir este archivo al servidor, aunque sea el mismo contenido:

```text
resources/views/app.blade.php
```

Al cambiar la fecha del archivo, Laravel lo recompila.

También puedes agregar un comentario temporal en el archivo y subirlo:

```blade
{{-- deploy 1.1.0 --}}
```

---

## 6. Verificación final

### 6.1 API

```text
https://escuela.rtsys.mx/api
```

Debe devolver JSON:

```json
{
  "name": "Laravel API",
  "type": "api",
  "version": "1.1.0",
  "laravel": "13.42.0",
  "php": "8.4.27",
  "environment": "production"
}
```

### 6.2 Health

```text
https://escuela.rtsys.mx/api/v1/mobile/health
```

Debe devolver:

```json
{
  "ok": true,
  "service": "school-control-mobile",
  "version": "1.1.0"
}
```

### 6.3 Login

La pantalla de login debe mostrar abajo:

```text
Laravel · v1.1.0
```

### 6.4 Código fuente

En `Ctrl + U` debe aparecer:

```html
<script data-page="app" type="application/json">
```

y **ya no**:

```html
<div id="app" data-page="...">
```

> Importante: usa `/api`, no `/api/v1`, para probar el home de la API. `/api/v1` no es un endpoint y cae en la ruta catch-all del frontend.

---

## 7. Checklist de despliegue manual

### Archivos de código que deben estar en el servidor

```text
app/Http/Middleware/VerifyCsrfToken.php
composer.json
composer.lock
config/app.php
config/sanctum.php
package.json
package-lock.json
routes/api.php
.env.example

resources/js/app.js
resources/js/constants.js
resources/js/Pages/Login.vue
resources/js/Layouts/MainLayout.vue
resources/js/Components/TheDialogConfirm.vue
resources/js/Components/TheDialogResponse.vue
```

### Archivos generados

```text
public/build/       ← completo, incluyendo manifest.json
vendor/             ← completo, o ejecutar composer install en el servidor
```

> Si subes `public/build`, borra primero el `public/build` viejo del servidor y luego sube el nuevo completo. Si solo pegas archivos encima, el `manifest.json` viejo puede seguir activo.

### Variables en el `.env` del servidor

```env
FRONTEND_VERSION=1.1.0
API_VERSION=1.1.0
```

> No subas tu `.env` local. El `.env` del servidor tiene su propio `APP_KEY`, credenciales de base de datos, etc.

### Archivos que NO se deben subir

```text
public/hot
node_modules/
.git/
storage/logs/
```

### Cachés que se deben borrar después de subir

```text
bootstrap/cache/config.php
bootstrap/cache/routes-*.php
bootstrap/cache/packages.php
bootstrap/cache/services.php
storage/framework/views/*.php
```

Con SSH:

```bash
php artisan view:clear
php artisan optimize:clear
```

---

## 8. Comandos útiles (SSH)

```bash
# Ver versión de Laravel
php artisan --version

# Limpiar vistas compiladas
php artisan view:clear

# Limpiar configuración, rutas, vistas y cachés de bootstrap
php artisan optimize:clear

# Instalar dependencias de producción
composer install --no-dev --optimize-autoloader

# Compilar el frontend
npm ci
npm run build

# Cachear configuración y rutas para producción (opcional)
php artisan optimize
```

---

## 9. Errores comunes

| Síntoma | Causa probable | Solución |
|---|---|---|
| Frontend en blanco y error `reading 'component'` | Vista Blade compilada vieja | Borrar `storage/framework/views/*.php` |
| `/api` devuelve HTML en vez de JSON | `routes/api.php` viejo o ruta cacheada | Subir `routes/api.php` y borrar `bootstrap/cache/routes-*.php` |
| Cambias `API_VERSION` y no se refleja | Configuración cacheada | `php artisan config:clear` o `php artisan optimize` |
| Cambias `FRONTEND_VERSION` y no se refleja | El frontend ya está compilado | Volver a ejecutar `npm run build` |
| El navegador sigue mostrando lo viejo | Caché del navegador u OPcache | `Ctrl + Shift + R` y limpiar OPcache |
| Se intenta cargar `http://localhost:5173` en producción | Existe `public/hot` | Borrar `public/hot` del servidor |

---

## 10. Versiones independientes

- **Frontend:** `FRONTEND_VERSION` en `.env`, se inyecta al compilar con `npm run build` y se muestra en el login.
- **API:** `API_VERSION` en `.env`, se lee en tiempo de ejecución y se expone en `GET /api`.

Puedes subir una sin la otra.
