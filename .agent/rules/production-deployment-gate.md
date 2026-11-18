---
description: Mandatory Staging and Production Deployment Gate Policy
globs: *
---
# Mandatory Production Deployment Gate Policy

## 1. Staging-First Deployment
- Every code change, feature, hotfix, template adjustment, or configuration update **MUST** be deployed to the **Staging environment** (`http://staging.redmapleagency.com`) first using `deploy_staging.ps1`.
- Direct unverified deployments to Production are strictly prohibited.

## 2. QA & Verification Requirement
- All changes must undergo full functional testing and QA review on Staging before any production discussion.

## 3. Explicit User Confirmation Gate for Production
- Code will **NEVER** move to Production (`https://invoicy.redmapleagency.com`) automatically.
- Production promotion (`promote_to_production.ps1`) is only executed when:
  1. The user explicitly instructs to deploy to Production, OR
  2. The assistant presents the verified staging results and explicitly asks the user for confirmation, and the user approves.
