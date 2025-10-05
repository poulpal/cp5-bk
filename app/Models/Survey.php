<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function results()
    {
        return $this->hasMany(SurveyResult::class);
    }

    public function getQuestionsAttribute($value)
    {
        return json_decode($value);
    }

    public function setQuestionsAttribute($value)
    {
        $this->attributes['questions'] = json_encode($value);
    }
}
