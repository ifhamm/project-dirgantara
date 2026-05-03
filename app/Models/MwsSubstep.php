<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MwsSubStep extends Model
{
    protected $fillable = [
        'mws_step_id', 'label', 'description', 'order'
    ];

    public function step()
    {
        return $this->belongsTo(MwsStep::class, 'mws_step_id');
    }
}