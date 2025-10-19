<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // O tu modelo Eloquent

class RecuperarArchivosCurriculums implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $documento;

    /**
     * Crea una nueva instancia del Job.
     * @param array $documento ['nombre', 'ruta', 'archivo']
     */
    public function __construct(array $documento)
    {
        $this->documento = $documento;
    }

    /**
     * Ejecuta el job.
     *s
     * @return void
     */
    public function handle()
    {
        $nombre = $this->documento['nombre'];
        $ruta   = $this->documento['ruta'];
        $contenido_binario = base64_decode($this->documento['archivo_base64']);
        
        // 1. Definir la ruta completa del archivo en el disco
        $ruta_completa = $ruta . '/' . $nombre;
        
        // 2. Verificación de existencia (¡SI EXISTE, NO HACE NADA!)
        if (Storage::disk('public')->exists($ruta_completa)) {
            Log::info('El archivo ya existe en la carpeta de destino.');
            return;
        }

        // 3. Extracción y guardado del BLOB
        try {
            // file_get_contents() NO se usa aquí; el $archivo_blob ya es el contenido binario.
            Storage::disk('public')->put($ruta_completa, $contenido_binario);
            
            // Puedes agregar logging aquí si lo necesitas
            Log::info("PDF exportado: " . $ruta_completa);

        } catch (\Exception $e) {
            // Manejo de errores (ej. permisos de disco)
            Log::error("Error al exportar PDF: " . $ruta_completa . " - " . $e->getMessage());
            // Si falla, puedes optar por reintentar el job: $this->release(60);
        }
    }
}