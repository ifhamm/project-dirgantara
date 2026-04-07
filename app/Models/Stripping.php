<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stripping extends Model
{
    use HasFactory;

    protected $table = 'stripping';

    protected $fillable = [
        'BDP NAME', 'BDP NUMBER', 'BDP_NUMBER_Eqv', 'QTY', 'UNIT', 'OP NUMBER', 'OP DATE', 'DEFECT',
        'MT NUMBER', 'MT QTY', 'MT DATE', 'mws_part_id', 'REMARK'
    ];

    public function mwsPart()
    {
        return $this->belongsTo(MwsPart::class);
    }
}
