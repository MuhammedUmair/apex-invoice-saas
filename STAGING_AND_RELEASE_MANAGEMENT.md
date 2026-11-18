# Invoicy Staging Environment & Release Pipeline Architecture

## 1. Overview & Rationale
Because active clients and agencies are now actively using **Invoicy by RedMaple** on production (`https://invoicy.redmapleagency.com`), live testing on production is strictly prohibited. All new enhancements, UI updates, and bug fixes must follow the Staging -> Approval -> Production lifecycle.

---

## 2. Environment Architecture

```mermaid
flowchart LR
    Dev[Local Development / Hotfix] -->|deploy_staging.ps1| Staging[Staging Subdomain: staging.redmapleagency.com]
    Staging -->|Owner QA & Approval| Gate{Approved?}
    Gate -->|Yes: promote_to_production.ps1| Prod[Production: invoicy.redmapleagency.com]
    Gate -->|No| Fix[Revise in Local / Staging]
```

### Staging Specifications:
1. **Subdomain:** `staging.redmapleagency.com` (or `stage.redmapleagency.com`)
2. **Directory Isolation:**
   - Production Core: `/home/u858731124/domains/redmapleagency.com/redmaple_core`
   - Staging Core: `/home/u858731124/domains/redmapleagency.com/redmaple_stage`
   - Staging Public Web: `/home/u858731124/domains/staging.redmapleagency.com/public_html`
3. **Database Isolation:**
   - Production Database: `u858731124_invoicy` (prefix: `inv_`)
   - Staging Database: `u858731124_invoicy_stage` or separate prefix `stage_`
4. **Environment Variables (`.env.staging`):**
   - `APP_ENV=staging`
   - `APP_DEBUG=true`
   - `APP_URL=https://staging.redmapleagency.com`

---

## 3. Automation Scripts

### A. Deploy to Staging: `deploy_staging.ps1`
Runs local test checks, packages delta files, and pushes directly to the remote staging environment without touching production.

### B. Promote to Production: `promote_to_production.ps1`
Performs:
1. Pre-flight check: ensures git working directory is clean.
2. Creates an automated timestamped rollback snapshot of production.
3. Uploads verified staging build to production core.
4. Executes `php artisan optimize:clear` and re-caches configuration and routes.
5. Verifies live production health check endpoint (`/cp/login` and `/login`).
