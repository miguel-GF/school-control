import { exportFile, Notify } from "./quasar";

function wrapCsvValue(val, formatFn) {
    let formatted = formatFn !== void 0 ? formatFn(val) : val;

    formatted =
        formatted === void 0 || formatted === null
            ? ""
            : limpiarCadena(String(formatted));

    formatted = formatted.split('"').join('""');
    /**
     * Excel accepts \n and \r in strings, but some other CSV parsers do not
     * Uncomment the next two lines to escape new lines
     */
    // .split('\n').join('\\n')
    // .split('\r').join('\\r')

    return `"${formatted}"`;
}

function limpiarCadena(cadena) {
    return cadena.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

export default class exportExcel {
    static exportarCsv(columns, rows, nombreArchivo) {
        // naive encoding to csv format
        const content = [columns.map((col) => wrapCsvValue(col.labelExcel))]
            .concat(
                rows.map((row) =>
                    columns
                        .map((col) =>
                            wrapCsvValue(
                                typeof col.field === "function"
                                    ? col.field(row)
                                    : row[
                                          col.field === void 0
                                              ? col.name
                                              : col.field
                                      ],
                                col.format
                            )
                        )
                        .join(",")
                )
            )
            .join("\r\n");

        const status = exportFile(`${nombreArchivo}.csv`, content, "text/csv");

        if (status !== true) {
            Notify.create({
                message: "El navegador denegó la descarga del archivo",
                icon: "report_problem",
                color: "negative",
                position: "top",
                actions: [
                    {
                        label: "x",
                        color: "white",
                        handler: () => {
                            /* ... */
                        },
                    },
                ],
            });
        } else {
            Notify.create({
                message: "Archivo descargado correctamente",
                icon: "thumb_up",
                color: "positive",
                position: "top",
                actions: [
                    {
                        label: "x",
                        color: "white",
                        handler: () => {
                            /* ... */
                        },
                    },
                ],
            });
        }
    }
}
