# Daftra Integration — Phase 2 Plan

> **Status**: Planning / Awaiting admin setup in Daftra.
> **Author**: Backend team.
> **Date**: 2026-04-22.
> **Context**: Phase 1 (Client invoices + payments + credit notes) is live in production. Phase 2 closes the remaining accounting gap around technician wallets, payouts, and tax liabilities.

---

## 1. لماذا نحتاج Phase 2؟

النظام حاليًا يسجل في دفتره الإيرادات الكاملة من العميل (Invoice + Client Payment) والمرتجعات (Credit Notes) عند إلغاء الطلبات.

لكن فيه فجوة محاسبية: الإيرادات المُسجلة **أكبر من ربح الشركة الحقيقي** لأنها بتتضمن جزء يرجع للفنيين (مستحقات) + الضريبة المستحقة للدولة.

### الفجوة الحالية

| الحدث | مسجّل في Daftra؟ | ملاحظات |
| --- | --- | --- |
| إيراد من طلب عميل مكتمل | ✅ نعم | فاتورة + سند قبض بمبلغ البنك |
| اشتراك فني في باقة | ✅ نعم | فاتورة + سند قبض |
| إلغاء طلب (مرتجع) | ✅ نعم | Credit Note |
| إضافة `subtotal` لمحفظة الفني | ❌ لا | فقط داخلي في `users.balance` |
| طلب سحب من الفني (Payout Request) | ❌ لا | جدول `payout_requests` داخلي |
| تحويل بنكي للفني (تصفير الرصيد) | ❌ لا | فقط `NewBankTransfer` event → FCM notification |
| فصل ضريبة القيمة المضافة كـ Liability | ❌ لا | الضريبة متدمجة مع الإيرادات |

**النتيجة**: المحاسب لازم يسجل يدويًا في دفتره 3 أنواع من القيود شهريًا — وده عرضة للخطأ والنسيان.

---

## 2. المطلوب من الأدمن (One-time Setup)

قبل ما نبدأ التطوير، الأدمن لازم يعمل الخطوات دي في دفتره على `https://wadhacompany.daftra.com`:

### الخطوة 1: فتح شجرة الحسابات
`الحسابات العامة → دليل الحسابات`

### الخطوة 2: إنشاء 3 حسابات جديدة (لو مش موجودة)

#### (أ) حساب مستحقات الفنيين
- **الحساب الأب**: `#2 الخصوم`
- **الاسم**: مستحقات الفنيين
- **النوع**: خصوم متداولة (Current Liabilities)
- **طبيعة الرصيد**: دائن
- **العملة**: ريال سعودي

#### (ب) حساب مصروف عمولات الفنيين
- **الحساب الأب**: `#5 المصروفات`
- **الاسم**: مصروف عمولات الفنيين
- **النوع**: مصروف تشغيلي (Operating Expense)
- **طبيعة الرصيد**: مدين
- **العملة**: ريال سعودي

#### (ج) حساب ضريبة القيمة المضافة المستحقة
- **غالبًا موجود مسبقًا** — ابحث أولًا بكلمة `ضريبة` أو `VAT` داخل دليل الحسابات.
- لو مش موجود، أنشئه تحت `#2 الخصوم`:
  - **الاسم**: ضريبة القيمة المضافة المستحقة
  - **النوع**: خصوم متداولة
  - **طبيعة الرصيد**: دائن

### الخطوة 3: استخراج الـ Account IDs
- افتح كل حساب بالضغط على اسمه.
- شوف الـ URL في المتصفح، الرقم اللي في آخره هو الـ ID.
- مثال: `https://wadhacompany.daftra.com/v2/owner/chart-of-accounts/1234` → ID = `1234`.

### الخطوة 4: تسليم الأرقام لفريق التطوير

| الحساب | Account ID |
| --- | --- |
| مستحقات الفنيين | `_______` |
| مصروف عمولات الفنيين | `_______` |
| ضريبة القيمة المضافة المستحقة | `_______` |

### الحسابات اللي تم استخدامها مسبقًا في Phase 1 (للمرجعية)

```env
DAFTRA_BANK_ACCOUNT_ID=2          # حساب البنك
DAFTRA_REVENUE_ACCOUNT_ID=28      # حساب الإيرادات
DAFTRA_RETURN_ACCOUNT_ID=368      # حساب مرتجع المبيعات
```

---

## 3. ما سيتم تطويره (Scope)

### 3.1 القيود المحاسبية اللي هنضيفها

#### القيد 1: إكمال طلب → مستحقات للفني
يتم تسجيله مع كل `OrderCompleted` event، بعد ما الـ invoice يتزامن مع دفتره.

