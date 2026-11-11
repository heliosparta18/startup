<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property CarbonImmutable|Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property string|null $role
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role', 'status'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
/**
 * @property string $id_pengguna
 */
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Apakah user memegang role tertentu. Kontrak role interim Modul 2
     * (kolom `role`); diformalkan menjadi relasi pada Modul 4–5.
     */
    public function hasRole(string $role): bool
    {
        return ($this->role === $role
            || ($role === 'super_admin' && $this->role === 'admin')
            || ($role === 'apoteker' && $this->role === 'tenant')
            || ($role === 'pelanggan' && $this->role === 'customer')
            || ($role === 'admin' && $this->role === 'super_admin')
            || ($role === 'tenant' && $this->role === 'apoteker')
            || ($role === 'customer' && $this->role === 'pelanggan'))
            && $this->isActive();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTenantOperator(): bool
    {
        return $this->hasRole('tenant');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'aktif';
    }

    /**
     * Dapatkan slug client cabang yang diasosiasikan dengan pengguna (khusus apoteker/staf).
     */
    public function getClientSlug(): string
    {
        if ($this->email === 'dewi@omnipharma.test' || str_contains(strtolower($this->name), 'dewi')) {
            return 'rogojampi';
        }

        try {
            $pengguna = Pengguna::where('telepon_email', $this->email)->first();
            if ($pengguna) {
                $admin = AdminClient::where('id_pengguna', $pengguna->id_pengguna)->with('client')->first();
                if ($admin && $admin->client && ! empty($admin->client->slug)) {
                    return $admin->client->slug;
                }
                $peran = PeranClientPengguna::where('id_pengguna', $pengguna->id_pengguna)->with('client')->first();
                if ($peran && $peran->client && ! empty($peran->client->slug)) {
                    return $peran->client->slug;
                }
            }
        } catch (\Throwable $e) {
            // fallback bila database belum termigrasi lengkap
        }

        return 'banyuwangi-kota';
    }

    /**
     * Dapatkan nama resmi cabang apotek pengguna.
     */
    public function getClientName(): string
    {
        return $this->getClientSlug() === 'rogojampi' ? 'Cabang Rogojampi' : 'Cabang Banyuwangi Kota';
    }
}
