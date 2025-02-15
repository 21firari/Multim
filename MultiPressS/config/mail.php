<?php
return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your-email@gmail.com',
        'password' => 'your-app-password',
        'encryption' => 'tls'
    ],
    'from' => [
        'address' => 'noreply@multipresshub.com',
        'name' => 'MultiPress Hub'
    ],
    'templates' => [
        'verification' => 'templates/emails/verification.php',
        'welcome' => 'templates/emails/welcome.php',
        'password_reset' => 'templates/emails/password-reset.php',
        'subscription' => 'templates/emails/subscription.php'
    ]
];