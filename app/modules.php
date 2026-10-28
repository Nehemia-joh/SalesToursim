<?php
/**
 * modules.php - configuration that drives the generic CRUD engine.
 *
 * Each module:
 *   table        DB table
 *   title        page title
 *   prefix       display-code prefix (INQ-001)
 *   ordered      default ORDER BY (applied outside the wrapper)
 *   select       list query (aliased computed columns allowed)
 *   cols         list columns [key, label, type]; type: text|int|money|date|badge|pct|code|link
 *   fields       editable fields [name, label, type, options?]; type: date|int|money|text|email|textarea|
 *                list:<list_name>|users|ref:clients|ref:inquiries|ref:bookings|currency
 */

function module_config(string $key): ?array
{
    static $modules = null;
    if ($modules === null) $modules = build_modules();
    return $modules[$key] ?? null;
}

function module_keys(): array
{
    return array_keys(build_modules());
}

function build_modules(): array
{
    // ---------- shared select option-loaders --------------------------------
    $M = [];

    // ============================================================ CLIENTS
    $M['clients'] = [
        'table'   => 'clients',
        'title'   => 'Client database',
        'prefix'  => 'CLI',
        'ordered' => 'code ASC',
        'select'  => "
            SELECT c.*,
              CONCAT('CLI-',LPAD(c.id,3,'0')) AS code,
              u.name AS owner_name,
              (SELECT COUNT(*) FROM inquiries i WHERE i.client_id=c.id)                     AS inquiries_count,
              (SELECT COUNT(*) FROM bookings b  WHERE b.client_id=c.id
                 AND b.status IN ('Confirmed','Completed'))                                 AS bookings_count,
              COALESCE((SELECT SUM(b.booking_value*COALESCE(fx.rate_to_base,1)) FROM bookings b
                 LEFT JOIN fx_rates fx ON fx.currency=b.currency
                 WHERE b.client_id=c.id AND b.status IN ('Confirmed','Completed')),0)       AS booking_value,
              COALESCE((SELECT SUM(b.actual_revenue*COALESCE(fx.rate_to_base,1)) FROM bookings b
                 LEFT JOIN fx_rates fx ON fx.currency=b.currency
                 WHERE b.client_id=c.id AND b.status IN ('Confirmed','Completed')),0)       AS revenue,
              COALESCE((SELECT SUM(p.amount*COALESCE(fx.rate_to_base,1))
                 FROM payments p JOIN bookings b ON b.id=p.booking_id
                 LEFT JOIN fx_rates fx ON fx.currency=p.currency
                 WHERE b.client_id=c.id),0)                                                 AS payments_received,
              COALESCE((SELECT SUM(b.booking_value*COALESCE(fx.rate_to_base,1) - COALESCE(pp.received,0))
                 FROM bookings b
                 LEFT JOIN fx_rates fx ON fx.currency=b.currency
                 LEFT JOIN (SELECT p.booking_id, SUM(p.amount*COALESCE(fx2.rate_to_base,1)) received
                            FROM payments p LEFT JOIN fx_rates fx2 ON fx2.currency=p.currency
                            GROUP BY p.booking_id) pp ON pp.booking_id=b.id
                 WHERE b.client_id=c.id AND b.booking_value IS NOT NULL),0)                  AS outstanding,
              CASE WHEN (SELECT COUNT(*) FROM bookings b3 WHERE b3.client_id=c.id
                          AND b3.status IN ('Confirmed','Completed')) > 0 THEN 'Active'
                   WHEN (SELECT COUNT(*) FROM inquiries i3 WHERE i3.client_id=c.id) = 0 THEN 'Inactive'
                   WHEN c.first_inquiry IS NOT NULL AND c.first_inquiry < DATE_SUB(CURDATE(), INTERVAL 60 DAY) THEN 'Dormant'
                   ELSE 'Prospect' END                                                      AS client_status
            FROM clients c
            LEFT JOIN users u ON u.id = c.owner_id",
        'cols' => [
            ['code', 'ID', 'code'],
            ['name', 'Client', 'text'],
            ['client_type', 'Type', 'text'],
            ['country', 'Country', 'text'],
            ['contact_person', 'Contact', 'text'],
            ['email', 'Email', 'text'],
            ['phone', 'Phone', 'text'],
            ['first_inquiry', 'First Inquiry', 'date'],
            ['owner_name', 'Owner', 'text'],
            ['inquiries_count', 'Inq', 'int'],
            ['bookings_count', 'Bkgs', 'int'],
            ['booking_value', 'Booking Value', 'money'],
            ['revenue', 'Actual Rev', 'money'],
            ['payments_received', 'Payments', 'money'],
            ['outstanding', 'Outstanding', 'money'],
            ['client_status', 'Status', 'badge'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['name',           'Client Name',    'text',        'req'],
            ['client_type',    'Client Type',    'list:client_type'],
            ['country',        'Country',        'text'],
            ['contact_person', 'Contact Person', 'text'],
            ['email',          'Email',          'email'],
            ['phone',          'Phone',          'text'],
            ['first_inquiry',  'First Inquiry',  'date'],
            ['owner_id',       'Account Owner',  'users'],
            ['notes',          'Notes',          'textarea'],
        ],
    ];

    // ============================================================ INQUIRIES
    $M['inquiries'] = [
        'table'   => 'inquiries',
        'title'   => 'Inquiry log',
        'prefix'  => 'INQ',
        'ordered' => 't.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('INQ-',LPAD(t.id,3,'0'))                       AS code,
              c.name                                                AS client_name,
              u.name                                                AS sp_name,
              DATEDIFF(CURDATE(), t.received_date)                  AS age_days
            FROM inquiries t
            LEFT JOIN clients c ON c.id = t.client_id
            LEFT JOIN users   u ON u.id = t.assigned_to",
        'defaults' => [
            'received_date' => 'today',
            'lead_status'   => 'Open',
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['received_date', 'Received', 'date'],
            ['client_name', 'Client', 'link'],
            ['contact_person', 'Contact', 'text'],
            ['email', 'Email', 'text'],
            ['phone', 'Phone', 'text'],
            ['country', 'Country', 'text'],
            ['client_type', 'Type', 'text'],
            ['lead_source', 'Source', 'text'],
            ['tour', 'Tour', 'text'],
            ['pax', 'Pax', 'int'],
            ['budget', 'Budget', 'money'],
            ['currency', 'Cur', 'text'],
            ['sp_name', 'Salesperson', 'text'],
            ['lead_status', 'Status', 'badge'],
            ['travel_date', 'Travel Date', 'date'],
            ['age_days', 'Age (d)', 'int'],
            ['next_follow_up', 'Next Follow-up', 'date'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['received_date',  'Received Date', 'date',          'req'],
            ['client_id',      'Client',        'ref:clients',   'req'],
            ['new_client_name', 'New client name', 'text',   '', 'virtual'],
            ['contact_person', 'Contact Person', 'text'],
            ['email',          'Email',          'email'],
            ['phone',          'Phone / WhatsApp', 'text'],
            ['country',        'Country',        'text'],
            ['tour',           'Tour / Request', 'text',         'req'],
            ['travel_date',    'Travel Date',   'date'],
            ['pax',            'Pax',           'int'],
            ['budget',         'Budget',        'money'],
            ['currency',       'Currency',      'currency'],
            ['client_type',    'Client Type',   'list:client_type'],
            ['lead_source',    'Lead Source',   'list:lead_source'],
            ['assigned_to',    'Assigned To',   'users'],
            ['lead_status',    'Lead Status',   'list:lead_status'],
            ['next_follow_up', 'Next Follow-up', 'date'],
            ['notes',          'Notes',         'textarea'],
        ],
    ];

    // ============================================================ OPPORTUNITIES (Pipeline)
    $M['pipeline'] = [
        'table'   => 'opportunities',
        'title'   => 'Sales pipeline',
        'prefix'  => 'OPP',
        'ordered' => 't.id ASC',
        'select'  => "
            SELECT t.*,
              CONCAT('OPP-',LPAD(t.id,3,'0'))                                    AS code,
              CONCAT('INQ-',LPAD(t.inquiry_id,3,'0'))                            AS inq_code,
              i.tour                                                             AS tour,
              c.name                                                             AS client_name,
              u.name                                                             AS sp_name,
              ROUND(COALESCE(t.estimated_value,0) * COALESCE(t.probability,0)/100, 0) AS weighted_value,
              CASE WHEN t.expected_close IS NOT NULL AND t.expected_close < CURDATE()
                        AND t.lead_status = 'Open' THEN 'Overdue' ELSE '' END    AS close_flag
            FROM opportunities t
            LEFT JOIN inquiries i ON i.id = t.inquiry_id
            LEFT JOIN clients   c ON c.id = i.client_id
            LEFT JOIN users     u ON u.id = t.assigned_to",
        'defaults' => [
            'opened_date'  => 'today',
            'sales_stage'  => 'New Inquiry',
            'lead_status'  => 'Open',
            'probability'  => 10,
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['inq_code', 'Inquiry', 'code'],
            ['client_name', 'Client', 'text'],
            ['tour', 'Tour', 'text'],
            ['sales_stage', 'Stage', 'badge'],
            ['probability', 'Prob %', 'int'],
            ['estimated_value', 'Est. Value', 'money'],
            ['weighted_value', 'Weighted', 'money'],
            ['expected_close', 'Exp. Close', 'date'],
            ['close_flag', 'Flag', 'text'],
            ['sp_name', 'Salesperson', 'text'],
            ['lead_status', 'Status', 'badge'],
            ['lost_reason', 'Lost Reason', 'text'],
        ],
        'fields' => [
            ['inquiry_id',      'Inquiry',       'ref:inquiries', 'req'],
            ['opened_date',     'Opened Date',   'date'],
            ['sales_stage',     'Sales Stage',   'list:sales_stage'],
            ['probability',     'Probability %', 'int'],
            ['estimated_value', 'Estimated Value', 'money'],
            ['currency',        'Currency',      'currency'],
            ['expected_close',  'Expected Close', 'date'],
            ['assigned_to',     'Salesperson',   'users'],
            ['last_contact',    'Last Contact',  'date'],
            ['next_follow_up',  'Next Follow-up', 'date'],
            ['lead_status',     'Lead Status',   'list:lead_status'],
            ['lost_reason',     'Lost /cold Reason', 'textarea'],
        ],
    ];

    // ============================================================ QUOTATIONS
    $M['quotations'] = [
        'table'   => 'quotations',
        'title'   => 'Quotation tracker',
        'prefix'  => 'QT',
        'ordered' => 't.quote_date DESC, t.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('QT-',LPAD(t.id,3,'0'))                       AS code,
              CONCAT('INQ-',LPAD(t.inquiry_id,3,'0'))              AS inq_code,
              c.name                                               AS client_name,
              u.name                                               AS sp_name,
              IF(t.booking_id IS NULL,'-',CONCAT('BK-',LPAD(t.booking_id,3,'0'))) AS bk_code,
              CASE WHEN t.status IN ('Sent','Revised') AND t.valid_until IS NOT NULL
                        AND t.valid_until < CURDATE() THEN 'Expired' ELSE '' END AS flag,
              (SELECT COUNT(*) FROM quote_views v WHERE v.quotation_id=t.id) AS view_count,
              (SELECT MAX(v.viewed_at) FROM quote_views v WHERE v.quotation_id=t.id) AS last_viewed
            FROM quotations t
            LEFT JOIN inquiries i ON i.id = t.inquiry_id
            LEFT JOIN clients   c ON c.id = i.client_id
            LEFT JOIN users     u ON u.id = t.prepared_by",
        'defaults' => [
            'quote_date' => 'today',
            'status'     => 'Draft',
            'version'    => 1,
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['quote_date', 'Quote Date', 'date'],
            ['inq_code', 'Inquiry', 'code'],
            ['client_name', 'Client', 'text'],
            ['tour', 'Tour', 'text'],
            ['pax', 'Pax', 'int'],
            ['amount', 'Quote Amount', 'money'],
            ['currency', 'Cur', 'text'],
            ['status', 'Status', 'badge'],
            ['valid_until', 'Valid Until', 'date'],
            ['flag', 'Flag', 'flag'],
            ['bk_code', 'Booking', 'code'],
            ['sp_name', 'Prepared By', 'text'],
            ['version', 'Version', 'int'],
            ['view_count', 'Views', 'int'],
            ['last_viewed', 'Last Viewed', 'datetime'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['quote_date',  'Quote Date',   'date',          'req'],
            ['inquiry_id',  'Inquiry',      'ref:inquiries', 'req'],
            ['booking_id',  'Linked Booking (optional)', 'ref:bookings'],
            ['tour',        'Tour',         'text',          'req'],
            ['pax',         'Pax',          'int'],
            ['amount',      'Quote Amount', 'money',         'req'],
            ['currency',    'Currency',     'currency'],
            ['status',      'Status',       'list:quotation_status'],
            ['valid_until', 'Valid Until',  'date'],
            ['prepared_by', 'Prepared By',  'users'],
            ['version',     'Version',      'int'],
            ['notes',       'Notes',        'textarea'],
        ],
    ];

    // ============================================================ FOLLOW-UPS
    $M['followups'] = [
        'table'   => 'follow_ups',
        'title'   => 'Follow-up manager',
        'prefix'  => 'FU',
        'ordered' => 't.status = \'Completed\' ASC, t.due_date ASC, t.id ASC',
        'select'  => "
            SELECT t.*,
              CONCAT('FU-',LPAD(t.id,3,'0'))                                      AS code,
              CONCAT('INQ-',LPAD(t.inquiry_id,3,'0'))                             AS inq_code,
              c.name                                                              AS client_name,
              i.tour                                                              AS tour,
              u.name                                                              AS sp_name,
              CASE WHEN t.status NOT IN ('Completed','Cancelled') AND t.due_date < CURDATE()
                   THEN CONCAT('Overdue by ', DATEDIFF(CURDATE(), t.due_date), 'd')
                   ELSE '' END                                                    AS flag,
              CASE WHEN t.status NOT IN ('Completed','Cancelled') AND t.due_date IS NOT NULL
                   THEN GREATEST(0, DATEDIFF(CURDATE(), t.due_date)) ELSE 0 END    AS days_overdue
            FROM follow_ups t
            LEFT JOIN inquiries i ON i.id = t.inquiry_id
            LEFT JOIN clients   c ON c.id = i.client_id
            LEFT JOIN users     u ON u.id = t.assigned_to",
        'defaults' => [
            'due_date' => '+3 days',
            'priority' => 'Medium',
            'status'   => 'Pending',
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['inq_code', 'Inquiry', 'code'],
            ['client_name', 'Client', 'text'],
            ['action', 'Action', 'text'],
            ['due_date', 'Due', 'date'],
            ['days_overdue', 'Days Overdue', 'int'],
            ['priority', 'Priority', 'badge'],
            ['status', 'Status', 'badge'],
            ['completed_date', 'Completed', 'date'],
            ['sp_name', 'Assigned To', 'text'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['inquiry_id',      'Inquiry / Lead Trace ID', 'ref:inquiries', 'req'],
            ['action',          'Action',          'text',          'req'],
            ['due_date',        'Due Date',        'date',          'req'],
            ['priority',        'Priority',        'list:priority'],
            ['assigned_to',     'Assigned To',     'users'],
            ['status',          'Status',          'list:task_status'],
            ['completed_date',  'Completed Date',  'date'],
            ['notes',           'Notes',           'textarea'],
        ],
    ];

    // ============================================================ BOOKINGS
    $M['bookings'] = [
        'table'   => 'bookings',
        'title'   => 'Bookings',
        'prefix'  => 'BK',
        'ordered' => 't.booking_date DESC, t.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('BK-',LPAD(t.id,3,'0'))                                     AS code,
              c.name                                                             AS client_name,
              u.name                                                             AS sp_name,
              CONCAT('INQ-',LPAD(t.inquiry_id,3,'0'))                            AS inq_code,
              COALESCE(pp.received,0)                                            AS received,
              CASE WHEN COALESCE(t.booking_value,0) > 0
                   THEN t.booking_value - COALESCE(pp.received,0) ELSE NULL END   AS outstanding,
              CASE WHEN t.status = 'Cancelled' then 'Cancelled'
                   WHEN t.status = 'Tentative' THEN 'Tentative'
                   WHEN COALESCE(t.booking_value,0) <= 0 THEN 'Unpaid'
                   WHEN t.booking_value - COALESCE(pp.received,0) <= 0 THEN 'Paid'
                   WHEN COALESCE(pp.received,0) > 0 THEN 'Partially Paid'
                   WHEN t.payment_due_date IS NOT NULL AND t.payment_due_date < CURDATE() THEN 'Overdue'
                   ELSE 'Unpaid' END                                              AS payment_status,
              CASE WHEN i.received_date IS NOT NULL AND t.booking_date IS NOT NULL
                   THEN DATEDIFF(t.booking_date, i.received_date) ELSE NULL END   AS days_to_confirm,
              CASE WHEN t.actual_revenue IS NOT NULL AND t.direct_cost IS NOT NULL
                   THEN t.actual_revenue - t.direct_cost ELSE NULL END            AS gross_profit,
              CASE WHEN COALESCE(t.actual_revenue,0) <> 0 AND t.direct_cost IS NOT NULL
                   THEN ROUND(100*(t.actual_revenue - t.direct_cost)/t.actual_revenue,1)
                   ELSE NULL END                                                  AS gross_margin
            FROM bookings t
            LEFT JOIN clients c ON c.id = t.client_id
            LEFT JOIN users   u ON u.id = t.salesperson
            LEFT JOIN inquiries i ON i.id = t.inquiry_id
            LEFT JOIN (SELECT booking_id, SUM(amount) AS received FROM payments GROUP BY booking_id) pp
                   ON pp.booking_id = t.id",
        'defaults' => [
            'booking_date' => 'today',
            'status'       => 'Tentative',
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['booking_date', 'Booked', 'date'],
            ['inq_code', 'Inquiry', 'code'],
            ['client_name', 'Client', 'link'],
            ['tour', 'Tour', 'text'],
            ['arrival', 'Arrival', 'date'],
            ['departure', 'Departure', 'date'],
            ['pax', 'Pax', 'int'],
            ['sp_name', 'Salesperson', 'text'],
            ['booking_value', 'Booking Value', 'money'],
            ['currency', 'Cur', 'text'],
            ['actual_revenue', 'Actual Rev', 'money'],
            ['direct_cost', 'Direct Cost', 'money'],
            ['gross_profit', 'Gross Profit', 'money'],
            ['gross_margin', 'Margin %', 'percent'],
            ['received', 'Received', 'money'],
            ['outstanding', 'Outstanding', 'money'],
            ['payment_due_date', 'Payment Due', 'date'],
            ['payment_status', 'Payment', 'badge'],
            ['status', 'Status', 'badge'],
            ['days_to_confirm', 'd to Confirm', 'int'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['inquiry_id',       'Inquiry / Lead Trace ID', 'ref:inquiries'],
            ['booking_date',     'Booking Date', 'date',          'req'],
            ['client_id',        'Client',       'ref:clients',   'req'],
            ['tour',             'Tour',         'text',          'req'],
            ['arrival',          'Arrival',      'date'],
            ['departure',        'Departure',    'date'],
            ['pax',              'Pax',          'int'],
            ['status',           'Status',       'list:booking_status'],
            ['salesperson',      'Salesperson',  'users'],
            ['booking_value',    'Booking Value', 'money'],
            ['currency',         'Currency',     'currency'],
            ['actual_revenue',   'Actual Revenue', 'money'],
            ['direct_cost',      'Direct Cost',  'money'],
            ['payment_due_date', 'Payment Due',  'date'],
            ['notes',            'Notes',        'textarea'],
        ],
    ];

    // ============================================================ PAYMENT SCHEDULE
    $M['schedule'] = [
        'table'   => 'payment_schedule',
        'title'   => 'Payment schedule',
        'prefix'  => 'SCH',
        'ordered' => 't.due_date ASC, t.id ASC',
        'select'  => "
            SELECT t.*,
              CONCAT('SCH-',LPAD(t.id,3,'0'))                                     AS code,
              IF(t.booking_id IS NULL,'',CONCAT('BK-',LPAD(t.booking_id,3,'0')))  AS bk_code,
              b.tour                                                              AS tour,
              c.name                                                              AS client_name,
              CASE WHEN t.amount_due IS NULL OR t.amount_due = 0 THEN '-'
                   WHEN t.amount_paid IS NOT NULL AND t.amount_paid >= t.amount_due THEN 'Paid'
                   WHEN t.amount_paid IS NOT NULL AND t.amount_paid > 0 THEN 'Partially Paid'
                   WHEN t.due_date IS NOT NULL AND t.due_date < CURDATE() THEN 'Overdue'
                   ELSE 'Unpaid' END                                              AS pay_status,
              CASE WHEN t.amount_due IS NULL OR t.amount_due = 0 THEN 0
                   WHEN t.amount_paid IS NOT NULL AND t.amount_paid >= t.amount_due
                        THEN GREATEST(0, IFNULL(DATEDIFF(t.date_paid, t.due_date),0))
                   ELSE GREATEST(0, DATEDIFF(CURDATE(), t.due_date)) END          AS days_late,
              CASE WHEN t.amount_due IS NOT NULL AND t.amount_paid IS NOT NULL
                   THEN t.amount_paid - t.amount_due ELSE NULL END                 AS variance
            FROM payment_schedule t
            LEFT JOIN bookings b ON b.id = t.booking_id
            LEFT JOIN clients c ON c.id = b.client_id",
        'defaults' => [
            'payment_type' => 'Deposit',
            'due_date'     => '+7 days',
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['bk_code', 'Booking', 'code'],
            ['client_name', 'Client', 'text'],
            ['tour', 'Tour', 'text'],
            ['payment_type', 'Type', 'text'],
            ['description', 'Description', 'text'],
            ['due_date', 'Due Date', 'date'],
            ['amount_due', 'Amount Due', 'money'],
            ['amount_paid', 'Paid', 'money'],
            ['variance', 'Variance', 'money'],
            ['date_paid', 'Date Paid', 'date'],
            ['pay_status', 'Status', 'badge'],
            ['days_late', 'Days Late', 'int'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['booking_id',   'Booking',    'ref:bookings', 'req'],
            ['payment_type', 'Milestone',  'list:payment_type'],
            ['description',  'Description', 'text'],
            ['due_date',     'Due Date',   'date',          'req'],
            ['amount_due',   'Amount Due', 'money',         'req'],
            ['amount_paid',  'Amount Paid', 'money'],
            ['date_paid',    'Date Paid',  'date'],
            ['notes',        'Notes',      'textarea'],
        ],
    ];

    // ============================================================ PAYMENTS
    $M['payments'] = [
        'table'   => 'payments',
        'title'   => 'Payments',
        'prefix'  => 'PAY',
        'ordered' => 't.payment_date DESC, t.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('PAY-',LPAD(t.id,3,'0'))                                     AS code,
              IF(t.booking_id IS NULL,'',CONCAT('BK-',LPAD(t.booking_id,3,'0')))  AS bk_code,
              c.name                                                              AS client_name,
              u.name                                                              AS sp_name,
              ROUND(COALESCE(t.amount,0) * COALESCE(fx.rate_to_base,1), 2)         AS base_amount
            FROM payments t
            LEFT JOIN bookings b ON b.id  = t.booking_id
            LEFT JOIN clients  c ON c.id  = b.client_id
            LEFT JOIN users    u ON u.id  = t.received_by
            LEFT JOIN fx_rates fx ON fx.currency = t.currency",
        'defaults' => [
            'payment_date' => 'today',
            'payment_type' => 'Deposit',
            'method'       => 'Bank Transfer',
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['bk_code', 'Booking', 'code'],
            ['client_name', 'Client', 'text'],
            ['payment_date', 'Date', 'date'],
            ['invoice_ref', 'Invoice Ref', 'text'],
            ['payment_type', 'Type', 'text'],
            ['amount', 'Amount', 'money'],
            ['currency', 'Cur', 'text'],
            ['base_amount', 'Base Amount', 'money'],
            ['method', 'Method', 'text'],
            ['sp_name', 'Received By', 'text'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['booking_id',   'Booking',     'ref:bookings', 'req'],
            ['payment_date', 'Payment Date', 'date',        'req'],
            ['invoice_ref',  'Invoice Ref', 'text'],
            ['payment_type', 'Payment Type', 'list:payment_type'],
            ['amount',       'Amount',      'money',        'req'],
            ['currency',     'Currency',    'currency'],
            ['method',       'Method',      'list:payment_method'],
            ['received_by',  'Received By', 'users'],
            ['notes',        'Notes',       'textarea'],
        ],
    ];

    // ============================================================ TARGETS
    $M['targets'] = [
        'table'   => 'sales_targets',
        'title'   => 'Sales targets',
        'prefix'  => 'TGT',
        'ordered' => 'year DESC, month DESC, sp_name ASC',
        'select'  => "
            SELECT t.*,
              CONCAT('TGT-',LPAD(t.id,3,'0'))                                     AS code,
              u.name                                                              AS sp_name,
              DATE_FORMAT(CONCAT(t.year,'-',LPAD(t.month,2,'0'),'-01'), '%b %Y')  AS month_label,
              COALESCE((SELECT ROUND(SUM(b.actual_revenue * COALESCE(fx.rate_to_base,1)),0)
                        FROM bookings b
                        LEFT JOIN fx_rates fx ON fx.currency = b.currency
                        WHERE b.salesperson = t.salesperson AND b.status IN ('Confirmed','Completed')
                          AND YEAR(b.booking_date) = t.year AND MONTH(b.booking_date) = t.month),0) AS actual,
              COALESCE((SELECT COUNT(*) FROM inquiries i
                        WHERE i.assigned_to = t.salesperson
                          AND YEAR(i.received_date) = t.year AND MONTH(i.received_date) = t.month),0) AS new_inquiries,
              COALESCE((SELECT COUNT(*) FROM bookings b
                        WHERE b.salesperson = t.salesperson
                          AND b.status IN ('Confirmed','Completed','In Progress')
                          AND YEAR(b.booking_date) = t.year AND MONTH(b.booking_date) = t.month),0) AS confirmed_bookings
            FROM sales_targets t
            LEFT JOIN users u ON u.id = t.salesperson",
        'defaults' => [
            'month' => 'current_month',
            'year'  => 'current_year',
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['month_label', 'Month', 'text'],
            ['sp_name', 'Salesperson', 'text'],
            ['target_revenue', 'Target', 'money'],
            ['actual', 'Actual', 'money'],
            ['variance', 'Variance', 'money'],
            ['achievement', 'Achievement', 'pct'],
            ['new_inquiries', 'New Inq', 'int'],
            ['confirmed_bookings', 'Bookings', 'int'],
            ['notes', 'Notes', 'text'],
        ],
        'fields' => [
            ['salesperson',    'Salesperson', 'users', 'req'],
            ['month',          'Month (1-12)', 'int',  'req'],
            ['year',           'Year',         'int',  'req'],
            ['target_revenue', 'Target Revenue (' . base_currency() . ' base)', 'money'],
            ['notes',          'Notes',       'textarea'],
        ],
        'postprocess_row' => true, // variance + achievement built in controller
    ];

    // ============================================================ ACTIVITIES
    $M['activities'] = [
        'table'   => 'activities',
        'title'   => 'Activity log',
        'prefix'  => 'ACT',
        'ordered' => 't.activity_date DESC, t.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('ACT-',LPAD(t.id,3,'0'))                                     AS code,
              CONCAT('INQ-',LPAD(t.inquiry_id,3,'0'))                             AS inq_code,
              c.name                                                              AS client_name,
              u.name                                                              AS sp_name
            FROM activities t
            LEFT JOIN inquiries i ON i.id = t.inquiry_id
            LEFT JOIN clients   c ON c.id = i.client_id
            LEFT JOIN users     u ON u.id = t.salesperson",
        'defaults' => [
            'activity_date' => 'today',
            'activity_type' => 'Call',
        ],
        'cols' => [
            ['code', 'ID', 'code'],
            ['activity_date', 'Date', 'date'],
            ['inq_code', 'Inquiry', 'code'],
            ['client_name', 'Client', 'text'],
            ['activity_type', 'Type', 'text'],
            ['summary', 'Summary', 'text'],
            ['next_action', 'Next Action', 'text'],
            ['next_action_date', 'Next Action Date', 'date'],
            ['sp_name', 'Salesperson', 'text'],
            ['duration_min', 'Min', 'int'],
        ],
        'fields' => [
            ['activity_date',    'Activity Date', 'date',          'req'],
            ['inquiry_id',       'Inquiry',       'ref:inquiries', 'req'],
            ['activity_type',    'Activity Type', 'list:activity_type'],
            ['summary',          'Summary',       'text',          'req'],
            ['next_action',      'Next Action',   'text'],
            ['next_action_date', 'Next Action Date', 'date'],
            ['salesperson',      'Salesperson',   'users'],
            ['duration_min',     'Duration (min)', 'int'],
        ],
    ];

    // ============================================================ TRAVELERS
    $M['travelers'] = [
        'table'   => 'travelers',
        'title'   => 'Travelers',
        'prefix'  => 'TRV',
        'ordered' => 't.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('TRV-',LPAD(t.id,3,'0'))                                     AS code,
              IF(t.booking_id IS NULL,'',CONCAT('BK-',LPAD(t.booking_id,3,'0')))  AS bk_code,
              IF(t.inquiry_id IS NULL,'',CONCAT('INQ-',LPAD(t.inquiry_id,3,'0'))) AS inq_code,
              c.name                                                              AS client_name,
              COALESCE(b.tour, i.tour)                                            AS tour
            FROM travelers t
            LEFT JOIN bookings  b ON b.id = t.booking_id
            LEFT JOIN inquiries i ON i.id = t.inquiry_id
            LEFT JOIN clients   c ON c.id = COALESCE(b.client_id, i.client_id)",
        'cols' => [
            ['code', 'ID', 'code'],
            ['full_name', 'Traveler', 'text'],
            ['bk_code', 'Booking', 'code'],
            ['inq_code', 'Inquiry', 'code'],
            ['client_name', 'Client', 'text'],
            ['tour', 'Tour', 'text'],
            ['nationality', 'Nationality', 'text'],
            ['passport_no', 'Passport', 'text'],
            ['passport_expiry', 'Passport Expiry', 'date'],
            ['dietary', 'Dietary', 'text'],
            ['allergies', 'Allergies', 'text'],
            ['emergency_contact', 'Emergency Contact', 'text'],
        ],
        'fields' => [
            ['booking_id',        'Booking (optional)', 'ref:bookings'],
            ['inquiry_id',        'Inquiry (optional)', 'ref:inquiries'],
            ['full_name',         'Full Name',          'text', 'req'],
            ['dob',               'Date of Birth',      'date'],
            ['nationality',       'Nationality',        'text'],
            ['passport_no',       'Passport Number',    'text'],
            ['passport_expiry',   'Passport Expiry',    'date'],
            ['dietary',           'Dietary Requirements', 'text'],
            ['allergies',         'Allergies',          'text'],
            ['medical_notes',     'Medical Notes',      'textarea'],
            ['emergency_contact', 'Emergency Contact',  'text'],
            ['room_preference',   'Room Preference',    'text'],
            ['notes',             'Notes',              'textarea'],
        ],
    ];

    // ============================================================ CONTENT LIBRARY
    $M['content_library'] = [
        'table'   => 'content_library',
        'title'   => 'Content library',
        'prefix'  => 'CNT',
        'ordered' => 't.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('CNT-',LPAD(t.id,3,'0')) AS code,
              IF(t.active = 1,'Active','Inactive') AS active_label
            FROM content_library t",
        'cols' => [
            ['code', 'ID', 'code'],
            ['title', 'Title', 'text'],
            ['category', 'Category', 'text'],
            ['tags', 'Tags', 'text'],
            ['active_label', 'Status', 'badge'],
            ['created_at', 'Created', 'datetime'],
        ],
        'fields' => [
            ['title',    'Title',    'text', 'req'],
            ['category', 'Category', 'text'],
            ['body',     'Content',  'textarea', 'req'],
            ['tags',     'Tags',     'text'],
            ['active',   'Active',   'bool'],
        ],
        'defaults' => ['active' => 1],
    ];

    // ============================================================ MESSAGE TEMPLATES
    $M['message_templates'] = [
        'table'   => 'message_templates',
        'title'   => 'Message templates',
        'prefix'  => 'MSG',
        'ordered' => 't.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('MSG-',LPAD(t.id,3,'0')) AS code,
              IF(t.active = 1,'Active','Inactive') AS active_label
            FROM message_templates t",
        'cols' => [
            ['code', 'ID', 'code'],
            ['name', 'Name', 'text'],
            ['channel', 'Channel', 'text'],
            ['subject', 'Subject', 'text'],
            ['category', 'Category', 'text'],
            ['active_label', 'Status', 'badge'],
            ['created_at', 'Created', 'datetime'],
        ],
        'fields' => [
            ['name',     'Name',     'text', 'req'],
            ['channel',  'Channel',  'text'],
            ['subject',  'Subject',  'text'],
            ['body',     'Message',  'textarea', 'req'],
            ['category', 'Category', 'text'],
            ['active',   'Active',   'bool'],
        ],
        'defaults' => ['active' => 1, 'channel' => 'Email'],
    ];

    // ============================================================ TASK TEMPLATES
    $M['task_templates'] = [
        'table'   => 'task_templates',
        'title'   => 'Task templates',
        'prefix'  => 'TSK',
        'ordered' => 't.id DESC',
        'select'  => "
            SELECT t.*,
              CONCAT('TSK-',LPAD(t.id,3,'0')) AS code,
              IF(t.active = 1,'Active','Inactive') AS active_label
            FROM task_templates t",
        'cols' => [
            ['code', 'ID', 'code'],
            ['name', 'Name', 'text'],
            ['trigger_stage', 'Trigger Stage', 'text'],
            ['action', 'Task', 'text'],
            ['due_days', 'Due (days)', 'int'],
            ['priority', 'Priority', 'badge'],
            ['active_label', 'Status', 'badge'],
        ],
        'fields' => [
            ['name',          'Name',          'text', 'req'],
            ['trigger_stage', 'Trigger Stage', 'list:sales_stage'],
            ['action',        'Task',          'text', 'req'],
            ['due_days',      'Due (days)',    'int'],
            ['priority',      'Priority',      'list:priority'],
            ['active',        'Active',        'bool'],
        ],
        'defaults' => ['active' => 1, 'due_days' => 3],
    ];

    return $M;
}
