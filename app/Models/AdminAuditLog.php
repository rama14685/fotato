<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\AdminAuditLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'admin_id',
        'action_type',
        'target_entity_type',
        'target_entity_id',
        'description',
        'changes',
        'ip_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the admin user who performed this action.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Log an admin action to the audit trail.
     */
    public static function logAction(
        int $adminId,
        string $actionType,
        ?string $targetEntityType = null,
        ?int $targetEntityId = null,
        ?string $description = null,
        ?array $changes = null,
        ?string $ipAddress = null
    ): self {
        return static::create([
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'target_entity_type' => $targetEntityType,
            'target_entity_id' => $targetEntityId,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }
}
