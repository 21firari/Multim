// config/app.php
<?php
return [
    'name' => 'MultiPress Hub',
    'version' => '1.0.0',
    'url' => 'https://be.hostsepeti.com.tr',
    'timezone' => 'Europe/Istanbul',
    'locale' => 'tr',
    'debug' => true,
    'maintenance_mode' => false,
    'admin_email' => 'admin@multipress-hub.com'
];

// config/database.php
<?php
return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'admin_multipress_hub',
    'username' => 'root_hub',
    'password' => 'l6?x87T0h',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'mph_'
];

// config/payment.php
<?php
return [
    'paytr' => [
        'merchant_id' => 'XXXXX',
        'merchant_key' => 'XXXXX',
        'merchant_salt' => 'XXXXX',
        'test_mode' => true
    ],
    'currency' => 'TRY',
    'vat_rate' => 18
];

// config/mail.php
<?php
return [
    'driver' => 'smtp',
    'host' => 'smtp.mailtrap.io',
    'port' => 2525,
    'username' => 'XXXXX',
    'password' => 'XXXXX',
    'encryption' => 'tls',
    'from' => [
        'address' => 'info@multipress-hub.com',
        'name' => 'MultiPress Hub'
    ]
];