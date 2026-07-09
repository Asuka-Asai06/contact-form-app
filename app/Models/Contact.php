<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'building',
        'detail',
    ];

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            1 => '男性',
            2 => '女性',
            3 => 'その他',
        };
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when(
                $filters['keyword'] ?? null,
                function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhereRaw(
                                'CONCAT(first_name,last_name) LIKE ?',
                                ["%{$keyword}%"]
                            );
                    });
                }
            )
            ->when(
                $filters['gender'] ?? null,
                fn ($q, $gender) => $q->where('gender', $gender)
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($q, $categoryId) => $q->where('category_id', $categoryId)
            )
            ->when(
                $filters['date'] ?? null,
                fn ($q, $date) => $q->whereDate('created_at', $date)
            );
    }
}
