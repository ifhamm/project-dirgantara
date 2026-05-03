<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MwsConsumable extends Model
{
    protected $fillable = [
        'mws_part_id', 'name', 'identification', 'quantity', 'order'
    ];

    public function mwsPart()
    {
        return $this->belongsTo(MwsPart::class, 'mws_part_id');
    }
}