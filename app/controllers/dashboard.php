<?php
/** dashboard.php - management dashboard (mirrors the Excel Dashboard sheet). */

function dashboard_index(): void
{
    $today = date('Y-m-d');
    $weekEnd = date('Y-m-d', strtotime('sunday this week'));
    $next7   = date('Y-m-d', strtotime('+7 days'));
    $month = date('Y-m');

    /* ---------- booking balances in the base currency ---------- */
    $balSql = "
        SELECT b.id, b.status, b.payment_due_date,
               b.booking_value * COALESCE(fx.rate_to_base,1) AS value_base,
               COALESCE(pp.received,0)                       AS received,
               b.booking_value * COALESCE(fx.rate_to_base,1) - COALESCE(pp.received,0) AS balance
        FROM bookings b
        LEFT JOIN fx_rates fx ON fx.currency = b.currency
        LEFT JOIN (SELECT p.booking_id, SUM(p.amount * COALESCE(fx2.rate_to_base,1)) received
                   FROM payments p LEFT JOIN fx_rates fx2 ON fx2.currency = p.currency
                   GROUP BY p.booking_id) pp ON pp.booking_id = b.id
        WHERE b.booking_value IS NOT NULL";

    /* ---------- headline KPIs ---------- */
    $kpi = [];
    $kpi['inquiries']          = (int)DB::val('SELECT COUNT(*) FROM inquiries');
    $kpi['open_leads']         = (int)DB::val("SELECT COUNT(*) FROM inquiries WHERE lead_status = 'Open'");
    $kpi['won']                = (int)DB::val("SELECT COUNT(*) FROM inquiries WHERE lead_status = 'Won'");
    $kpi['lost']               = (int)DB::val("SELECT COUNT(*) FROM inquiries WHERE lead_status = 'Lost'");
    $kpi['confirmed_bookings'] = (int)DB::val("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'");
    $kpi['completed_bookings'] = (int)DB::val("SELECT COUNT(*) FROM bookings WHERE status = 'Completed'");
    $kpi['firm_bookings']      = $kpi['confirmed_bookings'] + $kpi['completed_bookings'];
    $kpi['all_bookings']       = (int)DB::val('SELECT COUNT(*) FROM bookings');
    $kpi['lost_opps']          = (int)DB::val("SELECT COUNT(*) FROM opportunities WHERE lead_status = 'Lost'");

    $kpi['booking_value'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.booking_value * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency"
    );
    $kpi['actual_revenue'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status IN ('Confirmed','Completed')"
    );
    $kpi['direct_cost'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.direct_cost * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status IN ('Confirmed','Completed')"
    );
    $kpi['gross_profit'] = $kpi['actual_revenue'] - $kpi['direct_cost'];
    $kpi['margin_pct']   = pct($kpi['gross_profit'], $kpi['actual_revenue']);

    $kpi['payments_received'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(p.amount * COALESCE(fx.rate_to_base,1)),0),0)
         FROM payments p LEFT JOIN fx_rates fx ON fx.currency = p.currency"
    );
    $kpi['outstanding'] = (float)DB::val("SELECT COALESCE(ROUND(SUM(balance),0),0) FROM ($balSql) x");

    $od = DB::one(
        "SELECT COUNT(*) n, COALESCE(ROUND(SUM(balance),0),0) amt
         FROM ($balSql) x
         WHERE status NOT IN ('Tentative','Cancelled')
           AND COALESCE(received,0) = 0
           AND payment_due_date IS NOT NULL AND payment_due_date < ?
           AND balance > 0",
        [$today]
    );
    $kpi['overdue_invoices_n']   = (int)($od['n'] ?? 0);
    $kpi['overdue_invoices_amt'] = (float)($od['amt'] ?? 0);

    /* ---------- pipeline ---------- */
    $kpi['estimated_pipeline'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(o.estimated_value * COALESCE(fx.rate_to_base,1)),0),0)
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency"
    );
    $kpi['weighted_pipeline'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),2),0)
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency"
    );
    $kpi['weighted_open'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),0),0)
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency
         WHERE o.lead_status = 'Open'"
    );
    $kpi['open_pipeline_60'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),0),0)
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency
         WHERE o.expected_close BETWEEN ? AND ?",
        [$today, date('Y-m-d', strtotime('+60 days'))]
    );
    $kpi['open_pipeline'] = (int)DB::val("SELECT COUNT(*) FROM opportunities WHERE lead_status = 'Open'");

    /* ---------- conversion / averages ---------- */
    $kpi['win_rate'] = pct($kpi['won'], $kpi['inquiries']);
    $kpi['avg_booking_value'] = (float)DB::val(
        "SELECT COALESCE(AVG(b.booking_value * COALESCE(fx.rate_to_base,1)),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.booking_value > 0"
    );
    $kpi['avg_days_confirm'] = (float)DB::val(
        "SELECT COALESCE(AVG(DATEDIFF(b.booking_date, i.received_date)),0)
         FROM bookings b LEFT JOIN inquiries i ON i.id = b.inquiry_id
         WHERE i.received_date IS NOT NULL AND b.booking_date IS NOT NULL
           AND DATEDIFF(b.booking_date, i.received_date) >= 0"
    );

    /* ---------- follow-ups ---------- */
    $kpi['overdue_tasks'] = (int)DB::val(
        "SELECT COUNT(*) FROM follow_ups
         WHERE status NOT IN ('Completed','Cancelled') AND due_date < ?",
        [$today]
    );

    /* ---------- payment schedule ---------- */
    $kpi['sched_overdue_n'] = (int)DB::val(
        "SELECT COUNT(*) FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         WHERE b.status <> 'Cancelled' AND COALESCE(s.amount_paid,0) = 0
           AND s.due_date IS NOT NULL AND s.due_date < ?",
        [$today]
    );
    $kpi['sched_overdue_amt'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(s.amount_due * COALESCE(fx.rate_to_base,1)),0),0)
         FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status <> 'Cancelled' AND COALESCE(s.amount_paid,0) = 0
           AND s.due_date IS NOT NULL AND s.due_date < ?",
        [$today]
    );
    $kpi['sched_due7d_n'] = (int)DB::val(
        "SELECT COUNT(*) FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         WHERE b.status <> 'Cancelled' AND COALESCE(s.amount_paid,0) = 0
           AND s.due_date BETWEEN ? AND ?",
        [$today, $next7]
    );
    $kpi['sched_due7d_amt'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(s.amount_due * COALESCE(fx.rate_to_base,1)),0),0)
         FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status <> 'Cancelled' AND COALESCE(s.amount_paid,0) = 0
           AND s.due_date BETWEEN ? AND ?",
        [$today, $next7]
    );
    $kpi['sched_paid_n'] = (int)DB::val(
        "SELECT COUNT(*) FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         WHERE b.status <> 'Cancelled' AND s.amount_due > 0 AND COALESCE(s.amount_paid,0) >= s.amount_due"
    );
    $kpi['sched_total_n'] = (int)DB::val(
        "SELECT COUNT(*) FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         WHERE b.status <> 'Cancelled' AND s.amount_due > 0"
    );
    $kpi['collection_pct'] = pct($kpi['sched_paid_n'], $kpi['sched_total_n']);

    /* ---------- holds / quotes ---------- */
    $kpi['tentative_n'] = (int)DB::val("SELECT COUNT(*) FROM bookings WHERE status = 'Tentative'");
    $kpi['tentative_val'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.booking_value * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status = 'Tentative'"
    );
    $kpi['quotes_expiring'] = (int)DB::val(
        "SELECT COUNT(*) FROM quotations
         WHERE status IN ('Sent','Revised') AND valid_until BETWEEN ? AND ?",
        [$today, $next7]
    );
    $kpi['proposal_views_7d'] = (int)DB::val(
        "SELECT COUNT(*) FROM quote_views WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    $kpi['quotes_opened_7d'] = (int)DB::val(
        "SELECT COUNT(DISTINCT quotation_id) FROM quote_views WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    $kpi['quotes_awaiting'] = (int)DB::val(
        "SELECT COUNT(*) FROM quotations
         WHERE status IN ('Sent','Revised')
           AND (client_response IS NULL OR client_response = '')"
    );

    $kpi['month_revenue'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status IN ('Confirmed','Completed') AND DATE_FORMAT(b.booking_date,'%Y-%m') = ?",
        [$month]
    );

    /* ---------- action center: follow-ups by owner ---------- */
    $owners = DB::all(
        "SELECT u.id, u.name,
                SUM(CASE WHEN f.status NOT IN ('Completed','Cancelled') AND f.due_date < ? THEN 1 ELSE 0 END) AS overdue,
                SUM(CASE WHEN f.status NOT IN ('Completed','Cancelled') AND f.due_date BETWEEN ? AND ? THEN 1 ELSE 0 END) AS due_week,
                SUM(CASE WHEN f.status NOT IN ('Completed','Cancelled') THEN 1 ELSE 0 END) AS open_tasks
         FROM users u
         LEFT JOIN follow_ups f ON f.assigned_to = u.id
         WHERE u.active = 1 AND u.role <> 'admin'
         GROUP BY u.id, u.name
         ORDER BY overdue DESC, open_tasks DESC, u.name",
        [$today, $today, $weekEnd]
    );
    $unassigned = DB::one(
        "SELECT SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END) overdue,
                SUM(CASE WHEN due_date BETWEEN ? AND ? THEN 1 ELSE 0 END) due_week,
                COUNT(*) open_tasks
         FROM follow_ups
         WHERE assigned_to IS NULL AND status NOT IN ('Completed','Cancelled')",
        [$today, $today, $weekEnd]
    );
    if ($unassigned && (int)$unassigned['open_tasks'] > 0) {
        $owners[] = ['id' => 0, 'name' => 'Unassigned', 'overdue' => $unassigned['overdue'],
                     'due_week' => $unassigned['due_week'], 'open_tasks' => $unassigned['open_tasks']];
    }

    $todos = [];
    $todos['overdue_fu'] = DB::all(
        "SELECT f.*, CONCAT('FU-',LPAD(f.id,3,'0')) code, CONCAT('INQ-',LPAD(f.inquiry_id,3,'0')) inq_code,
                c.name AS client_name, u.name AS sp_name,
                GREATEST(0, DATEDIFF(CURDATE(), f.due_date)) days_overdue
         FROM follow_ups f
         LEFT JOIN inquiries i ON i.id = f.inquiry_id
         LEFT JOIN clients   c ON c.id = i.client_id
         LEFT JOIN users     u ON u.id = f.assigned_to
         WHERE f.status NOT IN ('Completed','Cancelled') AND f.due_date < ?
         ORDER BY f.due_date",
        [$today]
    );
    $todos['week_fu'] = DB::all(
        "SELECT f.*, CONCAT('FU-',LPAD(f.id,3,'0')) code, c.name AS client_name, u.name AS sp_name
         FROM follow_ups f
         LEFT JOIN inquiries i ON i.id = f.inquiry_id
         LEFT JOIN clients   c ON c.id = i.client_id
         LEFT JOIN users     u ON u.id = f.assigned_to
         WHERE f.status NOT IN ('Completed','Cancelled') AND f.due_date BETWEEN ? AND ?
         ORDER BY f.due_date",
        [$today, $weekEnd]
    );
    $todos['overdue_sch'] = DB::all(
        "SELECT s.*, CONCAT('SCH-',LPAD(s.id,3,'0')) code, CONCAT('BK-',LPAD(s.booking_id,3,'0')) bk_code,
                c.name AS client_name
         FROM payment_schedule s
         LEFT JOIN bookings b ON b.id = s.booking_id
         LEFT JOIN clients  c ON c.id = b.client_id
         WHERE b.status <> 'Cancelled' AND COALESCE(s.amount_paid,0) = 0
           AND s.due_date IS NOT NULL AND s.due_date < ?
         ORDER BY s.due_date",
        [$today]
    );
    $todos['due_sch'] = DB::all(
        "SELECT s.*, CONCAT('SCH-',LPAD(s.id,3,'0')) code,
                CONCAT('BK-',LPAD(s.booking_id,3,'0')) bk_code, c.name AS client_name
         FROM payment_schedule s
         LEFT JOIN bookings b ON b.id = s.booking_id
         LEFT JOIN clients  c ON c.id = b.client_id
         WHERE b.status <> 'Cancelled' AND COALESCE(s.amount_paid,0) = 0
           AND s.due_date BETWEEN ? AND ?
         ORDER BY s.due_date",
        [$today, $next7]
    );
    $todos['tentative'] = DB::all(
        "SELECT CONCAT('BK-',LPAD(b.id,3,'0')) bk_code, b.tour, c.name AS client_name,
                b.booking_value, b.currency, b.salesperson, u.name AS sp_name
         FROM bookings b LEFT JOIN clients c ON c.id = b.client_id LEFT JOIN users u ON u.id = b.salesperson
         WHERE b.status = 'Tentative' ORDER BY b.booking_value DESC"
    );
    $todos['expiring'] = DB::all(
        "SELECT CONCAT('QT-',LPAD(q.id,3,'0')) qt_code, q.tour, c.name AS client_name,
                q.valid_until, q.amount, q.currency, u.name AS sp_name
         FROM quotations q
         LEFT JOIN inquiries i ON i.id = q.inquiry_id
         LEFT JOIN clients   c ON c.id = i.client_id
         LEFT JOIN users     u ON u.id = q.prepared_by
         WHERE q.status IN ('Sent','Revised') AND q.valid_until BETWEEN ? AND ?
         ORDER BY q.valid_until",
        [$today, $next7]
    );

    /* ---------- running and upcoming tours ---------- */
    $todos['running_tours'] = DB::all(
        "SELECT CONCAT('BK-',LPAD(b.id,3,'0')) bk_code, b.tour, b.arrival, b.departure,
                b.pax, b.status, c.name AS client_name, u.name AS sp_name,
                (SELECT COUNT(*) FROM travelers t WHERE t.booking_id = b.id) AS traveler_count,
                GREATEST(0, DATEDIFF(b.departure, CURDATE())) AS days_left
         FROM bookings b
         LEFT JOIN clients c ON c.id = b.client_id
         LEFT JOIN users   u ON u.id = b.salesperson
         WHERE b.status NOT IN ('Cancelled','Completed')
           AND b.arrival IS NOT NULL AND b.departure IS NOT NULL
           AND b.arrival <= CURDATE() AND b.departure >= CURDATE()
         ORDER BY b.departure, b.id"
    );
    $todos['upcoming_tours'] = DB::all(
        "SELECT CONCAT('BK-',LPAD(b.id,3,'0')) bk_code, b.tour, b.arrival, b.departure,
                b.pax, b.status, c.name AS client_name, u.name AS sp_name,
                (SELECT COUNT(*) FROM travelers t WHERE t.booking_id = b.id) AS traveler_count,
                DATEDIFF(b.arrival, CURDATE()) AS days_to_arrival
         FROM bookings b
         LEFT JOIN clients c ON c.id = b.client_id
         LEFT JOIN users   u ON u.id = b.salesperson
         WHERE b.status NOT IN ('Cancelled','Completed')
           AND b.arrival IS NOT NULL
           AND b.arrival > CURDATE()
           AND b.arrival <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
         ORDER BY b.arrival, b.id"
    );

    /* ---------- funnel in the Excel stage order ---------- */
    $stageList = array_column(
        DB::all("SELECT value FROM list_items WHERE list_name = 'sales_stage' ORDER BY sort, value"),
        'value'
    );
    $stageRows = DB::all(
        "SELECT o.sales_stage, COUNT(*) n,
                COALESCE(ROUND(SUM(o.estimated_value * COALESCE(fx.rate_to_base,1)),0),0) value
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency
         GROUP BY o.sales_stage"
    );
    $byStage = [];
    foreach ($stageRows as $r) $byStage[(string)$r['sales_stage']] = $r;
    $funnel = [];
    foreach ($stageList as $st) {
        $funnel[] = [
            'sales_stage' => $st,
            'n'           => (int)($byStage[$st]['n'] ?? 0),
            'value'       => (float)($byStage[$st]['value'] ?? 0),
        ];
        unset($byStage[$st]);
    }
    foreach ($byStage as $st => $r) {
        $funnel[] = ['sales_stage' => $st, 'n' => (int)$r['n'], 'value' => (float)$r['value']];
    }

    /* ---------- 12-month trend (Excel dashboard) ---------- */
    $MONTHS = [1=>'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $trend = [];
    for ($i = 11; $i >= 0; $i--) {
        $ym = date('Y-m', strtotime("first day of -{$i} months"));
        [$yy, $mm] = explode('-', $ym);
        $rev  = (float)DB::val(
            "SELECT COALESCE(ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0),0)
             FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
             WHERE b.status IN ('Confirmed','Completed') AND DATE_FORMAT(b.booking_date,'%Y-%m') = ?",
            [$ym]
        );
        $targ = (float)DB::val(
            'SELECT COALESCE(SUM(target_revenue),0) FROM sales_targets WHERE year = ? AND month = ?',
            [(int)$yy, (int)$mm]
        );
        $inq = (int)DB::val("SELECT COUNT(*) FROM inquiries WHERE DATE_FORMAT(received_date,'%Y-%m') = ?", [$ym]);
        $bk  = (int)DB::val("SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(booking_date,'%Y-%m') = ?", [$ym]);
        $trend[] = [
            'ym'     => $ym,
            'label'  => $MONTHS[(int)$mm],
            'rev'    => $rev,
            'target' => $targ,
            'inq'    => $inq,
            'bk'     => $bk,
        ];
    }

    /* ---------- leaderboard ---------- */
    $lb = DB::all(
        "SELECT u.id, u.name,
           (SELECT COUNT(*) FROM inquiries i WHERE i.assigned_to = u.id) AS inq_n,
           (SELECT COUNT(*) FROM bookings b WHERE b.salesperson = u.id
              AND b.status IN ('Confirmed','Completed')) AS bk_n,
           (SELECT COALESCE(ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0),0)
              FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
              WHERE b.salesperson = u.id AND b.status IN ('Confirmed','Completed')) AS revenue,
           (SELECT COALESCE(SUM(t.target_revenue),0) FROM sales_targets t WHERE t.salesperson = u.id) AS target,
           (SELECT COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),2),0)
              FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency
              WHERE o.assigned_to = u.id) AS open_pipeline
         FROM users u WHERE u.role <> 'admin' AND u.active = 1"
    );
    foreach ($lb as &$r) $r['achievement'] = pct($r['revenue'], $r['target']);
    unset($r);
    usort($lb, fn($a, $b) => $b['achievement'] <=> $a['achievement']);

    /* ---------- top clients ---------- */
    $topClients = DB::all(
        "SELECT c.name, COUNT(b.id) bk_n,
                COALESCE(ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0),0) revenue
         FROM bookings b
         JOIN clients c ON c.id = b.client_id
         LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status IN ('Confirmed','Completed')
         GROUP BY c.id, c.name
         ORDER BY revenue DESC LIMIT 5"
    );

    view('dashboard', compact('kpi', 'todos', 'funnel', 'trend', 'lb', 'owners', 'topClients'));
}
