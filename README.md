# GFS Dashboard

Internal management dashboard for **Gundaling Farmstead** — built on Laravel 12. Provides real-time sales analytics, cost monitoring, production tracking, purchasing, inventory, and a staff helpdesk, all sourced from the POS database.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 / PHP 8.2+ |
| Frontend | Alpine.js v3, Tailwind CSS (via Vite) |
| Charts | Chart.js 4.4.4 (CDN) |
| Excel export | PhpOffice/PhpSpreadsheet v5.7 |
| Log viewer | opcodesio/log-viewer v3 |
| Mail | Office 365 SMTP (smtp.office365.com) |
| Database (app) | MySQL — `db_gfs_dashboard` |
| Database (reports) | MySQL — `db_gundaling` (POS, read-only) |
| Timezone | Asia/Jakarta (WIB) |

---

## Requirements

- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `gd`, `zip`, `openssl`
- MySQL 8.0+
- Node.js 18+ / npm
- Composer 2

---

## Installation

### 1. Clone and install dependencies

```bash
git clone <repo-url> gfs-dashboard-laravel
cd gfs-dashboard-laravel
composer install
npm install
```

### 2. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — see [Environment Variables](#environment-variables) below.

### 3. Migrate the app database

```bash
php artisan migrate
```

### 4. Build frontend assets

```bash
npm run build
# or for development:
npm run dev
```

### 5. Start the server

```bash
php artisan serve
```

### 6. Set up the scheduler (production)

Add to crontab:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Environment Variables

```dotenv
APP_NAME="GFS Dashboard"
APP_URL=http://127.0.0.1:8000

# App database (read/write)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_gfs_dashboard
DB_USERNAME=gfs_app
DB_PASSWORD=<password>

# POS reporting database (read-only)
DB_REPORTS_HOST=127.0.0.1
DB_REPORTS_PORT=3306
DB_REPORTS_DATABASE=db_gundaling
DB_REPORTS_USERNAME=gfs_read
DB_REPORTS_PASSWORD=<password>

# Mail — Office 365
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=noreply@pimsgundaling.com
MAIL_PASSWORD=<password>
MAIL_FROM_ADDRESS=noreply@pimsgundaling.com
MAIL_FROM_NAME="PIMS Gundaling"

# Helpdesk: copy of every new ticket goes to this address
SUPPORT_NOTIFY_EMAIL=support@pimsgundaling.com

# Daily cost alert (comma-separated for multiple recipients)
COST_ALERT_EMAIL=support@pimsgundaling.com
COST_THRESHOLD=45          # overall day cost % that triggers alert
ITEM_COST_THRESHOLD=95     # per-item cost % listed in alert email
```

---

## Database Architecture

Two separate MySQL connections are used:

| Connection key | Database | Purpose |
|---|---|---|
| `mysql` (default) | `db_gfs_dashboard` | App data: staff, tickets, page logs, cache, jobs |
| `reports_mysql` | `db_gundaling` | POS data: orders, inventory, production (read-only) |

All report controllers query `reports_mysql`. The app database is managed by Laravel migrations.

---

## Authentication & Security

The app uses **session-based staff authentication** — not Laravel's built-in Auth system.

- Login at `/login` (throttled to 5 attempts per minute)
- Session stores `staff_user_id`; middleware `StaffAuth` validates it on every request
- Logout via `POST /logout`
- Passwords are bcrypt-hashed; change password at `/settings/change-password`
- Route-level access is controlled by `CheckRoutePermission` middleware
- Permission assignments are managed in `/settings/security`
- Log Viewer (`/log-viewer`) is gated behind the `log-viewer.index` permission

### Staff roles & permissions

Staff users are managed in `/settings/staff`. Each user has a set of named permissions mapped to route groups. New users have no permissions until explicitly granted.

---

## Reports

All reports support **date range filtering** and **Excel export** (`.xlsx`). Reports query the read-only POS database.

### Sales Operations

| Report | Route | Description |
|---|---|---|
| Sales Transactions | `/sales` | All invoices with receipt viewer and line-item detail |
| Item Sales | `/item-sales` | Sales grouped by item, department, and category |
| Summary Sales | `/summary-sales` | Daily/period revenue and covers summary |
| No Sales | `/no-sales` | Days or sessions with zero sales |
| Void Report | `/reports/void` | All voided transactions |
| No-Sales Receipt Detail | `/reports/no-sales-receipt-detail` | Receipt-level breakdown for no-sale sessions |

### Daily Reports

| Report | Route | Description |
|---|---|---|
| Daily Category | `/reports/daily-category` | Revenue by category per day |
| Daily Hour | `/reports/daily-hour` | Hourly sales breakdown |
| Cashier Shift | `/reports/cashier-shift` | Shift-level sales by cashier |
| Opening Day | `/reports/opening-day` | Opening and closing times per outlet per day |

### Menu & Recipes

| Report | Route | Description |
|---|---|---|
| Recipe Report | `/reports/recipe` | Full recipe cost breakdown by item |
| Recipe Board | `/reports/recipe-board` | Plated recipe view for kitchen reference |
| Order Board | `/reports/order-board` | Live order board grouped by department |
| Market List | `/reports/market-list` | Purchasing list derived from recipes and quantities |

### Production

