<?php
/** reports.php - management reports: the Excel Management Reports KPIs plus team/source views. */

function reports_index(): void
{
    Auth::manager();
    $today = date('Y-m-d');

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

    $rep = [];
    $rep['inquiries']   = (int)DB::val('SELECT COUNT(*) FROM inquiries');
    $rep['open_leads']  = (int)DB::val("SELECT COUNT(*) FROM inquiries WHERE lead_status = 'Open'");
    $rep['won']         = (int)DB::val("SELECT COUNT(*) FROM inquiries WHERE lead_status = 'Won'");
    $rep['lost']        = (int)DB::val("SELECT COUNT(*) FROM inquiries WHERE lead_status = 'Lost'");
    $rep['confirmed']   = (int)DB::val("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'");
    $rep['completed']   = (int)DB::val("SELECT COUNT(*) FROM bookings WHERE status = 'Completed'");
    $rep['lost_opps']   = (int)DB::val("SELECT COUNT(*) FROM opportunities WHERE lead_status = 'Lost'");

    $rep['booking_value'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.booking_value * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency"
    );
    $rep['revenue'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status IN ('Confirmed','Completed')"
    );
    $rep['cost'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(b.direct_cost * COALESCE(fx.rate_to_base,1)),0),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.status IN ('Confirmed','Completed')"
    );
    $rep['gross_profit'] = $rep['revenue'] - $rep['cost'];
    $rep['margin_pct']   = pct($rep['gross_profit'], $rep['revenue']);

    $rep['payments'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(p.amount * COALESCE(fx.rate_to_base,1)),0),0)
         FROM payments p LEFT JOIN fx_rates fx ON fx.currency = p.currency"
    );
    $rep['outstanding'] = (float)DB::val("SELECT COALESCE(ROUND(SUM(balance),0),0) FROM ($balSql) x");

    $od = DB::one(
        "SELECT COUNT(*) n, COALESCE(ROUND(SUM(balance),0),0) amt
         FROM ($balSql) x
         WHERE status NOT IN ('Tentative','Cancelled')
           AND COALESCE(received,0) = 0
           AND payment_due_date IS NOT NULL AND payment_due_date < ?
           AND balance > 0",
        [$today]
    );
    $rep['overdue_invoices_n']   = (int)($od['n'] ?? 0);
    $rep['overdue_invoices_amt'] = (float)($od['amt'] ?? 0);

    $rep['estimated_pipeline'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(o.estimated_value * COALESCE(fx.rate_to_base,1)),0),0)
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency"
    );
    $rep['weighted_pipeline'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),2),0)
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency"
    );
    $rep['open_pipeline_60'] = (float)DB::val(
        "SELECT COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),0),0)
         FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency
         WHERE o.expected_close BETWEEN ? AND ?",
        [$today, date('Y-m-d', strtotime('+60 days'))]
    );

    $rep['overdue_tasks'] = (int)DB::val(
        "SELECT COUNT(*) FROM follow_ups
         WHERE status NOT IN ('Completed','Cancelled') AND due_date < ?",
        [$today]
    );

    /* ---------- conversion KPIs ---------- */
    $rep['conversion']     = pct($rep['won'], $rep['inquiries']);
    $rep['quotes']         = (int)DB::val('SELECT COUNT(*) FROM quotations');
    $rep['quotes_accepted'] = (int)DB::val("SELECT COUNT(*) FROM quotations WHERE status = 'Accepted'");
    $rep['quote_win_rate'] = pct($rep['quotes_accepted'], $rep['quotes']);
    $rep['avg_days_confirm'] = (float)DB::val(
        "SELECT COALESCE(AVG(DATEDIFF(b.booking_date, i.received_date)),0)
         FROM bookings b LEFT JOIN inquiries i ON i.id = b.inquiry_id
         WHERE i.received_date IS NOT NULL AND b.booking_date IS NOT NULL
           AND DATEDIFF(b.booking_date, i.received_date) >= 0"
    );
    $rep['avg_lead_value'] = (float)DB::val(
        "SELECT COALESCE(AVG(b.booking_value * COALESCE(fx.rate_to_base,1)),0)
         FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
         WHERE b.booking_value > 0"
    );

    /* ---------- cash collection coverage (paid milestones / all milestones) ---------- */
    $rep['sched_paid'] = (int)DB::val(
        "SELECT COUNT(*) FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         WHERE b.status <> 'Cancelled' AND s.amount_due > 0 AND COALESCE(s.amount_paid,0) >= s.amount_due"
    );
    $rep['sched_total'] = (int)DB::val(
        "SELECT COUNT(*) FROM payment_schedule s LEFT JOIN bookings b ON b.id = s.booking_id
         WHERE b.status <> 'Cancelled' AND s.amount_due > 0"
    );
    $rep['collection'] = pct($rep['sched_paid'], $rep['sched_total']);
    $rep['cash_vs_revenue'] = pct($rep['payments'], $rep['revenue']);

    /* ---------- per salesperson ---------- */
    $team = DB::all(
        "SELECT u.name,
           (SELECT COUNT(*) FROM inquiries i WHERE i.assigned_to = u.id) AS inq_n,
           (SELECT COUNT(*) FROM inquiries i WHERE i.assigned_to = u.id AND i.lead_status = 'Won') AS won_n,
           (SELECT COUNT(*) FROM bookings b WHERE b.salesperson = u.id
              AND b.status IN ('Confirmed','Completed')) AS bk_n,
           (SELECT COALESCE(ROUND(SUM(b.booking_value * COALESCE(fx.rate_to_base,1)),0),0)
              FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
              WHERE b.salesperson = u.id AND b.status IN ('Confirmed','Completed')) AS bk_value,
           (SELECT COALESCE(ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0),0)
              FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
              WHERE b.salesperson = u.id AND b.status IN ('Confirmed','Completed')) AS revenue,
           (SELECT COALESCE(SUM(t.target_revenue),0) FROM sales_targets t WHERE t.salesperson = u.id) AS target,
           (SELECT COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),2),0)
              FROM opportunities o LEFT JOIN fx_rates fx ON fx.currency = o.currency
              WHERE o.assigned_to = u.id) AS open_pipeline
         FROM users u WHERE u.role <> 'admin' AND u.active = 1 ORDER BY revenue DESC"
    );

    /* ---------- per source ---------- */
    $sources = DB::all(
        "SELECT COALESCE(i.lead_source,'(none)') src, COUNT(*) inq_n,
                SUM(i.lead_status = 'Won') won_n,
                COALESCE(ROUND(SUM(o.estimated_value * o.probability / 100 * COALESCE(fx.rate_to_base,1)),0),0) AS pipeline
         FROM inquiries i
         LEFT JOIN opportunities o ON o.inquiry_id = i.id
         LEFT JOIN fx_rates fx ON fx.currency = o.currency
         GROUP BY COALESCE(i.lead_source,'(none)')
         ORDER BY inq_n DESC"
    );

    /* ---------- monthly revenue vs target ---------- */
    $monthly = DB::all(
        "SELECT CONCAT(t.year,'-',LPAD(t.month,2,'0')) ym,
                COALESCE(SUM(t.target_revenue),0) target,
                COALESCE((SELECT ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0)
                          FROM bookings b LEFT JOIN fx_rates fx ON fx.currency = b.currency
                          WHERE b.status IN ('Confirmed','Completed')
                            AND YEAR(b.booking_date) = t.year AND MONTH(b.booking_date) = t.month),0) revenue
         FROM sales_targets t
         GROUP BY t.year, t.month
         ORDER BY t.year, t.month"
    );
    $have = array_column($monthly, 'ym');
    foreach (DB::all("SELECT DISTINCT CONCAT(YEAR(booking_date),'-',LPAD(MONTH(booking_date),2,'0')) ym
                      FROM bookings WHERE status IN ('Confirmed','Completed') ORDER BY 1") as $r) {
        if (!in_array($r['ym'], $have, true)) {
            $monthly[] = ['ym' => $r['ym'], 'target' => 0, 'revenue' => 0];
        }
    }
    usort($monthly, fn($a, $b) => $a['ym'] <=> $b['ym']);
    foreach ($monthly as &$m) {
        $m['label'] = date('M y', strtotime($m['ym'] . '-01'));
    }
    unset($m);

    /* ---------- lost reasons ---------- */
    $lost = DB::all(
        "SELECT COALESCE(NULLIF(TRIM(lost_reason),''),'(no reason logged)') reason,
                COUNT(*) n, COALESCE(SUM(estimated_value),0) value
         FROM opportunities WHERE lead_status IN ('Lost','Inactive') OR lost_reason <> ''
         GROUP BY reason ORDER BY n DESC"
    );

    /* ---------- upcoming travel ---------- */
    $upcoming = DB::all(
        "SELECT b.*, CONCAT('BK-',LPAD(b.id,3,'0')) bk_code, c.name AS client_name
         FROM bookings b LEFT JOIN clients c ON c.id = b.client_id
         WHERE b.status IN ('Confirmed','In Progress') AND b.arrival >= CURDATE()
         ORDER BY b.arrival LIMIT 15"
    );

    view('reports', get_defined_vars());
}
