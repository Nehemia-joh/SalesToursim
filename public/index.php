<?php
/**
 * index.php - application front controller for the Michael Tourism Sales Manager.
 *
 * Clean URLs are handled by public/router.php and public/.htaccess:
 *   /clients, /clients/new, /clients/5/edit, /quotations/5/build, /proposal/<token>
 *
 * The legacy query-string URLs still work for bookmarks and old links:
 *   index.php?page=bookings&action=form&id=3
 *
 * Run locally:
 *   php -S localhost:8000 -t <path-to-WebVersion>/public <path-to-WebVersion>/public/router.php
 */

declare(strict_types=1);

define('APP_PATH', dirname(__DIR__) . '/app');

require_once APP_PATH . '/Env.php';
require_once APP_PATH . '/DB.php';
require_once APP_PATH . '/Auth.php';
require_once APP_PATH . '/helpers.php';
require_once APP_PATH . '/drafts.php';
require_once APP_PATH . '/modules.php';
require_once APP_PATH . '/controllers/crud.php';
require_once APP_PATH . '/controllers/drafts.php';
require_once APP_PATH . '/controllers/dashboard.php';
require_once APP_PATH . '/controllers/reports.php';
require_once APP_PATH . '/controllers/settings.php';
require_once APP_PATH . '/controllers/users.php';
require_once APP_PATH . '/controllers/quotes.php';

/* Use the timezone configured on the server (important for "today" and date math). */
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Africa/Dar_es_Salaam'));

/** render a view inside the layout */
function view(string $name, array $data = [], bool $bare = false): void
{
    global $page;
    $__file = dirname(__DIR__) . '/app/views/' . $name . '.php';
    if (!is_file($__file)) { http_response_code(500); exit("View {$name} not found"); }
    extract($data, EXTR_SKIP);
    $user      = Auth::user();
    $flashes   = take_flashes();
    $base_cur  = base_currency();
    ob_start();
    require $__file;
    $content = ob_get_clean();
    require APP_PATH . '/views/layout.php';
}

$page = str_replace('-', '_', strtolower((string)($_GET['page'] ?? 'dashboard')));
$page = preg_replace('/[^a-z_]/', '', $page) ?: 'dashboard';

/* Reject unknown clean URLs instead of treating every slug as a module. */
$knownPages = [
    'dashboard', 'reports', 'help', 'settings', 'users',
    'login', 'logout', 'proposal', 'proposal_respond', '__not_found',
    'add_list_item', 'delete_list_item', 'save_fx', 'save_company',
    'save_user', 'delete_user', 'draft_save', 'draft_discard',
    'quote_builder', 'quote_builder_save', 'quote_share', 'quote_reset_link',
];
if (!in_array($page, $knownPages, true) && !module_config($page)) {
    $page = '__not_found';
}

/* ---------- routes that work without login ---------- */
if ($page === '__not_found') {
    http_response_code(404);
    view('not_found', ['title' => 'Page not found'], true);
    exit;
}

if ($page === 'login') {
    $err = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check($_POST);
        $err = Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($err === null) {
            $draft = draft_latest(Auth::id());
            if ($draft) redirect_path(draft_resume_url($draft));
            redirect('page=dashboard');
        }
    } else {
        // Already signed in? Do not show the login form again.
        if (Auth::user()) {
            $draft = draft_latest(Auth::id());
            if ($draft) redirect_path(draft_resume_url($draft));
            redirect('page=dashboard');
        }
    }

    view('login', ['err' => $err], true);
    exit;
}
if ($page === 'logout') {
    Auth::logout();
    redirect('page=login');
}

/* Public client-facing proposal (no login) */
if ($page === 'proposal') {
    quote_public((string)($_GET['token'] ?? ''));
    exit;
}
if ($page === 'proposal_respond') {
    quote_respond();
    exit;
}

/* ---------- everything below requires a session ---------- */
Auth::start();
Auth::check();

switch ($page) {
    case 'dashboard': dashboard_index();        break;
    case 'reports':   reports_index();          break;
    case 'help':      view('help');             break;
    case 'settings':  settings_index();         break;
    case 'users':     users_index();            break;

    case 'add_list_item':     settings_add_list_item();   break;
    case 'save_fx':           settings_save_fx();    break;
    case 'save_company':      settings_save_company(); break;
    case 'delete_list_item':  settings_delete_list_item(); break;
    case 'save_user':         users_save();          break;
    case 'delete_user':       users_delete();        break;

    case 'draft_save':        draft_save_endpoint();    break;
    case 'draft_discard':     draft_discard_endpoint(); break;

    case 'quote_builder':     quote_builder((int)($_GET['id'] ?? 0));   break;
    case 'quote_builder_save': quote_builder_save((int)($_POST['id'] ?? 0)); break;
    case 'quote_share':       quote_share((int)($_POST['id'] ?? 0));    break;
    case 'quote_reset_link':  quote_reset_link((int)($_POST['id'] ?? 0)); break;

    default:
        $m = module_config($page);
        if ($m) {
            $action = $_GET['action'] ?? 'list';
            $id     = (int)($_GET['id'] ?? 0);
            if ($action === 'form')           crud_form($page, $id);
            elseif ($action === 'save')       crud_save($page);
            elseif ($action === 'delete')     crud_delete($page, $id);
            else                              crud_index($page);
        } else {
            http_response_code(404);
            flash('err', 'Page not found: ' . e($page));
            redirect('page=dashboard');
        }
}
