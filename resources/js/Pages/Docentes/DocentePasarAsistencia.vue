<template>
  <MainLayout>
    <div class="q-pa-md">
      <q-table :rows="alumnosInicial" :columns="columns" :rows-per-page-options="[10]" :filter="filter"
        class="tabla-listado striped-table" row-key="numestudiante" selection="multiple"
        v-model:selected="alumnosSeleccionados">
        <template v-slot:top>
          <div class="row col-12">
            <div class="col-sm-6 col-md">
              <div class="text-bold">
                Pasar Asistencia - {{ obtenerPeriodo }}
              </div>
              <div class="text-bold">
                {{ obtenerSemestreGrupo }}
              </div>
            </div>
            <div class="col-sm-6 col-md-4 q-my-auto ellipsis-2-lines text-left">
              {{ obtenerLicenciaturaMateria }}
            </div>
            <div class="col-sm-4 col-md-2 text-left row">
              <div class="q-my-auto q-pr-xs">
                Fecha asistencia:
              </div>
              <q-input class="col" hide-bottom-space outlined dense v-model="fecha" mask="date" :rules="['date']">
                <template v-slot:append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-date v-model="fecha" :options="optionsFn">
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Close" color="primary" flat />
                        </div>
                      </q-date>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <div class="col-sm-4 col-md-3 q-pl-md">
              <q-input outlined dense debounce="300" v-model="filter" placeholder="Búsqueda">
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>
            <div class="col-sm-4 col-md-1 text-right">
              <q-btn @click="irCargasAcademicas()" dense icon-right="chevron_left" color="secondary" class="q-mr-md" />
              <q-btn dense color="primary" flat round icon="save" @click="confirmarAsistencias()">
                <q-tooltip anchor="bottom left">
                  Guardar Asistencias
                </q-tooltip>
              </q-btn>
            </div>
          </div>
        </template>
      </q-table>
    </div>
    <!-- MODALES -->
    <!-- DIALOGO DE CONFIRMACION -->
    <the-dialog-confirm :mostrar="mostrarModalConfirmar" titulo="Confirmar Asistencias"
      @cerrar="mostrarModalConfirmar = false" @aceptar="guardarAsistencias()">
      <template #body>
        <q-banner rounded class="col-12 bg-orange-4" v-html="obtenerMensajeConfirmacion">
        </q-banner>
      </template>
    </the-dialog-confirm>

    <!-- DIALOGO DE EXITO -->
    <the-dialog-response :mostrar="mostrarModalExito" mensaje="Asistencias agredadas correctamente"
      @aceptar="(mostrarModalExito = false), reiniciarValores()" />

    <!-- DIALOGO DE REEMPLAZAR CALIFICACIONES -->
    <the-dialog-confirm :mostrar="mostrarModalReemplazarCalificaciones" classes="card-width-xl"
      labelAceptar="Actualizar calificaciones"
      titulo="Actualizar Calificaciones" @cerrar="mostrarModalReemplazarCalificaciones = false"
      @aceptar="actualizarCalificaciones()">
      <template #body>
        <div class="row col-12 justify-around q-mb-md ">
          <div class="col-4">
            <q-banner rounded class="col-12 bg-orange-4" v-html="obtenerMensajeReemplazoConfirmacion" />
          </div>
          <div class="col-4">
            <q-banner rounded class="col-12 bg-orange-4" v-html="obtenerMensajeActualizaciones" />
          </div>
        </div>
        <q-table :rows="alumnosDataFinal" :columns="columnasActualizar" :rows-per-page-options="[10]" :filter="filter"
          class="tabla-listado-modal striped-table" row-key="numestudiante">
          <template v-slot:body-cell-anterior="props">
            <q-td :props="props">
              <q-checkbox :model-value="checarAsistenciaExistente(props.row)" disable />
            </q-td>
          </template>
          <template v-slot:body-cell-inicial="props">
            <q-td :props="props">
              <q-badge v-if="props.row.es_inicial">
                Nuevo alumno
              </q-badge>
            </q-td>
          </template>
          <template v-slot:body-cell-nuevo="props">
            <q-td :props="props">
              <q-checkbox v-model="props.row.asistencia" />
            </q-td>
          </template>
          <template v-slot:body-cell-cambio="props">
            <q-td :props="props">
              <template v-if="props.row.es_inicial || (checarAsistenciaExistente(props.row) != props.row.asistencia)">
                <q-icon name="sync" size="xs" />
              </template>
            </q-td>
          </template>
        </q-table>
      </template>
    </the-dialog-confirm>
  </MainLayout>
</template>

