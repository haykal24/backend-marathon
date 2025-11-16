<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'code',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function generateCode(string $phoneNumber): string
    {
        $code = str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        self::create([
            'phone_number' => $phoneNumber,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && is_null($this->used_at);
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}

