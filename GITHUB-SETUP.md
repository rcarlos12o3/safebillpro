# 🔐 Configuración de GitHub Secrets - Guía Rápida

## ✅ Preparación Completada

Ya tenemos todo listo en el servidor:
- ✅ SSH key existente verificada (`~/.ssh/id_ed25519`)
- ✅ Acceso SSH al servidor confirmado
- ✅ Directorio de backups creado: `/var/www/backups/safebill`

## 📋 Pasos para Configurar GitHub Secrets

### 1. Ir a la Configuración de Secrets

Abre este enlace en tu navegador:

```
https://github.com/rcarlos12o3/safebillpro/settings/secrets/actions
```

O manualmente:
1. Ve a: https://github.com/rcarlos12o3/safebillpro
2. Click en **Settings** (Configuración)
3. En el menú lateral izquierdo: **Secrets and variables** → **Actions**
4. Click en **New repository secret** (botón verde)

### 2. Agregar los 4 Secrets

Agrega estos secrets uno por uno haciendo click en "New repository secret":

#### Secret 1: PRODUCTION_HOST

- **Name:** `PRODUCTION_HOST`
- **Secret:** `206.189.215.101`
- Click en "Add secret"

#### Secret 2: PRODUCTION_USER

- **Name:** `PRODUCTION_USER`
- **Secret:** `root`
- Click en "Add secret"

#### Secret 3: PRODUCTION_PORT

- **Name:** `PRODUCTION_PORT`
- **Secret:** `22`
- Click en "Add secret"

#### Secret 4: PRODUCTION_SSH_KEY

- **Name:** `PRODUCTION_SSH_KEY`
- **Secret:** Copia TODO el contenido de tu clave privada (ver abajo)
- Click en "Add secret"

### 3. Obtener la Clave Privada SSH

En tu terminal, ejecuta:

```bash
cat ~/.ssh/id_ed25519
```

**Copia TODO el contenido**, incluyendo las líneas de inicio y fin:

```
-----BEGIN OPENSSH PRIVATE KEY-----
... (todo el contenido) ...
-----END OPENSSH PRIVATE KEY-----
```

**⚠️ IMPORTANTE:**
- Copia TODO desde `-----BEGIN` hasta `-----END`
- No agregues espacios extra al inicio o final
- No modifiques ningún carácter

### 4. Verificar que los Secrets Estén Configurados

Deberías ver los 4 secrets en la lista:
- ✅ PRODUCTION_HOST
- ✅ PRODUCTION_USER
- ✅ PRODUCTION_PORT
- ✅ PRODUCTION_SSH_KEY

**Nota:** GitHub solo muestra los nombres, no los valores (por seguridad).

## 🚀 Probar el Deployment

Una vez configurados los secrets, puedes probar el deployment:

### Opción 1: Deployment Automático (Push a main)

```bash
cd ~/Herd/safebillpro

# Hacer un pequeño cambio de prueba
echo "# CI/CD Test" >> .deployment-test.txt

# Commit y push
git add .
git commit -m "Test: Verificar CI/CD automático"
git push origin main
```

### Opción 2: Deployment Manual desde GitHub

1. Ve a: https://github.com/rcarlos12o3/safebillpro/actions
2. Click en "Deploy to Production" en el menú lateral
3. Click en "Run workflow" (botón azul)
4. Selecciona branch: `main`
5. Click en "Run workflow" (botón verde)

## 📊 Ver el Progreso del Deployment

1. Ve a: https://github.com/rcarlos12o3/safebillpro/actions
2. Verás el workflow ejecutándose
3. Click en el workflow para ver los logs en tiempo real

**Pasos que se ejecutarán:**
1. ✅ Checkout code
2. ✅ Setup Node.js 14
3. ✅ Setup PHP 7.4
4. ✅ Install Composer dependencies
5. ✅ Install NPM dependencies
6. ✅ Compile assets for production
7. ✅ Create deployment artifact
8. ✅ Upload artifact to server
9. ✅ Deploy on production server
   - Creating backup...
   - Extracting new release...
   - Setting permissions...
   - Clearing Laravel cache...
   - Running migrations...
   - Restarting services...
10. ✅ Health check

**Tiempo estimado:** 5-8 minutos

## 🔍 Verificar el Deployment

Después de que el workflow termine exitosamente:

```bash
# Verificar que el sitio responda
curl -I https://safebill.dev

# Debería retornar: HTTP/2 200 OK
```

O simplemente abre en tu navegador:
```
https://safebill.dev
```

## ❌ Solución de Problemas

### Error: "Permission denied (publickey)"

**Causa:** La clave SSH no se copió correctamente.

**Solución:**
1. Verifica que copiaste TODO el contenido de `cat ~/.ssh/id_ed25519`
2. Incluye `-----BEGIN OPENSSH PRIVATE KEY-----` y `-----END OPENSSH PRIVATE KEY-----`
3. No debe haber espacios extra al inicio o final
4. Borra el secret `PRODUCTION_SSH_KEY` en GitHub
5. Créalo de nuevo con el contenido correcto

### Error: "Host key verification failed"

**Causa:** El servidor no está en known_hosts de GitHub Actions.

**Solución:** El workflow está configurado para aceptar automáticamente. Si persiste, contacta al soporte.

### Error en "Compile assets"

**Causa:** Problema con node_modules o package.json.

**Solución:**
1. Verifica localmente:
   ```bash
   cd ~/Herd/safebillpro
   docker run --platform linux/amd64 --rm -v $(pwd):/app -w /app node:14 npm run production
   ```
2. Si funciona localmente, el error es del servidor

### El workflow se queda "en progreso"

**Solución:**
1. Espera 10 minutos (puede tardar)
2. Si sigue sin terminar, cancela el workflow
3. Revisa los logs para ver dónde se atascó
4. Vuelve a ejecutar

## 📚 Recursos Adicionales

- **Documentación completa:** [DEPLOYMENT.md](./DEPLOYMENT.md)
- **Setup local:** [SETUP-LOCAL.md](./SETUP-LOCAL.md)
- **GitHub Actions:** https://github.com/rcarlos12o3/safebillpro/actions

## ✅ Checklist Final

Antes de hacer tu primer deployment, verifica:

- [ ] 4 secrets configurados en GitHub
  - [ ] PRODUCTION_HOST = `206.189.215.101`
  - [ ] PRODUCTION_USER = `root`
  - [ ] PRODUCTION_PORT = `22`
  - [ ] PRODUCTION_SSH_KEY = (clave privada completa)
- [ ] SSH key funciona (ya verificado ✅)
- [ ] Directorio de backups creado (ya verificado ✅)
- [ ] .env existe en el servidor de producción
- [ ] Has hecho commit de los workflows de GitHub Actions

## 🎉 ¡Listo!

Una vez configurados los secrets, cada vez que hagas `git push origin main`,
GitHub Actions automáticamente:
1. Compilará los assets
2. Creará un backup
3. Desplegará a producción
4. Ejecutará migraciones
5. Verificará que todo funcione

**No más compilación manual, no más SSH manual!** 🚀

---

**Creado:** 20 de Octubre 2025
**Repositorio:** https://github.com/rcarlos12o3/safebillpro
