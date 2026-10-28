<?php
/** crud.php - generic list/form/save/delete engine driven by modules.php. */

/** options for select-type fields, cached per request */
function crud_options(string $type): array
{
    static $cache = [];
    if (isset($cache[$type])) return $cache[$type];

    $opts = [];
    if (strpos($type, 'list:') === 0) {
        $list = substr($type, 5);
        foreach (DB::all('SELECT value FROM list_items WHERE list_name = ? ORDER BY sort, value', [$list]) as $r) {
            $opts[] = $r['value'];
        }
    } elseif ($type === 'users') {
        foreach (DB::all('SELECT id, name FROM users WHERE active = 1 ORDER BY name') as $r) {
            $opts[$r['id']] = $r['name'];
        }
    } elseif (strpos($type, 'ref:') === 0) {
        switch (substr($type, 4)) {
            case 'clients':
                $opts = [null => '— new client / not set —'];
                foreach (DB::all('SELECT id, name FROM clients ORDER BY name') as $r)
                    $opts[$r['id']] = $r['name'];
                break;
            case 'inquiries':
                $opts = [null => '— select inquiry —'];
                foreach (DB::all("SELECT i.id, CONCAT('INQ-',LPAD(i.id,3,'0'),' — ',IFNULL(c.name,'(new client)'),' — ',i.tour) AS label
                                 FROM inquiries i LEFT JOIN clients c ON c.id=i.client_id ORDER BY i.id DESC") as $r)
                    $opts[$r['id']] = $r['label'];
                break;
            case 'bookings':
                $opts = [null => '— select booking —'];
                foreach (DB::all("SELECT b.id, CONCAT('BK-',LPAD(b.id,3,'0'),' — ',IFNULL(c.name,''),' — ',b.tour) AS label
                                 FROM bookings b LEFT JOIN clients c ON c.id=b.client_id ORDER BY b.id DESC") as $r)
                    $opts[$r['id']] = $r['label'];
                break;
        }
    } elseif ($type === 'currency') {
        foreach (DB::all('SELECT currency FROM fx_rates ORDER BY currency') as $r)
            $opts[$r['currency']] = $r['currency'];
        // put the base currency first, so new records default to the reporting currency
        $base = base_currency();
        if (isset($opts[$base])) {
            $opts = [$base => $base] + $opts;
        }
    }
    return $cache[$type] = $opts;
}

/** run the module list query; wrap it so search ORDER BY and search both work cleanly */
function crud_rows(array $m, string $q): array
{
    $q    = trim((string)$q);
    $sql  = 'SELECT base.* FROM (' . $m['select'] . ') base';
    $params = [];

    if (empty($m['ordered'])) {
        if ($q !== '') {
            $sql .= ' WHERE CONCAT_WS(\' \') LIKE ?';
            $params[] = '%' . $q . '%';
        }
        return crud_postprocess($m, DB::all($sql, $params));
    }

    $alias = str_replace('t.', 'base.', $m['ordered']);
    if ($q !== '') {
        $keys = [];
        foreach ($m['cols'] as [$k, , $t]) {
            // skip computed columns that are not in the SELECT
            if (in_array($t, ['pct', 'percent'], true) || $k === 'variance') continue;
            $keys[] = "`$k`";
        }
        $sql .= ' WHERE CONCAT_WS(\' \', ' . implode(', ', $keys) . ') LIKE ?';
        $params[] = '%' . $q . '%';
    }
    $sql .= ' ORDER BY ' . $alias;
    return crud_postprocess($m, DB::all($sql, $params));
}

/** computed list columns that are easier to build in PHP than SQL */
function crud_postprocess(array $m, array $rows): array
{
    if (empty($m['postprocess_row'])) return $rows;
    foreach ($rows as &$r) {
        $target = (float)($r['target_revenue'] ?? 0);
        $actual = (float)($r['actual'] ?? 0);
        $r['variance']    = $actual - $target;
        $r['achievement'] = $target > 0 ? round(100 * $actual / $target, 1) : null;
    }
    unset($r);
    return $rows;
}

/** module-specific preparation of $_POST before the generic save runs */
function crud_prepare_post(string $module): void
{
    if ($module !== 'inquiries') return;

    $clientId = (int)($_POST['client_id'] ?? 0);
    $newName  = trim((string)($_POST['new_client_name'] ?? ''));
    if ($clientId > 0 || $newName === '') return;

    // Re-use an existing client with the same name (case-insensitive), else create one.
    $existing = DB::val('SELECT id FROM clients WHERE LOWER(name) = LOWER(?) LIMIT 1', [$newName]);
    if ($existing) {
        $_POST['client_id'] = (string)(int)$existing;
        return;
    }

    DB::run(
        'INSERT INTO clients (name, client_type, country, contact_person, email, phone, first_inquiry, owner_id, notes)
         VALUES (?,?,?,?,?,?,?,?,?)',
        [
            $newName,
            ($_POST['client_type'] ?? '') ?: null,
            ($_POST['country'] ?? '') ?: null,
            ($_POST['contact_person'] ?? '') ?: null,
            ($_POST['email'] ?? '') ?: null,
            ($_POST['phone'] ?? '') ?: null,
            ($_POST['received_date'] ?? '') ?: null,
            (int)($_POST['assigned_to'] ?? 0) ?: null,
            'Created automatically from a new inquiry.',
        ]
    );
    $_POST['client_id'] = (string)(int)DB::pdo()->lastInsertId();
}

/** smart defaults pulled from the linked inquiry, so linked records stay consistent */
function crud_apply_defaults(string $module, array &$data): void
{
    $linked = ['quotations', 'bookings', 'followups', 'activities'];
    if (!in_array($module, $linked, true)) return;

    $iid = (int)($data['inquiry_id'] ?? 0);
    if ($iid <= 0) return;
    $inq = DB::one('SELECT * FROM inquiries WHERE id = ?', [$iid]);
    if (!$inq) return;

    if ($module === 'bookings' && empty($data['client_id'])) {
        $data['client_id'] = $inq['client_id'];
    }
    if (in_array($module, ['quotations', 'bookings'], true)) {
        if (empty($data['tour'])) $data['tour'] = $inq['tour'];
        if (empty($data['pax']))  $data['pax']  = $inq['pax'];
        if (empty($data['currency'])) $data['currency'] = $inq['currency'] ?: base_currency();
    }

    $ownerField = [
        'quotations' => 'prepared_by',
        'bookings'   => 'salesperson',
        'followups'  => 'assigned_to',
        'activities' => 'salesperson',
    ][$module] ?? null;
    if ($ownerField && empty($data[$ownerField])) {
        $data[$ownerField] = $inq['assigned_to'];
    }

    // Pull the expected value from the pipeline when no amount was typed.
    if ($module === 'quotations' && empty($data['amount'])) {
        $v = DB::val('SELECT estimated_value FROM opportunities WHERE inquiry_id = ? ORDER BY id DESC LIMIT 1', [$iid]);
        if ($v !== null && $v !== false) $data['amount'] = (float)$v;
    }
    if ($module === 'bookings' && empty($data['booking_value'])) {
        $v = DB::val('SELECT estimated_value FROM opportunities WHERE inquiry_id = ? ORDER BY id DESC LIMIT 1', [$iid]);
        if ($v !== null && $v !== false) $data['booking_value'] = (float)$v;
    }
}

function crud_index(string $module): void
{
    $m = module_config($module);
    if (!$m) { http_response_code(404); exit('Unknown module'); }
    $q    = $_GET['q'] ?? '';
    $rows = crud_rows($m, (string)$q);
    view('crud_list', ['m' => $m, 'module' => $module, 'rows' => $rows, 'q' => $q]);
}

function crud_form(string $module, int $id = 0): void
{
    $m = module_config($module);
    if (!$m) { http_response_code(404); exit('Unknown module'); }

    $row = null;
    if ($id > 0) {
        $row = DB::one('SELECT * FROM ' . $m['table'] . ' WHERE id = ?', [$id]);
        if (!$row) { flash('err', 'Record not found.'); redirect('page=' . $module); }
    }

    // old posted values after a validation bounce
    $old = $_SESSION['form_old'] ?? null;
    unset($_SESSION['form_old']);
    if ($old !== null) $row = $old;

    view('crud_form', ['m' => $m, 'module' => $module, 'row' => $row, 'id' => $id]);
}

/** cast one posted value according to its field type */
function crud_cast(string $type, $raw)
{
    if (is_array($raw)) $raw = implode(', ', $raw);
    $raw = trim((string)$raw);
    if ($raw === '') return null;
    if ($type === 'int')   return (int)$raw;
    if ($type === 'money') return round((float)str_replace([',', ' '], '', $raw), 2);
    if ($type === 'date')  return $raw;
    if ($type === 'currency') return strtoupper($raw);
    return $raw;
}

function crud_save(string $module): void
{
    $m = module_config($module);
    if (!$m) { http_response_code(404); exit('Unknown module'); }
    csrf_check($_POST);
    Auth::check();

    $id    = (int)($_POST['id'] ?? 0);

    // allow a new client to be created inline from the inquiry form
    crud_prepare_post($module);

    $data  = [];
    $reqFields = [];

    foreach ($m['fields'] as $f) {
        [$name, $label, $type] = $f;
        if (($f[4] ?? '') === 'virtual') continue;   // UI-only helper field
        $data[$name] = crud_cast($type, $_POST[$name] ?? '');
        if (($f[3] ?? '') === 'req') $reqFields[$name] = $label;
    }

    // fill linked fields (client, tour, owner, value) from the chosen inquiry
    crud_apply_defaults($module, $data);

    // required validation runs after defaults, so auto-filled values count
    $miss = [];
    foreach ($reqFields as $name => $label) {
        if (($data[$name] ?? null) === null || ($data[$name] ?? '') === '') $miss[] = $label;
    }

    // defaults
    if ($module !== 'clients' && empty($data['currency'])) unset($data['currency']);

    if ($miss) {
        flash('err', 'Required: ' . implode(', ', $miss));
        $_SESSION['form_old'] = $data;
        redirect('page=' . $module . '&action=form' . ($id ? '&id=' . $id : ''));
    }

    $cols = array_keys($data);
    if ($id > 0) {
        $set = implode(', ', array_map(fn($c) => "`$c` = ?", $cols));
        DB::run('UPDATE ' . $m['table'] . " SET {$set} WHERE id = ?",
            array_merge(array_values($data), [$id]));
        flash('ok', $m['title'] . ' item updated.');
    } else {
        $ph = implode(',', array_fill(0, count($cols), '?'));
        $names = implode(', ', array_map(fn($c) => "`$c`", $cols));
        DB::run('INSERT INTO ' . $m['table'] . " ({$names}) VALUES ({$ph})", array_values($data));
        $newId = (int)DB::pdo()->lastInsertId();
        flash('ok', $m['title'] . ' added as ' . code_of($m['prefix'], $newId) . '.');
        $id = $newId;
    }
    redirect('page=' . $module);
}

function crud_delete(string $module, int $id): void
{
    $m = module_config($module);
    if (!$m) { http_response_code(404); exit('Unknown module'); }
    Auth::manager();
    csrf_check(array_merge($_POST, ['_csrf' => $_POST['_csrf'] ?? ($_GET['_csrf'] ?? '')]));
    if ($id > 0) DB::run('DELETE FROM ' . $m['table'] . ' WHERE id = ?', [$id]);
    flash('ok', 'Record deleted.');
    redirect('page=' . $module);
}
