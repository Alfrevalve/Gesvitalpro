<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkCheckExclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'pattern',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the exclusion.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a URL matches this exclusion pattern.
     */
    public function matches(string $url): bool
    {
        // Convertir el patrón a una expresión regular válida
        $pattern = $this->convertPatternToRegex($this->pattern);
        
        return (bool) preg_match($pattern, $url);
    }

    /**
     * Convert a wildcard pattern to a regular expression.
     */
    protected function convertPatternToRegex(string $pattern): string
    {
        // Escapar caracteres especiales de regex
        $pattern = preg_quote($pattern, '/');
        
        // Convertir wildcards a patrones regex
        $pattern = str_replace(
            ['\*', '\?'],
            ['.*', '.'],
            $pattern
        );
        
        return '/^' . $pattern . '$/i';
    }

    /**
     * Scope a query to only include active exclusions.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get all URLs that match this exclusion pattern.
     */
    public function getMatchingUrls()
    {
        return BrokenLink::whereRaw("url REGEXP ?", [$this->pattern])
            ->orWhereRaw("url LIKE ?", [str_replace('*', '%', $this->pattern)])
            ->get();
    }

    /**
     * Check if the exclusion is being used.
     */
    public function isInUse(): bool
    {
        return $this->getMatchingUrls()->isNotEmpty();
    }

    /**
     * Get usage statistics for this exclusion.
     */
    public function getUsageStats(): array
    {
        $matchingUrls = $this->getMatchingUrls();
        
        return [
            'total_matches' => $matchingUrls->count(),
            'active_matches' => $matchingUrls->where('is_fixed', false)->count(),
            'fixed_matches' => $matchingUrls->where('is_fixed', true)->count(),
            'last_matched' => $matchingUrls->max('updated_at'),
        ];
    }

    /**
     * Validate a pattern before saving.
     */
    public static function validatePattern(string $pattern): bool
    {
        try {
            // Verificar que el patrón sea válido convirtiéndolo a regex
            $regex = (new static)->convertPatternToRegex($pattern);
            preg_match($regex, ''); // Intentar usar la regex
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($exclusion) {
            if (!static::validatePattern($exclusion->pattern)) {
                throw new \InvalidArgumentException('Invalid exclusion pattern');
            }
        });

        static::updating(function ($exclusion) {
            if (!static::validatePattern($exclusion->pattern)) {
                throw new \InvalidArgumentException('Invalid exclusion pattern');
            }
        });
    }

    /**
     * Get the validation rules for creating/updating an exclusion.
     */
    public static function rules(): array
    {
        return [
            'pattern' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!static::validatePattern($value)) {
                        $fail('The exclusion pattern is invalid.');
                    }
                },
            ],
            'reason' => 'required|string|max:255',
            'created_by' => 'required|exists:users,id',
        ];
    }
}
