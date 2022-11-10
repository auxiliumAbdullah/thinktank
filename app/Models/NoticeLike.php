<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeLike extends BaseModel
{
    protected $dates = ['created_at', 'updated_at'];

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    // }

}
