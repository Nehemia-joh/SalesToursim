# Michael Tourism — Sales Management System (Web Version)

A plain-PHP web edition of the Excel *Sales Management System – Tourism* workbook.
No frameworks, no composer — just PHP 8 + MySQL/MariaDB.

Versions of the system:
- **Excel edition:** `../Sales_Management_System_Tourism.xlsx` (demo) · `..._BLANK.xlsx` (production start)
- **Web edition:** this folder

---

## 1. Requirements

- PHP 8.1+ (`pdo_mysql` extension enabled — it is by default in PHP 8)
- MySQL or MariaDB

Check quickly: `php -m | grep pdo_mysql`

## 2. Credentials file (IMPORTANT)

The app reads its credentials from the file **one level above this folder**:

```
/home/nehemia/Documents/MichaelTourism/.envWebVersion
```

Required keys:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_mysql_user
DB_PASS=your_mysql_password
BASE_CURRENCY=USD
APP_KEY=any-long-random-string
APP_URL=http://localhost:8000
```

> The loader checks `.envWebVersion` first; legacy `.envSalesTourism` / `.env` names are still supported.
> `APP_URL` is used when the app builds the public proposal link. On the live site set it to `https://salestourism.nexabie.co.tz`.

## 3. Create the database and import the schema

```bash
mysql -u USER -p -e "CREATE DATABASE my_db_name CHARACTER SET utf8mb4"
mysql -u USER -p my_db_name < database/schema.sql
mysql -u USER -p my_db_name < database/seed.sql     # optional: demo data
```

If the database was created from the original schema (before the proposal/traveler upgrade), run the Phase 1 migration once:

```bash
mysql -u USER -p my_db_name < database/phase1_quotes_travelers.sql
```

If the database already existed before partial saves were added, also run Phase 2 once:

```bash
mysql -u USER -p my_db_name < database/phase2_partial_saves.sql
```

Fresh installs do **not** need either migration: `schema.sql` already contains the new columns, tables and `partial_saves`.

`seed.sql` re-creates the same demo dataset used in the Excel workbook
(Aug–Oct 2026, 45 inquiries, 20 bookings, $248,800 booking value,
$206,900 actual revenue, $294,762.50 weighted pipeline, etc.).

## 4. Run

```bash
php -S localhost:8000 -t /path/to/WebVersion/public /path/to/WebVersion/public/router.php
```

Then open <http://localhost:8000>. The router gives clean URLs:

```
/                    dashboard
/clients             client list
/clients/new         new client
/clients/5/edit      edit client 5
/quotations/5/build  quote builder
/proposal/<token>    public proposal
```

The old `index.php?page=...` URLs still work, so bookmarks do not break. On
Apache/LiteSpeed the included `public/.htaccess` rewrites non-file requests to
`public/router.php`.

### Partial saves

Long forms (all module records and the quote builder) autosave unsaved work as a
partial save. The draft is kept per user and restored after the next sign-in, so
a user can sign out and continue where they stopped. Use the **Save partial**
button to save immediately, or **Discard partial save** to start from the saved
record. Drafts are removed automatically after a successful save and expire
after 30 days.

### Deploy from GitHub to cPanel (live)

The Git repository root is this `WebVersion` folder. `.cpanel.yml` deploys:

```
$HOME/salestourism.nexabie.co.tz/
  .envWebVersion                <- create manually (NOT in Git)
  .envWebVersion.example        <- deployed from the repository
  app/                          <- private application code
  public/                       <- subdomain document root
  database/
```

#### 1. Push this folder to GitHub

```bash
cd WebVersion
git init -b main
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR-USER/YOUR-REPO.git
git push -u origin main
```

`.envWebVersion` is ignored by `.gitignore`; never commit it.

#### 2. Create the database in cPanel

1. cPanel → **MySQL Databases**: create a database and a user.
2. Add the user to the database with **ALL PRIVILEGES**.
3. Note the full names — cPanel prefixes them, e.g. `cpuser_sales` and `cpuser_salesuser`.
4. Open **phpMyAdmin**, select the database, then run/import `database/schema.sql`.
5. Optional: run/import `database/seed.sql` to load the demo data and demo login.
6. If this database already existed before the quote-builder upgrade, do **not** re-import `schema.sql`; import `database/phase1_quotes_travelers.sql` once instead.
7. For an existing database that predates partial saves, also import `database/phase2_partial_saves.sql` once.

