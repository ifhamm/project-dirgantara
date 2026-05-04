<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stripping extends Model
{
    use HasFactory;

    protected $table = 'stripping';

    protected $fillable = [
        'bdpName', 'bdpNumber', 'bdpNumberEqv', 'qty', 'unit', 'opNumber', 'opDate', 'defect',
        'mtNumber', 'mtQty', 'mtDate', 'mws_part_id', 'remark'
    ];

    public function mwsPart()
    {
        return $this->belongsTo(MwsPart::class);
    }
}
