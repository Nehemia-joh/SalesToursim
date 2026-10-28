<?php /** help.php - how to enter data, in the same order as the Excel workbook. */
$title = 'How to use';
?>
<div class="topbar">
  <div>
    <h1>How to use the system</h1>
    <div class="hint">Enter data in this order — every later screen fills itself from the earlier one.</div>
  </div>
  <div class="tools">
    <a class="btn primary" href="index.php?page=inquiries&action=form">+ Start a new inquiry</a>
  </div>
</div>

<div class="flow">
  <a href="index.php?page=clients"><b>1</b><span>Client</span></a>
  <a href="index.php?page=inquiries&action=form"><b>2</b><span>Inquiry</span></a>
  <a href="index.php?page=pipeline&action=form"><b>3</b><span>Pipeline</span></a>
  <a href="index.php?page=quotations&action=form"><b>4</b><span>Quote / proposal</span></a>
  <a href="index.php?page=followups&action=form"><b>5</b><span>Follow-up</span></a>
  <a href="index.php?page=bookings&action=form"><b>6</b><span>Booking</span></a>
  <a href="index.php?page=schedule&action=form"><b>7</b><span>Payment plan</span></a>
  <a href="index.php?page=payments&action=form"><b>8</b><span>Payment</span></a>
</div>

<div class="two">
  <div class="card">
    <h2>Golden rules</h2>
    <ol>
      <li><b>One client = one spelling.</b> Search the Client Database before adding a new client. Duplicate spellings split the history and the reports.</li>
      <li><b>Every inquiry gets an inquiry record first.</b> The pipeline, quotations, follow-ups, bookings and payments all hang off that inquiry.</li>
      <li><b>Keep the status honest.</b> Move the inquiry to <i>Won</i> only when a booking is confirmed; use <i>Lost</i> with a reason so management can see why.</li>
      <li><b>One currency per record.</b> Choose the currency the client is actually quoted in. The dashboard converts everything to <?= e($base_cur) ?> automatically.</li>
      <li><b>Enter payments as they arrive.</b> Cash reports and overdue alarms only work when payments are recorded.</li>
      <li><b>Never overwrite history.</b> Add a new quotation version, a new follow-up, or a new payment instead of editing an old one.</li>
    </ol>
  </div>

  <div class="card">
    <h2>What the buttons do</h2>
    <table class="grid small">
      <tbody>
        <tr><td><b>+ New</b></td><td>Adds a blank record. Required fields are marked with <b>*</b>.</td></tr>
        <tr><td><b>Edit</b></td><td>Changes the record in place. IDs and calculated columns are never typed by hand.</td></tr>
        <tr><td><b>Search</b></td><td>Filters the list on every visible column.</td></tr>
        <tr><td><b>Delete</b></td><td>Removes the record. Prefer setting the status to <i>Cancelled</i> or <i>Inactive</i>.</td></tr>
        <tr><td><b>Settings</b></td><td>Maintains the dropdown lists, exchange rates and company details.</td></tr>
      </tbody>
    </table>
    <p class="hint">Calculated columns (weighted value, outstanding, gross profit, achievement, days overdue) are never typed — they update from the records you enter.</p>
  </div>
</div>

