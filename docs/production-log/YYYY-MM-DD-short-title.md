# 📄 STRUCTURE TEMPLATE

---

## 1️⃣ Incident Metadata

```
Title:
Date:
Environment: (Production / Staging / Beta User)
Severity: (Low / Medium / High / Critical)
Detected By: (User / Monitoring / Self / QA)
Time to Detect:
Time to Resolve:
```

Tujuan:
Melatih sense terhadap detection delay.

---

## 2️⃣ Problem Summary (Neutral Tone)

Tuliskan fakta saja.

```
What happened?
What was the observable symptom?
Who was affected?
```

Contoh tone:

> Payroll export generated incorrect tax rounding for specific decimal combinations.

Bukan:

> The system broke due to weird user behavior.

No blame. No emotion.

---

## 3️⃣ Expected Behavior

```
What should have happened?
What invariant was assumed?
```

Ini penting karena sering kali:
bug = assumption mismatch.

---

## 4️⃣ Root Cause Analysis (Technical)

Format wajib:

```
Immediate cause:
Underlying cause:
Assumption error:
Boundary not protected:
```

Contoh:

* Immediate cause: Missing decimal normalization before tax calculation.
* Underlying cause: Assumed all currency values are 2 decimal precision.
* Assumption error: Did not enforce scale constraint at DB level.
* Boundary not protected: No validation rule on input layer.

Root cause bukan cuma “logic bug”.

Root cause = broken assumption.

---

## 5️⃣ Fix Strategy

```
Code fix:
Database change (if any):
Test added:
Logging improvement:
Rollback risk:
```

Wajib tambah test.

Kalau tidak ada test added → incomplete fix.

---

## 6️⃣ Tradeoff Introduced

Ini bagian paling penting.

```
What complexity increased?
What performance cost added?
What constraint tightened?
What flexibility reduced?
```

Principal thinking = every fix adds cost.

---

## 7️⃣ Prevention Layer

```
Monitoring improvement:
Alert added?
Validation added?
Refactor needed?
Process adjustment?
```

Ini membangun production awareness stack.

---

## 8️⃣ Emotional Reflection (Private)

Bagian ini opsional tapi powerful.

```
Initial reaction:
Defensive thought:
What I learned about my own bias:
```

Tujuan:
Ego separation.

---

## 9️⃣ One-Sentence Lesson

Format:

> Systems fail at the boundary we assume is safe.

atau

> Validation at DB layer would have prevented this entirely.

Ringkas. Tajam.

---

# 🧱 MINIMAL VERSION (Quick Log Mode)

Kalau incident kecil:

Gunakan format ringkas:

```
Issue:
Root cause:
Fix:
Test added:
Lesson:
```

Tetap wajib test & lesson.

---

# 📊 OPTIONAL — SCAR INDEX TABLE

Buat satu file index:

| Date | Area | Type | Severity | Root Cause Category |
| ---- | ---- | ---- | -------- | ------------------- |

Root Cause Category contoh:

* Validation gap
* Race condition
* Data integrity
* Edge case logic
* Assumption mismatch
* Performance boundary
* Human miscommunication

Setelah 1 tahun,
kamu akan melihat pola kegagalan.

Itu adalah production intuition dataset.

---

# 🧠 WHY THIS MATTERS

Engineers without production log:

Remember victories.

Engineers with production log:

Remember mistakes precisely.

Principal engineers:

Can recall 5 production scars instantly.

---

# 🚨 RULES

1. No deleting log.
2. No rewriting history.
3. No ego editing.
4. Every meaningful bug gets documented.
5. Every fix must add test or boundary protection.

---

# 📈 After 6 Months of Logging

You will gain:

* Faster debugging pattern recognition
* Cleaner assumption thinking
* Better interview storytelling
* Real operational credibility

And most importantly:

Calm under failure.

