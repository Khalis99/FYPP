<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * The teacher profile of the signed-in user (teacher routes only).
     */
    protected function currentTeacher(Request $request): Teacher
    {
        return $request->user()->teacher ?? abort(403, 'Your account is not linked to a teacher profile.');
    }
}
