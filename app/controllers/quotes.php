<?php
/** quotes.php - quote builder, public proposal, view tracking and client response. */

function quote_load(int $id): ?array
{
    return DB::one(
        "SELECT q.*,
                c.id AS client_id, c.name AS client_name, c.email AS client_email,
                c.phone AS client_phone, c.country AS client_country,
                c.contact_person AS client_contact,
                i.tour AS inquiry_tour, i.received_date, i.travel_date,
                u.name AS prepared_by_name
         FROM quotations q
         LEFT JOIN inquiries i ON i.id = q.inquiry_id
         LEFT JOIN bookings  b ON b.id = q.booking_id
         LEFT JOIN clients   c ON c.id = COALESCE(i.client_id, b.client_id)
         LEFT JOIN users     u ON u.id = q.prepared_by
         WHERE q.id = ?",
        [$id]
    );
}

function quote_load_by_token(string $token): ?array
{
    if ($token === '') return null;
    return DB::one(
        "SELECT q.*,
                c.id AS client_id, c.name AS client_name, c.email AS client_email,
                c.phone AS client_phone, c.country AS client_country,
                c.contact_person AS client_contact,
                i.tour AS inquiry_tour, i.received_date, i.travel_date,
                u.name AS prepared_by_name
         FROM quotations q
         LEFT JOIN inquiries i ON i.id = q.inquiry_id
         LEFT JOIN bookings  b ON b.id = q.booking_id
         LEFT JOIN clients   c ON c.id = COALESCE(i.client_id, b.client_id)
         LEFT JOIN users     u ON u.id = q.prepared_by
         WHERE q.public_token = ? AND q.public_enabled = 1
         LIMIT 1",
        [$token]
    );
}

function quote_builder(int $id): void
{
    Auth::check();
    $q = quote_load($id);
    if (!$q) {
        flash('err', 'Quotation not found.');
        redirect('page=quotations');
    }

    $days      = DB::all('SELECT * FROM quote_days  WHERE quotation_id = ? ORDER BY sort, day_no, id', [$id]);
    $items     = DB::all('SELECT * FROM quote_items WHERE quotation_id = ? ORDER BY sort, id', [$id]);
    $viewCount = (int)DB::val('SELECT COUNT(*) FROM quote_views WHERE quotation_id = ?', [$id]);
    $lastView  = DB::val('SELECT MAX(viewed_at) FROM quote_views WHERE quotation_id = ?', [$id]);
    $publicUrl = !empty($q['public_token'])
        ? absolute_url('page=proposal&token=' . urlencode((string)$q['public_token']))
        : '';
    $contentBlocks = DB::all("SELECT id, title, category, body FROM content_library WHERE active = 1 ORDER BY category, title");
    $messages      = DB::all("SELECT id, name, channel, subject, body FROM message_templates WHERE active = 1 ORDER BY channel, name");

    view('quote_builder', [
        'title'         => 'Quote Builder — ' . code_of('QT', $id),
        'q'             => $q,
        'days'          => $days,
        'items'         => $items,
        'viewCount'     => $viewCount,
        'lastView'      => $lastView,
        'publicUrl'     => $publicUrl,
        'contentBlocks' => $contentBlocks,
        'messages'      => $messages,
    ]);
}

