<?php
return [
    'merchant_id' => 'your-merchant-id',
    'merchant_key' => 'your-merchant-key',
    'merchant_salt' => 'your-merchant-salt',
    'test_mode' => true, // Production'da false yapılacak
    
    'currency' => 'TL',
    'lang' => 'tr',
    
    'packages' => [
        'basic' => [
            'name' => 'Temel Paket',
            'price' => 99.00,
            'sites' => 3,
            'posts_per_month' => 100,
            'storage' => 1024, // MB
            'features' => [
                'Temel WordPress Entegrasyonu',
                'İçerik Planlama',
                'Temel Analitikler'
            ]
        ],
        'professional' => [
            'name' => 'Profesyonel Paket',
            'price' => 199.00,
            'sites' => 10,
            'posts_per_month' => 500,
            'storage' => 5120, // MB
            'features' => [
                'Gelişmiş WordPress Entegrasyonu',
                'İçerik Planlama ve Zamanlama',
                'Detaylı Analitikler',
                'Öncelikli Destek'
            ]
        ],
        'enterprise' => [
            'name' => 'Kurumsal Paket',
            'price' => 399.00,
            'sites' => 25,
            'posts_per_month' => -1, // Sınırsız
            'storage' => 15360, // MB
            'features' => [
                'Tam WordPress Entegrasyonu',
                'Gelişmiş İçerik Yönetimi',
                'Özel Analitik Raporları',
                '7/24 Öncelikli Destek',
                'API Erişimi'
            ]
        ]
    ]
];