
### **Proyecto sistema escolar**

Desarrollado con:
+ Laravel 13
+ Inertia
+ Vue 3
+ Quasar 2
+ Node.js 18
+ PHP 8.4

Instalar dependencias de npm
```bash
npm install
```

Instalar dependencias de composer
```bash
composer install
```

En una terminal correr el servidor del cliente
```bash
npm run dev
```

En otra terminal correr el servidor del server
```bash
php artisan serve
```

```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Versiones desplegadas

- App (frontend): `APP_VERSION` en `.env`, se muestra en la pantalla de login.
- API: `API_VERSION` en `.env`, se expone en `GET /api`.

Ambas versiones son independientes y se pueden subir por separado.

### Documentación

- [Guía de despliegue y solución de pantalla en blanco](docs/despliegue.md)
