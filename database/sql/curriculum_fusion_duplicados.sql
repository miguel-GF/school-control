-- ============================================================
-- FUSION DE CURRICULUMS DUPLICADOS
--
-- IMPORTANTE:
--   1. Haz un respaldo completo de la base antes de ejecutar.
--   2. Ejecuta primero curriculum_fusion_preview.sql y revisa.
--   3. Ejecuta este script en una sola sesion de MySQL.
--
-- Que hace:
--   - Agrupa por celular normalizado (sin espacios, guiones, +, parentesis, puntos).
--   - Conserva el CV con mas documentos, mas campos completos y mas reciente.
--   - Mueve al CV principal los documentos que le faltan.
--   - Marca los documentos sobrantes como Baja.
--   - Rellena datos vacios del CV principal con los del mejor duplicado.
--   - Marca los CV duplicados como Baja y deja una nota en comentarios.
--
-- No cierra los CV principales. Para eso usa curriculum_cerrar_todos.sql.
-- ============================================================

SET SESSION SQL_SAFE_UPDATES = 0;
SET SESSION group_concat_max_len = 1000000;

DROP TEMPORARY TABLE IF EXISTS tmp_cv_score;
CREATE TEMPORARY TABLE tmp_cv_score AS
SELECT
  cd.curriculum_docente_id,
  REPLACE(
    REPLACE(
      REPLACE(
        REPLACE(
          REPLACE(
            REPLACE(COALESCE(cd.numero_celular, ''), ' ', ''),
          '-', ''),
        '+', ''),
      '(', ''),
    ')', ''),
  '.', '') AS tel_norm,
  (
    SELECT COUNT(*)
    FROM curriculum_docentes_archivos a
    WHERE a.curriculum_docente_id = cd.curriculum_docente_id
      AND LOWER(COALESCE(a.status, 'activo')) <> 'baja'
  ) AS docs_activos,
  (
    (CASE WHEN TRIM(COALESCE(cd.nombre, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN cd.fecha_nacimiento IS NOT NULL THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.ciudad, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.estado, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.pais, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.estado_civil, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.genero, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.correo_electronico, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.correo_institucional, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.numero_celular, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.domicilio_calle, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.domicilio_no_exterior, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.domicilio_colonia, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.domicilio_ciudad, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.domicilio_estado, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.domicilio_codigo_postal, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.dato_fiscal_rfc, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.dato_fiscal_curp, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.dato_fiscal_clabe, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.dato_fiscal_banco, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.enfermedades, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.alergias, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.tipo_sangre, '')) <> '' THEN 1 ELSE 0 END) +
    (CASE WHEN TRIM(COALESCE(cd.comentarios, '')) <> '' THEN 1 ELSE 0 END)
  ) AS campos_completos,
  COALESCE(cd.actualizacion_fecha, cd.registro_fecha, '1970-01-01 00:00:00') AS fecha_orden
FROM curriculum_docentes cd
WHERE LOWER(COALESCE(cd.status, 'activo')) <> 'baja'
  AND REPLACE(
    REPLACE(
      REPLACE(
        REPLACE(
          REPLACE(
            REPLACE(COALESCE(cd.numero_celular, ''), ' ', ''),
          '-', ''),
        '+', ''),
      '(', ''),
    ')', ''),
  '.', '') <> '';

DROP TEMPORARY TABLE IF EXISTS tmp_cv_canonical;
CREATE TEMPORARY TABLE tmp_cv_canonical AS
SELECT
  tel_norm,
  CAST(
    SUBSTRING_INDEX(
      GROUP_CONCAT(
        curriculum_docente_id
        ORDER BY docs_activos DESC, campos_completos DESC, fecha_orden DESC, curriculum_docente_id DESC
      ),
      ',',
      1
    ) AS UNSIGNED
  ) AS canonical_id
FROM tmp_cv_score
GROUP BY tel_norm;

DROP TEMPORARY TABLE IF EXISTS tmp_cv_merge;
CREATE TEMPORARY TABLE tmp_cv_merge AS
SELECT
  s.curriculum_docente_id AS duplicate_id,
  c.canonical_id,
  s.tel_norm
FROM tmp_cv_score s
JOIN tmp_cv_canonical c ON c.tel_norm = s.tel_norm
WHERE s.curriculum_docente_id <> c.canonical_id;

-- Mejor duplicado por CV principal, para rellenar datos vacios.
DROP TEMPORARY TABLE IF EXISTS tmp_cv_dup_principal;
CREATE TEMPORARY TABLE tmp_cv_dup_principal AS
SELECT
  m.canonical_id,
  CAST(
    SUBSTRING_INDEX(
      GROUP_CONCAT(
        m.duplicate_id
        ORDER BY s.docs_activos DESC, s.campos_completos DESC, s.fecha_orden DESC, m.duplicate_id DESC
      ),
      ',',
      1
    ) AS UNSIGNED
  ) AS duplicate_id
FROM tmp_cv_merge m
JOIN tmp_cv_score s ON s.curriculum_docente_id = m.duplicate_id
GROUP BY m.canonical_id;

START TRANSACTION;

