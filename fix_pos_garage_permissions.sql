-- Script para solucionar el problema de redirección de /pos/garage
-- Este script verifica y asigna los permisos necesarios para acceder a pos_garage

-- Mostrar los module_levels existentes relacionados con POS
SELECT id, value, description, module_id 
FROM module_levels 
WHERE value LIKE '%pos%' 
ORDER BY id;

-- Mostrar todos los usuarios del sistema
SELECT id, name, email, type 
FROM users 
ORDER BY id;

-- Mostrar los permisos actuales de todos los usuarios para módulos relacionados con POS
SELECT 
    u.id as user_id,
    u.name as user_name,
    u.email,
    ml.id as module_level_id,
    ml.value as permission,
    ml.description
FROM users u
JOIN module_level_user mlu ON u.id = mlu.user_id
JOIN module_levels ml ON mlu.module_level_id = ml.id
WHERE ml.value LIKE '%pos%'
ORDER BY u.id, ml.value;

-- Verificar si el permiso pos_garage existe en module_levels
SELECT COUNT(*) as pos_garage_exists 
FROM module_levels 
WHERE value = 'pos_garage';

-- Si el permiso pos_garage no existe, lo creamos (descomenta las siguientes líneas si es necesario)
-- INSERT INTO module_levels (value, description, module_id, created_at, updated_at) 
-- VALUES ('pos_garage', 'Venta rapida', 6, NOW(), NOW());

-- Obtener el ID del permiso pos_garage
SELECT id FROM module_levels WHERE value = 'pos_garage';

-- PARA ASIGNAR PERMISOS: Reemplaza [USER_ID] con el ID del usuario que necesita acceso
-- El siguiente comando asigna el permiso pos_garage al usuario especificado:

-- INSERT INTO module_level_user (module_level_id, user_id)
-- SELECT ml.id, [USER_ID]
-- FROM module_levels ml
-- WHERE ml.value = 'pos_garage'
-- AND NOT EXISTS (
--     SELECT 1 FROM module_level_user mlu 
--     WHERE mlu.module_level_id = ml.id 
--     AND mlu.user_id = [USER_ID]
-- );

-- EJEMPLO: Para asignar el permiso al usuario con ID 1, usar:
-- INSERT INTO module_level_user (module_level_id, user_id)
-- SELECT ml.id, 1
-- FROM module_levels ml
-- WHERE ml.value = 'pos_garage'
-- AND NOT EXISTS (
--     SELECT 1 FROM module_level_user mlu 
--     WHERE mlu.module_level_id = ml.id 
--     AND mlu.user_id = 1
-- );

-- Verificar que el permiso fue asignado correctamente
-- SELECT 
--     u.name as user_name,
--     u.email,
--     ml.value as permission,
--     ml.description
-- FROM users u
-- JOIN module_level_user mlu ON u.id = mlu.user_id
-- JOIN module_levels ml ON mlu.module_level_id = ml.id
-- WHERE u.id = [USER_ID] AND ml.value = 'pos_garage';