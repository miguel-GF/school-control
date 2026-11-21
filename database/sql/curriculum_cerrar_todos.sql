-- ============================================================
-- CERRAR TODOS LOS CURRICULUMS EXISTENTES
--
-- Deja todos los expedientes que no esten en Baja como Completado.
-- A partir de este momento el docente vera la pantalla de CV cerrado
-- y no podra subir ni modificar informacion. Solo soporte podria hacerlo.
--
-- Ejecutar DESPUES de curriculum_fusion_duplicados.sql.
-- ============================================================

SET SESSION SQL_SAFE_UPDATES = 0;

-- 1. Cerrar los que estan activos (o en cualquier estado distinto de Baja/Completado).
UPDATE curriculum_docentes
SET
  status = 'Completado',
  finalizado_fecha = COALESCE(finalizado_fecha, NOW()),
  actualizacion_fecha = NOW()
WHERE LOWER(COALESCE(status, 'activo')) NOT IN ('baja', 'completado');

-- 2. Asegurar fecha de finalizacion en los que ya estaban Completado.
UPDATE curriculum_docentes
SET
  finalizado_fecha = COALESCE(finalizado_fecha, NOW()),
  actualizacion_fecha = NOW()
WHERE LOWER(COALESCE(status, 'activo')) = 'completado'
  AND finalizado_fecha IS NULL;

-- Resultado
SELECT
  status,
  COUNT(*) AS total,
  SUM(CASE WHEN finalizado_fecha IS NOT NULL THEN 1 ELSE 0 END) AS con_fecha_finalizacion
FROM curriculum_docentes
GROUP BY status
ORDER BY total DESC;
