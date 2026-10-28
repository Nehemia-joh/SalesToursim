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
/home/nehemia/Documents/MichaelTourism/.envSalesTourism      (or .env)
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
```

> The loader checks `.envSalesTourism` first, then `.env`, then `WebVersion/.env`.

## 3. Create the database and import the schema

```bash
mysql -u USER -p -e "CREATE DATABASE my_db_name CHARACTER SET utf8mb4"
mysql -u USER -p my_db_name < database/schema.sql
mysql -u USER -p my_db_name < database/seed.sql     # optional: demo data
```

`seed.sql` re-creates the same demo dataset used in the Excel workbook
(Aug–Oct 2026, 45 inquiries, 20 bookings, $248,800 booking value,
$206,900 actual revenue, $294,762.50 weighted pipeline, etc.).

## 4. Run

```bash
php -S localhost:8000 -t /path/to/WebVersion/public
```

Then open <http://localhost:8000>.

### Deploy from GitHub to cPanel (live)

The Git repository root is this `WebVersion` folder. `.cpanel.yml` deploys:

```
$HOME/michaeltourism/
  .env                         <- create manually (NOT in Git)
  .env.example                 <- deployed from the repository
  WebVersion/
    app/                       <- private application code
    public/                    <- domain document root
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

`.env` is ignored by `.gitignore`; never commit it.

#### 2. Create the database in cPanel

1. cPanel → **MySQL Databases**: create a database and a user.
2. Add the user to the database with **ALL PRIVILEGES**.
3. Note the full names — cPanel prefixes them, e.g. `cpuser_sales` and `cpuser_salesuser`.
4. Open **phpMyAdmin**, select the database, then run/import `database/schema.sql`.
5. Optional: run/import `database/seed.sql` to load the demo data and demo login.

If you import only `schema.sql`, run `database/create_admin.sql` next (change the email and password in that file first) so you can log in.

#### 3. Create `.env` on the server

Use cPanel **File Manager** (or **Terminal**):

```bash
mkdir -p ~/michaeltourism
cp ~/repositories/YOUR-REPO/.env.example ~/michaeltourism/.env
chmod 600 ~/michaeltourism/.env
```

Edit `~/michaeltourism/.env` and fill in the real values:

```ini
APP_TIMEZONE=Africa/Dar_es_Salaam
BASE_CURRENCY=USD
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cpuser_sales
DB_USER=cpuser_salesuser
DB_PASS=your-real-password
APP_KEY=replace-with-openssl-rand-hex-32
```

The file must sit **one level above** `WebVersion`, not inside the repository.

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

`.cpanel.yml` copies `app/`, `public/`, `database/`, `README.md`, and `.env.example` into `~/michaeltourism/WebVersion`.

#### 5. Point the domain at `public`

cPanel → **Domains** (or **Subdomains**): set the document root to:

```
/home/YOUR-CPANEL-USER/michaeltourism/WebVersion/public
```

If your host does not allow a document root outside `public_html`, place the app under `public_html` and adjust `DEPLOYPATH` in `.cpanel.yml`.

#### 6. First login

Open `https://your-domain.com`.

If you imported `seed.sql`, log in with `admin@michaeltourism.com` / `demo1234`, then immediately change the password and delete the demo users. If you imported only `schema.sql`, create the first admin user first.

#### Troubleshooting

- **Deployment fails:** check that `app/` exists at the repository root; `.cpanel.yml` expects the repo root to be `WebVersion`.
- **500 error:** check `~/michaeltourism/.env` and the cPanel PHP error log.
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
| `app/Env.php`             | Reads `.envSalesTourism` |
| `app/DB.php`              | PDO helpers |
| `app/Auth.php`            | Login/session (sha256 salt+hash) |
| `app/modules.php`         | Module definitions (drives all list/add/edit screens) |
| `app/controllers/`        | Dashboard, Reports, Settings, Users + generic CRUD |
| `app/views/`              | HTML templates |
| `database/schema.sql`     | All tables |
| `database/gen_seed.py`    | Regenerates `seed.sql` (needs Python 3) |

## 6. Modules (mirror of the Excel sheets)

Dashboard · Client Database · Inquiry Log · Sales Pipeline (weighted) ·
Quotation Tracker · Follow-Up Manager · Bookings · Payment Schedule ·
Payments · Sales Targets · Activity Log · Management Reports · Settings &
Lists · Users.

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
3. Send → *Quotation Tracker* (Valid-Until expires automatically)
4. Follow-ups → *Follow-Up Manager* (overdues turn red)
5. Accepted → *Bookings* → set *Payment Schedule* milestones
6. Collect money → *Payments* (status/payment KPIs update everywhere)
7. Review → *Dashboard* & *Reports*; set monthly *Sales Targets* for the leaderboard
8. Need a refresher? Open **Management → How to use** for the full data-entry guide
