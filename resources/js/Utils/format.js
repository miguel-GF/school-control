//Método para formatear números, moneda, porcentaje
// tipo = number / currency / percent
export const formatearNumero = (
  valor,
  tipo = "number",
  multiplicarPorcentaje = true
) => {
  if (!valor) return Intl.NumberFormat("es-MX").format(0);
  const divisor = tipo == "percent" && !multiplicarPorcentaje ? 100 : 1;
  return valor && tipo == "number"
    ? Intl.NumberFormat("es-MX").format(valor)
    : Intl.NumberFormat("es-MX", {
        style: tipo,
        currency: "MXN",
      }).format(valor / divisor);
};
