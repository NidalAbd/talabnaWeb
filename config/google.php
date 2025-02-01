<?php

return [
    'application_name' => 'palservice',
    'client_id' => 'YOUR_CLIENT_ID',
    'client_secret' => 'YOUR_CLIENT_SECRET',
    'redirect_uri' => 'YOUR_REDIRECT_URI',
    'scopes' => [
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
    ],
    'access_type' => 'offline',
    'approval_prompt' => 'force',
];
