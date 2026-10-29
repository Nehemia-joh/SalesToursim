<?php
/**
 * router.php - clean URL front controller.
 *
 * Apache/LiteSpeed rewrite every non-file request here through .htaccess.
 * For PHP's built-in server, use:
 *
 *   php -S localhost:8000 -t public public/router.php
 *
 * The legacy index.php?page=... URLs keep working.
 */

declare(strict_types=1);

$requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
$requestPath = (string)(parse_url($requestUri, PHP_URL_PATH) ?: '/');

/* When used as a PHP built-in server router, let real files be served as-is. */
$root = realpath(__DIR__) ?: __DIR__;
$real = realpath(__DIR__ . $requestPath);
if ($real !== false && is_file($real) && str_starts_with($real, $root . DIRECTORY_SEPARATOR)) {
    return false;
}

/* Strip the directory where the app is mounted (works at root or in a subfolder). */
$script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/router.php'));
$base   = rtrim(str_replace('\\', '/', dirname($script)), '/');
if ($base === '.' || $base === '/') $base = '';

$path = rawurldecode($requestPath);
if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}
$path = '/' . trim($path, '/');
if ($path === '//') $path = '/';
if (str_contains($path, '..')) {
    $path = '/__not_found';
}

/** Map a clean path to the parameters index.php already understands. */
function mt_route_match(string $path): ?array
{
    $segments = array_values(array_filter(explode('/', trim($path, '/')), static fn($s) => $s !== ''));
    $n = count($segments);
    if ($n === 0) return ['page' => 'dashboard'];

    $s0 = strtolower($segments[0]);
    $s1 = $segments[1] ?? '';
    $s2 = $segments[2] ?? '';
    $s3 = $segments[3] ?? '';

    if ($s0 === 'login')   return ['page' => 'login'];
    if ($s0 === 'logout')  return ['page' => 'logout'];
    if ($s0 === 'dashboard') return ['page' => 'dashboard'];

    if ($s0 === 'reports' || $s0 === 'help' || $s0 === 'settings' || $s0 === 'users') {
        if ($s0 === 'users') {
            if ($s1 === 'save')   return ['page' => 'save_user'];
            if ($s1 === 'delete') return ['page' => 'delete_user'];
            if ($n >= 3 && ctype_digit($s1) && $s2 === 'edit') {
                return ['page' => 'users', 'edit' => (int)$s1];
            }
        }
        if ($s0 === 'settings') {
            if ($s1 === 'lists' && $s2 === 'add')    return ['page' => 'add_list_item'];
            if ($s1 === 'lists' && $s2 === 'delete') return ['page' => 'delete_list_item'];
            if ($s1 === 'fx')      return ['page' => 'save_fx'];
            if ($s1 === 'company') return ['page' => 'save_company'];
        }
        return ['page' => $s0];
    }

    if ($s0 === 'drafts') {
        if ($s1 === 'save')    return ['page' => 'draft_save'];
        if ($s1 === 'discard') return ['page' => 'draft_discard'];
    }

    if ($s0 === 'proposal') {
        if ($n === 1) return ['page' => 'proposal'];
        $token = $s1;
        if ($n >= 3 && $s2 === 'respond') {
            return ['page' => 'proposal_respond', 'token' => $token];
        }
        return ['page' => 'proposal', 'token' => $token];
    }

    if ($s0 === 'quotations') {
        if ($n >= 4 && ctype_digit($s1) && $s2 === 'build' && $s3 === 'save') {
            return ['page' => 'quote_builder_save', 'id' => (int)$s1];
        }
        if ($n >= 3 && ctype_digit($s1) && $s2 === 'build') {
            return ['page' => 'quote_builder', 'id' => (int)$s1];
        }
        if ($n >= 3 && ctype_digit($s1) && $s2 === 'share') {
            return ['page' => 'quote_share', 'id' => (int)$s1];
        }
        if ($n >= 3 && ctype_digit($s1) && $s2 === 'reset-link') {
            return ['page' => 'quote_reset_link', 'id' => (int)$s1];
        }
        if ($s1 === 'build' && $s2 === 'save') return ['page' => 'quote_builder_save'];
        if ($s1 === 'share')                    return ['page' => 'quote_share'];
        if ($s1 === 'reset-link')               return ['page' => 'quote_reset_link'];
        if ($s1 === 'new')                      return ['page' => 'quotations', 'action' => 'form'];
        if ($s1 === 'save')                     return ['page' => 'quotations', 'action' => 'save'];
        if ($n >= 3 && ctype_digit($s1) && $s2 === 'edit') {
            return ['page' => 'quotations', 'action' => 'form', 'id' => (int)$s1];
        }
        if ($n >= 3 && ctype_digit($s1) && $s2 === 'delete') {
            return ['page' => 'quotations', 'action' => 'delete', 'id' => (int)$s1];
        }
        return ['page' => 'quotations'];
    }

    /* Generic module routes: /content-library, /content-library/new, /clients/5/edit */
    $page = str_replace('-', '_', $s0);
    if ($n === 1) return ['page' => $page];
    if ($s1 === 'new')  return ['page' => $page, 'action' => 'form'];
    if ($s1 === 'save') return ['page' => $page, 'action' => 'save'];
    if ($n >= 3 && ctype_digit($s1) && $s2 === 'edit') {
        return ['page' => $page, 'action' => 'form', 'id' => (int)$s1];
    }
    if ($n >= 3 && ctype_digit($s1) && $s2 === 'delete') {
        return ['page' => $page, 'action' => 'delete', 'id' => (int)$s1];
    }
    if (ctype_digit($s1)) return ['page' => $page, 'id' => (int)$s1];

    return null;
}

$route = mt_route_match($path);
if ($route === null) {
    $route = ['page' => '__not_found'];
}

/* The path wins over any old ?page= parameter; other query values are preserved. */
$_GET = array_merge($_GET, $route);

require __DIR__ . '/index.php';
