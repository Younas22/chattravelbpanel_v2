<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappClick extends Model
{
    protected $fillable = ['session_id', 'whatsapp_label', 'whatsapp_number', 'page_url', 'page_title'];
}