If you import only `schema.sql`, run `database/create_admin.sql` next (change the email and password in that file first) so you can log in.

#### 3. Create `.envWebVersion` on the server

Use cPanel **File Manager** (or **Terminal**):

```bash
mkdir -p ~/salestourism.nexabie.co.tz
cp ~/repositories/YOUR-REPO/.envWebVersion.example ~/salestourism.nexabie.co.tz/.envWebVersion
chmod 600 ~/salestourism.nexabie.co.tz/.envWebVersion
```

Edit `~/salestourism.nexabie.co.tz/.envWebVersion` and fill in the real values:

```ini
APP_TIMEZONE=Africa/Dar_es_Salaam
BASE_CURRENCY=USD
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cpuser_sales
DB_USER=cpuser_salesuser
DB_PASS=your-real-password
APP_KEY=replace-with-openssl-rand-hex-32
APP_URL=https://salestourism.nexabie.co.tz
```

The file must sit outside `public/` — in the app folder next to `app/` and `public/`.

#### 4. Connect cPanel to GitHub

cPanel → **Files → Git Version Control → Create**:

- **Clone URL:** `https://github.com/YOUR-USER/YOUR-REPO.git`
- **Repository Path:** `~/repositories/michaeltourism`
- **Repository Name:** `michaeltourism`

For a private repository, set up a GitHub personal access token or deploy key first.

After creating the repository:

1. Open **Manage → Pull or Deploy**.
2. Click **Update from Remote**.
3. Click **Deploy HEAD Commit**.

`.cpanel.yml` copies `app/`, `public/`, `database/`, `README.md`, and `.envWebVersion.example` into `~/salestourism.nexabie.co.tz`.

#### 5. Point the subdomain at `public`

cPanel → **Domains** (or **Subdomains**): set the document root to:

```
/home/YOUR-CPANEL-USER/salestourism.nexabie.co.tz/public
```

If your host does not allow a document root outside `public_html`, place the app under `public_html` and adjust `DEPLOYPATH` in `.cpanel.yml`.

#### 6. First login

Open `https://salestourism.nexabie.co.tz`.

If you imported `seed.sql`, log in with `admin@michaeltourism.com` / `demo1234`, then immediately change the password and delete the demo users. If you imported only `schema.sql`, create the first admin user first.

#### Troubleshooting

- **Deployment fails:** check that `app/` exists at the repository root; `.cpanel.yml` expects the repo root to be `WebVersion`.
- **500 error:** check `~/salestourism.nexabie.co.tz/.envWebVersion` and the cPanel PHP error log.
- **Blank page:** ensure PHP 8.1+ and the `pdo_mysql` extension are enabled.
- **Wrong folder:** update `DEPLOYPATH` and `ENVPATH` at the top of `.cpanel.yml`.

**Demo logins** (from seed.sql — change immediately in production):

| Email                                | Role    | Password  |
|--------------------------------------|---------|-----------|
| admin@michaeltourism.com             | admin   | demo1234  |
| kelvin.mtui@michaeltourism.com       | manager | demo1234  |
| amina.hassan@michaeltourism.com      | sales   | demo1234  |
| joseph.mkumbo@michaeltourism.com     | sales   | demo1234  |
| grace.mushi@michaeltourism.com       | sales   | demo1234  |

## 5. What's inside

| Path                      | Purpose |
|---------------------------|---------|
| `public/index.php`        | Front controller (all URLs go through it) |
| `public/assets/style.css` | Styling |
| `app/Env.php`             | Reads `.envWebVersion` (legacy names supported) |
| `app/DB.php`              | PDO helpers |
| `app/Auth.php`            | Login/session (sha256 salt+hash) |
| `app/modules.php`         | Module definitions (drives all list/add/edit screens) |
| `app/controllers/`        | Dashboard, Reports, Settings, Users, Quote Builder + generic CRUD |
| `app/controllers/quotes.php` | Quote builder, public proposal, view tracking and client response |
| `app/views/`              | HTML templates |
| `app/views/quote_builder.php` | Day-by-day itinerary and priced-item editor |
| `app/views/proposal.php`  | Client-facing public proposal and print/PDF layout |
| `database/schema.sql`     | All tables (fresh install) |
| `database/phase1_quotes_travelers.sql` | Upgrade script for existing databases |
| `database/phase2_partial_saves.sql` | Adds the partial-save table to existing databases |
| `database/gen_seed.py`    | Regenerates `seed.sql` (needs Python 3) |

