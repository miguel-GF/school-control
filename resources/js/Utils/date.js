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
  return anio + "-" + mes + "-" + dia;
};
