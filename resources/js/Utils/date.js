//Método para obtener fecha actual como jueves, 17 de agosto de 2023
export const obtenerFechaActualCompletaMostrar = () => {
  let fecha = new Date();
  let opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  return fecha.toLocaleDateString('es-MX', opciones);
};

//Método para obtener fecha actual como DD/MM/YYYY
export const obtenerFechaActualMostrar = () => {
  let fecha = new Date();
  let opciones = { day: '2-digit', month: '2-digit', year: 'numeric' };
  return fecha.toLocaleDateString('es-MX', opciones);
};

//Método para obtener fecha actual como YYYY/MM/DD
export const obtenerFechaActualOperacion = () => {
  let fecha = new Date();
  let anio = fecha.getFullYear();
  let mes = ("0" + (fecha.getMonth() + 1)).slice(-2);
  let dia = ("0" + fecha.getDate()).slice(-2);
  return anio + "/" + mes + "/" + dia;
};

//Método para validar si es una fecha válida
export const esFechaValida = (fechaParam) => {
  const pattern = /^\d{4}\/\d{2}\/\d{2}$/;
  if (!pattern.test(fechaParam)) {
    return {
      status: 300,
      mensaje: 'El formato es incorrecto, debe ser YYYY/MM/DD',
    };
  }

  const parts = fechaParam.split("/");
  const year = parseInt(parts[0], 10);
  const month = parseInt(parts[1], 10) - 1; // Los meses en JavaScript son 0-indexados
  const day = parseInt(parts[2], 10);
  const date = new Date(year, month, day);

  if (
    date.getFullYear() !== year ||
    date.getMonth() !== month ||
    date.getDate() !== day
  ) {
    return {
      status: 301,
      mensaje: 'La fecha es inválida',
    };
  }

  return {
    status: 200,
    mensaje: 'Fecha es correcta',
  };
};
