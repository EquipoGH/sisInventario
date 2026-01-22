<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{

    protected $table = 'perfil'; 
    protected $primaryKey = 'idperfil'; 
    protected $keyType = 'int'; 
    public $incrementing = true; 

    public $timestamps = false;

    protected $perPage = 10; 

    protected $fillable = [
        'nomperfil',
    ]; 

    protected $casts = [
        'idperfil'  => 'integer',
        'nomperfil' => 'string',
    ]; 


    protected $appends = [
        'display_name',
        'nomperfil_normalizado',
    ]; 

    /*
    |--------------------------------------------------------------------------
    | Accessors / Mutators 
    |--------------------------------------------------------------------------
    | 
    */
    protected function nomperfil(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => (string) $value,
            set: function ($value) {
                $value = trim((string) $value);
                $value = preg_replace('/\s+/', ' ', $value);
                return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8'); 
            }
        ); 
    }

    
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nomperfil
        ); 
    }

    protected function nomperfilNormalizado(): Attribute
    {
        return Attribute::make(
            get: fn () => mb_strtolower(trim((string) $this->nomperfil), 'UTF-8')
        ); 
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (consultas limpias en listados AdminLTE)
    |--------------------------------------------------------------------------
    */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;

        return $query->where('nomperfil', 'ilike', "%{$term}%");
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('idperfil', 'desc');
    }

    public function scopeSelectList(Builder $query): Builder
    {
        return $query->select(['idperfil', 'nomperfil'])->ordered();
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas/Helpers de dominio (opcionales)
    |--------------------------------------------------------------------------
    */
    public function isAdminProfile(): bool
    {
        return mb_strtolower($this->nomperfil, 'UTF-8') === 'administrador';
    }
}
