<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!defined('APP_URL')) {
    define('APP_URL', rtrim(getenv('APP_URL') ?: '/stockflow', '/'));
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page, and try again.');
    }
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

function post_string(string $key, string $label, int $maxLength = 255, bool $required = true): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    if ($required && $value === '') {
        throw new InvalidArgumentException($label . ' is required.');
    }
    if (mb_strlen($value) > $maxLength) {
        throw new InvalidArgumentException($label . ' must be ' . $maxLength . ' characters or fewer.');
    }
    return $value;
}

function post_id(string $key, string $label, bool $required = true): ?int
{
    $value = $_POST[$key] ?? '';
    if ($value === '' && !$required) {
        return null;
    }
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('Choose a valid ' . strtolower($label) . '.');
    }
    return $id;
}

function post_number(string $key, string $label, float $minimum = 0): float
{
    $value = $_POST[$key] ?? '';
    if (!is_numeric($value) || (float) $value < $minimum) {
        throw new InvalidArgumentException($label . ' must be a number greater than or equal to ' . $minimum . '.');
    }
    return (float) $value;
}

function post_integer(string $key, string $label, int $minimum = 0): int
{
    $value = filter_var($_POST[$key] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimum]]);
    if ($value === false) {
        throw new InvalidArgumentException($label . ' must be a whole number greater than or equal to ' . $minimum . '.');
    }
    return $value;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_admin(): void
{
    if (!is_admin()) {
        http_response_code(403);
        exit('Administrator access is required.');
    }
}

function money($amount): string
{
    return '$' . number_format((float) $amount, 2);
}
