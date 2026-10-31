<?php

return [

    /*
    |--------------------------------------------------------------------------
    | School Information
    |--------------------------------------------------------------------------
    */

    'school_name' => env('SCHOOL_NAME', 'SMK Seri Londang'),

    /*
    |--------------------------------------------------------------------------
    | Interface Languages
    |--------------------------------------------------------------------------
    |
    | Code => name. The English text in the views is the translation key; the
    | Bahasa Melayu text lives in lang/ms.json.
    |
    */

    'locales' => [
        'en' => 'English',
        'ms' => 'Bahasa Melayu',
    ],

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
