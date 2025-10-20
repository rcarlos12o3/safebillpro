# Guía de Configuración - Ambiente de Pre-Producción Local

## Requisitos Previos

### Software Necesario

1. **Laravel Herd** - Servidor web local para Mac
   - Descargar desde: https://herd.laravel.com
   - Incluye PHP, Nginx, y MySQL

2. **Docker Desktop** - Para compilar assets
   - Descargar desde: https://www.docker.com/products/docker-desktop

3. **Git** - Para clonar el repositorio
   - Pre-instalado en macOS o desde: https://git-scm.com

## Paso 1: Preparar el Entorno

### 1.1 Instalar Laravel Herd

```bash
# Descargar e instalar Herd desde https://herd.laravel.com
# Herd creará automáticamente el directorio ~/Herd/
```

### 1.2 Instalar Docker Desktop

```bash
# Descargar e instalar Docker Desktop desde https://www.docker.com
# Asegurarse de que Docker esté corriendo antes de continuar
```

## Paso 2: Clonar el Proyecto

```bash
# Navegar al directorio de Herd
cd ~/Herd/

# Clonar el repositorio
git clone [URL_DEL_REPOSITORIO] safebillpro

# Entrar al directorio del proyecto
cd safebillpro
```

## Paso 3: Configurar Base de Datos

### 3.1 Crear la Base de Datos

```bash
# Abrir MySQL desde Herd (o usar cualquier cliente MySQL)
mysql -u root

# Crear la base de datos
CREATE DATABASE safebillpro_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

## Paso 4: Configurar Variables de Entorno

### 4.1 Copiar y Configurar .env

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# O si ya existe .env de producción, hacer backup
cp .env .env.production.backup
```

### 4.2 Editar .env con la siguiente configuración

```env
APP_NAME="Facturador Local"
APP_ENV=local
APP_KEY=base64:ZV4Ska9Vz/gPeQsT3wxgUjP9gz8Rsbt7eonp+raeFpU=
APP_DEBUG=true
APP_URL_BASE=safebillpro.test
APP_URL=http://${APP_URL_BASE}

LOG_CHANNEL=daily

# Base de Datos Local
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=safebillpro_dev
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Deshabilitar HTTPS para desarrollo local
FORCE_HTTPS=false

# Resto de configuraciones (mantener las del proyecto)
LIMIT_UUID_LENGTH_32=true
TENANCY_DATABASE_AUTO_DELETE=true
TENANCY_DATABASE_AUTO_DELETE_USER=true
ITEMS_PER_PAGE=20
PASSWORD_CHANGE=true
PREFIX_DATABASE=tenancy
```

## Paso 5: Instalar Dependencias de PHP

### 5.1 Descargar vendor desde Producción (Recomendado)

**Opción A: Desde el servidor de producción**

```bash
# En el servidor de producción
cd /var/www/safebill.dev/html
tar -czf vendor.tar.gz vendor/ composer.lock

# Descargar a tu Mac
scp root@206.189.215.101:/var/www/safebill.dev/html/vendor.tar.gz ~/Herd/safebillpro/

# Extraer en el proyecto local
cd ~/Herd/safebillpro
tar -xzf vendor.tar.gz
rm vendor.tar.gz
```

**Opción B: Instalar localmente (Alternativo)**

```bash
composer install --no-dev
```

**Nota:** La Opción A es más rápida y evita problemas de compatibilidad.

## Paso 6: Configurar Permisos

```bash
cd ~/Herd/safebillpro

# Dar permisos de escritura a storage y bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Si es necesario, cambiar el propietario
# chown -R $(whoami):staff storage bootstrap/cache
```

## Paso 7: Limpiar Cache de Laravel

```bash
cd ~/Herd/safebillpro

# Limpiar cache de configuración
php artisan config:clear

# Limpiar cache de vistas
php artisan view:clear

# Limpiar cache de rutas (si existe)
php artisan route:clear 2>/dev/null || true

# Limpiar cache general
php artisan cache:clear 2>/dev/null || true
```

## Paso 8: Ejecutar Migraciones

```bash
cd ~/Herd/safebillpro

# Ejecutar migraciones
php artisan migrate --seed

# Verificar conexión a la base de datos
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

## Paso 9: Compilar Assets (JavaScript/CSS)

### 9.1 Descargar node_modules desde Producción (Recomendado)

**Opción A: Desde el servidor de producción**

```bash
# En el servidor de producción
cd /var/www/safebill.dev/html
tar -czf node_modules.tar.gz node_modules/

# Descargar a tu Mac
scp root@206.189.215.101:/var/www/safebill.dev/html/node_modules.tar.gz ~/Herd/safebillpro/

# Extraer en el proyecto local
cd ~/Herd/safebillpro
tar -xzf node_modules.tar.gz
rm node_modules.tar.gz
```

### 9.2 Compilar Assets con Docker

```bash
cd ~/Herd/safebillpro

# Compilar para producción (optimizado)
docker run --platform linux/amd64 --rm -v $(pwd):/app -w /app node:14 npm run production

