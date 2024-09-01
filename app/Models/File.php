<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mime_type',
        'size',
        'fileable_id',
        'fileable_type',
    ];

    /**
     * Get the owning fileable model.
     */
    public function fileable()
    {
        return $this->morphTo();
    }
}
