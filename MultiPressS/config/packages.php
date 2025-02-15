<?php
// config/packages.php
return [
    'free' => [
        'name' => 'Ücretsiz',
        'price' => 0,
        'features' => [
            '3 WordPress Sitesi',
            'Temel İçerik Yönetimi',
            'Email Desteği'
        ]
    ],
    'professional' => [
        'name' => 'Profesyonel',
        'price' => 199,
        'features' => [
            '10 WordPress Sitesi',
            'Gelişmiş İçerik Yönetimi',
            'Öncelikli Destek',
            'API Erişimi',
            'İstatistikler'
        ]
    ],
    'enterprise' => [
        'name' => 'Kurumsal',
        'price' => 499,
        'features' => [
            'Sınırsız WordPress Sitesi',
            'Özel API Erişimi',
            '7/24 Öncelikli Destek',
            'Özel Entegrasyonlar',
            'Gelişmiş Raporlama'
        ]
    ]
];