<div class="card">
  <h2>Step-by-step data entry</h2>

  <h3>1 · Client Database</h3>
  <p>Add a client once: name, type (Direct / Agency / Corporate), country, contact person, email, phone and owner. The list shows inquiries, confirmed bookings, booking value, revenue, payments received and outstanding automatically.</p>
  <p class="hint">Shortcut: when you create an inquiry you can type a new client name in <b>“New client name”</b>. The system creates the client for you and copies the contact details across.</p>

  <h3>2 · Inquiry Log</h3>
  <p>This is the front door of the business. Record:</p>
  <ul>
    <li><b>Received date</b> — the day the request arrived (defaults to today).</li>
    <li><b>Client</b> — pick an existing client or type a new name.</li>
    <li><b>Contact person, email, phone, country</b> — the person you are actually talking to.</li>
    <li><b>Tour / request, travel date, pax, budget and currency</b>.</li>
    <li><b>Client type, lead source, assigned salesperson, lead status and next follow-up date</b>.</li>
  </ul>
  <p>When you save, the inquiry gets an <b>INQ-</b> number and the age in days is calculated for you.</p>

  <h3>3 · Sales Pipeline</h3>
  <p>Create one opportunity per inquiry. Choose the stage, probability, estimated value, expected close date and owner. The system calculates <b>weighted value = estimated value × probability %</b>.</p>
  <p>Stages follow the Excel order: New Inquiry → Qualified → Quotation Sent → Follow-Up → Negotiation → Confirmed → Lost / Inactive. If a deal is lost, always record the lost reason.</p>

  <h3>4 · Quotations &amp; proposals</h3>
  <p>Choose the inquiry and the tour, pax, amount, currency and owner are pre-filled when you save. Record the quote date, validity date, version and status (Draft / Sent / Revised / Accepted / Rejected). The list flags expired quotes and links the quote to the booking once it is won.</p>
  <p>For a client-ready proposal, open <b>Quotations → Build</b>. Add day-by-day itinerary lines, priced items, inclusions, exclusions, terms and a cover note. Optional items are shown separately from the base total. Then generate a <b>public proposal link</b> and send it to the client: it works without a login, records every view, and lets the client accept, request changes or decline online. An accepted proposal creates a tentative booking and a follow-up task automatically.</p>
  <p>Set the company name, email, phone, website and tagline in <b>Settings</b> — they appear on the proposal header and footer.</p>

  <h3>5 · Follow-Up Manager</h3>
  <p>Every call, email or meeting that needs a future action belongs here: action, due date, priority, owner and status. Overdue days are calculated from the due date. The dashboard shows overdue tasks by salesperson, due this week, and the total open workload.</p>

  <h3>6 · Bookings</h3>
  <p>Create the booking when the client confirms. Pick the inquiry and the client, tour, pax, salesperson and expected value are filled from the inquiry when you save (the owner updates as soon as you pick the inquiry). Enter:</p>
  <ul>
    <li><b>Booking value</b> — the contracted selling price.</li>
    <li><b>Actual revenue</b> and <b>direct cost</b> — used for gross profit and margin.</li>
    <li><b>Payment due date</b>, status (Tentative / Confirmed / In Progress / Completed / Cancelled) and notes.</li>
  </ul>
  <p>Received, outstanding, gross profit, margin and payment status are calculated. Tentative holds appear in the dashboard action centre until you confirm or release them.</p>

  <h3>6b · Travelers</h3>
  <p>Add every traveler against the booking (or inquiry): full name, date of birth, nationality, passport number and expiry, dietary needs, allergies, medical notes, emergency contact and room preference. The dashboard shows how many traveler profiles are linked to each running or upcoming tour, so you can see at a glance who is still missing.</p>

  <h3>7 · Payment Schedule</h3>
  <p>Split the booking into milestones — Deposit, Second Payment, Balance, etc. Each row has a due date, amount due, amount paid and date paid. The system calculates variance, days late and milestone status.</p>

  <h3>8 · Payments</h3>
  <p>Record every amount received against the booking: date, invoice reference, payment type, amount, currency, method and who received it. Refunds can be entered as negative amounts. The base-currency value is calculated using the exchange rates in Settings.</p>

  <h3>9 · Sales Targets</h3>
  <p>Set one target per salesperson per month: target revenue, plus the expected number of new inquiries and confirmed bookings. Actual revenue, variance and achievement % are calculated from the bookings.</p>

  <h3>10 · Activity Log</h3>
  <p>Use this as the diary: calls, emails, meetings, site visits and notes. Activities are linked to an inquiry so the full client history stays together.</p>

  <h3>11 · Content Library</h3>
  <p>Save reusable proposal text — inclusions, exclusions, payment terms, cancellation policies, destination notes and welcome paragraphs. In the Quote Builder, choose a block and insert it into the cover note, inclusions, exclusions or terms with one click.</p>

  <h3>12 · Message Templates</h3>
  <p>Save email, WhatsApp and SMS wording for quotes, reminders, payment chases and post-trip follow-ups. In the Quote Builder, pick a template and the system fills in the client name, quote number, tour, amount, validity date and salesperson before you copy it.</p>

  <h3>13 · Task Templates</h3>
  <p>Standardise the follow-up steps that should happen at each pipeline stage. Each template stores the task, trigger stage, due-in days and priority so the team works the same playbook. Click <b>Use</b> on a template to open a new follow-up with the task, priority and due date already filled in.</p>
