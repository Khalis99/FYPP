<?php

namespace App\Enums;

enum MaterialType: string
{
    case Notes = 'notes';
    case Slides = 'slides';
    case Worksheet = 'worksheet';
    case Reference = 'reference';
    case Other = 'other';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
