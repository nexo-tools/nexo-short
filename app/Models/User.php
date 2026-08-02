<?php

namespace App\Models;

use App\Notifications\ResetPasswordQueued;
use App\Notifications\VerifyEmailQueued;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Auth-mode groundwork (ADR-003): the standalone-local user. SSO linkage columns
// (nexo_sub, etc.) are added in Phase 5 when Nexo ID integration lands.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @return HasMany<Link, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

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
     * The account mails, queued and in this product's template, with the locale
     * pinned at dispatch (family rules C2 and C3). This tool had no mail at all
     * until 2026-08-02 — a local account that forgot its password was stuck.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify((new ResetPasswordQueued($token))->locale(app()->getLocale()));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify((new VerifyEmailQueued)->locale(app()->getLocale()));
    }
}