</div>

<div class="two">
  <div class="card">
    <h2>Status rules</h2>
    <table class="grid small">
      <thead><tr><th>Record</th><th>Status</th><th>Meaning</th></tr></thead>
      <tbody>
        <tr><td>Inquiry</td><td>Open</td><td>Still being worked.</td></tr>
        <tr><td>Inquiry</td><td>Won</td><td>A booking was confirmed.</td></tr>
        <tr><td>Inquiry</td><td>Lost</td><td>Client said no — record the reason.</td></tr>
        <tr><td>Booking</td><td>Tentative</td><td>Holding space; not yet confirmed.</td></tr>
        <tr><td>Booking</td><td>Confirmed</td><td>Client confirmed and deposit agreed.</td></tr>
        <tr><td>Booking</td><td>In Progress</td><td>Travel is happening.</td></tr>
        <tr><td>Booking</td><td>Completed</td><td>Travel finished; revenue counts.</td></tr>
        <tr><td>Booking</td><td>Cancelled</td><td>Do not count as sold.</td></tr>
        <tr><td>Follow-up</td><td>Pending</td><td>Still to do.</td></tr>
        <tr><td>Follow-up</td><td>Completed</td><td>Done — set the completed date.</td></tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>How the numbers are calculated</h2>
    <table class="grid small">
      <tbody>
        <tr><td><b>Weighted pipeline</b></td><td>Sum of estimated value × probability for every opportunity (all stages), converted to <?= e($base_cur) ?>.</td></tr>
        <tr><td><b>Win rate</b></td><td>Won inquiries ÷ all inquiries. This is the Excel definition, not won ÷ closed.</td></tr>
        <tr><td><b>Gross profit</b></td><td>Actual revenue − direct cost.</td></tr>
        <tr><td><b>Gross margin</b></td><td>Gross profit ÷ actual revenue.</td></tr>
        <tr><td><b>Outstanding</b></td><td>Booking value − payments received, in base currency.</td></tr>
        <tr><td><b>Overdue invoice</b></td><td>Nothing received, payment due date passed, booking not tentative/cancelled.</td></tr>
        <tr><td><b>Cash collection coverage</b></td><td>Paid milestones ÷ all milestones on non-cancelled bookings.</td></tr>
        <tr><td><b>Achievement</b></td><td>Actual revenue ÷ target revenue for the month and salesperson.</td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h2>Daily / weekly routine</h2>
  <div class="two">
    <div>
      <h3>Every day</h3>
      <ul>
        <li>Open the <a href="index.php?page=dashboard">Dashboard</a> and clear the red follow-ups.</li>
        <li>Log new inquiries the moment they arrive.</li>
        <li>Record payments on the day the money lands.</li>
        <li>Move pipeline stages and probabilities forward.</li>
      </ul>
    </div>
    <div>
      <h3>Every week</h3>
      <ul>
        <li>Review <a href="index.php?page=followups">Follow-Ups</a> and the “due this week” list.</li>
        <li>Chase overdue invoices and expiring quotes.</li>
        <li>Confirm or release tentative bookings.</li>
        <li>Review <a href="index.php?page=reports">Reports</a> with the team and update targets.</li>
      </ul>
    </div>
  </div>
  <p class="hint">Tip: use the <b>Settings → Dropdown Lists</b> page to add your own lead sources, client types, payment methods and lost reasons. They appear in every form immediately.</p>
</div>