-- 1. Mover documentos que el CV principal no tiene.
DROP TEMPORARY TABLE IF EXISTS tmp_cv_docs_mover;
CREATE TEMPORARY TABLE tmp_cv_docs_mover AS
SELECT
  MAX(a.curriculum_docente_archivo_id) AS archivo_id,
  m.canonical_id,
  a.tipo
FROM curriculum_docentes_archivos a
JOIN tmp_cv_merge m ON m.duplicate_id = a.curriculum_docente_id
WHERE LOWER(COALESCE(a.status, 'activo')) <> 'baja'
  AND NOT EXISTS (
    SELECT 1
    FROM curriculum_docentes_archivos ac
    WHERE ac.curriculum_docente_id = m.canonical_id
      AND ac.tipo = a.tipo
      AND LOWER(COALESCE(ac.status, 'activo')) <> 'baja'
  )
GROUP BY m.canonical_id, a.tipo;

UPDATE curriculum_docentes_archivos a
JOIN tmp_cv_docs_mover d ON d.archivo_id = a.curriculum_docente_archivo_id
SET
  a.curriculum_docente_id = d.canonical_id,
  a.actualizacion_fecha = NOW();

-- 2. Los documentos restantes de los duplicados ya no se usan.
UPDATE curriculum_docentes_archivos a
JOIN tmp_cv_merge m ON m.duplicate_id = a.curriculum_docente_id
SET
  a.status = 'Baja',
  a.actualizacion_fecha = NOW()
WHERE LOWER(COALESCE(a.status, 'activo')) <> 'baja';

-- 3. Rellenar datos vacios del CV principal desde el mejor duplicado.
UPDATE curriculum_docentes c
JOIN tmp_cv_dup_principal bd ON bd.canonical_id = c.curriculum_docente_id
JOIN curriculum_docentes d ON d.curriculum_docente_id = bd.duplicate_id
SET
  c.nombre = COALESCE(NULLIF(TRIM(c.nombre), ''), NULLIF(TRIM(d.nombre), '')),
  c.fecha_nacimiento = COALESCE(c.fecha_nacimiento, d.fecha_nacimiento),
  c.ciudad = COALESCE(NULLIF(TRIM(c.ciudad), ''), NULLIF(TRIM(d.ciudad), '')),
  c.estado = COALESCE(NULLIF(TRIM(c.estado), ''), NULLIF(TRIM(d.estado), '')),
  c.pais = COALESCE(NULLIF(TRIM(c.pais), ''), NULLIF(TRIM(d.pais), '')),
  c.estado_civil = COALESCE(NULLIF(TRIM(c.estado_civil), ''), NULLIF(TRIM(d.estado_civil), '')),
  c.genero = COALESCE(NULLIF(TRIM(c.genero), ''), NULLIF(TRIM(d.genero), '')),
  c.correo_electronico = COALESCE(NULLIF(TRIM(c.correo_electronico), ''), NULLIF(TRIM(d.correo_electronico), '')),
  c.correo_institucional = COALESCE(NULLIF(TRIM(c.correo_institucional), ''), NULLIF(TRIM(d.correo_institucional), '')),
  c.facebook = COALESCE(NULLIF(TRIM(c.facebook), ''), NULLIF(TRIM(d.facebook), '')),
  c.numero_casa = COALESCE(NULLIF(TRIM(c.numero_casa), ''), NULLIF(TRIM(d.numero_casa), '')),
  c.domicilio_calle = COALESCE(NULLIF(TRIM(c.domicilio_calle), ''), NULLIF(TRIM(d.domicilio_calle), '')),
  c.domicilio_no_exterior = COALESCE(NULLIF(TRIM(c.domicilio_no_exterior), ''), NULLIF(TRIM(d.domicilio_no_exterior), '')),
  c.domicilio_no_interior = COALESCE(NULLIF(TRIM(c.domicilio_no_interior), ''), NULLIF(TRIM(d.domicilio_no_interior), '')),
  c.domicilio_colonia = COALESCE(NULLIF(TRIM(c.domicilio_colonia), ''), NULLIF(TRIM(d.domicilio_colonia), '')),
  c.domicilio_ciudad = COALESCE(NULLIF(TRIM(c.domicilio_ciudad), ''), NULLIF(TRIM(d.domicilio_ciudad), '')),
  c.domicilio_estado = COALESCE(NULLIF(TRIM(c.domicilio_estado), ''), NULLIF(TRIM(d.domicilio_estado), '')),
  c.domicilio_codigo_postal = COALESCE(NULLIF(TRIM(c.domicilio_codigo_postal), ''), NULLIF(TRIM(d.domicilio_codigo_postal), '')),
  c.dato_fiscal_rfc = COALESCE(NULLIF(TRIM(c.dato_fiscal_rfc), ''), NULLIF(TRIM(d.dato_fiscal_rfc), '')),
  c.dato_fiscal_curp = COALESCE(NULLIF(TRIM(c.dato_fiscal_curp), ''), NULLIF(TRIM(d.dato_fiscal_curp), '')),
  c.dato_fiscal_clabe = COALESCE(NULLIF(TRIM(c.dato_fiscal_clabe), ''), NULLIF(TRIM(d.dato_fiscal_clabe), '')),
  c.dato_fiscal_banco = COALESCE(NULLIF(TRIM(c.dato_fiscal_banco), ''), NULLIF(TRIM(d.dato_fiscal_banco), '')),
  c.dato_fiscal_calle = COALESCE(NULLIF(TRIM(c.dato_fiscal_calle), ''), NULLIF(TRIM(d.dato_fiscal_calle), '')),
  c.dato_fiscal_no_exterior = COALESCE(NULLIF(TRIM(c.dato_fiscal_no_exterior), ''), NULLIF(TRIM(d.dato_fiscal_no_exterior), '')),
  c.dato_fiscal_no_interior = COALESCE(NULLIF(TRIM(c.dato_fiscal_no_interior), ''), NULLIF(TRIM(d.dato_fiscal_no_interior), '')),
  c.dato_fiscal_colonia = COALESCE(NULLIF(TRIM(c.dato_fiscal_colonia), ''), NULLIF(TRIM(d.dato_fiscal_colonia), '')),
  c.dato_fiscal_ciudad = COALESCE(NULLIF(TRIM(c.dato_fiscal_ciudad), ''), NULLIF(TRIM(d.dato_fiscal_ciudad), '')),
  c.dato_fiscal_estado = COALESCE(NULLIF(TRIM(c.dato_fiscal_estado), ''), NULLIF(TRIM(d.dato_fiscal_estado), '')),
  c.dato_fiscal_codigo_postal = COALESCE(NULLIF(TRIM(c.dato_fiscal_codigo_postal), ''), NULLIF(TRIM(d.dato_fiscal_codigo_postal), '')),
  c.porcentaje_actividad_profesional = CASE
    WHEN COALESCE(c.porcentaje_actividad_profesional, 0) = 0 THEN COALESCE(d.porcentaje_actividad_profesional, c.porcentaje_actividad_profesional)
    ELSE c.porcentaje_actividad_profesional
  END,
  c.porcentaje_asalariado = CASE
    WHEN COALESCE(c.porcentaje_asalariado, 0) = 0 THEN COALESCE(d.porcentaje_asalariado, c.porcentaje_asalariado)
    ELSE c.porcentaje_asalariado
  END,
  c.porcentaje_pensionado = CASE
    WHEN COALESCE(c.porcentaje_pensionado, 0) = 0 THEN COALESCE(d.porcentaje_pensionado, c.porcentaje_pensionado)
    ELSE c.porcentaje_pensionado
  END,
  c.porcentaje_docencia = CASE
    WHEN COALESCE(c.porcentaje_docencia, 0) = 0 THEN COALESCE(d.porcentaje_docencia, c.porcentaje_docencia)
    ELSE c.porcentaje_docencia
  END,
  c.enfermedades = COALESCE(NULLIF(TRIM(c.enfermedades), ''), NULLIF(TRIM(d.enfermedades), '')),
  c.alergias = COALESCE(NULLIF(TRIM(c.alergias), ''), NULLIF(TRIM(d.alergias), '')),
  c.tipo_sangre = COALESCE(NULLIF(TRIM(c.tipo_sangre), ''), NULLIF(TRIM(d.tipo_sangre), '')),
  c.comentarios = COALESCE(NULLIF(TRIM(c.comentarios), ''), NULLIF(TRIM(d.comentarios), '')),
  c.actualizacion_fecha = NOW();

