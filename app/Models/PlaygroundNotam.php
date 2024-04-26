<?php

namespace App\Models;

use App\Actions\ProcessPlaygroundNotam;
use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Enum\Tag;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaygroundNotam extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'text',
        'tag',
        'summary',
        'llm',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'tag'          => Tag::class,
        'llm'          => LLM::class,
        'status'       => NotamStatus::class,
        'processed_at' => 'datetime',
    ];

    protected $appends = [
        'is_processed',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PlaygroundSession::class, 'session_id');
    }

    protected function isProcessed(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->processed_at),
        );
    }

    public function process(): void
    {
        $action = new ProcessPlaygroundNotam;

        $action->process($this);
    }
}
