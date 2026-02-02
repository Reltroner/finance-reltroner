<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
</p>

<p align="center">
  <strong>FinanceReltroner</strong><br>
  Immutable Accounting Core • Laravel 12 • STEP 5.2B.4 Compliant
</p>

<p align="center">
  <img src="https://img.shields.io/badge/STEP-5.2%20Frozen-blue">
  <img src="https://img.shields.io/badge/Compliance-5.2B.4-success">
  <img src="https://img.shields.io/badge/PHP-8.2+-8892BF">
  <img src="https://img.shields.io/badge/Laravel-12.x-red">
</p>

---

## 📌 Overview

**FinanceReltroner** adalah **core accounting & finance engine** yang dirancang dengan prinsip:

> **Accounting data is immutable once posted.**

Project ini **bukan CRUD demo**, melainkan fondasi sistem keuangan yang:
- audit-ready  
- deterministic  
- aman terhadap data mutation  
- siap diskalakan ke ERP / enterprise system  

---

## 🧊 STEP 5.2 — STATUS: FROZEN

Mulai commit ini, **STEP 5.2 dinyatakan selesai dan dibekukan**.

### ✅ Final Compliance
- **STEP 5.2B.4 – Transaction Immutability**
- Lulus audit internal
- Semua test backend **PASS**
- UI & domain sudah konsisten

Tidak ada penambahan fitur baru di Step 5.2.
Perubahan selanjutnya **hanya boleh terjadi di Step 5.3+**.

---

## 🔒 Accounting Principles Implemented

### 1️⃣ Transaction Immutability
| Status | Editable |
|------|---------|
| draft | ✅ yes |
| posted | ❌ no |
| voided | ❌ no |

- `posted` dan `voided` **tidak bisa diubah atau dihapus**
- Dijaga oleh:
  - Domain guard (`TransactionGuard`)
  - Form request validation
  - UI lock
  - Whitebox test

---

### 2️⃣ Balanced Journal Enforcement
- Total debit **harus sama** dengan total credit
- Minimal **2 journal lines**
- Guard di:
  - frontend (UX)
  - backend (request + domain)

---

### 3️⃣ Fiscal Period Lock
- Fiscal year & period **ditentukan saat posting**
- Tidak dapat dimodifikasi setelah posted

---

## 🧪 Testing

### Test Command
```bash
composer test
