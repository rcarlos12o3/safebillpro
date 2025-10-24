# ⚠️ PRÁCTICAS SEGURAS DE DEPLOYMENT

## COMANDOS PROHIBIDOS EN PRODUCCIÓN

### 🚫 NUNCA ejecutar:

```bash
# Estos comandos BORRAN TODOS LOS DATOS:
php artisan tenancy:migrate:fresh
php artisan tenancy:migrate:refresh
php artisan tenancy:migrate:reset
php artisan migrate:fresh
php artisan migrate:refresh
php artisan migrate:reset
php artisan db:wipe

# En cualquier contexto de base de datos:
DROP DATABASE
TRUNCATE TABLE
DELETE FROM [tabla_importante] (sin WHERE muy específico)
```

## ✅ COMANDOS SEGUROS

### Para agregar nuevas migraciones:

```bash
# Verificar primero qué migraciones están pendientes
php artisan tenancy:migrate --pretend

# Ejecutar solo las migraciones nuevas (SEGURO)
php artisan tenancy:migrate

# Si necesitas rollback de UNA migración específica
php artisan tenancy:migrate:rollback --step=1
```

## 📝 PROCESO SEGURO DE DEPLOYMENT

### 1. Pre-deployment (Ambiente Local/Staging)

```bash
# Verificar qué va a cambiar
git status
git diff

# Probar migraciones en base de datos de prueba
php artisan tenancy:migrate --pretend

# Ver SQL que se ejecutará
php artisan tenancy:migrate --pretend --verbose
```

### 2. Backup ANTES de deployment

```bash
# Backup de TODAS las bases de datos tenant
mysqldump -u root --all-databases > backup_$(date +%Y%m%d_%H%M%S).sql

# O backup selectivo de tenants
for db in $(mysql -u root -e "SHOW DATABASES LIKE 'tenancy_%'" -sN); do
    mysqldump -u root $db > backup_${db}_$(date +%Y%m%d_%H%M%S).sql
done
```

### 3. Deployment en Producción

```bash
# 1. Pull código
git pull origin main

# 2. Instalar dependencias
composer install --no-dev --optimize-autoloader

# 3. Compilar assets
npm run production

# 4. SOLO ejecutar migraciones nuevas (SIN fresh, SIN refresh)
php artisan tenancy:migrate

# 5. Limpiar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Verificación Post-deployment

```bash
# Verificar que los datos siguen ahí
mysql -u root -e "
SELECT
    TABLE_SCHEMA as tenant,
    (SELECT COUNT(*) FROM \`TABLE_SCHEMA\`.documents) as docs,
    (SELECT COUNT(*) FROM \`TABLE_SCHEMA\`.companies) as companies
FROM INFORMATION_SCHEMA.SCHEMATA
WHERE SCHEMA_NAME LIKE 'tenancy_%'
LIMIT 5;
"

# Verificar logs de errores
tail -f storage/logs/laravel.log
```

## 🔐 PROTECCIÓN ADICIONAL

### Agregar a .bashrc o .zshrc:

```bash
# Función de seguridad para comandos peligrosos
migrate-fresh() {
    echo "⛔ COMANDO BLOQUEADO"
    echo "migrate:fresh BORRA TODOS LOS DATOS"
    echo "Si realmente necesitas hacer esto, usa: command php artisan migrate:fresh"
    return 1
}

alias tenancy:migrate:fresh='echo "⛔ COMANDO PROHIBIDO - BORRA TODOS LOS DATOS DE TODOS LOS TENANTS"'
alias tenancy:migrate:refresh='echo "⛔ COMANDO PROHIBIDO - BORRA TODOS LOS DATOS DE TODOS LOS TENANTS"'
```

## 📊 REGLA DE ORO

**SI UN COMANDO INCLUYE `fresh`, `refresh`, `reset`, `wipe`, `DROP` o `TRUNCATE`:**

1. ⏸️  PARA
2. 🤔 PIENSA: "¿Esto va a borrar datos?"
3. 💾 BACKUP primero
4. 🧪 PRUEBA en development
5. ✅ SOLO entonces ejecuta (si es absolutamente necesario)

## 🆘 EN CASO DE ERROR

Si accidentalmente se borran datos:

1. **NO PÁNICO**
2. **NO ejecutar más comandos**
3. **Verificar binlog**: `SHOW BINARY LOGS;`
4. **Recuperar desde backup** (si existe)
5. **Recuperar desde binlog** (si está habilitado)
6. **Contactar DBA/responsable** inmediatamente

## ⚠️ ESTE DOCUMENTO EXISTE POR UN ERROR REAL

El 23 de octubre de 2025, se ejecutó accidentalmente `php artisan tenancy:migrate:fresh` en un tenant de desarrollo, borrando todos los datos. Aunque era ambiente de prueba, esto habría sido CATASTRÓFICO en producción.

**LECCIÓN APRENDIDA**: NUNCA usar comandos destructivos sin backup previo.
