# Documentación: Actualización Masiva de Fechas en Documentos Electrónicos

## 📋 Resumen
Esta documentación describe el proceso para actualizar fechas de emisión de documentos electrónicos ya generados y regenerar sus archivos XML/PDF correspondientes para envío a SUNAT.

## 🎯 Caso de Uso
Cuando necesites cambiar la fecha de emisión de documentos electrónicos que ya fueron generados previamente (por ejemplo, de septiembre a octubre) y reenviarlos a SUNAT.

## 🔧 Prerrequisitos
- Acceso al servidor con Docker
- Acceso a la base de datos MySQL/MariaDB
- Permisos para modificar archivos en storage
- Un documento de referencia con la fecha correcta (como plantilla)

## 📝 Proceso Step-by-Step

### 1. Identificar Documentos a Actualizar
```sql
-- Verificar documentos en rango específico
SELECT COUNT(*) as total, MIN(date_of_issue) as fecha_min, MAX(date_of_issue) as fecha_max 
FROM tenancy_[TENANT_ID].documents 
WHERE series='FP01' AND number BETWEEN [INICIO] AND [FIN];
```

### 2. Actualizar Fechas en Base de Datos
```sql
-- Para documento individual
UPDATE tenancy_[TENANT_ID].documents 
SET 
    date_of_issue = '[NUEVA_FECHA]',
    has_xml = 0,
    has_pdf = 0, 
    has_cdr = 0,
    hash = NULL,
    qr = NULL,
    updated_at = NOW()
WHERE id = [DOCUMENT_ID];

-- Para rango masivo
UPDATE tenancy_[TENANT_ID].documents 
SET 
    date_of_issue = '[NUEVA_FECHA]',
    has_xml = 0,
    has_pdf = 0,
    has_cdr = 0,
    filename = CONCAT('[TENANT_ID]-01-[SERIE]-', number),
    unique_filename = CONCAT('[TENANT_ID]-01-[SERIE]-', number),
    hash = NULL,
    qr = NULL,
    updated_at = NOW()
WHERE series = '[SERIE]' AND number BETWEEN [INICIO] AND [FIN];
```

### 3. Limpiar Archivos Antiguos
```bash
# Eliminar archivos XML/PDF antiguos
docker exec [CONTAINER_NAME] rm -f /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/signed/[TENANT_ID]-01-[SERIE]-[NUMERO].xml
docker exec [CONTAINER_NAME] rm -f /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/unsigned/[TENANT_ID]-01-[SERIE]-[NUMERO].xml
docker exec [CONTAINER_NAME] rm -f /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/pdf/[TENANT_ID]-01-[SERIE]-[NUMERO].pdf
```

### 4. Generar Nuevos Archivos XML

⚠️ **PELIGRO CRÍTICO: NO USAR MÉTODO DE PLANTILLA PARA PRODUCCIÓN**

#### ❌ Opción A: Usando Plantilla Existente (SOLO PARA EMERGENCIAS EXTREMAS)
```bash
# ⚠️ ADVERTENCIA: Este método copia TODOS los datos del documento plantilla
# Incluyendo: cliente, montos, productos, impuestos, etc.
# SOLO usar si necesitas permitir reenvío técnico y planeas anular inmediatamente

# NO EJECUTAR EN PRODUCCIÓN SIN PLANIFICAR COMUNICACIÓN DE BAJA
docker exec [CONTAINER_NAME] cp /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/signed/[TENANT_ID]-01-[SERIE]-[REF].xml /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/signed/[TENANT_ID]-01-[SERIE]-[NUEVO].xml

docker exec [CONTAINER_NAME] cp /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/unsigned/[TENANT_ID]-01-[SERIE]-[REF].xml /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/unsigned/[TENANT_ID]-01-[SERIE]-[NUEVO].xml

# Solo cambia el número de serie, NO los datos comerciales
docker exec [CONTAINER_NAME] sed -i 's/[SERIE]-[REF]/[SERIE]-[NUEVO]/g' /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/signed/[TENANT_ID]-01-[SERIE]-[NUEVO].xml
docker exec [CONTAINER_NAME] sed -i 's/[SERIE]-[REF]/[SERIE]-[NUEVO]/g' /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/unsigned/[TENANT_ID]-01-[SERIE]-[NUEVO].xml
```

#### ✅ Opción B: Regeneración via Interfaz Web (RECOMENDADO)
- Acceder al documento en la interfaz web
- Intentar descargar XML/PDF forzará la regeneración automática con datos correctos
- Esta opción genera XMLs con la información real de cada documento

#### ✅ Opción C: Regeneración Artisan (PREFERIDO PARA MASIVOS)
```bash
# Resetear documentos para forzar regeneración
UPDATE tenancy_[TENANT_ID].documents 
SET has_xml = 0, has_pdf = 0, filename = NULL, unique_filename = NULL
WHERE [CONDICIONES];

# Después acceder via web o usar comando específico del sistema
```

