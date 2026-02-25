<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Finance Reltroner</strong><br>
  Enterprise-Grade Accounting & Deterministic Financial Infrastructure • Laravel 12
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Domain-Finance-blue">
  <img src="https://img.shields.io/badge/Architecture-Contract--Driven-success">
  <img src="https://img.shields.io/badge/Immutability-Enforced-critical">
  <img src="https://img.shields.io/badge/Determinism-Guaranteed-brightgreen">
  <img src="https://img.shields.io/badge/Freeze-5.1--5.5-Locked-black">
  <img src="https://img.shields.io/badge/PHP-8.2+-8892BF">
  <img src="https://img.shields.io/badge/Laravel-12.x-red">
</p>

---

# 📌 Finance Reltroner — Architectural Overview

**Finance Reltroner** is not a CRUD accounting application.

It is a:

> Contract-Locked · Deterministic · Audit-Grade
> Financial Infrastructure Engine

The system is built around:

* Contract-based accounting
* Immutability enforcement
* Deterministic financial computation
* Audit-first governance
* Strict architectural layering

It is designed for:

> Historical integrity.
> Financial correctness.
> Computational determinism.

This is an enterprise-grade accounting kernel.

---

# 🧱 Frozen Architecture Baseline (STEP 5.1 – 5.5)

Phase 5 is officially frozen.

| Step | Layer                   | Status    |
| ---- | ----------------------- | --------- |
| 5.1  | Domain Data Contract    | 🔒 FROZEN |
| 5.2  | Write Governance        | 🔒 FROZEN |
| 5.3  | Read Architecture       | 🔒 FROZEN |
| 5.4  | Snapshot & Analytics    | 🔒 FROZEN |
| 5.5  | Budget vs Actual Engine | 🔒 FROZEN |

All public contracts within this range are locked.

Any modification requires:

* Freeze Revoke Protocol
* Contract Migration
* Full Regression Re-certification

---

# 🧠 Architectural Principles

---

## 1️⃣ Contract-Based Accounting (STEP 5.1)

The database is the source of truth.

Examples:

* `normal_balance` is mandatory and explicit
* No implicit inference
* No hidden fallback
* No runtime tradition-based logic

Accounting behavior follows data contracts, not conventions.

---

## 2️⃣ Write Immutability (STEP 5.2)

Once a transaction is:

* Posted
* Or fiscal period locked

It cannot be edited or deleted.

Corrections occur via reversal entries only.

Single write path:

```
UI / API / Seeder
        ↓
TransactionService
        ↓
Observer
        ↓
Database
```

No bypass allowed.

---

## 3️⃣ Read Is Pure (STEP 5.3)

The read layer:

* Does not mutate
* Does not “fix” data
* Does not infer missing state
* Does not correct domain errors

Trial Balance, P&L, and Balance Sheet are deterministic projections.

---

## 4️⃣ Snapshot = Immutable Financial Memory (STEP 5.4)

The snapshot layer is:

* Append-only
* Hash-verified
* Ledger-drift protected
* Deterministically serialized

Snapshots are never updated.

Analytics layer:

* KPI
* Projection
* Forecast
* Scenario

All implemented as pure functions.

---

## 5️⃣ Financial Comparison Kernel (STEP 5.5)

Budget vs Actual engine:

```php
compare(int $fiscalPeriodId, int $version): array
generate(int $fiscalPeriodId, int $version): BudgetVsActualReportDTO
```

Guarantees:

* Deterministic
* 4-decimal rounding
* Division-by-zero guard
* Missing metric guard
* Missing period guard
* Explicit sorting
* No mutation
* No DB access in compute layer

This is the financial comparison kernel.

---

# 🔐 Determinism Standard

Finance Reltroner enforces:

> Identical input → Identical output

Enforced via:

* Explicit sorting
* Strict typing
* 4-decimal precision
* No `random()`
* No `time()`
* No hidden state
* Snapshot hashing

Probabilistic drift is not tolerated.

---

# 🏗 Layer Separation

| Layer           | Responsibility       | Mutation    |
| --------------- | -------------------- | ----------- |
| Database        | Truth storage        | Controlled  |
| Service (Write) | Journal governance   | Controlled  |
| Service (Read)  | Projection           | None        |
| Snapshot        | Aggregation          | Append-only |
| Analytics       | Pure math            | None        |
| Budget Engine   | Financial comparison | None        |
| Reporting       | Projection only      | None        |
| Controller      | HTTP boundary        | None        |

There is no cross-layer leakage.

---

# 🧪 Testing Baseline

Current certification:

* 73 tests
* 209 assertions
* 0 failures
* Freeze validated

Run:

```bash
php artisan test
```

Tests are governance.

If tests fail:

> Freeze integrity is compromised.

---

# 📊 Domain Coverage

## Included

* Double-entry journal
* Ledger enforcement
* Fiscal period locking
* Snapshot versioning
* KPI engine
* Forecast engine
* Scenario engine
* Budget definition
* Budget vs Actual comparison
* Deterministic reporting

## Explicitly Excluded

* Authentication
* Payment processing
* Invoicing workflows
* ERP synchronization
* UI-heavy modules
* Multi-tenant isolation (future phase)

---

# 🚧 Change Policy

---

## 🔴 Forbidden Without Freeze Revoke

* Modify frozen public signatures
* Change rounding precision
* Remove guards
* Alter sorting behavior
* Modify DTO schema
* Add silent fallback logic
* Introduce mutation into compute layer

---

## 🟢 Allowed

* Add new phases (5.6+)
* Add new tests
* Performance optimization (no behavior change)
* Documentation updates
* Additive read-only reporting

---

# 🧭 Strategic Roadmap

| Phase | Description                  | Status  |
| ----- | ---------------------------- | ------- |
| 5.1   | Domain Contract Foundation   | 🔒      |
| 5.2   | Write Governance             | 🔒      |
| 5.3   | Read Architecture            | 🔒      |
| 5.4   | Snapshot & Analytics         | 🔒      |
| 5.5   | Budget vs Actual Kernel      | 🔒      |
| 5.6   | Forecast vs Budget Engine    | Planned |
| 6.x   | Multi-Entity & Consolidation | Planned |

Future phases must layer above frozen contracts.

---

# 🔑 Governance Philosophy

Finance Reltroner is built on one principle:

> Accounting history must never lie.

Extended principle:

> Financial comparison must never drift.

Determinism is mandatory.
Immutability is mandatory.
Contracts are binding.

---

# 🏛 Project Authority

Maintained under:

[https://github.com/Reltroner/finance-reltroner](https://github.com/Reltroner/finance-reltroner)

Architecture governed by freeze notices STEP 5.1 – STEP 5.5.

---

# 📄 License

Built on Laravel Framework.
Licensed under MIT unless otherwise specified.

---

# 🧊 Final Statement

Finance Reltroner is no longer a feature collection.

It is now:

> A contract-locked,
> deterministic,
> audit-safe,
> financial computation infrastructure.

Baseline secured.
Architecture stabilized.
Freeze active.
