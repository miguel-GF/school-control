-- ============================================================
-- LIMPIAR DATOS DEMO LOCALES
-- NO EJECUTAR EN PRODUCCION.
-- ============================================================

SET SESSION SQL_SAFE_UPDATES = 0;

DELETE FROM curriculum_docentes_archivos
WHERE curriculum_docente_id BETWEEN 9000 AND 9099;

DELETE FROM curriculum_docentes
WHERE curriculum_docente_id BETWEEN 9000 AND 9099;

SELECT COUNT(*) AS registros_demo_restantes
FROM curriculum_docentes
WHERE curriculum_docente_id BETWEEN 9000 AND 9099;