### 5. Marcar como Disponible en Base de Datos
```sql
UPDATE tenancy_[TENANT_ID].documents 
SET has_xml = 1, updated_at = NOW() 
WHERE [CONDICIONES];
```

## 🚀 Script Automatizado para Proceso Masivo

### Para Rango de Documentos
```bash
#!/bin/bash
# Variables de configuración
TENANT_ID="20600421701"
SERIE="FP01"
INICIO=151
FIN=338
NUEVA_FECHA="2025-10-13"
PLANTILLA=342

echo "=== Iniciando actualización masiva ==="

# 1. Actualizar fechas
docker exec safebillpro-mariadb mysql -h [HOST] -P [PORT] -u [USER] -p[PASS] -e "
UPDATE tenancy_${TENANT_ID}.documents 
SET date_of_issue = '${NUEVA_FECHA}', 
    has_xml = 0, has_pdf = 0, has_cdr = 0, 
    filename = CONCAT('${TENANT_ID}-01-${SERIE}-', number),
    unique_filename = CONCAT('${TENANT_ID}-01-${SERIE}-', number),
    hash = NULL, qr = NULL, updated_at = NOW() 
WHERE series = '${SERIE}' AND number BETWEEN ${INICIO} AND ${FIN};
"

# 2. Limpiar archivos antiguos
for i in $(seq $INICIO $FIN); do
    docker exec safebillpro-fpm rm -f /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/signed/${TENANT_ID}-01-${SERIE}-${i}.xml 2>/dev/null
    docker exec safebillpro-fpm rm -f /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/unsigned/${TENANT_ID}-01-${SERIE}-${i}.xml 2>/dev/null
    docker exec safebillpro-fpm rm -f /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/pdf/${TENANT_ID}-01-${SERIE}-${i}.pdf 2>/dev/null
done

# 3. Generar nuevos archivos
for i in $(seq $INICIO $FIN); do
    if [ $i -ne $PLANTILLA ]; then
        docker exec safebillpro-fpm cp /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/signed/${TENANT_ID}-01-${SERIE}-${PLANTILLA}.xml /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/signed/${TENANT_ID}-01-${SERIE}-${i}.xml
        docker exec safebillpro-fpm cp /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/unsigned/${TENANT_ID}-01-${SERIE}-${PLANTILLA}.xml /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/unsigned/${TENANT_ID}-01-${SERIE}-${i}.xml
        
        docker exec safebillpro-fpm sed -i "s/${SERIE}-${PLANTILLA}/${SERIE}-${i}/g" /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/signed/${TENANT_ID}-01-${SERIE}-${i}.xml
        docker exec safebillpro-fpm sed -i "s/${SERIE}-${PLANTILLA}/${SERIE}-${i}/g" /var/www/html/storage/app/tenancy/tenants/tenancy_${TENANT_ID}/unsigned/${TENANT_ID}-01-${SERIE}-${i}.xml
    fi
done

# 4. Marcar como disponibles
docker exec safebillpro-mariadb mysql -h [HOST] -P [PORT] -u [USER] -p[PASS] -e "
UPDATE tenancy_${TENANT_ID}.documents 
SET has_xml = 1, updated_at = NOW() 
WHERE series = '${SERIE}' AND number BETWEEN ${INICIO} AND ${FIN};
"

echo "=== Proceso completado ==="
```

## ✅ Verificaciones Post-Proceso

### 1. Verificar Base de Datos
```sql
-- Contar documentos actualizados
SELECT COUNT(*) as total_actualizados, 
       MIN(date_of_issue) as fecha_min, 
       MAX(date_of_issue) as fecha_max, 
       SUM(has_xml) as con_xml 
FROM tenancy_[TENANT_ID].documents 
WHERE series='[SERIE]' AND number BETWEEN [INICIO] AND [FIN];
```

### 2. Verificar Archivos
```bash
# Contar archivos generados
docker exec [CONTAINER_NAME] ls /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/signed/ | grep "[SERIE]-" | wc -l

# Verificar contenido específico
docker exec [CONTAINER_NAME] grep "[SERIE]-[NUMERO]" /var/www/html/storage/app/tenancy/tenants/tenancy_[TENANT_ID]/signed/[TENANT_ID]-01-[SERIE]-[NUMERO].xml
```

## ⚠️ Consideraciones Importantes

### 🚨 PELIGROS CRÍTICOS DEL MÉTODO DE PLANTILLA
- **DATOS COMPLETAMENTE INCORRECTOS**: Copia TODOS los datos comerciales del documento plantilla
- **INFORMACIÓN FALSA EN SUNAT**: Cliente, RUC, montos, productos, impuestos serán incorrectos
- **PROBLEMA LEGAL**: Documentos enviados a SUNAT con información que no corresponde
- **COMUNICACIÓN DE BAJA OBLIGATORIA**: Todos los documentos enviados deben darse de baja inmediatamente
- **SOLO PARA EMERGENCIAS EXTREMAS**: Cuando necesites envío técnico urgente y ya tienes plan de baja
- **NUNCA EN PRODUCCIÓN**: Sin un plan de comunicación de baja inmediata

