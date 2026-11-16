-- School Control - tarjetas guardadas del alumno
-- Equivalente a la migración 2026_11_16_000000_create_payment_methods_table.
-- Ejecutar sobre la base de datos del entorno destino (por ejemplo, escolarprueba).
--
-- Una tarjeta puede guardarse en el perfil sin estar adjunta al customer de
-- Stripe. Se marca attached = 1 cuando Stripe la adjunta tras un pago exitoso.
-- Así el alta no dispara la verificación de saldo de Stripe.

CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alumno_id` bigint unsigned NOT NULL,
  `gateway` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_payment_method_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `last4` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `exp_month` tinyint unsigned DEFAULT NULL,
  `exp_year` smallint unsigned DEFAULT NULL,
  `attached` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_alumno_gateway_provider_unique`
    (`alumno_id`,`gateway`,`provider_payment_method_id`),
  KEY `payment_methods_alumno_id_index` (`alumno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