<script>
import MainLayout from "../../Layouts/MainLayout.vue";
import { loading } from "../../Utils/loading";
import { obtenerFechaActualOperacion, esFechaValida } from "../../Utils/date";
import { notify } from "../../Utils/notify";
import { cloneDeep } from "lodash";
export default {
  props: ["alumnos", "status", "mensaje", "idCargaAcademica"],
  components: { MainLayout },
  data() {
    return {
      filter: "",
      columns: [
        {
          name: "numeroEstudiante",
          label: "# Alumno",
          align: "left",
          field: (row) => row.numestudiante,
          format: (val) => val ?? "--",
          sortable: true,
        },
        {
          name: "alumno",
          label: "Alumno",
          align: "left",
          field: (row) => row.alumno_nombre,
          format: (val) => val ?? "--",
          sortable: true,
        },
      ],
      columnasActualizar: [
        {
          name: "numeroEstudiante",
          label: "# Alumno",
          align: "left",
          field: (row) => row.numestudiante,
          format: (val) => val ?? "--",
          sortable: true,
        },
        {
          name: "alumno",
          label: "Alumno",
          align: "left",
          field: (row) => row.alumno_nombre,
          format: (val) => val ?? "--",
          sortable: true,
        },
        {
          name: "inicial",
          label: "",
          align: "center",
          sortable: false,
        },
        {
          name: "anterior",
          label: "Anterior Asistencia",
          align: "center",
          sortable: false,
        },
        {
          name: "nuevo",
          label: "Nueva Asistencia",
          align: "center",
          field: (row) => row.asistencia,
          format: (val) => val ?? "--",
          sortable: false,
        },
        {
          name: "cambio",
          label: "",
          align: "center",
          sortable: false,
        },
      ],
      alumnosSeleccionados: [],
      alumnosOriginal: [],
      alumnosInicial: [],
      alumnosDataFinal: [],
      asistenciasExistentes: [],
      mostrarModalConfirmar: false,
      mostrarModalExito: false,
      mostrarModalReemplazarCalificaciones: false,
      mensajeConfirmacion: "",
      fecha: obtenerFechaActualOperacion(),
      fechaActual: obtenerFechaActualOperacion(),
      optionsFn(date) {
        return date <= obtenerFechaActualOperacion();
      },
    };
  },
  created() {
    this.alumnosOriginal = cloneDeep(this.alumnos);
    this.alumnosInicial = cloneDeep(this.alumnos);
    loading(false);
  },
  // updated() {
  //   loading(false);
  //   if (this.status == 200) {
  //     this.mostrarModalExito = true;
  //   } else {
  //     return notify(this.mensaje, 'error');
  //   }
  // },
  computed: {
    obtenerPeriodo() {
      if (this.alumnosInicial.length == 0) {
        return "--";
      }
      const { periodo } = this.alumnos[0];
      return periodo;
    },
    obtenerSemestreGrupo() {
      if (this.alumnos.length == 0) {
        return "--";
      }

      const { semestre, grupo } = this.alumnos[0];
      let datos = [];
      datos[0] = "Semestre: " + semestre;
      datos[1] = "Grupo: " + grupo;

      return `${datos.join(" | ")}`;
    },
    obtenerLicenciaturaMateria() {
      if (this.alumnos.length == 0) {
        return "--";
      }

      const { licenciatura, materia } = this.alumnos[0];
      let datos = [];
      datos[0] = licenciatura;
      datos[1] = materia;

      return `${datos.join(" | ")}`;
    },
    obtenerMensajeConfirmacion() {
      const alumnosConAsistenciaCantidad =
        this.alumnosSeleccionados.length;
      const alumnosCantidad = this.alumnosInicial.length;
      let mensajes = [];
      mensajes[0] = `Guardará asistencias con la siguiente información:<br>`;
      mensajes[1] = `Fecha: ${this.fecha}`;
      mensajes[2] = `Número de alumnos: ${alumnosCantidad}`;
      mensajes[3] = `Número de alumnos que asistieron: ${alumnosConAsistenciaCantidad}`;
      mensajes[4] = `Alumnos con asistencia: ${alumnosConAsistenciaCantidad} de ${alumnosCantidad}`;
      return mensajes.join("<br>");
    },
    obtenerMensajeReemplazoConfirmacion() {
      const asistenciasCantidad = this.asistenciasExistentes.length;
      let mensajes = [];
      mensajes[0] = this.mensajeConfirmacion;
      mensajes[1] = `Número de asistencias existentes: ${asistenciasCantidad}`;
      return mensajes.join("<br>");
    },
    obtenerMensajeActualizaciones() {
      let diferentes = 0;
      this.alumnosDataFinal.forEach(alumno => {
        if (alumno.es_inicial || (this.checarAsistenciaExistente(alumno) != alumno.asistencia)) {
          diferentes++;
        }
      });
      let mensajes = [];
      mensajes[0] = `Número de asistencias que cambiarán: ${diferentes}<br>`;
      mensajes[1] = `<span class="invisible">valor</span>`;
      return mensajes.join("<br>");
    }
  },
  methods: {
    confirmarAsistencias() {
      try {
        this.validarFechas();
        this.mostrarModalConfirmar = true;
      } catch (error) {
        return notify(error, "error");
      }
    },
    validarFechas() {
      if (!this.fecha) {
        throw "Debe introducir la fecha de asistencia";
      }
      const resValidacionFecha = esFechaValida(this.fecha);
      if (resValidacionFecha.status >= 300) {
        throw resValidacionFecha.mensaje;
      }
    },
    async guardarAsistencias() {
      try {
        this.mostrarModalConfirmar = false;
        let alumnosDataFinal = cloneDeep(this.alumnosInicial);
        this.alumnosSeleccionados.forEach((as) => {
          const index = alumnosDataFinal.findIndex(
            (a) => a.numestudiante == as.numestudiante
          );
          if (index >= 0) {
            alumnosDataFinal[index].asistencia = true;
          }
        });
        const { semestre, grupo, licenciatura, materia, periodo } =
          this.alumnosInicial[0];
        this.alumnosDataFinal = alumnosDataFinal;
        const form = {
          fecha: this.fecha,
          licenciatura,
          semestre,
          grupo,
          idCargaAcademica: this.idCargaAcademica,
          alumnos: JSON.stringify(alumnosDataFinal),
          periodo,
          materia,
        };
        loading(true, "Agregando ...");
        const respuesta = await axios.post(
          "/docente/pasarAsistencias",
          form
        );
        const { data, status, statusText } = respuesta;
        if (Number(status) != 200) {
          throw `Ocurrio un error al hacer la solicitud: ${statusText || "--"
          }`;
        }
        if (data.status >= 300) {
          throw data.mensaje;
        } else if (data.status == 200) {
          this.mostrarModalExito = true;
        } else if (data.status == 201) {
          this.mensajeConfirmacion = data.mensaje;
          this.asistenciasExistentes = data.asistencias;
          this.alumnosDataFinal.map((alumno) => {
            const existe = this.asistenciasExistentes.find((ae) => ae.numestudiante == alumno.numestudiante);
            if (existe) {
              alumno.es_inicial = false;
            } else {
              alumno.es_inicial = true;
            }
          });
          this.mostrarModalReemplazarCalificaciones = true;
        }
        loading(false);
      } catch (error) {
        loading(false);
        notify(error, "error");
      }
    },
    irCargasAcademicas() {
      this.mostrarModalExito = false;
      loading(true);
      this.$inertia.get("/docente/cargasAcademicas");
    },
    reiniciarValores() {
      this.alumnosSeleccionados = [];
      this.alumnosInicial = cloneDeep(this.alumnosOriginal);
    },
    checarAsistenciaExistente(alumno) {
      const existe = this.asistenciasExistentes.find((ae) => ae.numestudiante == alumno.numestudiante);
      if (existe) {
        return Number(existe.asistencia) == 1 ? true : false;
      }
      return false;
    },
    async actualizarCalificaciones() {
      try {
        this.mostrarModalReemplazarCalificaciones = false;
        let alumnosDataFinal = cloneDeep(this.alumnosDataFinal);
        alumnosDataFinal.forEach((alumno) => {
          const alumnoExiste = this.asistenciasExistentes.find((ae) => ae.numestudiante == alumno.numestudiante);
          if (alumnoExiste) {
            alumno.idAsistencias = alumnoExiste.idAsistencias;
          }
        });
        const { semestre, grupo, licenciatura, materia, periodo } =
          this.alumnosInicial[0];
        const form = {
          fecha: this.fecha,
          licenciatura,
          semestre,
          grupo,
          idCargaAcademica: this.idCargaAcademica,
          alumnos: JSON.stringify(alumnosDataFinal),
          periodo,
          materia,
        };
        loading(true, "Actualizando ...");
        const respuesta = await axios.post(
          "/docente/actualizarAsistencias",
          form
        );
        const { data, status, statusText } = respuesta;
        if (Number(status) != 200) {
          throw `Ocurrio un error al hacer la solicitud: ${statusText || "--"
          }`;
        }
        if (data.status != 200) {
          throw data.mensaje;
        } 
        loading(false);
        notify(data.mensaje, "exito");
      } catch (error) {
        loading(false);
        notify(error, "error");
      }
    },
  },
};
</script>
