<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    protected $fillable = [
        'scan_id', 'vulnerabilities', 'dependencies', 'score', 'duration_seconds', 'summary'
    ];

    protected $casts = [
        'vulnerabilities' => 'array',
        'dependencies' => 'array',
        'score' => 'integer',
        'duration_seconds' => 'float',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}