function quote_builder_save(int $id): void
{
    Auth::check();
    csrf_check($_POST);

    $q = quote_load($id);
    if (!$q) {
        flash('err', 'Quotation not found.');
        redirect('page=quotations');
    }

    $tour        = trim((string)($_POST['tour'] ?? ''));
    $pax         = (int)($_POST['pax'] ?? 0);
    $currency    = strtoupper(trim((string)($_POST['currency'] ?? base_currency())));
    $status      = trim((string)($_POST['status'] ?? 'Draft')) ?: 'Draft';
    $validUntil  = trim((string)($_POST['valid_until'] ?? '')) ?: null;
    $quoteDate   = trim((string)($_POST['quote_date'] ?? '')) ?: date('Y-m-d');
    $version     = max(1, (int)($_POST['version'] ?? 1));
    $coverNote   = trim((string)($_POST['cover_note'] ?? ''));
    $inclusions  = trim((string)($_POST['inclusions'] ?? ''));
    $exclusions  = trim((string)($_POST['exclusions'] ?? ''));
    $terms       = trim((string)($_POST['terms'] ?? ''));
    $publicOn    = isset($_POST['public_enabled']) ? 1 : 0;
    $amount      = round((float)str_replace([',', ' '], '', (string)($_POST['amount'] ?? '0')), 2);

    if ($tour === '') {
        flash('err', 'Tour / proposal title is required.');
        redirect('page=quote_builder&id=' . $id);
    }

    /* ---------- itinerary days ---------- */
    $dayRows = [];
    $rawDays = $_POST['days'] ?? [];
    if (is_array($rawDays)) {
        foreach ($rawDays as $i => $d) {
            if (!is_array($d)) continue;
            $row = [
                'day_no'        => (int)($d['day_no'] ?? 0) ?: ($i + 1),
                'title'         => trim((string)($d['title'] ?? '')),
                'description'   => trim((string)($d['description'] ?? '')),
                'accommodation' => trim((string)($d['accommodation'] ?? '')),
                'meals'         => trim((string)($d['meals'] ?? '')),
                'notes'         => trim((string)($d['notes'] ?? '')),
                'sort'          => $i,
            ];
            if ($row['title'] === '' && $row['description'] === '' && $row['accommodation'] === ''
                && $row['meals'] === '' && $row['notes'] === '') continue;
            $dayRows[] = $row;
        }
    }

    /* ---------- priced items ---------- */
    $itemRows  = [];
    $baseTotal = 0.0;
    $rawItems  = $_POST['items'] ?? [];
    if (is_array($rawItems)) {
        foreach ($rawItems as $i => $it) {
            if (!is_array($it)) continue;
            $category  = trim((string)($it['category'] ?? ''));
            $desc      = trim((string)($it['description'] ?? ''));
            $qty       = (float)($it['qty'] ?? 1);
            $unit      = trim((string)($it['unit'] ?? ''));
            $unitPrice = (float)str_replace([',', ' '], '', (string)($it['unit_price'] ?? '0'));
            $optional  = !empty($it['optional']) ? 1 : 0;
            if ($qty <= 0) $qty = 1;
            $total = round($qty * $unitPrice, 2);

            if ($category === '' && $desc === '' && $unitPrice == 0.0) continue;

            $itemRows[] = [
                'category'    => $category,
                'description' => $desc,
                'qty'         => $qty,
                'unit'        => $unit,
                'unit_price'  => round($unitPrice, 2),
                'total'       => $total,
                'optional'    => $optional,
                'sort'        => $i,
            ];
            if (!$optional) $baseTotal += $total;
        }
    }

    /* When priced items exist, the base total becomes the quote amount. */
    if ($itemRows) $amount = round($baseTotal, 2);

    $pdo = DB::pdo();
    try {
        $pdo->beginTransaction();

        DB::run(
            'UPDATE quotations
             SET quote_date = ?, valid_until = ?, tour = ?, pax = ?, amount = ?, currency = ?,
                 status = ?, version = ?, cover_note = ?, inclusions = ?, exclusions = ?,
                 terms = ?, public_enabled = ?
             WHERE id = ?',
            [
                $quoteDate, $validUntil, $tour, $pax > 0 ? $pax : null, $amount, $currency,
                $status, $version, $coverNote ?: null, $inclusions ?: null, $exclusions ?: null,
                $terms ?: null, $publicOn, $id,
            ]
        );

        DB::run('DELETE FROM quote_days WHERE quotation_id = ?', [$id]);
        foreach ($dayRows as $r) {
            DB::run(
                'INSERT INTO quote_days (quotation_id, day_no, title, description, accommodation, meals, notes, sort)
                 VALUES (?,?,?,?,?,?,?,?)',
                [$id, $r['day_no'], $r['title'] ?: null, $r['description'] ?: null,
                 $r['accommodation'] ?: null, $r['meals'] ?: null, $r['notes'] ?: null, $r['sort']]
            );
        }

        DB::run('DELETE FROM quote_items WHERE quotation_id = ?', [$id]);
        foreach ($itemRows as $r) {
            DB::run(
                'INSERT INTO quote_items (quotation_id, category, description, qty, unit, unit_price, total, optional, sort)
                 VALUES (?,?,?,?,?,?,?,?,?)',
                [$id, $r['category'] ?: null, $r['description'] ?: null, $r['qty'], $r['unit'] ?: null,
                 $r['unit_price'], $r['total'], $r['optional'], $r['sort']]
            );
        }

        $pdo->commit();
        flash('ok', 'Proposal saved.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Quote builder save failed: ' . $e->getMessage());
        flash('err', 'Could not save the proposal. Please try again.');
    }

    redirect('page=quote_builder&id=' . $id);
}