-- 4. Marcar duplicados como Baja y dejar rastro del CV principal.
UPDATE curriculum_docentes c
JOIN tmp_cv_merge m ON m.duplicate_id = c.curriculum_docente_id
SET
  c.status = 'Baja',
  c.finalizado_fecha = COALESCE(c.finalizado_fecha, NOW()),
  c.actualizacion_fecha = NOW(),
  c.comentarios = CONCAT_WS(
    ' ',
    NULLIF(TRIM(c.comentarios), ''),
    CONCAT('[Fusionado en CV #', m.canonical_id, ']')
  );

COMMIT;

-- Resultado: cuantos registros quedaron como principales y cuantos como duplicados.
SELECT
  'registros_fusionados' AS concepto,
  COUNT(*) AS total
FROM tmp_cv_merge;

SELECT
  'registros_principales' AS concepto,
  COUNT(DISTINCT canonical_id) AS total
FROM tmp_cv_merge;

-- Detalle final de los grupos fusionados.
SELECT
  c.tel_norm AS celular_normalizado,
  c.canonical_id AS cv_principal,
  cd.nombre AS nombre_principal,
  cd.status AS status_principal,
  GROUP_CONCAT(m.duplicate_id ORDER BY m.duplicate_id) AS cvs_marcados_baja
FROM tmp_cv_canonical c
JOIN tmp_cv_merge m ON m.canonical_id = c.canonical_id
LEFT JOIN curriculum_docentes cd ON cd.curriculum_docente_id = c.canonical_id
GROUP BY c.tel_norm, c.canonical_id, cd.nombre, cd.status
ORDER BY c.tel_norm;
