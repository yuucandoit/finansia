<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'icon',
        'color',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'category_id');
    }

    /**
     * Scope untuk mengambil kategori sistem (user_id IS NULL) dan kategori milik user terkait.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }

    /**
     * Mencari kategori yang ada atau membuat kebutuhan baru untuk user secara otomatis.
     */
    public static function findOrCreateKebutuhan(string $name, int $userId): self
    {
        $nameTrimmed = trim($name);
        $nameLower = strtolower($nameTrimmed);

        $existing = static::forUser($userId)
            ->whereRaw('LOWER(name) = ?', [$nameLower])
            ->first();

        if ($existing) {
            return $existing;
        }

        $colors = ['blue', 'emerald', 'indigo', 'purple', 'pink', 'amber', 'teal', 'orange'];
        $color = $colors[abs(crc32($nameLower)) % count($colors)];

        $icon = 'fa-tag';
        if (str_contains($nameLower, 'kos') || str_contains($nameLower, 'kontrakan') || str_contains($nameLower, 'sewa') || str_contains($nameLower, 'rumah')) {
            $icon = 'fa-house';
        } elseif (str_contains($nameLower, 'makan') || str_contains($nameLower, 'jajan') || str_contains($nameLower, 'kopi') || str_contains($nameLower, 'resto') || str_contains($nameLower, 'kuliner')) {
            $icon = 'fa-utensils';
        } elseif (str_contains($nameLower, 'bensin') || str_contains($nameLower, 'transport') || str_contains($nameLower, 'ojol') || str_contains($nameLower, 'mobil') || str_contains($nameLower, 'motor')) {
            $icon = 'fa-car';
        } elseif (str_contains($nameLower, 'listrik') || str_contains($nameLower, 'air') || str_contains($nameLower, 'wifi') || str_contains($nameLower, 'pulsa') || str_contains($nameLower, 'kuota')) {
            $icon = 'fa-bolt';
        } elseif (str_contains($nameLower, 'belanja') || str_contains($nameLower, 'sembako') || str_contains($nameLower, 'pasar') || str_contains($nameLower, 'mall')) {
            $icon = 'fa-shopping-bag';
        } elseif (str_contains($nameLower, 'sedekah') || str_contains($nameLower, 'zakat') || str_contains($nameLower, 'infaq') || str_contains($nameLower, 'donasi')) {
            $icon = 'fa-hand-holding-heart';
        } elseif (str_contains($nameLower, 'gym') || str_contains($nameLower, 'olahraga') || str_contains($nameLower, 'fitness')) {
            $icon = 'fa-dumbbell';
        } elseif (str_contains($nameLower, 'obat') || str_contains($nameLower, 'dokter') || str_contains($nameLower, 'sehat') || str_contains($nameLower, 'klinik')) {
            $icon = 'fa-heartbeat';
        } elseif (str_contains($nameLower, 'sekolah') || str_contains($nameLower, 'kuliah') || str_contains($nameLower, 'buku') || str_contains($nameLower, 'kursus')) {
            $icon = 'fa-graduation-cap';
        } elseif (str_contains($nameLower, 'cicilan') || str_contains($nameLower, 'hutang') || str_contains($nameLower, 'kredit') || str_contains($nameLower, 'arisan')) {
            $icon = 'fa-credit-card';
        }

        return static::create([
            'user_id' => $userId,
            'name'    => ucwords($nameTrimmed),
            'type'    => 'Cash Out',
            'color'   => $color,
            'icon'    => $icon,
        ]);
    }
}