function quote_share(int $id): void
{
    Auth::check();
    csrf_check($_POST);
    $q = quote_load($id);
    if (!$q) {
        flash('err', 'Quotation not found.');
        redirect('page=quotations');
    }

    $token = trim((string)($q['public_token'] ?? ''));
    if ($token === '') {
        do {
            $token = bin2hex(random_bytes(16));
        } while (DB::val('SELECT COUNT(*) FROM quotations WHERE public_token = ?', [$token]) > 0);
    }

    DB::run('UPDATE quotations SET public_token = ?, public_enabled = 1 WHERE id = ?', [$token, $id]);
    flash('ok', 'Public proposal link is ready.');
    redirect('page=quote_builder&id=' . $id);
}

function quote_reset_link(int $id): void
{
    Auth::check();
    csrf_check($_POST);
    $q = quote_load($id);
    if (!$q) {
        flash('err', 'Quotation not found.');
        redirect('page=quotations');
    }

    do {
        $token = bin2hex(random_bytes(16));
    } while (DB::val('SELECT COUNT(*) FROM quotations WHERE public_token = ?', [$token]) > 0);

    DB::run('UPDATE quotations SET public_token = ?, public_enabled = 1 WHERE id = ?', [$token, $id]);
    flash('ok', 'A new public proposal link was generated. The old link no longer works.');
    redirect('page=quote_builder&id=' . $id);
}

function quote_public(string $token): void
{
    $token = trim($token);
    $q     = quote_load_by_token($token);

    if (!$q) {
        http_response_code(404);
        view('proposal_not_found', ['title' => 'Proposal not available'], true);
        return;
    }

    /* Log the public view (GET only). Signed-in staff previews do not inflate the count. */
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        Auth::start();
        if (empty($_SESSION['user_id'])) {
            DB::run(
                'INSERT INTO quote_views (quotation_id, viewed_at, ip, user_agent) VALUES (?, NOW(), ?, ?)',
                [
                    $q['id'],
                    substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
                    substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
                ]
            );
        }
    }

    $days      = DB::all('SELECT * FROM quote_days  WHERE quotation_id = ? ORDER BY sort, day_no, id', [$q['id']]);
    $items     = DB::all('SELECT * FROM quote_items WHERE quotation_id = ? ORDER BY sort, id', [$q['id']]);
    $viewCount = (int)DB::val('SELECT COUNT(*) FROM quote_views WHERE quotation_id = ?', [$q['id']]);

    $company = [
        'name'    => setting('company_name', 'Michael Tourism'),
        'email'   => setting('company_email', ''),
        'phone'   => setting('company_phone', ''),
        'website' => setting('company_website', ''),
        'tagline' => setting('company_tagline', 'Safari sales & travel proposals'),
    ];

    $notice = null;
    if (($_GET['r'] ?? '') === 'accepted') $notice = 'Thank you — your acceptance has been recorded. We will contact you shortly to confirm the booking.';
    if (($_GET['r'] ?? '') === 'changes')  $notice = 'Thank you — your request for changes has been recorded. We will send a revised proposal shortly.';
    if (($_GET['r'] ?? '') === 'declined') $notice = 'Thank you for letting us know. We have recorded your response.';

    view('proposal', [
        'title'     => 'Proposal ' . code_of('QT', (int)$q['id']),
        'q'         => $q,
        'days'      => $days,
        'items'     => $items,
        'viewCount' => $viewCount,
        'company'   => $company,
        'notice'    => $notice,
        'token'     => $token,
    ], true);
}

