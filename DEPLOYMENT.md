# Guía de Deployment - CI/CD con GitHub Actions

## 📋 Índice

1. [Flujo de Trabajo](#flujo-de-trabajo)
2. [Configuración Inicial](#configuración-inicial)
3. [Secretos de GitHub](#secretos-de-github)
4. [Workflows Configurados](#workflows-configurados)
5. [Proceso de Deployment](#proceso-de-deployment)
6. [Rollback en Caso de Errores](#rollback-en-caso-de-errores)
7. [Solución de Problemas](#solución-de-problemas)

---

## 🔄 Flujo de Trabajo

### Desarrollo Local

```
Mac (Desarrollo)
    ↓
Hacer cambios en el código
    ↓
Probar localmente en http://safebillpro.test
    ↓
Commit & Push a GitHub
    ↓
GitHub Actions (CI/CD)
    ↓
Servidor de Producción (206.189.215.101)
```

### Proceso Automatizado

1. **Desarrollas en Mac** (local o Mac Studio)
2. **Haces commit y push** a GitHub
3. **GitHub Actions automáticamente**:
   - ✅ Ejecuta tests
   - ✅ Verifica sintaxis de PHP
   - ✅ Compila assets (CSS/JS)
   - ✅ Crea backup en producción
   - ✅ Despliega a producción
   - ✅ Ejecuta migraciones
   - ✅ Limpia caches
   - ✅ Reinicia servicios
   - ✅ Verifica que el sitio responda

---

## ⚙️ Configuración Inicial

### 1. Preparar el Servidor de Producción

#### 1.1 Crear directorio para backups

```bash
ssh root@206.189.215.101

# Crear directorio de backups
mkdir -p /var/www/backups/safebill
chown -R www-data:www-data /var/www/backups/safebill
```

#### 1.2 Generar SSH Key para GitHub Actions

```bash
# En tu Mac, generar una nueva SSH key para deployment
ssh-keygen -t ed25519 -C "github-actions@safebillpro" -f ~/.ssh/safebillpro_deploy

# Esto creará dos archivos:
# - ~/.ssh/safebillpro_deploy (clave privada) - para GitHub Secrets
# - ~/.ssh/safebillpro_deploy.pub (clave pública) - para el servidor
```

#### 1.3 Agregar la clave pública al servidor

```bash
# Copiar la clave pública al servidor
ssh-copy-id -i ~/.ssh/safebillpro_deploy.pub root@206.189.215.101

# O manualmente:
cat ~/.ssh/safebillpro_deploy.pub | ssh root@206.189.215.101 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
```

#### 1.4 Verificar acceso SSH

```bash
# Probar que la clave funciona
ssh -i ~/.ssh/safebillpro_deploy root@206.189.215.101 "echo 'SSH OK'"
```

---

## 🔐 Secretos de GitHub

### 2. Configurar GitHub Secrets

Ve a tu repositorio en GitHub: https://github.com/rcarlos12o3/safebillpro

1. Click en **Settings** (Configuración)
2. En el menú lateral, click en **Secrets and variables** → **Actions**
3. Click en **New repository secret**

#### Secretos Necesarios

Agrega los siguientes secretos uno por uno:

| Nombre del Secret | Valor | Descripción |
|-------------------|-------|-------------|
| `PRODUCTION_HOST` | `206.189.215.101` | IP del servidor de producción |
| `PRODUCTION_USER` | `root` | Usuario SSH (puede ser root u otro usuario) |
| `PRODUCTION_PORT` | `22` | Puerto SSH (generalmente 22) |
| `PRODUCTION_SSH_KEY` | Contenido de `~/.ssh/safebillpro_deploy` | Clave privada SSH (todo el contenido del archivo) |

#### Cómo obtener la clave privada:

```bash
# En tu Mac, mostrar el contenido de la clave privada
cat ~/.ssh/safebillpro_deploy

# Copiar TODO el contenido, desde:
# -----BEGIN OPENSSH PRIVATE KEY-----
# hasta
# -----END OPENSSH PRIVATE KEY-----
```

**⚠️ IMPORTANTE:** Copia TODO el contenido, incluyendo las líneas de inicio y fin.

---

## 📦 Workflows Configurados

### Workflow 1: Tests (`tests.yml`)

**Se ejecuta cuando:**
- Haces push a `main` o `develop`
- Creas un Pull Request

**Qué hace:**
- ✅ Ejecuta tests de PHPUnit
- ✅ Verifica sintaxis de PHP
- ✅ Compila assets para verificar que no hay errores
- ✅ Crea una base de datos temporal para tests

### Workflow 2: Deploy to Production (`deploy-production.yml`)

**Se ejecuta cuando:**
- Haces push a la rama `main`
- Ejecutas manualmente desde GitHub (workflow_dispatch)

**Qué hace:**
1. Instala dependencias de Composer (sin dev)
2. Instala dependencias de NPM
3. Compila assets optimizados para producción
4. Crea un archivo comprimido con el código
5. Conecta al servidor vía SSH
6. Crea backup del código actual
7. Extrae el nuevo código
8. Ajusta permisos
9. Limpia caches de Laravel
10. Ejecuta migraciones
11. Optimiza Laravel (config cache, route cache)
12. Reinicia servicios (PHP-FPM, Supervisor)
13. Verifica que el sitio responda (health check)

---

## 🚀 Proceso de Deployment

### Opción 1: Deployment Automático (Recomendado)

```bash
# 1. En tu Mac, hacer cambios en el código
cd ~/Herd/safebillpro

# 2. Probar localmente
open http://safebillpro.test

# 3. Commit tus cambios
git add .
git commit -m "Descripción de los cambios"

# 4. Push a GitHub - esto activa el deployment automáticamente
git push origin main
```

**Resultado:**
- GitHub Actions se ejecuta automáticamente
- Puedes ver el progreso en: https://github.com/rcarlos12o3/safebillpro/actions
- En ~5-10 minutos, tus cambios estarán en producción

### Opción 2: Deployment Manual desde GitHub

1. Ve a: https://github.com/rcarlos12o3/safebillpro/actions
2. Selecciona **Deploy to Production** en el menú lateral
3. Click en **Run workflow**
4. Selecciona la rama `main`
5. Click en **Run workflow** (verde)

### Ver el Progreso del Deployment

1. Ve a: https://github.com/rcarlos12o3/safebillpro/actions
2. Click en el workflow que se está ejecutando
3. Verás el log en tiempo real de cada paso

**Ejemplo de logs:**
```
✅ Checkout code
✅ Setup Node.js 14
✅ Setup PHP 7.4
✅ Install Composer dependencies
✅ Compile assets for production
✅ Upload artifact to server
✅ Deploy on production server
   - Creating backup...
   - Extracting new release...
   - Setting permissions...
   - Clearing Laravel cache...
   - Running migrations...
   - Deployment completed successfully!
✅ Health check
```

---

## ⏪ Rollback en Caso de Errores

Si algo sale mal, puedes hacer rollback rápidamente:

### Rollback Manual

```bash
# Conectar al servidor
ssh root@206.189.215.101

# Ver backups disponibles
ls -lh /var/www/backups/safebill/

# Ejemplo de output:
# backup_20251020_143025.tar.gz  <- Más reciente
# backup_20251020_120015.tar.gz
# backup_20251019_180530.tar.gz

# Restaurar el backup más reciente
cd /var/www/safebill.dev/html
tar -xzf /var/www/backups/safebill/backup_20251020_120015.tar.gz

# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Reiniciar servicios
systemctl reload php7.4-fpm
# O si usas Docker:
docker exec safebillpro-fpm php-fpm reload
```

### Rollback desde Git

Si el problema es en el código:

```bash
# En tu Mac
cd ~/Herd/safebillpro

# Ver los últimos commits
git log --oneline -5

# Revertir al commit anterior
git revert HEAD
git push origin main

# O hacer hard reset (⚠️ cuidado, esto borra cambios)
git reset --hard HEAD~1
git push origin main --force
```

---

## 🔍 Solución de Problemas

### El deployment falla en "Upload artifact to server"

**Error:** `Permission denied (publickey)`

**Solución:**
1. Verifica que el secret `PRODUCTION_SSH_KEY` esté configurado correctamente
2. Verifica que la clave pública esté en el servidor:
   ```bash
   ssh root@206.189.215.101 "cat ~/.ssh/authorized_keys"
   ```

### El deployment falla en "Compile assets"

**Error:** `npm ERR! Failed at the ... script`

**Solución:**
1. Verifica que `package.json` tenga los scripts correctos
2. Prueba compilar localmente:
   ```bash
   docker run --platform linux/amd64 --rm -v $(pwd):/app -w /app node:14 npm run production
   ```

### El sitio muestra error 500 después del deployment

**Posibles causas:**

1. **Permisos incorrectos:**
   ```bash
   ssh root@206.189.215.101
   cd /var/www/safebill.dev/html
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

2. **Cache no limpiado:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Migraciones fallidas:**
   ```bash
   php artisan migrate --force
   ```

4. **Ver logs de Laravel:**
   ```bash
   tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
   ```

### GitHub Actions se queda "en progreso" sin finalizar

**Solución:**
1. Ve a: https://github.com/rcarlos12o3/safebillpro/actions
2. Click en el workflow en progreso
3. Click en "Cancel workflow" (arriba a la derecha)
4. Revisa los logs para ver dónde se atascó
5. Corrige el problema y vuelve a ejecutar

### El health check falla pero el sitio funciona

**Causa:** El sitio podría tardar más de 10 segundos en reiniciarse

**Solución:**
Edita `.github/workflows/deploy-production.yml`:
```yaml
- name: Health check
  run: |
    echo "Waiting 30 seconds for services to restart..."
    sleep 30  # Aumentar de 10 a 30 segundos
```

---

## 📊 Monitoreo

### Ver logs del deployment

```bash
# En GitHub
https://github.com/rcarlos12o3/safebillpro/actions

# En el servidor
ssh root@206.189.215.101
tail -f /var/www/safebill.dev/html/storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Verificar el estado del sitio

```bash
# Desde tu Mac
curl -I https://safebill.dev

# Debería retornar: HTTP/2 200
```

---

## 🎯 Mejores Prácticas

### 1. Desarrollo

- ✅ Siempre prueba en local antes de hacer push
- ✅ Usa commits descriptivos
- ✅ No subas archivos sensibles (.env, credenciales)
- ✅ Mantén el .gitignore actualizado

### 2. Deployment

- ✅ Haz deployment en horarios de bajo tráfico
- ✅ Comunica a los usuarios si es un cambio mayor
- ✅ Mantén backups antes de cambios críticos
- ✅ Verifica el health check después del deployment

### 3. Seguridad

- ✅ Nunca compartas las SSH keys
- ✅ Rota las SSH keys periódicamente
- ✅ Usa secretos de GitHub para credenciales
- ✅ No hagas commits directos a main (usa branches)

---

## 📝 Comandos Útiles

### Ver estado del deployment

```bash
# Ver workflows recientes
gh run list --repo rcarlos12o3/safebillpro

# Ver logs de un workflow específico
gh run view [RUN_ID] --log

# Ejecutar workflow manualmente
gh workflow run deploy-production.yml --ref main
```

### Monitoreo del servidor

```bash
# Conectar al servidor
ssh root@206.189.215.101

# Ver procesos de PHP
ps aux | grep php

# Ver uso de memoria
free -h

# Ver espacio en disco
df -h

# Ver últimos backups
ls -lht /var/www/backups/safebill/ | head -5
```

---

## 🔗 Enlaces Útiles

- **Repositorio:** https://github.com/rcarlos12o3/safebillpro
- **GitHub Actions:** https://github.com/rcarlos12o3/safebillpro/actions
- **Sitio de Producción:** https://safebill.dev
- **Servidor:** 206.189.215.101

---

## ✅ Checklist de Configuración

Antes de hacer tu primer deployment, verifica:

- [ ] Secretos de GitHub configurados
  - [ ] PRODUCTION_HOST
  - [ ] PRODUCTION_USER
  - [ ] PRODUCTION_PORT
  - [ ] PRODUCTION_SSH_KEY
- [ ] SSH Key agregada al servidor
- [ ] Directorio de backups creado
- [ ] .env de producción existe en el servidor
- [ ] Permisos correctos en el servidor
- [ ] Tests pasan localmente
- [ ] Assets compilados correctamente

---

**Última actualización:** 20 de Octubre 2025
