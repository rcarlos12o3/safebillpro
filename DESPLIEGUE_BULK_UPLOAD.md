# Despliegue del Módulo de Carga Masiva

## 📋 Resumen de Cambios

### Archivos Modificados
1. **app/Http/Middleware/RedirectModuleLevel.php**
   - Agregado reconocimiento del grupo `bulk_upload` (línea ~220)
   - Agregado caso de redirección para `bulk_upload` (línea ~387)

2. **app/Http/Controllers/Tenant/UserController.php**
   - Mejorada lógica del método `tables()` para manejar múltiples escenarios:
     - Usuario autenticado con permisos → usa sus permisos como referencia
     - Usuario admin con permisos → usa sus permisos como referencia
     - Sin usuarios con permisos → muestra TODOS los permisos disponibles
   - **Beneficio**: Mantiene compatibilidad con tenants existentes y permite configurar tenants nuevos

3. **app/Http/Controllers/Tenant/BulkUploadController.php** (nuevo)
   - Controlador para el módulo de carga masiva

4. **resources/views/tenant/bulk_upload/** (nuevos)
   - Vistas del módulo de carga masiva

### Migraciones Nuevas

#### Sistema (database/migrations/)
- **2025_10_24_115047_add_bulk_upload_to_module_levels.php**
  - Agrega permiso `bulk_upload` a la tabla `module_levels`
  - ✅ **Idempotente**: Verifica antes de insertar
  - ✅ **Seguro**: No modifica datos existentes

#### Tenant (database/migrations/tenant/)
- **2025_10_23_191217_create_bulk_upload_temp_table.php**
  - Crea tabla `bulk_upload_temp` para almacenar documentos temporales
  - Agrega permiso `bulk_upload` en cada tenant
  - ✅ **Idempotente**: Verifica antes de crear tabla e insertar permiso
  - ✅ **Seguro**: No afecta datos existentes

---

## 🚀 Proceso de Despliegue

### PASO 1: Backup (OBLIGATORIO)
```bash
# Base de datos del sistema
mysqldump -u root safebillpro > backup_sistema_$(date +%Y%m%d_%H%M%S).sql

# Base de datos de tenants (opcional pero recomendado)
# Se puede hacer desde el panel de administración o con script
```

### PASO 2: Pull del código
```bash
cd /ruta/al/proyecto
git pull origin main
```

### PASO 3: Ejecutar migraciones con verificación previa

```bash
# Ver qué migraciones se ejecutarán (modo preview)
php artisan migrate --pretend

# Si todo se ve bien, ejecutar migraciones del sistema
php artisan migrate

# Ver qué migraciones de tenant se ejecutarán (modo preview)
php artisan tenancy:migrate --pretend

# Si todo se ve bien, ejecutar migraciones de tenant
php artisan tenancy:migrate
```

**Resultado esperado:**
```
Migrating: 2025_10_24_115047_add_bulk_upload_to_module_levels
Migrated:  2025_10_24_115047_add_bulk_upload_to_module_levels

Migrating: 2025_10_23_191217_create_bulk_upload_temp_table
Migrated:  2025_10_23_191217_create_bulk_upload_temp_table
```

### PASO 4: Limpiar caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### PASO 5: Optimizar para producción
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔐 Asignación de Permisos

### Opción A: Desde la Interfaz Web (Recomendado)
1. Ingresar como administrador
2. Ir a **Configuration → Usuarios**
3. Editar el usuario → Pestaña **Permisos**
4. Expandir módulo **"Ventas"**
5. Marcar el checkbox **"Carga Masiva"**
6. Guardar

### Opción B: Desde SQL (Para múltiples usuarios)

```sql
-- Conectarse a la base de datos del tenant
USE tenancy_XXXXXXXX;

-- 1. Ver usuarios administradores
SELECT id, name, email FROM users WHERE type = 'admin';

-- 2. Ver el ID del permiso bulk_upload
SELECT id FROM module_levels WHERE value = 'bulk_upload';

-- 3. Asignar permiso a un usuario específico (reemplazar USER_ID y PERMISSION_ID)
INSERT INTO module_level_user (user_id, module_level_id)
VALUES (USER_ID, PERMISSION_ID);

-- Ejemplo: Asignar a usuario ID 2
-- INSERT INTO module_level_user (user_id, module_level_id)
-- SELECT 2, id FROM module_levels WHERE value = 'bulk_upload'
-- WHERE NOT EXISTS (
--     SELECT 1 FROM module_level_user
--     WHERE user_id = 2 AND module_level_id = (SELECT id FROM module_levels WHERE value = 'bulk_upload')
-- );

-- 4. Verificar que se asignó correctamente
SELECT
    u.name,
    u.email,
    ml.description as permiso
FROM module_level_user mlu
JOIN users u ON mlu.user_id = u.id
JOIN module_levels ml ON mlu.module_level_id = ml.id
WHERE ml.value = 'bulk_upload';
```

---

## ✅ Verificación Post-Despliegue

### 1. Verificar que las migraciones se ejecutaron
```bash
# Sistema
php artisan migrate:status | grep bulk_upload

# Tenant
php artisan tenancy:migrate --status | grep bulk_upload
```

### 2. Verificar que el permiso existe en la BD
```sql
-- Sistema
SELECT * FROM module_levels WHERE value = 'bulk_upload';

-- Tenant (conectarse a cada tenant)
USE tenancy_XXXXXXXX;
SELECT * FROM module_levels WHERE value = 'bulk_upload';
SELECT * FROM bulk_upload_temp LIMIT 1;  -- Debe existir la tabla
```

### 3. Verificar que el módulo aparece en el menú
- Login como administrador con permisos
- Verificar que aparece **"Ventas" → "Carga Masiva"** en el sidebar

### 4. Probar acceso a la ruta
- Acceder a `/bulk-upload`
- Debe mostrar la interfaz de carga masiva
- NO debe redirigir a otra página

---

## 🔧 Troubleshooting

### Problema: "Sin Datos" en pestaña Permisos al editar usuario

**Causa**: El usuario administrador no tiene permisos asignados aún.

**Solución**:
1. El nuevo código detecta esta situación automáticamente
2. Muestra TODOS los permisos disponibles
3. Asignar permisos básicos desde la interfaz

**Alternativa SQL**:
```sql
-- Asignar módulos básicos al usuario admin
INSERT INTO module_user (user_id, module_id)
SELECT 2, id FROM modules WHERE value IN ('documents', 'configuration', 'establishments')
WHERE NOT EXISTS (SELECT 1 FROM module_user WHERE user_id = 2 AND module_id = modules.id);

-- Asignar permisos básicos
INSERT INTO module_level_user (user_id, module_level_id)
SELECT 2, id FROM module_levels
WHERE value IN ('new_document', 'list_document', 'bulk_upload', 'users', 'users_establishments')
AND NOT EXISTS (SELECT 1 FROM module_level_user WHERE user_id = 2 AND module_level_id = module_levels.id);
```

### Problema: Redirección a /documents/create al intentar acceder

**Causa**: Usuario no tiene permisos asignados y el middleware redirige por defecto.

**Solución**: Asignar permisos al usuario (ver sección "Asignación de Permisos")

### Problema: No aparece "Carga Masiva" en el menú

**Causa**: Usuario no tiene el permiso `bulk_upload` asignado.

**Solución**:
1. Verificar en BD: `SELECT * FROM module_level_user WHERE module_level_id = (SELECT id FROM module_levels WHERE value = 'bulk_upload') AND user_id = X;`
2. Si no existe, asignar (ver SQL en sección "Asignación de Permisos")
3. Cerrar sesión y volver a entrar

---

## 📊 Impacto en Producción

### Cambios de Comportamiento

**Antes**:
- El método `tables()` buscaba usuario con ID=1 hardcodeado
- Si no existía, causaba error

**Ahora**:
- Busca usuario autenticado primero
- Luego busca cualquier admin con permisos
- Si no hay ninguno, muestra todos los permisos disponibles

**Impacto**:
- ✅ Positivo para tenants nuevos o sin configurar
- ✅ Compatible con tenants existentes que tienen usuario 1
- ⚠️ Los administradores podrían ver más opciones de permisos al editar usuarios (es intencional)

### Riesgos

- **Bajo**: Todas las migraciones son idempotentes
- **Bajo**: No se modifican datos existentes
- **Bajo**: UserController mejorado mantiene compatibilidad

---

## 📝 Notas Adicionales

- Los usuarios deben **cerrar sesión y volver a entrar** después de asignar permisos
- El permiso `bulk_upload` está asociado al módulo "Ventas" (module_id = 1)
- La tabla `bulk_upload_temp` se crea automáticamente en cada tenant
- El middleware valida el permiso en cada petición a `/bulk-upload/*`

---

## 🆘 Rollback (En caso de problemas)

### Si hay problemas críticos:

```bash
# 1. Revertir código
git revert HEAD

# 2. Revertir migraciones del sistema
php artisan migrate:rollback --step=1

# 3. Revertir migraciones de tenants
php artisan tenancy:migrate:rollback --step=1

# 4. Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## ✅ Checklist Final

Antes de dar por completado el despliegue:

- [ ] Backup de base de datos realizado
- [ ] Código actualizado desde git
- [ ] Migraciones ejecutadas con éxito (sistema)
- [ ] Migraciones ejecutadas con éxito (tenants)
- [ ] Caché limpiado
- [ ] Permiso `bulk_upload` existe en module_levels
- [ ] Tabla `bulk_upload_temp` existe en tenants
- [ ] Al menos un usuario admin tiene permiso asignado
- [ ] Menu "Carga Masiva" aparece en sidebar
- [ ] Acceso a `/bulk-upload` funciona correctamente
- [ ] No hay errores en logs

---

**Fecha de creación**: 2025-10-24
**Versión**: 1.0
