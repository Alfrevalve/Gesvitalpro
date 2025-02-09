<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Traits\Auditable;

class Configuracion extends Model
{
    use Auditable;

    protected $table = 'configuraciones';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener una configuración por su clave
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Cache::remember('config.' . $key, 3600, function () use ($key, $default) {
            $config = static::where('key', $key)->first();
            
            if (!$config) {
                return $default;
            }

            $value = $config->value;

            // Convertir el valor según su tipo
            switch ($config->type) {
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'integer':
                    return (int) $value;
                case 'float':
                    return (float) $value;
                case 'json':
                    return json_decode($value, true);
                default:
                    return $value;
            }
        });
    }

    /**
     * Establecer una configuración
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string $type
     * @param string|null $description
     * @return bool
     */
    public static function set($key, $value, $group = null, $type = 'string', $description = null)
    {
        // Preparar el valor según el tipo
        switch ($type) {
            case 'json':
                $value = is_string($value) ? $value : json_encode($value);
                break;
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                break;
            default:
                $value = (string) $value;
        }

        $result = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
                'description' => $description
            ]
        );

        // Limpiar el caché para esta configuración
        Cache::forget('config.' . $key);

        return (bool) $result;
    }

    /**
     * Obtener todas las configuraciones por grupo
     *
     * @param string|null $group
     * @return array
     */
    public static function getAllByGroup($group = null)
    {
        $query = static::query();

        if ($group) {
            $query->where('group', $group);
        }

        $configs = $query->get();
        $result = [];

        foreach ($configs as $config) {
            $result[$config->key] = static::get($config->key);
        }

        return $result;
    }

    /**
     * Eliminar una configuración
     *
     * @param string $key
     * @return bool
     */
    public static function remove($key)
    {
        $result = static::where('key', $key)->delete();
        Cache::forget('config.' . $key);
        return (bool) $result;
    }

    /**
     * Verificar si existe una configuración
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Obtener el valor de la configuración
     *
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Establecer el valor de la configuración
     *
     * @param mixed $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        switch ($this->type) {
            case 'json':
                $this->attributes['value'] = is_string($value) ? $value : json_encode($value);
                break;
            case 'boolean':
                $this->attributes['value'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                break;
            default:
                $this->attributes['value'] = (string) $value;
        }

        // Limpiar el caché cuando se modifica el valor
        Cache::forget('config.' . $this->key);
    }
}
