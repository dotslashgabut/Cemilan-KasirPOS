# Security Audit Report - PHP Backend

## Overview
This document outlines the security audit findings for the PHP backend of the Cemilan KasirPOS application. The audit focuses on code analysis, configuration review, and potential vulnerabilities.

**Last Updated:** 2025-11-27

## Status Summary

| ID | Finding | Severity | Status |
|----|---------|----------|--------|
| 1 | Hardcoded Credentials | **High** | 🔴 Open |
| 2 | Sensitive Data Exposure (Logs) | **High** | 🟢 Resolved |
| 3 | Rate Limiting Race Condition | **Medium** | 🟢 Resolved |
| 4 | CORS Configuration | **Medium** | 🔴 Open |
| 5 | Input Sanitization & XSS | **Medium** | 🟡 Partially Mitigated |
| 6 | Legacy Password Support | **Low** | 🟢 Resolved |
| 7 | File Permissions | **Medium** | 🟢 Resolved |
| 8 | HTTPS Enforcement | **Medium** | 🔴 Open |

## Detailed Findings

### 1. Hardcoded Credentials
- **Severity**: **High**
- **Description**: 
    - Database credentials have default fallbacks in `config.php` (root/empty).
    - JWT secret has a hardcoded fallback in `auth.php`.
- **Recommendation**: 
    - Ensure `.env` is used in production.
    - Remove hardcoded fallbacks in production code or ensure they are never reached.
    - **Critical**: Change the default JWT secret in production.

### 2. Sensitive Data Exposure & Logging
- **Severity**: **High**
- **Status**: **Resolved**
- **Resolution**: 
    - `logic.php` now logs only IDs or errors, avoiding full PII payloads.
    - `.htaccess` now denies access to `.log` and `.json` files.

### 3. Rate Limiting Race Condition
- **Severity**: **Medium**
- **Status**: **Resolved**
- **Resolution**: 
    - `rate_limit.php` now uses `flock()` to ensure exclusive access to the JSON file during writes.

### 4. CORS Configuration
- **Severity**: **Medium** (Production)
- **Description**: 
    - `config.php` sets `Access-Control-Allow-Origin` to `*` or reflects the request origin if not specified.
- **Recommendation**: 
    - In production, restrict `Access-Control-Allow-Origin` to the specific domain of the frontend application.

### 5. Input Sanitization & XSS
- **Severity**: **Medium**
- **Description**: 
    - `index.php` uses `strip_tags` for sanitization. This is not a complete defense against XSS.
    - `auth.php` comments suggest storing JWT in `localStorage`, which is vulnerable to XSS.
- **Recommendation**: 
    - Use `htmlspecialchars` when outputting user-generated content (though this is an API, so the frontend is responsible for rendering).
    - Validate input strictly (partially covered by `validator.php`).
    - Consider using `HttpOnly` cookies for JWT storage to mitigate XSS token theft.

### 6. Legacy Password Support
- **Severity**: **Low**
- **Status**: **Resolved**
- **Resolution**: 
    - Plaintext password fallback has been removed from `login.php`. All passwords must be hashed.

### 7. File Permissions & Structure
- **Severity**: **Medium**
- **Status**: **Resolved**
- **Resolution**: 
    - `.htaccess` is configured to deny access to sensitive files (`.log`, `.json`, `.env`).

### 8. HTTPS Enforcement
- **Severity**: **Medium**
- **Description**:
    - HSTS (HTTP Strict Transport Security) header is currently commented out in `config.php`.
- **Recommendation**:
    - Uncomment the HSTS header in `config.php` when deploying to a production environment with SSL enabled.

## Action Plan
1.  **Environment Variables**: Ensure production server has `JWT_SECRET`, `DB_USER`, `DB_PASS` set in environment or a secure `.env` file. (User Action Required)
2.  **Review CORS**: Update `config.php` to use a specific allowed origin in production. (User Action Required)
3.  **Disable Legacy Auth**: After a transition period, remove the plain text password fallback in `login.php`.
4.  **Enable HTTPS**: Uncomment HSTS in `config.php` after SSL is configured.