| Report | Route | Description |
|---|---|---|
| Production Summary | `/reports/production-summary` | Batch production totals by item |
| Production Card | `/reports/production-card` | Individual batch/production card detail |

### Purchasing

| Report | Route | Description |
|---|---|---|
| Purchase Summary | `/reports/purchase-summary` | Purchase totals by supplier and category |
| Purchase Detail | `/reports/purchase-detail` | Line-item purchase transactions |
| Purchase Detail by Partner | `/reports/purchase-detail-partner` | Purchases filtered by supplier/partner |

### Inventory

| Report | Route | Description |
|---|---|---|
| Consumption (Warehouse) | `/reports/consumption-warehouse` | Ingredient consumption from warehouse issues |
| Consumption (Invoice Detail) | `/reports/consumption-detail-invoice` | Consumption broken down by invoice |
| Physical Stock Count | `/reports/physical-stock-count-summary` | Stocktake results vs. system quantities |
| Transfer Detail | `/reports/transfer-detail` | Inter-outlet/warehouse stock transfers |
| Waste Summary | `/reports/waste-summary` | Recorded waste by item and department |

### Analytics

| Report | Route | Description |
|---|---|---|
| Sales Forecast | `/reports/sales-forecast` | Time-series forecast using historical moving average |

---

## Dashboard

Route: `/dashboard`

Displays KPI cards (revenue, covers, average spend, orders), a daily sales trend chart, hourly sales distribution, breakdown by department and category, payment method split, and outlet comparison — all for the selected date range via Chart.js 4.4.4.

---

## Helpdesk / Ticketing System

Anyone (including guests) can submit a support ticket. Staff manage tickets from the internal portal.

### Public (no login required)

| Action | Route |
|---|---|
| Submit a ticket | `GET/POST /tickets/create` |
| Check ticket status by reference number | `GET/POST /tickets/status` |

### Internal staff portal (requires permission)

| Action | Route |
|---|---|
| Ticket list | `/support/tickets` |
| Ticket detail + reply | `/support/tickets/{id}` |
| Update status | `PATCH /support/tickets/{id}/status` |
| Assign to staff member | `PATCH /support/tickets/{id}/assign` |

**Email notifications sent:**

- To `SUPPORT_NOTIFY_EMAIL` when a new ticket is submitted
- To the assigned staff member when a ticket is assigned to them
- To the ticket submitter (if email provided) when a reply is posted

---

## Daily Cost Alert System

An automated artisan command checks whether the End of Day (EOD) has been closed and, if the overall food cost percentage exceeds the configured threshold, sends an alert email.

**Schedule:** every 30 minutes between 18:00–23:59 WIB.

**Logic:**

1. Check cache — if alert already sent today, stop.
2. Query `tbl_daily_procedures` — if EOD not yet closed, stop.
3. Calculate `SUM(quantity × unitCost) / SUM(quantity × unitPrice) × 100` from `v_order_index`.
4. If cost % ≤ threshold, mark as done, stop.
5. Find all items where cost % > `ITEM_COST_THRESHOLD`, ordered worst first.
6. Send alert email to `COST_ALERT_EMAIL` with KPI summary and item table.
7. Cache result to prevent duplicate sends.

**Manual usage:**

```bash
# Check today
php artisan cost:check-daily

# Check a specific date
php artisan cost:check-daily --date=2026-05-30

# Force resend even if already sent today
php artisan cost:check-daily --force
```

---

## Activity Logging

Every authenticated page visit is logged to the `user_page_logs` table via `LogPageVisit` terminable middleware. Logs include staff user ID, route name, URL, HTTP method, status code, IP address, and timestamp.

Page log viewer available to admins at `/settings/page-logs`.

---

## Settings

| Page | Route | Description |
|---|---|---|
| Staff management | `/settings/staff` | Create, edit, and deactivate staff accounts |
| Security / permissions | `/settings/security` | Grant and revoke route permissions per staff member |
| Change password | `/settings/change-password` | Self-service password change (all authenticated users) |
| Page activity log | `/settings/page-logs` | View page visit history (admin permission required) |
| Log viewer | `/log-viewer` | Laravel application log viewer (admin permission required) |

---

## Artisan Commands

```bash
# Daily cost check (usually run by scheduler)
php artisan cost:check-daily [--date=YYYY-MM-DD] [--force]

# Clear caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Migrations
php artisan migrate
php artisan migrate:status

# Run scheduler manually (for testing)
php artisan schedule:run
```

---

## Global Toast Notifications

Flash any of these from a controller redirect and the notification appears automatically:

```php
return redirect()->back()->with('success', 'Saved successfully.');
return redirect()->back()->with('error', 'Something went wrong.');
return redirect()->back()->with('warning', 'Please review before continuing.');
return redirect()->back()->with('info', 'Record updated.');
```

JavaScript API also available in the browser console:

```js
window.toast.success('Done!');
window.toast.error('Failed.');
window.toast.warning('Check this.');
window.toast.info('FYI.');
```

---

## Default Login

| Field | Value |
|---|---|
| Username | `admin` |
| Password | `admin123` |

Change the admin password immediately after first login via `/settings/change-password`.

---

## License

Internal proprietary software — Gundaling Farmstead / PIMS. Not for redistribution.
