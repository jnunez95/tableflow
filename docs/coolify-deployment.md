# TableFlow en Coolify

Guía basada en [Coolify Laravel docs](https://coolify.io/docs/applications/laravel).

## Configuración en Coolify UI

| Campo | Valor |
|-------|-------|
| Build Pack | **Nixpacks** |
| Ports Exposes | **80** |
| Branch | `main` |

## Base de datos (requerida)

Crea **MySQL** en el mismo proyecto y usa sus credenciales:

```env
APP_NAME=TableFlow
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...   # php artisan key:generate --show
APP_URL=https://tu-dominio.sslip.io

DB_CONNECTION=mysql
DB_HOST=<mysql-host-interno-coolify>
DB_PORT=3306
DB_DATABASE=tableflow_central
DB_USERNAME=root
DB_PASSWORD=<password>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Post-deployment command

En Coolify → Application → **Post Deployment**:

```bash
php artisan migrate --force && php artisan storage:link && php artisan optimize
```

## Servidor localhost (Colima / macOS)

Si usas Colima, en **Servers → localhost** pon **SSH port = 2222**.

## Archivos de deploy en este repo

- `nixpacks.toml` — nginx + php-fpm + queue worker + scheduler (supervisor)
- Corrige el crash `duplicate location "/"` del build Nixpacks por defecto
- Incluye `fastcgi_buffer_size 8k` para Inertia.js

## Redeploy

Tras push a `main`, haz **Redeploy** en Coolify (rebuild completo).
