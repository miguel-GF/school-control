-- School Control - campos para API key de la app móvil
-- Equivalente a la migración 2026_11_16_000001_add_mobile_key_fields_to_api_keys_table.
-- Ejecutar sobre la base de datos del entorno destino.

ALTER TABLE `api_keys`
  ADD COLUMN `flavor` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `descripcion`,
  ADD COLUMN `key_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `key`,
  ADD KEY `api_keys_key_hash_index` (`key_hash`);
