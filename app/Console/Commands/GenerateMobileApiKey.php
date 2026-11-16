<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;

class GenerateMobileApiKey extends Command
{
    protected $signature = 'mobile:key
        {flavor : Flavor de la escuela, por ejemplo ipes, demo o valle}
        {--description= : Descripción opcional para identificar la key}';

    protected $description = 'Genera una API key para la app móvil y guarda solo su hash.';

    public function handle(): int
    {
        $flavor = trim((string) $this->argument('flavor'));
        if ($flavor === '') {
            $this->error('Indica un flavor, por ejemplo: php artisan mobile:key ipes');
            return self::FAILURE;
        }

        $plainKey = bin2hex(random_bytes(32));
        $hash = hash('sha256', $plainKey);
        $description = trim((string) $this->option('description'));

        ApiKey::create([
            'key' => substr($hash, 0, 50),
            'key_hash' => $hash,
            'flavor' => $flavor,
            'descripcion' => $description !== '' ? $description : "Mobile {$flavor}",
            'status' => 'Activo',
        ]);

        $define = strtoupper($flavor) . '_APP_KEY';

        $this->info("API key para {$flavor}: {$plainKey}");
        $this->warn('Guárdala ahora; no se volverá a mostrar.');
        $this->line("Build: --dart-define={$define}={$plainKey}");

        return self::SUCCESS;
    }
}
