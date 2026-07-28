<?php

namespace App\Models;

use App\Enums\DocumentState;
use App\Observers\DocumentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([DocumentObserver::class])]
class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'state',
    ];

    protected $attributes = [
        'state' => DocumentState::DRAFT->value,
    ];

    protected function casts(): array
    {
        return [
            'state' => DocumentState::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(DocumentHistory::class);
    }
}
