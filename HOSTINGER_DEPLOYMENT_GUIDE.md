# Hostinger Premium Web Hosting Deployment Guide
## RedMaple Invoice Generator — Commercial Multi-Tenant SaaS Platform

This production deployment guide contains exact, step-by-step instructions for installing and running **RedMaple Invoice Generator** on **Hostinger Premium Web Hosting** (or Hostinger Cloud/Business Hosting).

---

### 1. Hostinger Environment Requirements & hPanel Configuration

1. Log in to your **Hostinger hPanel**.
2. Navigate to **Advanced > PHP Configuration**:
   - **PHP Version**: Select `PHP 8.2` or `PHP 8.3` (both fully supported).
   - **PHP Extensions**: Verify the following are checked/enabled:
     - `bcmath` (required for integer monetary precision)
     - `curl` (for Stripe & PayPal API communications)
     - `fileinfo` (for PDF generation and MIME detection)
     - `mbstring` (for multilingual character encoding & RTL support)
     - `openssl` (for AES-256 tenant credential encryption)
     - `pdo_mysql` (for database connectivity)
     - `gd` or `imagick` (for logo processing)
3. Navigate to **Advanced > Cache Manager**:
   - Ensure **Automatic Cache** is either disabled or governed by our supplied `.htaccess` LiteSpeed rules (to prevent dynamic invoicing calculations from being cached).

---

### 2. File Architecture & Directory Placement

For maximum commercial security on shared hosting, the Laravel core code (`app/`, `database/`, `.env`, `storage/`) must be stored **above** or **outside** the public web root (`public_html/`).

#### Option A: Directory Structure (Recommended by Hostinger)
1. In Hostinger File Manager or via SSH, upload the project archive to:
   ```text
   /home/u123456789/domains/yourdomain.com/invoice-saas/
   ```
2. Move the contents of `public/` directly into your web root:
   ```text
   /home/u123456789/domains/yourdomain.com/public_html/
   ```
3. Update `public_html/index.php` paths to point to the parent app directory:
   ```php
   require __DIR__.'/../invoice-saas/vendor/autoload.php';
   require_once __DIR__.'/../invoice-saas/bootstrap/app.php';
   ```

#### Option B: Single-Directory with Root `.htaccess` (Zero Configuration)
If deploying directly into `public_html/`:
- Upload the entire project into `public_html/`.
- The included `.htaccess` file automatically routes all web requests to `/public/` while strictly blocking direct browser access to `.env`, `composer.json`, and database directories.

---

### 3. Database Creation & Environment (.env) Setup

1. In hPanel, go to **Databases > Management**:
   - Create a new MySQL database (e.g. `u123456789_invoices`).
   - Create a database user and generate a strong password.
2. In the project root, copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
3. Edit `.env` with your Hostinger database and application credentials:
   ```dotenv
   APP_NAME="RedMaple Invoice Generator"
   APP_ENV=production
   APP_KEY=base64:... # Generate via 'php artisan key:generate'
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=u123456789_invoices
   DB_USERNAME=u123456789_user
   DB_PASSWORD=YourDatabasePasswordHere

   # Hostinger Free Business Email (SMTP)
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.hostinger.com
   MAIL_PORT=465
   MAIL_USERNAME=billing@yourdomain.com
   MAIL_PASSWORD=YourEmailPasswordHere
   MAIL_ENCRYPTION=ssl
   MAIL_FROM_ADDRESS="billing@yourdomain.com"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

---

### 4. Running Migrations & Seeding Initial Data

Run the database setup via Hostinger SSH Terminal or the hPanel Web Terminal:

```bash
# 1. Install Composer dependencies (if not uploaded pre-packaged)
composer install --no-dev --optimize-autoloader

# 2. Generate Application Encryption Key
php artisan key:generate

# 3. Run database migrations with predefined seeders
php artisan migrate --seed --force

# 4. Cache configurations and routes for maximum performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Pre-seeded Default Accounts & Data:
- **Default Admin Account**:
  - **Email**: `admin@redmaplestudio.com` (or `admin@apexstudios.com`)
  - **Password**: `Password123!`
  - **Workspace**: RedMaple Digital Studio (Pro Plan active)
- **Pre-loaded Industry Presets**:
  - Web Development & Hosting (5 services)
  - Digital Marketing & SMM (4 services)
  - Embroidery Digitizing & Vector Art (4 services)
  - Graphic Design & Brand Identity (3 services)
  - Consulting & Accounting (2 services)
- **Pre-configured Launch Coupons**:
  - `LAUNCH2026` (20% off recurring)
  - `HOSTINGER50` (50% off first month)
  - `SAVE10FIXED` ($10 flat discount)

---

### 5. Configuring Hostinger Cron Jobs (Automated Billing & Reminders)

Hostinger Premium hosting includes full Scheduled Tasks (Cron) support.

1. In hPanel, navigate to **Advanced > Cron Jobs**.
2. Select **Custom** schedule.
3. Set the interval to every minute:
   ```text
   * * * * *
   ```
4. Set the command line to your PHP binary path:
   ```bash
   /usr/bin/php /home/u123456789/domains/yourdomain.com/invoice-saas/artisan schedule:run >> /dev/null 2>&1
   ```
5. Click **Save**.

---

### 6. Stripe & PayPal Dual Gateway Setup

#### Stripe Integration:
1. In your **Stripe Dashboard > Developers > API Keys**:
   - Set `STRIPE_KEY` and `STRIPE_SECRET` in `.env` (or via Workspace Settings).
2. Under **Webhooks**, add endpoint:
   - **URL**: `https://yourdomain.com/webhooks/stripe`
   - **Events to listen for**:
     - `invoice.payment_succeeded`
     - `customer.subscription.updated`
     - `customer.subscription.deleted`

#### PayPal Integration:
1. In your **PayPal Developer Dashboard**:
   - Create a REST API app for Subscriptions and Payments.
   - Set `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET`.
2. Under **Webhooks**, add endpoint:
   - **URL**: `https://yourdomain.com/webhooks/paypal`
   - **Events to listen for**:
     - `PAYMENT.SALE.COMPLETED`
     - `BILLING.SUBSCRIPTION.CANCELLED`

---

### 7. PDF Security & Password Protection Architecture

- **Private Storage**: Invoices and generated PDF files are saved to `storage/app/invoices/{workspace_id}/` which cannot be accessed directly via any HTTP URL.
- **Signed URL Access**: Invoices are delivered only via temporary signed URLs with expiration checks.
- **Password Protection**: When "Password Protect Customer PDF" is enabled on an invoice, the recipient is prompted with a secure password challenge screen (`/invoices/p/{token}/pdf-unlock`). The password is validated via bcrypt hashing before granting access to the file stream.
