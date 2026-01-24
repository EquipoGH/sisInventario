<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class DocumentoSustento extends Model
{
    use HasFactory;

    protected $table = 'documento_sustento';
    protected $primaryKey = 'id_documento';
    public $incrementing = true;

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'fecha_documento'
    ];

    protected $casts = [
        'fecha_documento' => 'date'
    ];

    public function getRouteKeyName()
    {
        return 'id_documento';
    }

    // ==================== ACCESSORS ====================

    // Tipo de documento en mayúsculas
    protected function tipoDocumento(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // ⭐ NUEVO: Número de documento en mayúsculas (consistencia)
    protected function numeroDocumento(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Fecha formateada
    public function getFechaFormateadaAttribute()
    {
        return Carbon::parse($this->fecha_documento)->format('d/m/Y');
    }

    // ==================== RELACIONES ====================

    // ⭐ CRÍTICO: Relación con Bien (hasMany)
    public function bienes()
    {
        return $this->hasMany(Bien::class, 'id_documento', 'id_documento');
    }

    // Relación con Movimiento (cuando la implementes)
    // public function movimientos()
    // {
    //     return $this->hasMany(Movimiento::class, 'id_documento', 'id_documento');
    // }

    // ==================== MÉTODOS AUXILIARES ====================

    // ⭐ NUEVO: Contar bienes asociados
    public function cantidadBienes()
    {
        return $this->bienes()->count();
    }

    // ⭐ NUEVO: Verificar si tiene bienes asociados
    public function tieneBienes()
    {
        return $this->bienes()->exists();
    }

    // ⭐ NUEVO: Descripción completa para SELECT
    public function descripcionCompleta()
    {
        return "{$this->tipo_documento} - {$this->numero_documento} ({$this->fecha_formateada})";
    }

    // ⭐ NUEVO: Método para obtener bienes asociados con detalles
    public function bienesConDetalles()
    {
        return $this->bienes()
                    ->with(['tipoBien'])
                    ->orderBy('codigo_patrimonial')
                    ->get();
    }

    // ==================== SCOPES ====================

    // ⭐ NUEVO: Buscar por tipo de documento
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_documento', 'ILIKE', "%{$tipo}%");
    }

    // ⭐ NUEVO: Buscar por número de documento
    public function scopePorNumero($query, $numero)
    {
        return $query->where('numero_documento', 'ILIKE', "%{$numero}%");
    }

    // ⭐ NUEVO: Documentos que tienen bienes asociados
    public function scopeConBienes($query)
    {
        return $query->has('bienes');
    }

    // ⭐ NUEVO: Documentos sin bienes asociados
    public function scopeSinBienes($query)
    {
        return $query->doesntHave('bienes');
    }

    // ⭐ NUEVO: Documentos por rango de fecha
    public function scopePorRangoFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_documento', [$fechaInicio, $fechaFin]);
    }

    // ⭐ NUEVO: Documentos recientes (últimos 30 días)
    public function scopeRecientes($query)
    {
        return $query->where('fecha_documento', '>=', Carbon::now()->subDays(30));
    }
}
