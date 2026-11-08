<?php

namespace App\Core;

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\AdminMiddleware;
use App\Core\Middleware\GuestMiddleware;

class Router
{
    private array $routes;

    /** Nama pendek middleware -> class aslinya */
    private const MIDDLEWARE = [
        'auth'  => AuthMiddleware::class,
        'admin' => AdminMiddleware::class,
        'guest' => GuestMiddleware::class,
    ];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (BASE_URL !== '' && str_starts_with($uri, BASE_URL)) {
            $uri = substr($uri, strlen(BASE_URL));
        }

        $uri = '/' . ltrim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method][$uri])) {
            $route           = $this->routes[$method][$uri];
            $controllerClass = $route[0];
            $action          = $route[1];
            $middlewares     = $route[2] ?? [];      // daftar satpam (boleh kosong)

            // Jalankan satpam satu per satu SEBELUM controller dipanggil
            foreach ($middlewares as $nama) {
                if (!isset(self::MIDDLEWARE[$nama])) {
                    throw new \RuntimeException("Middleware '{$nama}' tidak dikenal.");
                }
                $class = self::MIDDLEWARE[$nama];
                (new $class())->handle();
            }

            $controller = new $controllerClass();
            $controller->$action();
            return;
        }

        http_response_code(404);
        echo '404 - Halaman tidak ditemukan: ' . htmlspecialchars($uri);
    }
}
