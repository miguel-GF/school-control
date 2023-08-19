
### **Proyecto sistema escolar**

Desarrollado con:
+ Laravel 10
+ Inertia
+ Vue 3
+ Quasar 2
+ Node.js 18
+ Php 8.1

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