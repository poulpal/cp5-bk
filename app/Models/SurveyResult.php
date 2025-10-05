<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyResult extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function getResultsAttribute($value)
    {
        return json_decode($value);
    }

    public function setResultsAttribute($value)
    {
        $this->attributes['results'] = json_encode($value);
    }
}
