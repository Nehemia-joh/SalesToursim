<?php
/** settings.php - lists editor, FX rates, company settings. */

function settings_index(): void
{
    Auth::manager();

    $lists = [];
    foreach (DB::all('SELECT id, list_name, sort, value FROM list_items ORDER BY list_name, sort, value') as $r) {
        $lists[$r['list_name']][] = $r;
    }

    $fx = DB::all('SELECT * FROM fx_rates ORDER BY currency');
    $company = DB::all('SELECT * FROM settings ORDER BY k');

    view('settings', compact('lists', 'fx', 'company'));
}

function settings_add_list_item(): void
{
    Auth::manager();
    csrf_check($_POST);
    $name  = trim($_POST['list_name'] ?? '');
    $value = trim($_POST['value'] ?? '');
    if ($name !== '' && $value !== '') {
        $max = (int)DB::val('SELECT COALESCE(MAX(sort),0) FROM list_items WHERE list_name = ?', [$name]);
        DB::run('INSERT INTO list_items (list_name, value, sort) VALUES (?,?,?)', [$name, $value, $max + 10]);
        flash('ok', 'List item added.');
    } else {
        flash('err', 'Both list name and value are required.');
    }
    redirect('page=settings');
}

function settings_save_fx(): void
{
    Auth::manager();
    csrf_check($_POST);
    $curs  = $_POST['currency'] ?? [];
    $rates = $_POST['rate'] ?? [];
    $n = min(count($curs), count($rates));
    for ($i = 0; $i < $n; $i++) {
        $cur  = strtoupper(trim($curs[$i]));
        $rate = str_replace(',', '.', trim($rates[$i]));
        if ($cur === '' || $rate === '') continue;
        if (!is_numeric($rate) || (float)$rate <= 0) {
            flash('err', "Rate for {$cur} must be a positive number.");
            redirect('page=settings');
        }
        DB::run('INSERT INTO fx_rates (currency, rate_to_base) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE rate_to_base = VALUES(rate_to_base)',
                [$cur, (float)$rate]);
    }
    flash('ok', 'FX rates saved.');
    redirect('page=settings');
}

function settings_save_company(): void
{
    Auth::manager();
    csrf_check($_POST);
    $k = trim($_POST['k'] ?? '');
    $v = trim($_POST['v'] ?? '');
    if ($k !== '' && $v !== '') {
        DB::run('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, $v]);
        flash('ok', 'Setting saved.');
    }
    redirect('page=settings');
}

function settings_delete_list_item(): void
{
    Auth::manager();
    $id = (int)($_POST['id'] ?? 0);
    csrf_check($_POST);
    if ($id > 0) DB::run('DELETE FROM list_items WHERE id = ?', [$id]);
    flash('ok', 'List item removed.');
    redirect('page=settings');
}