function quote_respond(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }

    $token    = trim((string)($_POST['token'] ?? ''));
    $response = trim((string)($_POST['response'] ?? ''));
    $comment  = trim((string)($_POST['comment'] ?? ''));
    $q        = quote_load_by_token($token);

    if (!$q) {
        http_response_code(404);
        view('proposal_not_found', ['title' => 'Proposal not available'], true);
        return;
    }

    if (!in_array($response, ['accepted', 'changes', 'declined'], true)) {
        redirect('page=proposal&token=' . urlencode($token));
    }

    if (($q['client_response'] ?? '') === 'Accepted') {
        redirect('page=proposal&token=' . urlencode($token) . '&r=accepted');
    }

    $pdo = DB::pdo();
    try {
        $pdo->beginTransaction();

        if ($response === 'accepted') {
            DB::run(
                "UPDATE quotations
                 SET status = 'Accepted', client_response = 'Accepted', client_comment = ?, responded_at = NOW()
                 WHERE id = ?",
                [$comment ?: null, $q['id']]
            );

            if (empty($q['booking_id'])) {
                DB::run(
                    "INSERT INTO bookings
                     (inquiry_id, client_id, tour, pax, status, booking_date, salesperson,
                      booking_value, currency, notes)
                     VALUES (?,?,?,?, 'Tentative', CURDATE(), ?, ?, ?, ?)",
                    [
                        $q['inquiry_id'] ?: null,
                        $q['client_id'] ?: null,
                        $q['tour'] ?: null,
                        $q['pax'] ?: null,
                        $q['prepared_by'] ?: null,
                        $q['amount'] ?: null,
                        $q['currency'] ?: base_currency(),
                        'Created automatically from accepted proposal ' . code_of('QT', (int)$q['id']) . '.',
                    ]
                );
                $bookingId = (int)DB::pdo()->lastInsertId();
                DB::run('UPDATE quotations SET booking_id = ? WHERE id = ?', [$bookingId, $q['id']]);
            }

            if (!empty($q['inquiry_id'])) {
                DB::run(
                    "INSERT INTO follow_ups
                     (inquiry_id, action, due_date, priority, assigned_to, status, notes)
                     VALUES (?,?,DATE_ADD(CURDATE(), INTERVAL 2 DAY),'High',?, 'Pending', ?)",
                    [
                        $q['inquiry_id'],
                        'Confirm booking and collect deposit',
                        $q['prepared_by'] ?: null,
                        'Auto-created from accepted proposal ' . code_of('QT', (int)$q['id']) . '.',
                    ]
                );
            }

            $pdo->commit();
            redirect('page=proposal&token=' . urlencode($token) . '&r=accepted');
        }

        if ($response === 'changes') {
            DB::run(
                "UPDATE quotations
                 SET status = 'Revised', client_response = 'Changes requested',
                     client_comment = ?, responded_at = NOW()
                 WHERE id = ?",
                [$comment ?: null, $q['id']]
            );

            if (!empty($q['inquiry_id'])) {
                DB::run(
                    "INSERT INTO follow_ups
                     (inquiry_id, action, due_date, priority, assigned_to, status, notes)
                     VALUES (?,?,DATE_ADD(CURDATE(), INTERVAL 1 DAY),'High',?, 'Pending', ?)",
                    [
                        $q['inquiry_id'],
                        'Revise proposal and reply to client',
                        $q['prepared_by'] ?: null,
                        'Client requested changes on ' . code_of('QT', (int)$q['id']) . '.',
                    ]
                );
            }

            $pdo->commit();
            redirect('page=proposal&token=' . urlencode($token) . '&r=changes');
        }

        DB::run(
            "UPDATE quotations
             SET status = 'Rejected', client_response = 'Declined',
                 client_comment = ?, responded_at = NOW()
             WHERE id = ?",
            [$comment ?: null, $q['id']]
        );

        if (!empty($q['inquiry_id'])) {
            DB::run(
                "INSERT INTO follow_ups
                 (inquiry_id, action, due_date, priority, assigned_to, status, notes)
                 VALUES (?,?,DATE_ADD(CURDATE(), INTERVAL 7 DAY),'Medium',?, 'Pending', ?)",
                [
                    $q['inquiry_id'],
                    'Follow up on declined proposal',
                    $q['prepared_by'] ?: null,
                    'Client declined ' . code_of('QT', (int)$q['id']) . '.',
                ]
            );
        }

        $pdo->commit();
        redirect('page=proposal&token=' . urlencode($token) . '&r=declined');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Proposal response failed: ' . $e->getMessage());
        http_response_code(500);
        exit('Could not save your response. Please contact us directly.');
    }
}
