<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CannedReply extends Model
{
    protected $fillable = ['title', 'body', 'shortcut'];
}
