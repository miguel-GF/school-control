-- ============================================================
-- DIAGNOSTICO DE CURRICULUMS DUPLICADOS
-- Este script NO modifica informacion permanente.
-- Solo crea tablas temporales para revisar que se fusionaria.
--
-- Criterio para elegir el CV principal:
--   1. El que tenga mas documentos activos.
--   2. El que tenga mas campos completos.
--   3. El mas reciente (actualizacion_fecha o registro_fecha).
--   4. El de mayor curriculum_docente_id.
-- ============================================================

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

DROP TEMPORARY TABLE IF EXISTS tmp_cv_groups;
CREATE TEMPORARY TABLE tmp_cv_groups AS
SELECT tel_norm
FROM tmp_cv_score
GROUP BY tel_norm
HAVING COUNT(*) > 1;

-- Grupos con mas de un registro
SELECT
  c.tel_norm AS celular_normalizado,
  c.canonical_id AS cv_principal,
  COUNT(*) AS total_registros,
  SUM(CASE WHEN s.curriculum_docente_id = c.canonical_id THEN 1 ELSE 0 END) AS registros_principales,
  SUM(CASE WHEN s.curriculum_docente_id <> c.canonical_id THEN 1 ELSE 0 END) AS registros_a_fusionar,
  MAX(s.docs_activos) AS max_documentos,
  MAX(s.campos_completos) AS max_campos_completos
FROM tmp_cv_score s
JOIN tmp_cv_canonical c ON c.tel_norm = s.tel_norm
GROUP BY c.tel_norm, c.canonical_id
HAVING COUNT(*) > 1
ORDER BY total_registros DESC, c.tel_norm;

-- Detalle de cada registro: cual se conserva y cuales se fusionan
SELECT
  s.tel_norm AS celular_normalizado,
  s.curriculum_docente_id,
  c.canonical_id AS cv_principal,
  cd.nombre,
  cd.status,
  cd.fecha_nacimiento,
  s.docs_activos,
  s.campos_completos,
  s.fecha_orden,
  CASE
    WHEN s.curriculum_docente_id = c.canonical_id THEN 'CONSERVAR'
    ELSE 'FUSIONAR / MARCAR BAJA'
  END AS accion
FROM tmp_cv_score s
JOIN tmp_cv_canonical c ON c.tel_norm = s.tel_norm
JOIN tmp_cv_groups g ON g.tel_norm = s.tel_norm
LEFT JOIN curriculum_docentes cd ON cd.curriculum_docente_id = s.curriculum_docente_id
ORDER BY s.tel_norm, s.curriculum_docente_id;

-- Documentos que se moverian al CV principal
SELECT
  m.canonical_id AS cv_principal,
  m.duplicate_id AS cv_duplicado,
  a.curriculum_docente_archivo_id,
  a.tipo,
  a.nombre,
  a.status AS status_documento
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
ORDER BY m.canonical_id, a.tipo, a.curriculum_docente_archivo_id;

-- Documentos de duplicados que se marcarian como Baja porque el principal ya tiene ese tipo
SELECT
  m.canonical_id AS cv_principal,
  m.duplicate_id AS cv_duplicado,
  a.curriculum_docente_archivo_id,
  a.tipo,
  a.nombre,
  a.status AS status_documento
FROM curriculum_docentes_archivos a
JOIN tmp_cv_merge m ON m.duplicate_id = a.curriculum_docente_id
WHERE LOWER(COALESCE(a.status, 'activo')) <> 'baja'
  AND EXISTS (
    SELECT 1
    FROM curriculum_docentes_archivos ac
    WHERE ac.curriculum_docente_id = m.canonical_id
      AND ac.tipo = a.tipo
      AND LOWER(COALESCE(ac.status, 'activo')) <> 'baja'
  )
ORDER BY m.canonical_id, a.tipo, a.curriculum_docente_archivo_id;
