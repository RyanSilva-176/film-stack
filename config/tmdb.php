<?php

return [

    /*
    |--------------------------------------------------------------------------
    * Use Refactored Services
    |--------------------------------------------------------------------------
    |
    | Esta configuração permite alternar entre os serviços originais e
    | refatorados do TMDB. Útil para migração gradual e testes A/B.
    |
    | true = Usa serviços refatorados (nova arquitetura)
    | false = Usa serviços originais (comportamento atual)
    |
    */

    'use_refactored_services' => env('TMDB_USE_REFACTORED_SERVICES', false),

    /*
    |--------------------------------------------------------------------------
    * Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configurações específicas de cache para os serviços TMDB
    |
    */

    'cache' => [
        'dynamic_ttl' => 300,
        'static_ttl' => 86400,
        'prefix' => 'tmdb_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configurações para rate limiting da API TMDB
    |
    */

    'rate_limiting' => [
        'delay_ms' => 250,
        'max_retries' => 3,
        'retry_delay_ms' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Settings
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para busca
    |
    */

    'search' => [
        'max_pages_per_query' => 5,
        'results_per_page' => 20,
        'min_word_length' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Settings
    |--------------------------------------------------------------------------
    |
    | Configurações para URLs de imagens
    |
    */

    'images' => [
        'poster_sizes' => ['w92', 'w154', 'w185', 'w342', 'w500', 'w780', 'original'],
        'backdrop_sizes' => ['w300', 'w780', 'w1280', 'original'],
        'default_poster_size' => 'w500',
        'default_backdrop_size' => 'w1280',
    ],

];
