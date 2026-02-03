#!/bin/bash

# Script para compilar assets usando Docker
# Usa Node 14 en contenedor para evitar problemas de compatibilidad
# Uso: ./compile-assets.sh [dev|production]
# Por defecto usa production

# Determinar el modo de compilación
MODE="${1:-production}"

if [ "$MODE" != "dev" ] && [ "$MODE" != "production" ]; then
    echo "❌ Error: Modo inválido '$MODE'"
    echo "Uso: ./compile-assets.sh [dev|production]"
    exit 1
fi

echo "======================================"
echo "Compilando assets con Docker + Node 14"
echo "Modo: $MODE"
echo "======================================"

# Verificar que estamos en el directorio correcto
if [ ! -f "package.json" ]; then
    echo "❌ Error: No se encontró package.json"
    echo "Ejecuta este script desde el directorio raíz del proyecto"
    exit 1
fi

# Instalar dependencias npm si no existen
if [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependencias npm..."
    docker run --platform linux/amd64 --rm \
        -v $(pwd):/app \
        -w /app \
        node:14 \
        npm install
fi

# Compilar assets
echo "🔨 Compilando assets..."
docker run --platform linux/amd64 --rm \
    -v $(pwd):/app \
    -w /app \
    node:14 \
    npm run $MODE

if [ $? -eq 0 ]; then
    echo ""
    echo "======================================"
    echo "✅ Assets compilados exitosamente ($MODE)!"
    echo "======================================"
    echo ""
    echo "Archivos generados:"
    ls -lh public/css/ | grep -E '(app|auth)\.css$'
    ls -lh public/js/ | grep -E '(app|vendor|manifest)\.js$'
else
    echo ""
    echo "======================================"
    echo "❌ Error al compilar assets"
    echo "======================================"
    exit 1
fi
