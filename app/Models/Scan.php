<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Scan extends Model
{
    protected $fillable = [
        'user_id', 'repository_url', 'branch', 'scan_type', 'env_file_path',
        'project_upload_path', 'status', 'progress', 'priority', 'completed_at',
        'modules',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'progress' => 'integer',
        'modules' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(ScanResult::class);
    }
}
