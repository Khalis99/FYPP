<?php

return [

    /*
    |--------------------------------------------------------------------------
    | School Information
    |--------------------------------------------------------------------------
    */

    'school_name' => env('SCHOOL_NAME', 'SMK Seri Londang'),

    'tagline' => 'Lighting the way for teachers',

    /*
    |--------------------------------------------------------------------------
    | File Uploads
    |--------------------------------------------------------------------------
    |
    | Allowed file extensions and the maximum size (in kilobytes) for teaching
    | materials, assignment attachments and recorded submissions.
    |
    */

    'uploads' => [
        'mimes' => ['pdf', 'docx', 'pptx', 'xlsx', 'jpg', 'jpeg', 'png'],
        'max_kb' => 10240,
    ],

];
