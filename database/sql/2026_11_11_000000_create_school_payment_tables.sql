-- School Control - tablas de pagos móviles
-- Equivalente a la migración 2026_11_11_000000_create_school_payment_tables.
-- Ejecutar sobre la base de datos del entorno destino (por ejemplo, escolarprueba).
-- Solo crea estructura; no inserta datos.
--
-- Orden importante:
--   1. payment_customers
--   2. payments
--   3. payment_attempts (depende de payments)
--
-- Si las tablas ya existen, CREATE TABLE IF NOT EXISTS no las modifica.
-- En ese caso compara columnas/índices antes de ejecutar.

CREATE TABLE IF NOT EXISTS `payment_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alumno_id` bigint unsigned NOT NULL,
  `gateway` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_customers_alumno_id_gateway_unique` (`alumno_id`,`gateway`),
  UNIQUE KEY `payment_customers_gateway_provider_customer_id_unique` (`gateway`,`provider_customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alumno_id` bigint unsigned NOT NULL,
  `amount_cents` int unsigned NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_provider_payment_id_unique` (`provider_payment_id`),
  KEY `payments_alumno_id_index` (`alumno_id`),
  KEY `payments_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `failure_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_attempts_payment_id_index` (`payment_id`),
  KEY `payment_attempts_provider_payment_id_index` (`provider_payment_id`),
  KEY `payment_attempts_status_index` (`status`),
  CONSTRAINT `payment_attempts_payment_id_foreign`
    FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Requerida por Sanctum para el login móvil (POST /api/v1/mobile/auth/login).
-- Si el entorno ya tiene personal_access_tokens, este CREATE no hace nada.
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificación rápida después de ejecutar:
-- SHOW TABLES LIKE 'payment%';
-- SHOW TABLES LIKE 'personal_access_tokens';
-- DESCRIBE payments;
-- DESCRIBE payment_customers;
-- DESCRIBE payment_attempts;
