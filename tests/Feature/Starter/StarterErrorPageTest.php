<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

test('specific error pages use the local Tabler design', function (string $code, string $title) {
    $html = view("errors::{$code}")->render();

    expect($html)
        ->toContain("<title>{$code} · {$title}")
        ->toContain('assets/tabler/dist/css/tabler.min.css')
        ->toContain('assets/tabler/dist/js/tabler-theme.min.js')
        ->toContain('assets/tabler/dist/js/tabler.min.js')
        ->toContain('Kembali ke Beranda')
        ->toContain("Kode error: <span class=\"font-monospace\">{$code}</span>")
        ->not->toContain('cdn.');
})->with([
    ['400', 'Permintaan Tidak Valid'],
    ['401', 'Autentikasi Diperlukan'],
    ['403', 'Akses Ditolak'],
    ['404', 'Halaman Tidak Ditemukan'],
    ['405', 'Metode Tidak Diizinkan'],
    ['408', 'Permintaan Kehabisan Waktu'],
    ['419', 'Session Telah Berakhir'],
    ['422', 'Data Tidak Dapat Diproses'],
    ['429', 'Terlalu Banyak Permintaan'],
    ['500', 'Terjadi Kesalahan Server'],
    ['503', 'Aplikasi Sedang Dalam Pemeliharaan'],
]);

test('generic client and server error fallbacks use the same design', function (string $view, int $status) {
    $html = view($view, [
        'exception' => new HttpException($status),
    ])->render();

    expect($html)
        ->toContain((string) $status)
        ->toContain('Kembali ke Beranda')
        ->toContain('assets/tabler/dist/css/tabler.min.css');
})->with([
    ['errors::4xx', 418],
    ['errors::5xx', 502],
]);

test('a real missing route renders the custom error page with security headers', function () {
    config()->set('app.debug', false);

    $response = $this->get('/starter-missing-page-for-error-test')
        ->assertNotFound()
        ->assertSee('Halaman Tidak Ditemukan')
        ->assertSee('Kembali ke Beranda')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeaderMissing('Content-Security-Policy');
});
