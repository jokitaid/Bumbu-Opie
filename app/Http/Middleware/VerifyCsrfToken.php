<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        '/api/*',
        'api/login',
        'api/register',
        'api/logout',
        'api/user',
        'api/keranjang/*',
        'api/alamat/*',
        'api/pesanan/*',
        'api/notifikasi/*',
        'api/produks/*',
        'api/kategoris/*',
        'api/midtrans/*'
    ];
} 