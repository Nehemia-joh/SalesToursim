<?php
/** helpers.php - escaping, money/currency, flash, csrf, badges, url helpers. */

/** current URL query builder */
function url(string $query): string
{
    return 'index.php?' . $query;
}

/** escape for HTML */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** display code from a prefix + numeric id: INQ-001 */
function code_of(string $prefix, $id): string
{
    return $id === null ? '' : $prefix . '-' . str_pad((string)(int)$id, 3, '0', STR_PAD_LEFT);
}

/** base currency from .envWebVersion (default USD) */
function base_currency(): string
{
    return strtoupper(Env::get('BASE_CURRENCY', 'USD'));
}

/** format money: 18,400 or 18,400 USD */
function money($n, bool $with_cur = false): string
{
    if ($n === null || $n === '') return '—';
    $s = number_format((float)$n, 0);
    return $with_cur ? $s . ' ' . base_currency() : $s;
}

/** format money with up to 2 decimals, trimming trailing zeros (for weighted pipeline) */
function money_precise($n, bool $with_cur = false): string
{
    if ($n === null || $n === '') return '—';
    $s = number_format((float)$n, 2);
    $s = rtrim(rtrim($s, '0'), '.');
    return $with_cur ? $s . ' ' . base_currency() : $s;
}

/** convert an amount in a currency to base using fx_rates */
function to_base_sql(string $expr, string $currency_col, string $alias = 'fx'): string
{
    return "ROUND(({$expr}) * COALESCE((SELECT rate_to_base FROM fx_rates WHERE currency = {$currency_col}), 1), 2)";
}

/** percent safe-divide */
function pct($part, $whole): float
{
    return ($whole > 0) ? round(100 * $part / $whole, 1) : 0.0;
}

/* ---------- flash messages ---------- */
function flash(string $kind, string $msg): void
{
    Auth::start();
    $_SESSION['flash'][$kind] = $msg;
}
function take_flashes(): array
{
    Auth::start();
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- redirect ---------- */
function redirect(string $query): void
{
    header('Location: index.php?' . $query);
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    Auth::start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(array $post): void
{
    $sent = $post['_csrf'] ?? '';
    if (!hash_equals(csrf_token(), (string)$sent)) {
        http_response_code(419);
        exit('Session expired - please go back and retry.');
    }
}

/* ---------- badge colours for statuses ---------- */
function badge(string $status): string
{
    $s = trim((string)$status);
    if ($s === '' || $s === '-') return '<span class="bdg dim">—</span>';
    $map = [
        // green
        'Completed' => 'ok', 'Accepted' => 'ok', 'Paid' => 'ok', 'Active' => 'ok',
        'Confirmed' => 'ok', 'Won' => 'ok', 'Yes' => 'ok',
        // blue/teal
        'Scheduled' => 'info', 'Sent' => 'info', 'Prospect' => 'info', 'Partially Paid' => 'info',
        'Qualified' => 'info', 'In Progress' => 'info', 'Revised' => 'info', 'Refunded' => 'info',
        // amber
        'Tentative' => 'warn', 'Pending' => 'warn', 'Unpaid' => 'warn', 'Overdue' => 'bad',
        'Partially Converted' => 'warn', 'Quote Sent' => 'warn', 'Dormant' => 'warn',
        // red
        'Lost' => 'bad', 'Cancelled' => 'bad', 'Declined' => 'bad', 'Expired' => 'bad', 'Rejected' => 'bad',
        // grey
        'Inactive' => 'dim', 'No' => 'dim', 'Draft' => 'dim', 'Open' => 'info',
    ];
    $cls = $map[$s] ?? 'dim';
    return '<span class="bdg ' . $cls . '">' . e($s) . '</span>';
}

/** progress bar for percentages */
function pct_bar(float $pct): string
{
    $pct = max(0.0, min(150.0, $pct));
    $cls = $pct >= 100 ? 'ok' : ($pct >= 75 ? 'mid' : 'low');
    return '<div class="pbar"><div class="fill ' . $cls . '" style="width:'
        . sprintf('%01.1f%%', $pct) . '"></div></div>'
        . '<span class="pnum">' . number_format($pct, 1) . '%</span>';
}

/** compact number for chart axes: 1.2M / 80k / 950 */
function short_money($n): string
{
    $n = (float)$n;
    if (abs($n) >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1), '0'), '.') . 'M';
    if (abs($n) >= 1000)    return rtrim(rtrim(number_format($n / 1000, 0), '0'), '.') . 'k';
    return number_format($n, 0);
}

/**
 * Dependency-free grouped bar chart (inline SVG).
 * $series = [['key'=>'revenue','label'=>'Revenue','color'=>'#0F766E'], ...]
 */
function svg_grouped_bars(array $rows, string $labelKey, array $series, int $width = 900, int $height = 260): string
{
    if (!$rows || !$series) return '<p class="hint">No data to chart yet.</p>';

    $max = 0.0;
    foreach ($rows as $r) foreach ($series as $s) $max = max($max, (float)($r[$s['key']] ?? 0));
    if ($max <= 0) $max = 1;

    $padL = 54; $padR = 12; $padT = 14; $padB = 34;
    $plotW = max(10, $width - $padL - $padR);
    $plotH = max(10, $height - $padT - $padB);
    $n = count($rows);
    $groupW = $plotW / $n;
    $barW = max(3, ($groupW * 0.68) / count($series));

    $out = '<svg class="svgchart" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="Chart">';

    // horizontal grid + y labels
    for ($i = 0; $i <= 4; $i++) {
        $y = $padT + $plotH - ($plotH * $i / 4);
        $val = $max * $i / 4;
        $out .= '<line x1="' . $padL . '" y1="' . round($y, 1) . '" x2="' . ($width - $padR)
              . '" y2="' . round($y, 1) . '" class="cgrid"/>';
        $out .= '<text x="' . ($padL - 7) . '" y="' . round($y + 3, 1)
              . '" class="clabel" text-anchor="end">' . e(short_money($val)) . '</text>';
    }

    foreach ($rows as $i => $r) {
        $gx = $padL + $i * $groupW + ($groupW - $barW * count($series)) / 2;
        foreach ($series as $j => $s) {
            $v = max(0, (float)($r[$s['key']] ?? 0));
            $h = $plotH * $v / $max;
            $x = $gx + $j * $barW;
            $y = $padT + $plotH - $h;
            $out .= '<rect x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . round($barW - 2, 1)
                  . '" height="' . round($h, 1) . '" rx="2" fill="' . e($s['color']) . '">'
                  . '<title>' . e((string)($r[$labelKey] ?? '')) . ' — ' . e($s['label']) . ': '
                  . e(number_format($v)) . '</title></rect>';
        }
        $cx = $padL + $i * $groupW + $groupW / 2;
        $out .= '<text x="' . round($cx, 1) . '" y="' . ($height - 12)
              . '" class="clabel" text-anchor="middle">' . e((string)($r[$labelKey] ?? '')) . '</text>';
    }

    return $out . '</svg>';
}
