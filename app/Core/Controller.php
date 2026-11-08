<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);

        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View '{$view}' tidak ditemukan.");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/' . $layout . '.php';
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    protected function old(string $key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }

    protected function setOld(array $data): void
    {
        $_SESSION['old'] = $data;
    }

    protected function clearOld(): void
    {
        unset($_SESSION['old']);
    }

    protected function errors(string $key): ?string
    {
        return $_SESSION['errors'][$key] ?? null;
    }

    protected function setErrors(array $errors): void
    {
        $_SESSION['errors'] = $errors;
    }

    protected function clearErrors(): void
    {
        unset($_SESSION['errors']);
    }
}
