<?php
/** users.php - user management (admin only). */

function users_index(): void
{
    Auth::admin();
    $rows = DB::all('SELECT id, name, email, role, active, created_at FROM users ORDER BY role, name');
    $editId = (int)($_GET['edit'] ?? 0);
    $u = $editId > 0 ? DB::one('SELECT id, name, email, role, active FROM users WHERE id=?', [$editId]) : null;
    view('users', compact('rows', 'u'));
}

function users_save(): void
{
    Auth::admin();
    csrf_check($_POST);
    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $role  = in_array($_POST['role'] ?? '', ['admin', 'manager', 'sales'], true) ? $_POST['role'] : 'sales';
    $active = isset($_POST['active']) ? 1 : 0;
    $pass  = (string)($_POST['password'] ?? '');

    if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        flash('err', 'Name and a valid email are required.');
        redirect('page=users');
    }

    if ($id > 0) {
        DB::run('UPDATE users SET name=?, email=?, role=?, active=? WHERE id=?',
                [$name, $email, $role, $active, $id]);
        if ($pass !== '') Auth::setPassword($id, $pass);
        flash('ok', 'User updated.');
    } else {
        if (DB::one('SELECT id FROM users WHERE email=?', [$email])) {
            flash('err', 'That email is already registered.');
            redirect('page=users');
        }
        $salt = bin2hex(random_bytes(16));
        DB::run('INSERT INTO users (name,email,salt,password_hash,role,active) VALUES (?,?,?,?,?,?)',
                [$name, $email, $salt, hash('sha256', $salt . ($pass !== '' ? $pass : 'changeme99')), $role, $active]);
        flash('ok', 'User added' . ($pass === '' ? ' with temporary password "changeme99" - change it now.' : '.'));
    }
    redirect('page=users');
}

function users_delete(): void
{
    Auth::admin();
    csrf_check($_POST);
    $id = (int)($_POST['id'] ?? 0);
    if ($id === Auth::id()) {
        flash('err', 'You cannot delete your own account.');
    } elseif ($id > 0) {
        DB::run('UPDATE users SET active=0 WHERE id=?', [$id]);
        flash('ok', 'User deactivated (records keep their history).');
    }
    redirect('page=users');
}
