# Scripts de fusion de curriculums

Estos scripts se ejecutan en MySQL sobre la base de `school-control`.

## Orden recomendado en produccion

1. **Respaldo completo de la base de datos** y, si es posible, de
   `storage/app/curriculums_archivos`.
2. Ejecutar `curriculum_fusion_preview.sql` y revisar los resultados.
   No modifica datos permanentes.
3. Ejecutar `curriculum_fusion_duplicados.sql`.
4. Ejecutar `curriculum_cerrar_todos.sql`.

## Criterio de fusion

Para cada celular normalizado (sin espacios, guiones, `+`, parentesis ni puntos)
se conserva como principal el curriculum que:

1. Tiene mas documentos activos.
2. Tiene mas campos completos.
3. Tiene la fecha de actualizacion/registro mas reciente.
4. Tiene el `curriculum_docente_id` mas alto.

Los documentos que le faltan al principal se mueven desde los duplicados.
Los documentos sobrantes se marcan como `Baja`.
Los curriculums duplicados se marcan como `Baja` y se agrega
`[Fusionado en CV #<id>]` en `comentarios`.

`curriculum_cerrar_todos.sql` deja todos los expedientes no fusionados como
`Completado` y con `finalizado_fecha`, por lo que el docente ya no podra
modificar ni subir informacion.

## Scripts solo para local

- `curriculum_demo_seed.sql`: crea expedientes duplicados de prueba.
- `curriculum_demo_active.sql`: crea un expediente activo para probar la
  continuacion de captura.
- `curriculum_demo_cleanup.sql`: elimina los registros demo 9000-9099.

No ejecutar los scripts demo en produccion.
