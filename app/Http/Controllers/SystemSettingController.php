<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSystemSettingRequest;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemSettingController extends Controller
{
    public function edit()
    {
        $settings = SystemSetting::query()->pluck('value', 'key')->toArray();

        // Opcional: URLs listos para usar en Blade (evitas asset('storage/...'))
        $settings['logo_url'] = !empty($settings['logo_path'])
            ? Storage::disk('public')->url($settings['logo_path'])
            : null;

        $settings['favicon_url'] = !empty($settings['favicon_path'])
            ? Storage::disk('public')->url($settings['favicon_path'])
            : null;

        $settings['logo_reportes_url'] = !empty($settings['logo_reportes_path'])
            ? Storage::disk('public')->url($settings['logo_reportes_path'])
            : null;

        return view('configuracion.settings', compact('settings'));
    }

    public function update(UpdateSystemSettingRequest $request)
    {
        // Normaliza: trim strings (evita guardar "   ")
        $data = collect($request->validated())->map(function ($v) {
            return is_string($v) ? trim($v) : $v;
        })->all();

        $this->setMany([
            // Identidad
            'nombre_institucion' => [$data['nombre_institucion'], 'string'],
            'slogan' => [$data['slogan'] ?? '', 'string'],
            'ruc' => [$data['ruc'] ?? '', 'string'],
            'web' => [$data['web'] ?? '', 'string'],

            // Contacto
            'direccion' => [$data['direccion'] ?? '', 'string'],
            'telefono' => [$data['telefono'] ?? '', 'string'],
            'email_contacto' => [$data['email_contacto'] ?? '', 'string'],

            // Regional
            'moneda' => [$data['moneda'], 'string'],
            'timezone' => [$data['timezone'], 'string'],
            'locale' => [$data['locale'], 'string'],
            'date_format' => [$data['date_format'], 'string'],

            // Reportes
            'pie_reportes' => [$data['pie_reportes'] ?? '', 'text'],
            'texto_legal' => [$data['texto_legal'] ?? '', 'text'],

            // Soporte (institución)
            'correo_soporte' => [$data['correo_soporte'] ?? '', 'string'],
            'telefono_soporte' => [$data['telefono_soporte'] ?? '', 'string'],

            // Sistema / AdminLTE
            'items_por_pagina' => [(string) $data['items_por_pagina'], 'number'],
            'sidebar_theme' => [$data['sidebar_theme'], 'string'],
        ]);

        // Uploads (disco public)
        if ($request->hasFile('logo')) {
            $this->replacePublicFile('logo_path', $request->file('logo'), 'branding', 'image');
        }

        if ($request->hasFile('favicon')) {
            $this->replacePublicFile('favicon_path', $request->file('favicon'), 'branding', 'image');
        }

        if ($request->hasFile('logo_reportes')) {
            $this->replacePublicFile('logo_reportes_path', $request->file('logo_reportes'), 'branding', 'image');
        }

        // Si cacheas settings con rememberForever, esto es lo correcto para invalidar
        Cache::forget('settings:all');

        return redirect()
            ->route('configuracion.institucion')
            ->with('success', 'Configuración actualizada.');
    }

    private function setOne(string $key, $value, string $type = 'string'): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    private function setMany(array $items): void
    {
        foreach ($items as $key => $payload) {
            [$value, $type] = $payload;
            $this->setOne($key, $value, $type);
        }
    }

    private function replacePublicFile(string $key, $file, string $dir, string $type): void
    {
        $oldPath = (string) SystemSetting::where('key', $key)->value('value');

        $newPath = $file->store($dir, 'public'); // retorna "branding/xxxx.ext" [web:22]
        $this->setOne($key, $newPath, $type);

        if (!empty($oldPath) && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
