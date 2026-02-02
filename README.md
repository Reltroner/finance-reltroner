<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Finance Reltroner</strong><br>
  Enterprise-Grade Accounting & Ledger System • Laravel 12
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Domain-Finance-blue">
  <img src="https://img.shields.io/badge/Architecture-Audit--Driven-success">
  <img src="https://img.shields.io/badge/Immutability-Enforced-critical">
  <img src="https://img.shields.io/badge/PHP-8.2+-8892BF">
  <img src="https://img.shields.io/badge/Laravel-12.x-red">
</p>

---

## 📌 Overview

**Finance Reltroner** is an **enterprise-grade accounting and finance engine** designed with
**immutability, auditability, and fiscal correctness as first-class principles**.

This repository implements:
- Journal transactions
- General ledger
- Fiscal period locking
- Equity & retained earnings safeguards
- Strict audit compliance

It is **not a CRUD finance app**.  
It is a **controlled accounting system**.

---

## 🎯 Core Objectives

- 📒 Accurate journal-based accounting
- 🔒 Enforced immutability after posting / closing
- 🧾 Full audit trail without exception paths
- 📆 Fiscal period awareness & locking
- 🔗 Trust-based authentication via Reltroner Gateway

---

## 🧠 Architectural Principles

### 1️⃣ Audit-First Design
Every rule exists to satisfy:
- External audit expectations
- Accounting best practices
- Deterministic historical truth

Nothing is “convenient” if it breaks auditability.

---

### 2️⃣ Immutability Is Mandatory
Once a transaction is:
- **Posted**
- Or belongs to a **closed fiscal period**

➡️ it **cannot be mutated or deleted**.

Corrections require **reversals**, not edits.

---

### 3️⃣ Separation of Concerns

| Layer | Responsibility |
|----|----|
| Controller | HTTP orchestration only |
| Request | Validation & guardrails |
| Service | Accounting rules |
| Guard | Fiscal & immutability enforcement |
| Model | Persistence only |

---

## 🔐 Authentication & Trust Model

Finance Reltroner **does not authenticate users directly**.

It trusts the **Reltroner Gateway** via signed tokens.

Required `.env` configuration:

```env
RELTRONER_GATEWAY_ISSUER=http://app.reltroner.test
RELTRONER_GATEWAY_AUDIENCE=finance.reltroner.test
RELTRONER_MODULE_SIGNING_KEY=shared-secret
````

---

## 🧾 STEP 5.2 — OFFICIALLY FROZEN

### ✅ Status: **FINAL — AUDIT PASSED**

**STEP 5.2: Equity & Retained Earnings Contracts**
has been fully implemented, tested, and frozen.

No further modifications are allowed without:

* Explicit version bump
* New audit cycle

---

### 🔒 What STEP 5.2 Guarantees

* Immutable transaction records
* Fiscal period locking (5.2B.4)
* Equity account protection
* No backdoor mutations
* Deterministic ledger history

---

### 📦 Key Components (STEP 5.2)

| Component              | Purpose                           |
| ---------------------- | --------------------------------- |
| `TransactionService`   | Central accounting logic          |
| `TransactionGuard`     | Fiscal & immutability enforcement |
| `PeriodClosingService` | Period finalization               |
| Request Objects        | Validation & locking rules        |
| Whitebox Tests         | Audit verification                |

---

## 🧪 Testing Strategy

Finance Reltroner uses **whitebox testing** for audit compliance.

### Run Tests

```bash
composer test
```

or:

```bash
php artisan test
```

### Test Coverage Includes

* Transaction immutability
* Closed fiscal period rejection
* Balanced journal enforcement
* Forbidden updates & deletes
* Ledger correctness

All STEP 5.2 tests **PASS** and are part of the freeze criteria.

---

## 🗂️ Domain Scope

### Included

* Journal Transactions
* Transaction Details
* General Ledger
* Fiscal Periods
* Attachments
* Tax Applications

### Explicitly Excluded

* Authentication logic
* User management
* Payment gateways
* Invoicing UI logic

---

## 🚧 Change Policy

### 🔴 Forbidden Without New Phase

* Editing posted transactions
* Editing closed periods
* Deleting ledger history
* Bypassing guards

### 🟢 Allowed

* New phases (5.3+)
* Additive features
* Read-only optimizations
* Reporting extensions

---

## 🧭 Roadmap

| Phase | Description                  | Status       |
| ----- | ---------------------------- | ------------ |
| 5.1   | Journal Foundation           | ✅            |
| 5.2   | Equity & Retained Earnings   | ✅ **FROZEN** |
| 5.3   | Reporting & Analytics        | planned      |
| 6.x   | Consolidation / Multi-Entity | planned      |

---

## 🤝 Contribution Rules

* Respect immutability
* Never bypass guards
* Every change must be test-backed
* Accounting rules > convenience

This repository assumes contributors **understand accounting fundamentals**.

---

## 📄 License

This project is built on top of the **Laravel Framework**
and is licensed under the **MIT License**, unless stated otherwise.

---

> **“In accounting, history must never lie — even for convenience.”**
> — Finance Reltroner Principle
