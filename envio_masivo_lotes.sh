#!/bin/bash

# Script para envío masivo en lotes de documentos a SUNAT
# Evita sobrecarga del sistema enviando de a 10 documentos por vez

TENANT_ID="20600421701"
SERIE="FP01"
INICIO=149
FIN=338
LOTE_SIZE=10

echo "=== Iniciando envío masivo en lotes ==="
echo "Tenant: $TENANT_ID"
echo "Serie: $SERIE"
echo "Rango: $INICIO - $FIN"
echo "Tamaño de lote: $LOTE_SIZE documentos"
echo ""

total_procesados=0
total_exitosos=0
total_errores=0

for ((i=$INICIO; i<=$FIN; i+=$LOTE_SIZE)); do
    fin_lote=$((i + LOTE_SIZE - 1))
    if [ $fin_lote -gt $FIN ]; then
        fin_lote=$FIN
    fi
    
    echo "📦 Procesando lote: $SERIE-$i a $SERIE-$fin_lote"
    
    # Marcar solo este lote para envío
    docker exec safebillpro-mariadb mysql -h db-mysql-sfo2-99276-do-user-19346629-0.j.db.ondigitalocean.com -P 25060 -u dantecortes -pAVNS_Z-N8505c_qXJMeIQn5K -e "
    UPDATE tenancy_${TENANT_ID}.documents 
    SET send_server = 1, 
        success_sunat_shipping_status = 0,
        sunat_shipping_status = NULL,
        success_shipping_status = 0,
        shipping_status = NULL
    WHERE series = '${SERIE}' AND number BETWEEN $i AND $fin_lote;
    "
    
    # Desmarcar todos los demás para evitar procesamiento masivo
    docker exec safebillpro-mariadb mysql -h db-mysql-sfo2-99276-do-user-19346629-0.j.db.ondigitalocean.com -P 25060 -u dantecortes -pAVNS_Z-N8505c_qXJMeIQn5K -e "
    UPDATE tenancy_${TENANT_ID}.documents 
    SET send_server = 0
    WHERE series = '${SERIE}' AND (number < $i OR number > $fin_lote) AND number BETWEEN $INICIO AND $FIN;
    "
    
    # Ejecutar envío para este lote
    echo "🚀 Enviando lote..."
    docker exec safebillpro-fpm php /var/www/html/artisan tenancy:run online:send-all > /tmp/envio_resultado.log 2>&1
    
    # Verificar resultados del lote
    exitosos=$(docker exec safebillpro-mariadb mysql -h db-mysql-sfo2-99276-do-user-19346629-0.j.db.ondigitalocean.com -P 25060 -u dantecortes -pAVNS_Z-N8505c_qXJMeIQn5K -e "
    SELECT COUNT(*) FROM tenancy_${TENANT_ID}.documents 
    WHERE series = '${SERIE}' AND number BETWEEN $i AND $fin_lote 
    AND success_sunat_shipping_status = 1;" | tail -n 1)
    
    errores=$(docker exec safebillpro-mariadb mysql -h db-mysql-sfo2-99276-do-user-19346629-0.j.db.ondigitalocean.com -P 25060 -u dantecortes -pAVNS_Z-N8505c_qXJMeIQn5K -e "
    SELECT COUNT(*) FROM tenancy_${TENANT_ID}.documents 
    WHERE series = '${SERIE}' AND number BETWEEN $i AND $fin_lote 
    AND success_sunat_shipping_status = 0;" | tail -n 1)
    
    lote_size_real=$((fin_lote - i + 1))
    total_procesados=$((total_procesados + lote_size_real))
    total_exitosos=$((total_exitosos + exitosos))
    total_errores=$((total_errores + errores))
    
    echo "✅ Exitosos: $exitosos | ❌ Con errores: $errores | 📊 Total procesado: $total_procesados"
    echo ""
    
    # Pausa entre lotes para no sobrecargar el sistema
    sleep 5
done

echo "=== Resumen Final ==="
echo "📊 Total documentos procesados: $total_procesados"
echo "✅ Total exitosos: $total_exitosos"
echo "❌ Total con errores: $total_errores"
echo "📈 Tasa de éxito: $(( (total_exitosos * 100) / total_procesados ))%"

# Reactivar todos para posibles reintentos
docker exec safebillpro-mariadb mysql -h db-mysql-sfo2-99276-do-user-19346629-0.j.db.ondigitalocean.com -P 25060 -u dantecortes -pAVNS_Z-N8505c_qXJMeIQn5K -e "
UPDATE tenancy_${TENANT_ID}.documents 
SET send_server = 1
WHERE series = '${SERIE}' AND number BETWEEN $INICIO AND $FIN AND success_sunat_shipping_status = 0;
"

echo "=== Proceso completado ==="