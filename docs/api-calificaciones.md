# API móvil: calificaciones

Endpoint para que la app del alumno consulte sus calificaciones usando el token de Sanctum.

## `GET /api/v1/mobile/calificaciones`

- Autenticación: `Authorization: Bearer <token>`
- La matrícula se toma del alumno autenticado; no se recibe por query ni por body.
- Query opcional: `periodo` para filtrar un periodo exacto.

### Respuesta

```json
{
  "data": [
    {
      "id": 6798,
      "clave_materia": "LCPS255",
      "materia": "AUDITORIA FINANCIERA I",
      "periodo": "Otoño 2021",
      "semestre": 5,
      "grupo": "A",
      "asistencias": 0,
      "faltas": 0,
      "primer_parcial": 10,
      "segundo_parcial": 9,
      "ordinario": 10,
      "extraordinario": null,
      "final": 10,
      "status": "Activo"
    }
  ],
  "periodos": ["Otoño 2021"]
}
```

La consulta reutiliza `AlumnoServiceData::obteneDataCalificaciones` y
`AlumnoRepoData::obtenerCalificacionesPorId`. Solo incluye registros con
`status = Activo` y ordena las materias por nombre.

Los campos numéricos vacíos o no válidos se devuelven como `null` para que la
app muestre `--`.
