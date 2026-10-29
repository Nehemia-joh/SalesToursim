<?php
/**
 * drafts.php - server-side partial saves.
 *
 * Unsaved form data is stored per user and per form. When the user signs in
 * again, the latest draft is offered back so work can continue where it stopped.
 */

function draft_key_crud(string $module, int $id): string
{
    return 'crud:' . $module . ':' . $id;
}

function draft_route_crud(string $module, int $id): string
{
    return route_path('page=' . $module . '&action=form' . ($id > 0 ? '&id=' . $id : ''));
}

function draft_label_crud(array $m, int $id): string
{
    return $id > 0
        ? $m['title'] . ' ' . code_of((string)($m['prefix'] ?? ''), $id)
        : 'New ' . strtolower((string)($m['title'] ?? 'record'));
}

function draft_key_quote(int $id): string
{
    return 'quote:' . $id;
}

function draft_route_quote(int $id): string
{
    return route_path('page=quote_builder&id=' . $id);
}

/** Keep draft routes local and safe to redirect to later. */
function draft_clean_route(string $route): string
{
    $route = trim(str_replace(["\r", "\n"], '', $route));
    if ($route === '' || $route[0] !== '/' || str_contains($route, '..')) return '';
    return substr($route, 0, 255);
}

/** Decode the URL-encoded form payload into a PHP array. */
function draft_payload_array(string $payload): array
{
    if ($payload === '') return [];
    parse_str($payload, $data);
    return is_array($data) ? $data : [];
}

function draft_get(int $userId, string $key): ?array
{
    if ($userId <= 0 || $key === '') return null;
    try {
        return DB::one(
            'SELECT id, user_id, draft_key, route, label, payload, updated_at
             FROM partial_saves WHERE user_id = ? AND draft_key = ? LIMIT 1',
            [$userId, $key]
        );
    } catch (Throwable $e) {
        error_log('Partial save lookup failed: ' . $e->getMessage());
        return null;
    }
}

function draft_latest(int $userId): ?array
{
    if ($userId <= 0) return null;
    try {
        return DB::one(
            'SELECT id, user_id, draft_key, route, label, payload, updated_at
             FROM partial_saves WHERE user_id = ? ORDER BY updated_at DESC, id DESC LIMIT 1',
            [$userId]
        );
    } catch (Throwable $e) {
        error_log('Partial save lookup failed: ' . $e->getMessage());
        return null;
    }
}

function draft_save_row(int $userId, string $key, string $route, string $label, string $payload): bool
{
    $key    = substr(trim($key), 0, 190);
    $route  = draft_clean_route($route);
    $label  = substr(trim($label), 0, 190);
    if ($userId <= 0 || $key === '' || $route === '' || $payload === '') return false;
    if (strlen($payload) > 1048576) return false; // 1 MB safety limit

    try {
        DB::run(
            'INSERT INTO partial_saves (user_id, draft_key, route, label, payload)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE route = VALUES(route), label = VALUES(label),
                                     payload = VALUES(payload), updated_at = NOW()',
            [$userId, $key, $route, $label, $payload]
        );

        // Housekeeping: keep the table small without touching active drafts.
        DB::run(
            'DELETE FROM partial_saves WHERE user_id = ? AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)',
            [$userId]
        );
        return true;
    } catch (Throwable $e) {
        error_log('Partial save failed: ' . $e->getMessage());
        return false;
    }
}

/** URL that reopens the draft, including the app's base path. */
function draft_resume_url(array $draft): string
{
    $route = draft_clean_route((string)($draft['route'] ?? ''));
    if ($route === '') return url('page=dashboard');
    $sep = str_contains($route, '?') ? '&' : '?';
    return base_path() . $route . $sep . 'resume=1';
}

function draft_clear(int $userId, string $key): void
{
    if ($userId <= 0 || $key === '') return;
    try {
        DB::run('DELETE FROM partial_saves WHERE user_id = ? AND draft_key = ?', [$userId, $key]);
    } catch (Throwable $e) {
        error_log('Partial save clear failed: ' . $e->getMessage());
    }
}