# O para desarrollo (con source maps)
docker run --platform linux/amd64 --rm -v $(pwd):/app -w /app node:14 npm run dev
```

**Por qué usamos Docker:**
- El proyecto usa `node-sass@4.14.1` que requiere Python 2 y no es compatible con Apple Silicon (M1/M2/M3)
- Docker con `--platform linux/amd64` emula arquitectura x86 para compilar correctamente
- Evita instalar Node 14 y Python 2 en tu sistema

**Tiempo de compilación:** ~4-5 minutos

### 9.3 Verificar Assets Compilados

```bash
ls -lh public/js/app.js    # Debería ser ~7-8 MB
ls -lh public/css/app.css  # Debería ser ~600-700 KB
```

## Paso 10: Configurar Herd (Automático)

Laravel Herd detecta automáticamente los proyectos en `~/Herd/`:

```bash
# Abrir la aplicación Herd desde el Dock o Spotlight
open -a "Herd"

# El proyecto safebillpro debería aparecer automáticamente en la lista
# URL: http://safebillpro.test
```

### 10.1 Verificar Configuración

```bash
# Verificar que el sitio responde
curl -I http://safebillpro.test

# Debería retornar HTTP 200 o 302 (redirect al login)
```

## Paso 11: Acceder al Sistema

### 11.1 Abrir en el Navegador

```bash
open http://safebillpro.test
```

### 11.2 Verificar Funcionamiento

- ✅ Página de login visible
- ✅ Título: "Facturador Local"
- ✅ Laravel Debug Bar en la parte inferior (modo desarrollo)
- ✅ Assets CSS/JS cargados correctamente

## Solución de Problemas Comunes

### Error: "Connection refused" al acceder a la BD

```bash
# Verificar que MySQL esté corriendo en Herd
# Abrir la app Herd > Settings > Services > MySQL: ON

# Verificar conexión
php artisan tinker
>>> DB::connection()->getPdo();
```

### Error: "The stream or file could not be written"

```bash
# Problema de permisos en storage
chmod -R 775 storage bootstrap/cache
```

### Error: "Mix Manifest not found"

```bash
# Assets no compilados, ejecutar:
docker run --platform linux/amd64 --rm -v $(pwd):/app -w /app node:14 npm run production
```

### El sitio no carga (404 Not Found)

```bash
# Verificar que el proyecto está en ~/Herd/
pwd  # Debería mostrar: /Users/[usuario]/Herd/safebillpro

# Reiniciar Herd
# Abrir Herd > Settings > Restart Services
```

### Error: "No application encryption key"

```bash
# Generar nueva key (solo si no existe APP_KEY en .env)
php artisan key:generate
```

## Flujo de Trabajo de Desarrollo

### Compilar Assets Cuando Hagas Cambios en JS/CSS

```bash
cd ~/Herd/safebillpro

# Para desarrollo (más rápido, con source maps)
docker run --platform linux/amd64 --rm -v $(pwd):/app -w /app node:14 npm run dev

# Para producción (optimizado, minificado)
docker run --platform linux/amd64 --rm -v $(pwd):/app -w /app node:14 npm run production
```

### Ver Logs en Tiempo Real

```bash
tail -f storage/logs/laravel-*.log
```

### Limpiar Cache Durante Desarrollo

```bash
php artisan config:clear && php artisan view:clear && php artisan cache:clear
```

## Arquitectura del Proyecto

### Stack Tecnológico
- **Backend:** Laravel 5.8 (PHP 7.4)
- **Frontend:** Vue.js 2.5
- **Base de Datos:** MySQL/MariaDB
- **CSS:** SASS (compilado con node-sass 4.14.1)
- **Assets:** Laravel Mix 3.0.0

### Directorios Importantes
```
safebillpro/
├── app/                 # Código PHP de la aplicación
├── modules/             # Módulos personalizados
├── public/              # Assets públicos (CSS, JS compilados)
├── resources/           # Vistas, assets sin compilar
│   ├── js/              # JavaScript/Vue.js
│   └── sass/            # SASS/CSS
├── storage/             # Logs, cache, uploads
├── vendor/              # Dependencias PHP (Composer)
├── node_modules/        # Dependencias JavaScript (NPM)
└── .env                 # Configuración del entorno
```

## Notas Importantes

### ⚠️ NO Subir a Git
- `.env` (contiene credenciales)
- `vendor/` (se instala con composer)
- `node_modules/` (se instala con npm)
- `public/js/app.js` y `public/css/app.css` (se compilan)
- `storage/logs/*` (logs locales)

### ✅ Mantener en Sync con Producción
- Código fuente (git pull)
- Base de datos (dump periódico si es necesario)
- Assets compilados (recompilar después de cambios)

### 🔄 Antes de Subir Cambios a Producción
1. Probar completamente en local
2. Compilar assets con `npm run production`
3. Commit solo del código fuente (no assets compilados si no es necesario)
4. En producción, hacer pull y recompilar

## Información de Contacto del Proyecto

- **Repositorio:** [URL del repositorio de GitHub]
- **Servidor de Producción:** 206.189.215.101
- **URL de Producción:** https://safebill.dev

---

**Última actualización:** 20 de Octubre 2025
**Versión de Laravel:** 5.8.x
**Versión de PHP:** 7.4.33
**Versión de Node (Docker):** 14.x