## 6. Modules (mirror of the Excel sheets)

Dashboard · Client Database · Inquiry Log · Sales Pipeline (weighted) ·
Quotation Tracker + Quote Builder · Follow-Up Manager · Bookings · Travelers ·
Payment Schedule · Payments · Sales Targets · Activity Log · Content Library ·
Message Templates · Task Templates · Management Reports · Settings & Lists · Users.

### Quote-to-cash additions (Phase 1)

- **Quote Builder** — day-by-day itinerary, priced items (with optional extras), cover note, inclusions, exclusions and terms.
- **Public proposal** — a secure, no-login client link with branded print/PDF layout; the client can accept, request changes or decline online.
- **Proposal view tracking** — every public view is logged; the quote list and builder show view counts and the last view time.
- **Accepted-proposal automation** — accepting a proposal creates a tentative booking and a follow-up task, and links the booking back to the quote.
- **Traveler profiles** — passport, date of birth, nationality, dietary needs, allergies, medical notes, emergency contact and room preference per traveler.
- **Content library** — reusable inclusions, exclusions, terms, policies and destination notes, insertable from the Quote Builder.
- **Message templates** — reusable email/WhatsApp/SMS wording with automatic quote, client and amount placeholders.
- **Task templates** — standard follow-up actions by pipeline stage, due-in days and priority; click **Use** to create a pre-filled follow-up.
- **Running tours dashboard** — tours currently on the road and departures in the next 7 days, with pax and traveler-profile counts.

Business logic carried over from the workbook:

- **Payment status** (Bookings): Paid → Partially Paid → Unpaid/Overdue, per status rules
- **Weighted pipeline** = estimated value × probability %, across every opportunity
  (the dashboard also shows the open-only value as a subtitle)
- **Overdue follow-ups** exclude Completed/Cancelled and are flagged in the Dashboard Action Center
- **Achievement %** = actual revenue (Confirmed+Completed) ÷ monthly target
- **Gross profit / margin** = actual revenue − direct cost, and the resulting %
- **Win rate** = Won inquiries ÷ all inquiries (the Excel definition)
- **Cash collection coverage** = fully paid milestones ÷ all milestones
- **Outstanding** = booking value − payments received, in the base currency
- **Overdue invoices** = nothing received, payment due date passed, booking not Tentative/Cancelled
- **Currency layer**: every amount is stored in its own currency and converted
  to the base currency (`BASE_CURRENCY`) via the FX rates in *Settings*

The web edition adds the Excel columns that were previously missing: inquiry
contact/email/phone/country, client payments received and notes, booking gross
profit/margin, target new-inquiry and booking counts, payment-schedule variance,
and follow-up days overdue. The Dashboard now mirrors the workbook's action
centre, 12-month trend, ordered funnel and leaderboard totals, and Reports
contains the full Excel KPI list.

### Data entry help

Every screen has a **How to use** page (sidebar → Management → How to use)
with the same step-by-step order as the workbook. Useful shortcuts:

- Type a brand-new client directly on the inquiry form (no need to create the client first).
- Pick an inquiry on a quotation or booking and the client, tour, pax, owner and
  expected value are filled automatically when you save (the owner updates as
  soon as you choose the inquiry).
- New records default to today, the current month/year, the base currency and
  sensible statuses. Money fields accept negative amounts for refunds.

## 7. Roles

- **admin** – everything incl. Users
- **manager** – everything except Users; can delete records
- **sales** – add/edit operations, no deletes, no Settings/Users

## 8. Security notes for production

- Set a strong, unique `APP_KEY`
- Use real user accounts; delete the demo users or reset passwords (Users page)
- Serve over HTTPS when hosted (session cookie is HttpOnly, same-site Lax)
- MySQL user needs only: SELECT/INSERT/UPDATE/DELETE on the app database

## 9. Typical workflow (same as Excel edition)

1. New inquiry arrives → *Inquiry Log* (+ New)
2. Track opportunities → *Sales Pipeline*
3. Build and send → *Quotation Tracker → Build* (itinerary, priced items, public proposal link; Valid-Until expires automatically)
4. Follow-ups → *Follow-Up Manager* (overdues turn red)
5. Accepted → *Bookings* → set *Payment Schedule* milestones
6. Collect money → *Payments* (status/payment KPIs update everywhere)
7. Review → *Dashboard* & *Reports*; set monthly *Sales Targets* for the leaderboard
8. Need a refresher? Open **Management → How to use** for the full data-entry guide