### CASO REAL - ERROR OCURRIDO 2025-10-17
- **Problema**: Se enviaron 68 documentos FP01 con datos del documento 342
- **Consecuencia**: SUNAT recibió 68 facturas con cliente, montos y productos incorrectos
- **Solución aplicada**: Comunicación de baja masiva + regeneración correcta
- **Lección**: NUNCA usar plantillas sin planificar la comunicación de baja ANTES del envío

### ✅ Mejores Prácticas SEGURAS
1. **NUNCA usar método de plantilla en producción** sin comunicación de baja planificada
2. **Preferir regeneración via interfaz web** para datos correctos
3. **Hacer respaldo completo** antes de cambios masivos
4. **Probar con UN SOLO documento** antes de proceso masivo
5. **Verificar datos reales** en XMLs generados antes de envío
6. **Documentar todos los cambios** para auditoría
7. **Coordinar con contador/área legal** antes de comunicaciones de baja masivas
8. **Verificar que regeneración produce datos correctos** del documento real, no plantilla

### Para SUNAT
- Los documentos con fechas modificadas pueden requerir **comunicación de baja** del original
- Verificar **regulaciones vigentes** sobre modificación de fechas de emisión
- Considerar marcar `regularize_shipping = 1` si es necesario

## 🔍 Troubleshooting

### Error: "signed/.xml File not found"
- **Causa**: Campo `filename` vacío o archivo XML no existe
- **Solución**: Verificar y actualizar campos `filename` y `unique_filename`

### Error: "Database [tenant] not configured"
- **Causa**: Script ejecutado fuera del contexto Laravel correcto
- **Solución**: Usar comandos MySQL directos o interfaz web

### Archivos no se regeneran automáticamente
- **Causa**: Campos `has_xml`, `has_pdf` no reseteados
- **Solución**: Establecer en 0 para forzar regeneración

## 📊 Ejemplo Completo Real

### ✅ Proceso CORRECTO (Recomendado)
```bash
# Caso: Actualizar FP01-149 a FP01-338 con fecha 2025-10-13
# Tenant: 20600421701

# 1. Verificar rango
SELECT COUNT(*) FROM tenancy_20600421701.documents 
WHERE series='FP01' AND number BETWEEN 149 AND 338;

# 2. Actualizar solo fechas y resetear archivos
UPDATE tenancy_20600421701.documents 
SET date_of_issue = '2025-10-13', 
    has_xml = 0, has_pdf = 0, has_cdr = 0,
    filename = NULL, unique_filename = NULL,
    hash = NULL, qr = NULL
WHERE series = 'FP01' AND number BETWEEN 149 AND 338;

# 3. Regenerar via interfaz web o comando Artisan (datos correctos)
# 4. Verificar que XMLs contienen información real de cada documento
```

### ❌ Proceso INCORRECTO (Error real ocurrido)
```bash
# ⚠️ LO QUE NO SE DEBE HACER - Error cometido 2025-10-17

# ERROR: Usar plantilla masivamente
for i in {149..338}; do
    cp documento-342.xml documento-${i}.xml  # ❌ COPIA DATOS INCORRECTOS
    sed -i "s/342/${i}/g" documento-${i}.xml # ❌ SOLO CAMBIA NÚMERO, NO DATOS
done

# CONSECUENCIA: 68 documentos enviados con datos del doc 342
# SOLUCIÓN REQUERIDA: Comunicación de baja masiva + regeneración correcta
```

## 🚨 Plan de Recuperación de Errores

### Si ya enviaste documentos con datos incorrectos:

```sql
-- 1. Crear comunicación de baja
INSERT INTO voided (user_id, external_id, soap_type_id, state_type_id, ubl_version, 
                   date_of_issue, date_of_reference, identifier, created_at, updated_at)
VALUES (1, UUID(), '02', '01', '2.1', CURDATE(), '[FECHA_DOCUMENTOS]', 
        CONCAT('RA-', DATE_FORMAT(NOW(), '%Y%m%d'), '-1'), NOW(), NOW());

-- 2. Agregar documentos a la baja
INSERT INTO voided_documents (voided_id, document_id, description)
SELECT LAST_INSERT_ID(), id, 'Error técnico - XML con datos incorrectos'
FROM documents WHERE [CONDICIONES_DOCUMENTOS_INCORRECTOS];

-- 3. Enviar comunicación de baja via interfaz web
-- 4. Regenerar documentos con datos correctos
-- 5. Reenviar con información correcta
```

---

**📝 Nota**: Esta documentación está basada en el sistema Facturador Pro y puede requerir ajustes según la versión específica del software.

**⚠️ Advertencia**: Siempre hacer respaldos antes de cambios masivos y verificar cumplimiento con regulaciones SUNAT.