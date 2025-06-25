<?php

namespace App\Models;

use App\Models\CustomModel;

class Favorite extends CustomModel
{
    protected $fillable = [
        'user_id',
        'word',
        'phonetics',
        'definitions',
        'partOfSpeech',
        'examples',
        'synonyms',
    ];

    protected $casts = [
        'phonetics' => 'array',
        'definitions' => 'array',
        'examples' => 'array',
        'synonyms' => 'array',
    ];

    public function setPhoneticsAttribute($value)
    {
        $this->attributes['phonetics'] = json_encode($value);
    }

    public function setDefinitionsAttribute($value)
    {
        $this->attributes['definitions'] = json_encode($value);
    }

    public function setExamplesAttribute($value)
    {
        $this->attributes['examples'] = json_encode($value);
    }

    public function setSynonymsAttribute($value)
    {
        $this->attributes['synonyms'] = json_encode($value);
    }
}
