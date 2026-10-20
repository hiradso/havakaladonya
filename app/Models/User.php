<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Gender;
use App\Support\Roles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * `role` (and the old `is_admin`) are deliberately NOT here, so a visitor can never
     * give themselves panel access through a registration or profile form.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'is_admin' => 'boolean',
        ];
    }

    /** Only staff (users with a role) may open the /admin panel. */
    public function canAccessPanel(Panel $panel): bool
    {
        return array_key_exists((string) $this->role, Roles::LABELS);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === Roles::ADMIN;
    }

    public function roleLabel(): string
    {
        return Roles::LABELS[$this->role] ?? '—';
    }

    /** Avatar of the panel by role and gender (public/images/avatars/{role}-{gender}.svg). */
    public function getFilamentAvatarUrl(): ?string
    {
        if (! array_key_exists((string) $this->role, Roles::LABELS)) {
            return null;
        }

        $gender = $this->gender === Gender::FEMALE ? Gender::FEMALE : Gender::MALE;

        return asset("images/avatars/{$this->role}-{$gender}.svg");
    }
}