```
Dr. مصروف عمولات الفنيين    (order.subtotal)
    Cr. مستحقات الفنيين              (order.subtotal)

المرجع (Reference): Order #{id} - {service_provider.name}
```

#### القيد 2: تحويل بنكي للفني → تسوية المستحقات
يتم تسجيله عند `NewBankTransfer` event (لما الأدمن يعمل "تصفير الرصيد").

```
Dr. مستحقات الفنيين         (amount)
    Cr. البنك                        (amount)

المرجع: Payout #{id} - {service_provider.name} - IBAN: {iban}
```

#### القيد 3: فصل VAT من الإيراد
تعديل الـ Invoice الحالي في Phase 1 بحيث يُقسم كـ line items:

```
Invoice Line 1: الخدمة          → Revenue Account (28)
Invoice Line 2: VAT 15%         → VAT Payable Account (الجديد)
-------------------------------------------------------------
Total                            → Bank Account (2)
```

بدل ما الضريبة تتحط كلها على الـ Revenue Account.

### 3.2 الملفات اللي هتتعدل

| الملف | التغيير |
| --- | --- |
| `config/services.php` | إضافة 3 keys جديدة للـ Daftra config |
| `.env.example` | إضافة الـ 3 account IDs |
| `app/Utils/Services/Daftra.php` | إضافة method `createJournalEntry()` |
| `app/Jobs/SyncInvoiceToDaftra.php` | تقسيم الـ invoice items لفصل الضريبة |
| `app/Jobs/SyncTechnicianPayableToDaftra.php` (جديد) | إنشاء قيد مستحقات الفني |
| `app/Jobs/SyncPayoutToDaftra.php` (جديد) | إنشاء قيد تحويل بنكي |
| `app/Listeners/CreateOrderInvoice.php` | dispatch الـ job الجديد بعد الـ invoice sync |
| `app/Listeners/RecordPayoutInDaftra.php` (جديد) | listener لـ `NewBankTransfer` event |
| `backend/README.md` | تحديث runbook section |

### 3.3 الاختبارات (Tests)

هنضيف Feature tests جديدة في `tests/Feature/Daftra/`:

- `test_order_completion_records_technician_payable_journal_entry`
- `test_payout_records_bank_transfer_journal_entry`
- `test_invoice_splits_revenue_and_vat_into_separate_lines`
- `test_payable_journal_is_idempotent_on_retry`
- `test_payout_journal_is_idempotent_on_retry`
- `test_payable_is_skipped_when_subtotal_is_zero`
- `test_institution_member_order_credits_institution_not_individual` (edge case existing)

كل الاختبارات هتستخدم `Http::fake()` زي Phase 1 — مفيش real API calls.

### 3.4 Idempotency (منع التكرار)

نفس pattern الـ two-phase idempotency اللي عملناه في Phase 1:

- جدول `invoices`: هنضيف `daftra_payable_journal_id` عمود جديد.
- جدول `payout_requests`: هنضيف `daftra_journal_id` عمود جديد.
- الـ jobs هتتخطى لو الـ journal_id موجود (pre-check) وهتحفظه بعد النجاح (post-save).

### 3.5 Migrations

```sql
ALTER TABLE invoices ADD COLUMN daftra_payable_journal_id VARCHAR(255) NULL INDEX;
ALTER TABLE payout_requests ADD COLUMN daftra_journal_id VARCHAR(255) NULL INDEX;
```

---

## 4. الأمور اللي **مش** في Scope

عشان نحدد التوقعات بوضوح:

- ❌ تسجيل مصاريف التشغيل (استضافة، تسويق، رواتب إدارة) في دفتره — يدوي.
- ❌ توزيع الأرباح على المؤسسات (institutions) بتفصيل مختلف عن الأفراد — هنستخدم نفس الـ flow.
- ❌ إنشاء الفنيين كـ Suppliers/Vendors في دفتره — هنستخدم Journal Entries فقط بدون بناء علاقة Vendor → أسهل وأقل تعقيدًا.
- ❌ ربط `OrderExtra` (الخدمات الإضافية اللي الفني يضيفها أثناء الطلب) — الـ Phase 1 ما بتضمنهاش، وهنسيبها للـ Phase 3.

---

## 5. المخاطر والتخفيف

