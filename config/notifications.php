<?php

return [

    'default' => 'mail',

    'channels' => [
        'mail' => [
            'driver' => 'mail',
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        // Otros canales pueden ser añadidos aquí, como SMS, Pusher, etc.
    ],

];
