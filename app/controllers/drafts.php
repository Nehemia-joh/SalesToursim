<?php
/** drafts.php controller - AJAX endpoints for partial saves. */

function draft_save_endpoint(): void
{
    Auth::check();
    csrf_check($_POST);

    $ok = draft_save_row(
        Auth::id(),
        (string)($_POST['_draft_key'] ?? ''),
        (string)($_POST['_draft_route'] ?? ''),
        (string)($_POST['_draft_label'] ?? ''),
        (string)($_POST['_draft_payload'] ?? '')
    );

    header('Content-Type: application/json; charset=utf-8');
    http_response_code($ok ? 200 : 422);
    echo json_encode([
        'ok'       => $ok,
        'saved_at' => date('H:i'),
        'message'  => $ok ? 'Partial save stored.' : 'Could not store the partial save.',
    ]);
    exit;
}

function draft_discard_endpoint(): void
{
    Auth::check();
    csrf_check($_POST);

    draft_clear(Auth::id(), (string)($_POST['_draft_key'] ?? ''));

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true]);
    exit;
}
