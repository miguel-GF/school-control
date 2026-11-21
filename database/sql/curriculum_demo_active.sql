-- ============================================================
-- REGISTRO DEMO ACTIVO PARA PROBAR EL FLUJO DE CONTINUAR CAPTURA
-- NO EJECUTAR EN PRODUCCION.
--
-- Ejecutar DESPUES de curriculum_cerrar_todos.sql.
-- Celular demo: 5544444444
-- ============================================================

INSERT INTO curriculum_docentes (
  curriculum_docente_id, folio, nombre, fecha_nacimiento, ciudad, estado, pais,
  estado_civil, genero, correo_electronico, correo_institucional, numero_celular,
  domicilio_calle, domicilio_no_exterior, domicilio_colonia, domicilio_ciudad,
  domicilio_estado, domicilio_codigo_postal, status, registro_fecha,
  actualizacion_fecha, comentarios
) VALUES
(9031, 9031, 'PEDRO DEMO', '1988-03-10', 'PUEBLA', 'PUEBLA', 'MEXICO',
 'casado', 'hombre', NULL, 'pedro.demo@ipes.edu.mx', '5544444444',
 'CALLE DEMO', '40', 'CENTRO', 'PUEBLA', 'PUEBLA', '72000',
 'Activo', '2026-11-20 10:00:00', '2026-11-20 10:00:00', 'DEMO ACTIVO');

INSERT INTO curriculum_docentes_archivos (
  curriculum_docente_archivo_id, curriculum_docente_id, tipo, nombre, archivo,
  extension, tamanio_humano, tamanio, status, registro_fecha, descripcion, ruta
) VALUES
(9031, 9031, 'cv', 'PEDRO_DEMO_CV.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-20 10:00:00', 'CV PEDRO', 'curriculums_archivos/9031_PEDRO_DEMO'),
(9032, 9031, 'ine', 'PEDRO_DEMO_INE.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-20 10:00:00', 'INE PEDRO', 'curriculums_archivos/9031_PEDRO_DEMO'),
(9033, 9031, 'curp', 'PEDRO_DEMO_CURP.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-20 10:00:00', 'CURP PEDRO', 'curriculums_archivos/9031_PEDRO_DEMO');