| الخطر | التخفيف |
| --- | --- |
| الأدمن يمسح/يغير الـ accounts في دفتره | الـ Daftra request هترجع 404 → نسجل في log بدون retry لو error type = validation |
| تكرار قيد نفس الطلب/الـ payout | Two-phase idempotency + unique constraint على الـ daftra id |
| Race condition لو فني يكمل طلبين في نفس اللحظة | Queue بـ serialized processing + `ShouldBeUnique` على الـ job |
| تضارب بين Phase 1 و Phase 2 لو admin غيّر account IDs | README runbook هيوضح إن تغيير الـ IDs لازم يتم offline فقط |

---

## 6. خطة التنفيذ (Implementation Plan)

### Day 1 — Foundation
- [ ] استلام الـ 3 account IDs من الأدمن.
- [ ] إضافة config keys + `.env.example`.
- [ ] Migration لإضافة `daftra_*_journal_id` columns.
- [ ] إضافة method `createJournalEntry()` في `App\Utils\Services\Daftra` + احتواؤها بـ `Cache::lock`.

### Day 2 — Journal Entries
- [ ] Job: `SyncTechnicianPayableToDaftra` + dispatch من `CreateOrderInvoice` listener.
- [ ] Job: `SyncPayoutToDaftra` + listener `RecordPayoutInDaftra` لـ `NewBankTransfer` event.
- [ ] تقسيم Invoice items في `SyncInvoiceToDaftra` لفصل VAT.

### Day 2.5 — Testing
- [ ] كتابة الـ 7 feature tests المذكورة أعلاه.
- [ ] Regression: تشغيل كل الـ Daftra tests اللي في Phase 1 للتأكد من عدم الكسر.

### Day 3 — Staging / Server Verification
- [ ] Deploy على staging (أو production مباشرة لأنه مفيش staging حاليًا).
- [ ] Smoke test عبر `tinker`:
  - طلب وهمي يكتمل → تأكد إن القيد ظهر في دفتره.
  - Payout وهمي → تأكد إن البنك اتخصم وحساب المستحقات اتقلّ.
- [ ] تحديث `backend/README.md` بالـ Phase 2 runbook.

**إجمالي الوقت المتوقع**: 2.5 يوم تطوير + 0.5 يوم اختبار = **3 أيام عمل**.

---

## 7. معايير القبول (Definition of Done)

- [ ] كل الـ 3 account IDs الجديدة مضبوطة في `.env` على السيرفر.
- [ ] Migration متنفذة بنجاح.
- [ ] 7 tests جديدة passing + كل الـ tests القديمة لسه passing.
- [ ] طلب فعلي على السيرفر يكتمل → القيود التالية ظاهرة في دفتره:
  - [ ] فاتورة مبيعات مع فصل VAT.
  - [ ] سند قبض.
  - [ ] قيد يومي: Dr. مصروف عمولات / Cr. مستحقات الفنيين.
- [ ] Payout فعلي على السيرفر → قيد يومي: Dr. مستحقات / Cr. بنك.
- [ ] الـ README بيحتوي على troubleshooting section للـ Phase 2.
- [ ] المحاسب أكد إن الأرقام في دفتره متطابقة مع التقارير المالية في لوحة التحكم.

---

## 8. المرجع السريع — Data Flow Diagram

```
[عميل يكمل طلب]
      │
      ▼
OrderCompleted event
      │
      ├─────> CreateOrderInvoice listener
      │             │
      │             ├──> users.balance += subtotal  (داخلي)
      │             │
      │             ├──> SyncInvoiceToDaftra (Phase 1 — existing)
      │             │       ├─> POST /clients (if no daftra_id)
      │             │       ├─> POST /invoices (فاتورة مع فصل VAT — Phase 2)
      │             │       └─> POST /invoice_payments (سند قبض)
      │             │
      │             └──> SyncTechnicianPayableToDaftra (Phase 2 — جديد)
      │                     └─> POST /journal_entries
      │                         (Dr. عمولات / Cr. مستحقات)

[أدمن يصفّر رصيد فني]
      │
      ▼
NewBankTransfer event
      │
      └─────> RecordPayoutInDaftra listener (Phase 2 — جديد)
                    │
                    └──> SyncPayoutToDaftra job
                            └─> POST /journal_entries
                                (Dr. مستحقات / Cr. بنك)
```

---

## 9. متى نبدأ؟

هنبدأ التطوير بمجرد استلام الـ 3 account IDs من الأدمن عبر الـ `.env` أو رسالة مباشرة.

جهّز الـ IDs وابعتها بالشكل التالي:

```
DAFTRA_TECHNICIAN_PAYABLE_ACCOUNT_ID=XXXX
DAFTRA_COMMISSION_EXPENSE_ACCOUNT_ID=XXXX
DAFTRA_TAX_LIABILITY_ACCOUNT_ID=XXXX
```
