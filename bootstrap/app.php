<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. የ Vercel Proxyዎችን በሙሉ ማመን
        $middleware->trustProxies(at: '*');

        // 2. 419 Errorን ሙሉ በሙሉ ለማጥፋት የ CSRF ማጣሪያን ለሁሉም ፎርሞች ማለፍ
        $middleware->validateCsrfTokens(except: [
            '*', // ሁሉንም ፎርሞች እና ጥያቄዎች ያለ 419 እንዲያልፉ ያደርጋል
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Vercel Storage Path
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
