# Security Audit Report

## Overview
This document outlines the security audit findings for the Cemilan KasirPOS application.
**Note:** The application fully utilizes the **PHP Native backend** as the primary server.

**Last Updated:** 2025-12-02 (Security Fixes Applied)

## Status Summary

| ID | Component | Finding | Severity | Status |
|----|-----------|---------|----------|--------|
| P1 | PHP | Hardcoded Credentials | **High** | 🟢 Resolved |
| P2 | PHP | Sensitive Data Exposure | **High** | 🟢 Resolved |
| P3 | PHP | Rate Limiting Race Condition | **Medium** | 🟢 Resolved |
| P4 | PHP | CORS Configuration | **Medium** | 🟢 Resolved |
| P5 | PHP | Input Sanitization & XSS | **Medium** | 🟡 Partially Mitigated |
| P6 | PHP | Legacy Password Support | **Low** | 🟢 Resolved |
| P7 | PHP | File Permissions | **Medium** | 🟢 Resolved |
| P8 | PHP | HTTPS Enforcement | **Medium** | 🟢 Resolved |
| P9 | PHP | Batch Insert Validation Bypass | **Medium** | 🟢 Resolved |
| P10 | PHP | Weak Randomness (UUID) | **Low** | 🟢 Resolved |

## Detailed Findings

### PHP Backend (Primary)

*These findings apply to the `php_server` directory.*

#### P1. Hardcoded Credentials
- **Severity**: **High**
- **Status**: **Resolved**
- **Resolution**: 
    - `auth.php` now prioritizes `getenv('JWT_SECRET')`.
    - If `JWT_SECRET` is missing in production (debug off), a critical error is logged.
    - Default fallback is only used for development.

#### P2. Sensitive Data Exposure & Logging
- **Severity**: **High**
- **Status**: **Resolved**
- **Resolution**: 
    - `logic.php` now logs only IDs or errors, avoiding full PII payloads.
    - `.htaccess` now denies access to `.log` and `.json` files.

#### P3. Rate Limiting Race Condition
- **Severity**: **Medium**
- **Status**: **Resolved**
- **Resolution**: 
    - `rate_limit.php` now uses `flock()` to ensure exclusive access to the JSON file during writes.

#### P4. CORS Configuration
- **Severity**: **Medium** (Production)
- **Status**: **Resolved**
- **Resolution**: 
    - `config.php` now checks for `ALLOWED_ORIGINS` environment variable.
    - If set, it only allows origins from that list.
    - Default `*` is used only if `ALLOWED_ORIGINS` is not set (Development fallback).

#### P5. Input Sanitization & XSS
- **Severity**: **Medium**
- **Description**: 
    - `index.php` uses `strip_tags` for sanitization. This is not a complete defense against XSS.
    - `auth.php` comments suggest storing JWT in `localStorage`, which is vulnerable to XSS.
- **Recommendation**: 
    - Use `htmlspecialchars` when outputting user-generated content (though this is an API, so the frontend is responsible for rendering).
    - Validate input strictly (partially covered by `validator.php`).
    - Consider using `HttpOnly` cookies for JWT storage to mitigate XSS token theft.

#### P6. Legacy Password Support
- **Severity**: **Low**
- **Status**: **Resolved**
- **Resolution**: 
    - Plaintext password fallback has been removed from `login.php`. All passwords must be hashed with bcrypt (starting with `$2`).

#### P7. File Permissions & Structure
- **Severity**: **Medium**
- **Status**: **Resolved**
- **Resolution**: 
    - `.htaccess` is configured to deny access to sensitive files (`.log`, `.json`, `.env`).

#### P8. HTTPS Enforcement
- **Severity**: **Medium**
- **Status**: **Resolved**
- **Resolution**:
    - HSTS header logic in `config.php` has been uncommented and improved to only activate when `HTTPS` is detected.

#### P9. Batch Insert Validation Bypass
- **Severity**: **Medium**
- **Status**: **Resolved**
- **Resolution**:
    - `index.php` now calls `validateInput($resource, $item)` inside the batch processing loop.
    - The entire batch process throws an exception if any item fails validation.

#### P10. Weak Randomness (UUID)
- **Severity**: **Low**
- **Status**: **Resolved**
- **Resolution**:
    - `logic.php` now uses `random_int()` instead of `mt_rand()` for UUID generation, providing cryptographically secure entropy.

## Action Plan

1.  **Environment Setup**: Ensure `.env` is properly configured in production with `JWT_SECRET` and `ALLOWED_ORIGINS`.
2.  **PHP Maintenance**: Maintain security updates for the PHP backend.
3.  **Future**: Consider implementing HttpOnly cookies for better XSS protection (P5).
