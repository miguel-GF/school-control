-- ============================================================
-- DATOS DEMO PARA PROBAR LA FUSION EN LOCAL
-- NO EJECUTAR EN PRODUCCION.
--
-- Inserta:
--   - 3 registros duplicados para el celular 5511111111.
--   - 2 registros duplicados para el celular 5522222222.
--   - 1 registro unico para el celular 5533333333.
--
-- Los IDs son 9001+ para poder limpiarlos despues con
-- curriculum_demo_cleanup.sql.
-- ============================================================

INSERT INTO curriculum_docentes (
  curriculum_docente_id, folio, nombre, fecha_nacimiento, ciudad, estado, pais,
  estado_civil, genero, correo_electronico, correo_institucional, numero_celular,
  domicilio_calle, domicilio_no_exterior, domicilio_colonia, domicilio_ciudad,
  domicilio_estado, domicilio_codigo_postal, dato_fiscal_rfc, dato_fiscal_curp,
  dato_fiscal_clabe, dato_fiscal_banco, porcentaje_actividad_profesional,
  porcentaje_asalariado, porcentaje_pensionado, porcentaje_docencia,
  status, registro_fecha, actualizacion_fecha, comentarios
) VALUES
(9001, 9001, 'ANA LOPEZ', '1990-01-15', 'PUEBLA', 'PUEBLA', 'MEXICO',
 'soltero', 'mujer', 'ana@gmail.com', 'ana@ipes.edu.mx', '5511111111',
 'CALLE 1', '10', 'CENTRO', 'PUEBLA', 'PUEBLA', '72000', 'LOPA900115XXX',
 'LOPA900115MPLPNN01', '123456789012345678', 'BBVA', 25, 25, 25, 25,
 'Activo', '2026-11-01 10:00:00', '2026-11-10 10:00:00', 'DEMO FUSION'),
(9002, 9002, 'ANA LOPEZ', '1990-01-15', 'PUEBLA', 'PUEBLA', 'MEXICO',
 'soltero', 'mujer', NULL, 'ana@ipes.edu.mx', '55 1111 1111',
 'CALLE 1', '10', 'CENTRO', 'PUEBLA', 'PUEBLA', '72000', NULL, NULL, NULL, NULL,
 25, 25, 25, 25, 'Activo', '2026-11-02 10:00:00', '2026-11-11 10:00:00', 'DEMO FUSION'),
(9003, 9003, 'ANA LOPEZ', '1990-01-15', 'PUEBLA', 'PUEBLA', 'MEXICO',
 'soltero', 'mujer', NULL, 'ana@ipes.edu.mx', '5511111111',
 NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
 25, 25, 25, 25, 'Activo', '2026-11-03 10:00:00', '2026-11-12 10:00:00', 'DEMO FUSION'),
(9010, 9010, 'LUIS PEREZ', '1985-05-20', 'PUEBLA', 'PUEBLA', 'MEXICO',
 'casado', 'hombre', NULL, 'luis@ipes.edu.mx', '5522222222',
 NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
 25, 25, 25, 25, 'Activo', '2026-11-04 10:00:00', '2026-11-13 10:00:00', 'DEMO FUSION'),
(9011, 9011, 'LUIS PEREZ', '1985-05-20', 'PUEBLA', 'PUEBLA', 'MEXICO',
 'casado', 'hombre', NULL, 'luis@ipes.edu.mx', '5522222222',
 'AV 2', '20', 'CENTRO', 'PUEBLA', 'PUEBLA', '72000', NULL, NULL, NULL, NULL,
 25, 25, 25, 25, 'Activo', '2026-11-05 10:00:00', '2026-11-14 10:00:00', 'DEMO FUSION'),
(9020, 9020, 'MARIA UNICA', '1992-08-08', 'PUEBLA', 'PUEBLA', 'MEXICO',
 'soltero', 'mujer', NULL, 'maria@ipes.edu.mx', '5533333333',
 'CALLE 3', '30', 'CENTRO', 'PUEBLA', 'PUEBLA', '72000', NULL, NULL, NULL, NULL,
 25, 25, 25, 25, 'Activo', '2026-11-06 10:00:00', '2026-11-15 10:00:00', 'DEMO FUSION');

INSERT INTO curriculum_docentes_archivos (
  curriculum_docente_archivo_id, curriculum_docente_id, tipo, nombre, archivo,
  extension, tamanio_humano, tamanio, status, registro_fecha, descripcion, ruta
) VALUES
(9001, 9001, 'cv', 'ANA_LOPEZ_CV.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-01 10:00:00', 'CV ANA', 'curriculums_archivos/9001_ANA_LOPEZ'),
(9002, 9001, 'ine', 'ANA_LOPEZ_INE.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-01 10:00:00', 'INE ANA', 'curriculums_archivos/9001_ANA_LOPEZ'),
(9003, 9001, 'planeacion_academica', 'ANA_LOPEZ_PLANEACION.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-01 10:00:00', 'PLANEACION ANA', 'curriculums_archivos/9001_ANA_LOPEZ'),
(9004, 9002, 'curp', 'ANA_LOPEZ_CURP.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-02 10:00:00', 'CURP ANA', 'curriculums_archivos/9002_ANA_LOPEZ'),
(9005, 9002, 'domicilio', 'ANA_LOPEZ_DOMICILIO.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-02 10:00:00', 'DOMICILIO ANA', 'curriculums_archivos/9002_ANA_LOPEZ'),
(9006, 9003, 'cv', 'ANA_LOPEZ_CV_VIEJO.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-03 10:00:00', 'CV VIEJO ANA', 'curriculums_archivos/9003_ANA_LOPEZ'),
(9007, 9003, 'acta_nacimiento', 'ANA_LOPEZ_ACTA.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-03 10:00:00', 'ACTA ANA', 'curriculums_archivos/9003_ANA_LOPEZ'),
(9010, 9010, 'ine', 'LUIS_PEREZ_INE.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-04 10:00:00', 'INE LUIS', 'curriculums_archivos/9010_LUIS_PEREZ'),
(9011, 9011, 'cv', 'LUIS_PEREZ_CV.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-05 10:00:00', 'CV LUIS', 'curriculums_archivos/9011_LUIS_PEREZ'),
(9012, 9011, 'curp', 'LUIS_PEREZ_CURP.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-05 10:00:00', 'CURP LUIS', 'curriculums_archivos/9011_LUIS_PEREZ'),
(9013, 9011, 'acta_nacimiento', 'LUIS_PEREZ_ACTA.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-05 10:00:00', 'ACTA LUIS', 'curriculums_archivos/9011_LUIS_PEREZ'),
(9020, 9020, 'cv', 'MARIA_UNICA_CV.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-06 10:00:00', 'CV MARIA', 'curriculums_archivos/9020_MARIA_UNICA'),
(9021, 9020, 'ine', 'MARIA_UNICA_INE.PDF', '', 'pdf', '1 KB', 1024, 'Activo', '2026-11-06 10:00:00', 'INE MARIA', 'curriculums_archivos/9020_MARIA_UNICA');
