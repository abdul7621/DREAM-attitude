<?php

return [

    'lock_file' => storage_path('app/.installed'),

    'env_example' => base_path('.env.example'),

    'env_target' => base_path('.env'),

    'min_php' => '8.2',

    'required_extensions' => [
        'bcmath',
        'ctype',
        'curl',
        'fileinfo',
        'json',
        'mbstring',
        'openssl',
        'pdo',
        'tokenizer',
        'xml',
    ],

];
