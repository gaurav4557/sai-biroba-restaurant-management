<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'role',
        'password',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * All table assignments for this waiter (historical and active).
     */
    public function waiterAssignments(): HasMany
    {
        return $this->hasMany(TableWaiterAssignment::class, 'waiter_id');
    }

    /**
     * Currently active table assignments for this waiter.
     */
    public function activeWaiterAssignments(): HasMany
    {
        return $this->hasMany(TableWaiterAssignment::class, 'waiter_id')->where('status', 'active');
    }

    /**
     * All assignments created by this user (as admin).
     */
    public function assignedTablesByAdmin(): HasMany
    {
        return $this->hasMany(TableWaiterAssignment::class, 'assigned_by');
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to active staff members with the 'waiter' role.
     */
    public function scopeWaiters(Builder $query): Builder
    {
        return $query->where('role', 'waiter')->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    public function isWaiter(): bool
    {
        return $this->role === 'waiter' && $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }
}
