<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlaygroundSession extends Model
{
    use HasFactory;
    use HasUuids;
    use Prunable;

    protected $fillable = [
        'ip_address',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session) {
            $session->ip_address ??= request()->ip();
        });

        static::deleted(function (self $session) {
            $session->notams()->delete();
        });
    }

    public function prunable(): Builder
    {
        return static::query()
            ->where('updated_at', '<=', now()->subMonth())
            ->orWhere(function (Builder $query) {
                $query->whereDoesntHave('notams')
                    ->where('updated_at', '<', now()->subHour());
            });
    }

    public function notams(): HasMany
    {
        return $this->hasMany(PlaygroundNotam::class, 'session_id');
    }
}
