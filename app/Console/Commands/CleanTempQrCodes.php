<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempQrCodes extends Command
{
    protected $signature = 'qr:clean';
    protected $description = 'Limpia archivos QR temporales antiguos';

    public function handle()
    {
        $files = Storage::disk('local')->files('temp');
        $count = 0;

        foreach ($files as $file) {
            if (str_contains($file, 'temp_qr_')) {
                $timestamp = Storage::disk('local')->lastModified($file);
                
                // Borrar si tiene más de 1 hora
                if (now()->timestamp - $timestamp > 3600) {
                    Storage::disk('local')->delete($file);
                    $count++;
                }
            }
        }

        $this->info("Eliminados {$count} archivos QR temporales.");
        return 0;
    }
}
