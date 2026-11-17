-- School Control - fix completo para la API key de la app demo
-- Base de datos: IPES
-- Key asociada al APK demo compilado el 16/11/2026.
-- Si las columnas flavor/key_hash ya existen, NO ejecutes los ALTER;
-- salta directo al INSERT.

-- 1) Columnas necesarias (solo si faltan)
ALTER TABLE `api_keys`
  ADD COLUMN `flavor` varchar(32)
    COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `descripcion`;

ALTER TABLE `api_keys`
  ADD COLUMN `key_hash` varchar(64)
    COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `key`;

ALTER TABLE `api_keys`
  ADD KEY `api_keys_key_hash_index` (`key_hash`);

-- 2) INSERT de la key exacta con la que se compiló el APK demo
INSERT INTO `api_keys` (`key`, `key_hash`, `flavor`, `descripcion`, `status`)
VALUES (
  'fe6fb4baddbe7dc7cba508d2a0ec2b0ba060f64d130ccfb882',
  'fe6fb4baddbe7dc7cba508d2a0ec2b0ba060f64d130ccfb8829392e290767fa2',
  'demo',
  'IPES demo APK',
  'Activo'
);

-- 3) Verificación
SELECT
  `api_key_id`,
  `flavor`,
  `descripcion`,
  `status`,
  LENGTH(`key_hash`) AS hash_len
FROM `api_keys`
WHERE `flavor` = 'demo';
