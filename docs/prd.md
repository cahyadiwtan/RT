
# MASTER PRD

# Sistem Administrasi & Transparansi RT Berbasis Laravel

**Versi:** 1.0
**Status:** Development Specification
**Framework:** Laravel
**Database:** MySQL/MariaDB
**Frontend:** Blade + Tailwind CSS + Alpine.js
**Target:** Web application responsive, mudah di-hosting dan mudah dikembangkan dengan AI coding agent.

---

# 1. INSTRUKSI UTAMA UNTUK AI DEVELOPER

Dokumen ini merupakan **single source of truth** untuk development aplikasi.

AI developer wajib:

1. Mengikuti requirement dalam dokumen ini.
2. Jangan mengubah arsitektur tanpa alasan teknis yang kuat.
3. Jangan menambahkan dependency yang tidak diperlukan.
4. Jangan membuat SPA React/Vue untuk MVP.
5. Gunakan Laravel + Blade.
6. Gunakan MySQL/MariaDB.
7. Gunakan Service Layer untuk business logic kompleks.
8. Gunakan Form Request untuk validasi.
9. Gunakan Policy/Middleware untuk authorization.
10. Gunakan database transaction untuk transaksi keuangan.
11. Jangan menghapus transaksi keuangan secara permanen.
12. Gunakan audit log untuk perubahan penting.
13. Buat automated test untuk business logic penting.
14. Jangan membuat asumsi business rule baru jika belum ditentukan.
15. Jika requirement ambigu, prioritaskan struktur yang paling sederhana, aman, dan mudah dikembangkan.

---

# 2. TUJUAN APLIKASI

Aplikasi digunakan untuk membantu pengurus RT mengelola:

* Data rumah.
* Data warga.
* Akun warga.
* Iuran bulanan.
* Pembayaran iuran.
* Tunggakan.
* Saldo pembayaran.
* Aspirasi warga.
* Inventaris/aset RT.
* Event/kegiatan RT.
* Keuangan event.
* Transparansi penggunaan dana kegiatan.
* Audit aktivitas pengurus.

Warga dapat menggunakan aplikasi untuk:

* Melihat profil.
* Melihat rumah.
* Melihat status iuran.
* Melihat tunggakan.
* Melihat saldo.
* Melihat riwayat pembayaran.
* Mengirim aspirasi.
* Melihat inventaris RT.
* Melihat event RT.
* Melihat status pembayaran event.
* Melihat pemasukan event.
* Melihat pengeluaran event.
* Melihat saldo event.

---

# 3. PRINSIP ARSITEKTUR

Gunakan arsitektur sederhana:

```text
Laravel
├── Blade
├── Tailwind CSS
├── Alpine.js
├── Eloquent ORM
├── MySQL/MariaDB
└── Laravel Storage
```

Tidak perlu:

```text
React
Vue SPA
Node server
Microservices
Separate API server
Kubernetes
Message broker
```

Kecuali diperlukan pada fase selanjutnya.

---

# 4. ROLE

Minimal terdapat dua role:

## 4.1 Pengurus

Dapat:

* Mengelola rumah.
* Mengelola warga.
* Mengelola akun.
* Memverifikasi NIK.
* Mengelola iuran.
* Mencatat pembayaran.
* Mengelola event.
* Mengelola pemasukan event.
* Mengelola pengeluaran event.
* Mengelola inventaris.
* Mengelola aspirasi.
* Melihat laporan.
* Melihat audit log.

## 4.2 Warga

Dapat:

* Login.
* Melihat profil.
* Melihat rumah.
* Melihat iuran.
* Melihat pembayaran.
* Melihat tunggakan.
* Melihat saldo.
* Mengirim aspirasi.
* Melihat inventaris.
* Melihat event.
* Melihat keuangan event.
* Melihat status pembayaran event.
* Mengubah password.

Warga tidak boleh mengubah data administrasi RT.

---

# 5. AUTHENTICATION

## Login

Warga menggunakan username:

```text
blok + nomor rumah
```

Contoh:

```text
A01
A02
B01
B12
```

Username harus unik.

User memiliki:

```text
username
password
role
resident_id
is_active
```

---

# 6. FORGOT PASSWORD

Flow:

```text
Lupa Password
↓
Masukkan username
↓
Masukkan NIK
↓
Validasi username + NIK
↓
Pastikan warga sudah diverifikasi
↓
Reset password
```

NIK harus cocok dengan data resident.

Gunakan:

* rate limiting,
* generic error message,
* Laravel password hashing.

Jangan menampilkan NIK secara penuh.

---

# 7. DATA RUMAH

Table:

`houses`

Fields:

```text
id
block
house_number
address
status
notes
created_at
updated_at
```

Unique:

```text
block + house_number
```

Satu rumah dapat memiliki banyak warga.

Relationship:

```text
House hasMany Residents
```

---

# 8. DATA WARGA

Table:

`residents`

Fields:

```text
id
house_id
nik
nomor_kk
nama_lengkap
tempat_lahir
tanggal_lahir
jenis_kelamin
nomor_telepon
email
status_warga
hubungan_dalam_keluarga
is_verified
verified_at
notes
created_at
updated_at
```

NIK harus unique.

Status:

```text
aktif
pindah
meninggal
tidak_aktif
```

---

# 9. USER ACCOUNT

Table:

`users`

Fields:

```text
id
resident_id
username
password
role
is_active
last_login_at
created_at
updated_at
```

Pengurus dapat membuat akun warga.

Warga hanya dapat melihat dan mengubah password sendiri.

---

# 10. DASHBOARD WARGA

Dashboard menampilkan:

```text
Nama
Rumah
Blok

Iuran Bulanan
Rp30.000

Pembayaran Terakhir
10 Agustus 2026

Tunggakan
Rp20.000

Saldo
Rp10.000
```

Menu:

```text
Dashboard
Profil Saya
Rumah Saya
Iuran
Riwayat Pembayaran
Event
Inventaris RT
Aspirasi
Ubah Password
Logout
```

---

# 11. DASHBOARD PENGURUS

Menampilkan:

```text
Total Rumah
Total Warga
Warga Aktif

Tagihan Bulan Ini
Pembayaran Bulan Ini
Total Tunggakan

Event Aktif
Aspirasi Baru

Total Aset
Aset Dipinjam
Aset Bermasalah
```

---

# 12. MODUL IURAN RT

Iuran bulanan merupakan kewajiban rutin.

Contoh:

```text
Rp30.000 / bulan
```

Nominal iuran dapat berubah berdasarkan periode.

---

# 13. MONTHLY FEE SETTINGS

Table:

`monthly_fee_settings`

Fields:

```text
id
amount
effective_from
effective_until
description
is_active
created_at
updated_at
```

Contoh:

```text
Jan-Jun 2026
Rp30.000

Jul-Des 2026
Rp35.000
```

Tagihan lama tidak boleh berubah ketika nominal baru dibuat.

---

# 14. MONTHLY BILLS

Table:

`monthly_bills`

Fields:

```text
id
house_id
billing_period
amount
paid_amount
remaining_amount
status
due_date
created_at
updated_at
```

Status:

```text
unpaid
partial
paid
```

Unique:

```text
house_id + billing_period
```

---

# 15. PAYMENT

Table:

`payments`

Fields:

```text
id
house_id
resident_id
payment_date
amount
payment_method
reference_number
notes
recorded_by
status
created_at
updated_at
```

Payment method:

```text
cash
transfer
other
```

Status:

```text
active
void
```

---

# 16. PAYMENT ALLOCATION

Table:

`payment_allocations`

Fields:

```text
id
payment_id
bill_id
amount
created_at
updated_at
```

Gunakan FIFO:

```text
Payment
↓
Tagihan paling lama
↓
Tagihan berikutnya
↓
Tagihan berikutnya
```

---

# 17. CONTOH PAYMENT ALLOCATION

Tagihan:

```text
Juni       30.000
Juli       30.000
Agustus    30.000
September  30.000
```

Pembayaran:

```text
100.000
```

Alokasi:

```text
Juni       30.000
Juli       30.000
Agustus    30.000
September  10.000
```

---

# 18. CREDIT BALANCE

Jika pembayaran lebih besar daripada kewajiban:

```text
Total Payment
-
Total Allocation
=
Credit Balance
```

Contoh:

```text
Tagihan       30.000
Pembayaran   100.000
Credit        70.000
```

Credit dapat digunakan untuk tagihan bulan berikutnya.

---

# 19. TUNGGAKAN

Formula:

```text
SUM(remaining_amount)
```

untuk seluruh tagihan yang belum lunas.

Jangan menghitung tunggakan hanya berdasarkan jumlah bulan.

Pembayaran sebagian harus didukung.

---

# 20. GENERATE MONTHLY BILL

Sistem dapat membuat tagihan bulanan secara otomatis.

Gunakan Laravel Scheduler atau proses manual.

Harus idempotent.

Jalankan dua kali:

```text
Tidak boleh menghasilkan duplicate bill.
```

---

# 21. PAYMENT TRANSACTION

Semua proses pembayaran harus:

```text
DB Transaction
```

Flow:

```text
Create Payment
↓
Find Outstanding Bills
↓
Allocate FIFO
↓
Update Bills
↓
Calculate Credit
↓
Create Audit Log
↓
Commit
```

Jika gagal:

```text
Rollback
```

---

# 22. KOREKSI PEMBAYARAN

Jangan hard delete payment.

Gunakan:

```text
void
reversal
adjustment
```

Semua koreksi dicatat pada audit log.

---

# 23. MODUL ASPIRASI

Table:

`aspirations`

Fields:

```text
id
resident_id
title
description
category
status
attachment
created_at
updated_at
```

Category:

```text
keamanan
kebersihan
fasilitas
lingkungan
sosial
lainnya
```

Status:

```text
submitted
reviewed
in_progress
resolved
rejected
```

---

# 24. ASPIRATION UPDATE

Table:

`aspiration_updates`

Fields:

```text
id
aspiration_id
user_id
status
comment
created_at
updated_at
```

Warga dapat melihat status dan komentar tindak lanjut.

---

# 25. MODUL INVENTARIS / ASET RT

Tujuan:

Membuat daftar aset RT yang dapat dilihat warga.

Contoh:

```text
Kursi
Meja
Tenda
Sound System
Speaker
Proyektor
Laptop
Printer
Peralatan kebersihan
Peralatan olahraga
```

---

# 26. ASSET CATEGORY

Table:

`asset_categories`

Fields:

```text
id
name
description
is_active
created_at
updated_at
```

Contoh:

```text
Perlengkapan Kegiatan
Elektronik
Kebersihan
Keamanan
Olahraga
Administrasi
Furniture
Lainnya
```

---

# 27. ASSET

Table:

`assets`

Fields:

```text
id
asset_code
category_id
name
description
quantity
unit
condition
status
location
acquisition_date
acquisition_source
acquisition_price
current_value
brand
model
serial_number
photo
notes
created_by
created_at
updated_at
```

Asset code unique:

```text
AST-0001
AST-0002
AST-0003
```

---

# 28. ASSET CONDITION

```text
baik
rusak_ringan
rusak_berat
tidak_layak
hilang
```

# 29. ASSET STATUS

```text
tersedia
dipinjam
dalam_perbaikan
tidak_aktif
hilang
```

Condition dan status adalah dua hal berbeda.

---

# 30. ASSET MOVEMENT

Table:

`asset_movements`

Fields:

```text
id
asset_id
type
quantity
from_location
to_location
condition_before
condition_after
reference_type
reference_id
notes
performed_by
created_at
```

Type:

```text
addition
adjustment
loan
return
transfer
repair
lost
found
disposal
```

Semua perubahan stok penting harus tercatat.

---

# 31. ASSET LOAN

Table:

`asset_loans`

Fields:

```text
id
asset_id
resident_id
quantity
loan_date
expected_return_date
returned_at
status
purpose
notes
approved_by
created_at
updated_at
```

Status:

```text
pending
approved
borrowed
returned
late
cancelled
```

Peminjaman dapat dikembangkan setelah MVP.

---

# 32. INVENTARIS UNTUK WARGA

Warga dapat melihat:

```text
Nama aset
Kategori
Jumlah
Kondisi
Status
Lokasi umum
Foto
Deskripsi
```

Warga tidak dapat mengubah data.

Jangan tampilkan secara default:

```text
Harga beli
Nomor invoice
Supplier
Catatan internal
Informasi internal pengurus
```

---

# 33. MODUL EVENT

Event digunakan untuk kegiatan RT.

Contoh:

```text
17 Agustus
Halal Bihalal
Kerja Bakti
Wisata Warga
Kegiatan Sosial
Turnamen
Rapat Warga
```

---

# 34. EVENT

Table:

`events`

Fields:

```text
id
event_code
title
slug
description
category
location
start_at
end_at
registration_deadline
payment_deadline
funding_type
required_payment
target_amount
status
visibility
cover_image
created_by
created_at
updated_at
```

Event code:

```text
EVT-2026-001
```

---

# 35. EVENT FUNDING TYPE

```text
free
resident_fee
voluntary
rt_fund
mixed
```

Contoh:

```text
resident_fee
Rp50.000
```

---

# 36. EVENT STATUS

```text
draft
published
registration_open
ongoing
completed
cancelled
closed
```

Flow:

```text
Draft
↓
Published
↓
Registration Open
↓
Ongoing
↓
Completed
↓
Closed
```

---

# 37. EVENT PARTICIPANT

Table:

`event_participants`

Fields:

```text
id
event_id
resident_id
participation_status
payment_required
payment_amount
payment_status
registered_at
notes
created_at
updated_at
```

Participation status:

```text
invited
registered
confirmed
cancelled
attended
absent
```

Payment status:

```text
unpaid
partial
paid
waived
```

---

# 38. EVENT PAYMENT

Pembayaran event dipisahkan dari pembayaran iuran bulanan.

Table:

`event_payments`

Fields:

```text
id
event_id
event_participant_id
resident_id
payment_date
amount
payment_method
reference_number
notes
recorded_by
status
created_at
updated_at
```

Status:

```text
active
void
```

---

# 39. EVENT PAYMENT RULE

Pembayaran event harus mendukung:

* Full payment.
* Partial payment.
* Overpayment.
* Void/reversal.

Contoh:

```text
Kewajiban:
100.000

Bayar:
50.000

Sisa:
50.000
```

---

# 40. EVENT INCOME

Selain iuran warga, event dapat memiliki pemasukan lain.

Table:

`event_income`

Fields:

```text
id
event_id
income_type
description
amount
income_date
source
reference_number
notes
recorded_by
status
created_at
updated_at
```

Income type:

```text
resident_fee
donation
sponsor
rt_fund
other
```

---

# 41. EVENT EXPENSE

Table:

`event_expenses`

Fields:

```text
id
event_id
category_id
description
amount
expense_date
vendor
receipt_number
receipt_file
notes
recorded_by
status
created_at
updated_at
```

Status:

```text
active
void
```

---

# 42. EVENT EXPENSE CATEGORY

Table:

`event_expense_categories`

Fields:

```text
id
name
description
is_active
created_at
updated_at
```

Contoh:

```text
Konsumsi
Sewa Tempat
Sewa Peralatan
Dekorasi
Transportasi
Dokumentasi
Hadiah
Perlengkapan
Administrasi
Lainnya
```

---

# 43. EVENT BUDGET

Table:

`event_budgets`

Fields:

```text
id
event_id
category_id
description
estimated_amount
actual_amount
notes
created_at
updated_at
```

Contoh:

```text
Konsumsi
Budget:
2.500.000

Realisasi:
2.350.000
```

---

# 44. EVENT FINANCIAL CALCULATION

Total pemasukan:

```text
SUM(active event payments)
+
SUM(active event income)
```

Total pengeluaran:

```text
SUM(active event expenses)
```

Saldo:

```text
Total Income - Total Expense
```

Target progress:

```text
Total Income / Target Amount × 100
```

Progress maksimal ditampilkan 100%.

---

# 45. EVENT DASHBOARD

Contoh:

```text
Event:
17 Agustus 2026

Peserta:
100

Sudah Bayar:
82

Belum Bayar:
18

Target:
Rp5.000.000

Terkumpul:
Rp4.500.000

Pengeluaran:
Rp4.000.000

Saldo:
Rp500.000
```

---

# 46. EVENT TRANSPARENCY

Warga dapat melihat:

```text
Detail event
Tanggal
Lokasi

Target dana
Total dana terkumpul
Jumlah peserta
Jumlah warga yang sudah membayar
Total pengeluaran
Saldo

Rincian pemasukan
Rincian pengeluaran
```

Warga dapat melihat status pembayaran dirinya:

```text
Kewajiban
Sudah dibayar
Sisa
Status
```

---

# 47. PRIVACY EVENT PAYMENT

Default:

```text
payment_visibility = summary
```

Pilihan:

```text
private
summary
names_only
```

### private

Warga hanya melihat status pembayaran sendiri.

### summary

Warga melihat statistik:

```text
100 wajib bayar
82 sudah bayar
18 belum bayar
```

### names_only

Warga dapat melihat:

```text
Budi — Lunas
Siti — Lunas
Andi — Belum
```

Nominal warga lain tetap disembunyikan.

---

# 48. EVENT EXPENSE TRANSPARENCY

Warga dapat melihat:

```text
Konsumsi
Rp2.000.000

Tenda
Rp1.000.000

Dekorasi
Rp500.000

Sound System
Rp500.000
```

Jika bukti pengeluaran boleh dipublikasikan:

```text
Lihat Bukti
```

---

# 49. EVENT CLOSE

Sebelum event ditutup, sistem menampilkan:

```text
Total Income
Total Expense
Balance
Outstanding Payment
```

Pengurus harus mengonfirmasi.

Setelah `closed`:

* transaksi tidak boleh dihapus,
* perubahan harus melalui adjustment,
* semua perubahan dicatat audit log.

---

# 50. EVENT FINANCIAL SERVICE

Gunakan:

`EventFinancialService`

Method:

```text
getTotalIncome()
getTotalExpense()
getBalance()
getTargetProgress()
getPaymentSummary()
getBudgetSummary()
```

Semua halaman menggunakan service yang sama.

---

# 51. EVENT PAYMENT SERVICE

Gunakan:

`EventPaymentService`

Method:

```text
recordPayment()
voidPayment()
calculateParticipantPaymentStatus()
getOutstandingParticipants()
```

Semua transaksi menggunakan:

```text
DB::transaction()
```

---

# 52. AUDIT LOG

Table:

`activity_logs`

Fields:

```text
id
user_id
action
subject_type
subject_id
old_values
new_values
ip_address
user_agent
created_at
```

Aktivitas penting:

```text
login
logout
create resident
update resident
verify resident
create payment
void payment
create event
update event
create event income
create event expense
void event expense
create asset
update asset
stock adjustment
create aspiration
update aspiration
```

---

# 53. SECURITY

Wajib:

* Laravel authentication.
* Password hashing.
* CSRF protection.
* XSS protection.
* SQL injection protection.
* Form Request validation.
* Authorization Policy.
* Role middleware.
* Rate limiting login.
* Rate limiting forgot password.
* HTTPS production.
* Secure session.
* Audit logging.

NIK harus dimasking ketika ditampilkan.

Contoh:

```text
3273********1234
```

---

# 54. AUTHORIZATION

Warga:

```text
Hanya data dirinya sendiri.
```

Warga tidak boleh:

```text
Melihat NIK warga lain.
Melihat transaksi iuran warga lain secara detail.
Mengubah pembayaran.
Mengubah event.
Mengubah inventaris.
Mengubah aspirasi warga lain.
```

Pengurus:

```text
Dapat mengakses seluruh data administratif RT.
```

Gunakan Laravel Policy.

---

# 55. DATABASE RELATIONSHIP

Relationship utama:

```text
House
 ├── hasMany Residents
 ├── hasMany MonthlyBills
 └── hasMany Payments

Resident
 ├── belongsTo House
 ├── hasOne User
 ├── hasMany Aspirations
 ├── hasMany EventParticipants
 └── hasMany EventPayments

User
 └── belongsTo Resident

MonthlyBill
 ├── belongsTo House
 └── hasMany PaymentAllocations

Payment
 ├── belongsTo House
 ├── belongsTo Resident
 └── hasMany PaymentAllocations

PaymentAllocation
 ├── belongsTo Payment
 └── belongsTo MonthlyBill

Event
 ├── hasMany EventParticipants
 ├── hasMany EventPayments
 ├── hasMany EventIncome
 ├── hasMany EventExpenses
 └── hasMany EventBudgets

EventParticipant
 ├── belongsTo Event
 └── belongsTo Resident

EventPayment
 ├── belongsTo Event
 ├── belongsTo EventParticipant
 └── belongsTo Resident

EventIncome
 └── belongsTo Event

EventExpense
 ├── belongsTo Event
 └── belongsTo ExpenseCategory

AssetCategory
 └── hasMany Assets

Asset
 ├── belongsTo AssetCategory
 ├── hasMany AssetLoans
 └── hasMany AssetMovements

AssetLoan
 ├── belongsTo Asset
 └── belongsTo Resident

AssetMovement
 ├── belongsTo Asset
 └── belongsTo User
```

---

# 56. DATABASE TABLE FINAL

Minimal database:

```text
users
houses
residents

monthly_fee_settings
monthly_bills
payments
payment_allocations

aspirations
aspiration_updates

asset_categories
assets
asset_loans
asset_movements

events
event_participants
event_payments
event_income
event_expenses
event_expense_categories
event_budgets

activity_logs
```

Laravel default:

```text
password_reset_tokens
sessions
cache
jobs
```

hanya jika digunakan oleh Laravel configuration.

---

# 57. LARAVEL DIRECTORY

Gunakan struktur:

```text
app/
├── Models/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
│
├── Services/
│   ├── BillingService.php
│   ├── PaymentService.php
│   ├── PaymentAllocationService.php
│   ├── EventService.php
│   ├── EventPaymentService.php
│   ├── EventFinancialService.php
│   ├── AssetService.php
│   └── AssetLoanService.php
│
├── Policies/
└── Providers/
```

---

# 58. CONTROLLER RULE

Controller hanya menangani:

```text
Request
↓
Validation
↓
Authorization
↓
Service
↓
Response
```

Jangan menaruh business logic panjang di Controller.

Contoh:

```text
PaymentController
    ↓
PaymentService
    ↓
PaymentAllocationService
```

---

# 59. VALIDATION RULE

Gunakan Form Request.

Contoh:

```text
StoreResidentRequest
StorePaymentRequest
StoreEventRequest
StoreEventPaymentRequest
StoreEventExpenseRequest
StoreAssetRequest
StoreAspirationRequest
```

Jangan melakukan validasi kompleks langsung di Blade.

---

# 60. SOFT DELETE

Gunakan soft delete untuk entity yang membutuhkan histori:

* Resident.
* House.
* Asset.
* Event jika diperlukan.

Jangan menggunakan hard delete untuk transaksi:

* Payment.
* Event Payment.
* Event Income.
* Event Expense.
* Payment Allocation.

Gunakan status:

```text
void
reversal
inactive
```

---

# 61. TESTING

Gunakan PHPUnit/Pest sesuai standar Laravel project.

Wajib test:

## Authentication

```text
Warga dapat login.
Pengurus dapat login.
Warga tidak dapat mengakses admin.
Forgot password berhasil dengan data valid.
Forgot password gagal dengan data invalid.
```

## Resident

```text
NIK unique.
Warga hanya dapat melihat dirinya.
```

## Billing

```text
Generate bill.
Tidak duplicate.
```

## Payment

```text
30k bill + 30k payment = paid.
30k bill + 20k payment = partial.
3 × 30k bill + 100k payment = 3 paid + 10k next bill.
30k bill + 100k payment = 70k credit.
```

## Event

```text
Create event.
Create participant.
Record payment.
Partial payment.
Event income.
Event expense.
Balance calculation.
Target progress.
Void payment.
Void expense.
```

## Asset

```text
Create asset.
Stock adjustment.
Cannot loan unavailable asset.
Movement history.
```

## Security

```text
Warga tidak dapat mengakses data warga lain.
Warga tidak dapat mengubah payment.
Warga tidak dapat mengubah event.
Warga tidak dapat mengubah asset.
```

---

# 62. UI PRINCIPLE

UI harus:

* Responsive.
* Mobile-first.
* Sederhana.
* Mudah digunakan oleh pengguna non-teknis.
* Menggunakan bahasa Indonesia.
* Menggunakan Rupiah.
* Menggunakan date format Indonesia.
* Memiliki empty state.
* Memiliki confirmation dialog untuk destructive action.
* Memiliki pagination.
* Memiliki search.
* Memiliki filter.

---

# 63. FORMAT UANG

Gunakan:

```text
Rp30.000
Rp100.000
Rp1.500.000
```

Database menyimpan integer/decimal tanpa format.

Jangan menyimpan:

```text
"Rp30.000"
```

sebagai nilai database.

---

# 64. FORMAT TANGGAL

Gunakan database format standar:

```text
YYYY-MM-DD
YYYY-MM-DD HH:MM:SS
```

UI:

```text
24 Agustus 2026
```

---

# 65. REPORTING

Pengurus membutuhkan:

## Laporan Iuran

Filter:

```text
Periode
Blok
Status
```

## Laporan Pembayaran

Filter:

```text
Tanggal
Metode
Warga
Rumah
```

## Laporan Tunggakan

Menampilkan:

```text
Rumah
Warga
Total tunggakan
```

## Laporan Inventaris

```text
Kode
Nama
Kategori
Jumlah
Kondisi
Status
Lokasi
```

## Laporan Event

```text
Event
Peserta
Target
Pemasukan
Pengeluaran
Saldo
```

Export minimal:

```text
CSV
Excel
```

PDF dapat dibuat setelah MVP.

---

# 66. MENU PENGURUS

```text
Dashboard

Data Warga
├── Rumah
├── Warga
└── Akun Warga

Iuran RT
├── Pengaturan Iuran
├── Tagihan
├── Pembayaran
├── Tunggakan
└── Laporan

Event & Kegiatan
├── Semua Event
├── Buat Event
├── Peserta
├── Pembayaran
├── Pemasukan
├── Pengeluaran
├── Anggaran
└── Laporan

Inventaris
├── Semua Aset
├── Kategori
├── Peminjaman
├── Pengembalian
├── Kondisi
└── Riwayat

Aspirasi

Audit Log

Pengaturan

Logout
```

---

# 67. MENU WARGA

```text
Dashboard

Profil Saya
Rumah Saya

Iuran
├── Status Iuran
└── Riwayat Pembayaran

Event & Kegiatan

Inventaris RT

Aspirasi

Ubah Password

Logout
```

---

# 68. PHASE DEVELOPMENT

## Phase 1 — Foundation

```text
Laravel setup
Database
Authentication
Role
Layout
Navigation
Authorization
```

## Phase 2 — Master Data

```text
Rumah
Warga
User
Verifikasi NIK
```

## Phase 3 — Iuran

```text
Fee settings
Monthly bills
Generate bills
Payment
Payment allocation
Credit
Arrears
```

## Phase 4 — Dashboard

```text
Dashboard warga
Dashboard pengurus
Reports
```

## Phase 5 — Aspirasi

```text
Create
Review
Status
Follow-up
```

## Phase 6 — Inventaris

```text
Category
Asset
Stock
Condition
Movement
Loan
```

## Phase 7 — Event

```text
Event
Participant
Payment
Income
Expense
Budget
Financial summary
```

## Phase 8 — Quality

```text
Audit log
Testing
Security
Performance
Export
```

---

# 69. DEFINITION OF DONE

Setiap fitur harus memenuhi:

```text
[ ] Migration
[ ] Model
[ ] Relationship
[ ] Form Request
[ ] Validation
[ ] Authorization
[ ] Controller
[ ] Service jika dibutuhkan
[ ] Blade UI
[ ] Empty state
[ ] Error handling
[ ] Audit log jika diperlukan
[ ] Feature test
[ ] Unit test jika terdapat business logic
[ ] Tidak ada N+1 query yang diketahui
[ ] Tidak membocorkan data sensitif
```

---

# 70. DEVELOPMENT ORDER UNTUK AI

AI developer **jangan mengembangkan seluruh aplikasi sekaligus**.

Kerjakan secara incremental:

```text
STEP 1
Project setup
↓
STEP 2
Database foundation
↓
STEP 3
Authentication
↓
STEP 4
Role & authorization
↓
STEP 5
House
↓
STEP 6
Resident
↓
STEP 7
Resident account
↓
STEP 8
Monthly fee
↓
STEP 9
Monthly bill
↓
STEP 10
Payment
↓
STEP 11
Payment allocation
↓
STEP 12
Credit & arrears
↓
STEP 13
Resident dashboard
↓
STEP 14
Admin dashboard
↓
STEP 15
Aspirations
↓
STEP 16
Assets
↓
STEP 17
Asset movement
↓
STEP 18
Asset loan
↓
STEP 19
Events
↓
STEP 20
Event participants
↓
STEP 21
Event payments
↓
STEP 22
Event income
↓
STEP 23
Event expenses
↓
STEP 24
Event budget
↓
STEP 25
Event financial summary
↓
STEP 26
Reports
↓
STEP 27
Audit
↓
STEP 28
Security
↓
STEP 29
Testing
↓
STEP 30
Production preparation
```

---

# 71. ATURAN AI CODING AGENT

Sebelum membuat kode:

1. Baca requirement yang berkaitan dengan fitur.
2. Periksa model/database yang sudah ada.
3. Jangan membuat tabel duplikat.
4. Jangan membuat business logic yang bertentangan dengan service existing.
5. Buat migration.
6. Buat/update model.
7. Buat relationship.
8. Buat Form Request.
9. Buat Policy.
10. Buat Service.
11. Buat Controller.
12. Buat Route.
13. Buat Blade.
14. Buat Test.
15. Jalankan test.
16. Perbaiki error.
17. Jangan melanjutkan ke fitur berikutnya jika test fitur saat ini gagal.

---

# 72. ATURAN KHUSUS KEUANGAN

Semua modul keuangan harus mengikuti prinsip:

```text
Transaction
+
Allocation
+
Audit
+
No Hard Delete
```

Iuran RT dan Event memiliki ledger terpisah.

```text
Monthly Billing
    ≠
Event Billing
```

Jangan mencampur kedua transaksi.

---

# 73. ATURAN KHUSUS INVENTARIS

Inventaris bukan hanya:

```text
asset.quantity
```

Perubahan jumlah harus dapat dilacak.

Gunakan:

```text
Asset
+
Asset Movement
```

Sehingga sistem mengetahui:

```text
Jumlah awal
+
Penambahan
-
Pengurangan
=
Jumlah sekarang
```

---

# 74. ATURAN KHUSUS EVENT

Event merupakan container:

```text
Event
├── Participants
├── Payments
├── Income
├── Expenses
└── Budget
```

Setiap event memiliki laporan keuangannya sendiri.

Formula utama:

```text
Income
-
Expense
=
Balance
```

---

# 75. TRANSPARANSI RT

Prinsip utama aplikasi:

> **Warga dapat mengetahui kondisi administrasi RT, kegiatan RT, aset RT, serta penggunaan dana kegiatan secara transparan tanpa membuka data pribadi yang tidak diperlukan.**

Warga dapat mengetahui:

```text
Apa yang dimiliki RT
Apa kegiatan RT
Berapa dana terkumpul
Dana digunakan untuk apa
Berapa dana tersisa
Status pembayaran dirinya
Status aspirasi dirinya
```

---

# 76. FUTURE FEATURES

Jangan implementasikan pada MVP, tetapi arsitektur harus memungkinkan:

```text
WhatsApp Notification
Email Notification
Payment Gateway
QRIS
QR Code Asset
Mobile App
Push Notification
Surat Pengantar RT
Digital Voting
Voting Warga
Multi-RT
Multi-RW
Multi-Kelurahan
Maintenance Asset
Depreciation Asset
Public Dashboard
```

---

# 77. FINAL MVP SCOPE

MVP wajib memiliki:

## Authentication

```text
Login
Logout
Forgot Password
Change Password
```

## Warga

```text
Data warga
Rumah
Profil
Iuran
Pembayaran
Tunggakan
Saldo
Aspirasi
Event
Inventaris
```

## Pengurus

```text
Dashboard
Rumah
Warga
Akun
Iuran
Pembayaran
Tunggakan
Aspirasi
Event
Event Finance
Inventaris
Reports
Audit
```

## Financial

```text
Monthly Billing
Payment
Allocation
Credit
Arrears

Event Payment
Event Income
Event Expense
Event Budget
Event Balance
```

## Asset

```text
Asset
Category
Condition
Status
Movement
Loan
```

---

# 78. FINAL PRODUCT VISION

Aplikasi ini pada akhirnya menjadi:

**"Pusat Administrasi dan Transparansi RT"**

dengan empat domain utama:

```text
                    SISTEM RT
                       │
        ┌──────────────┼──────────────┐
        │              │              │
      WARGA          KEUANGAN       KEGIATAN
        │              │              │
     Data Warga      Iuran          Event
     Rumah           Payment        Peserta
     Akun            Tunggakan      Pemasukan
     Aspirasi        Saldo          Pengeluaran
                                   Budget
        │              │              │
        └──────────────┼──────────────┘
                       │
                   INVENTARIS
                       │
                 Aset RT
                 Kondisi
                 Jumlah
                 Peminjaman
                 Histori
```

Prioritas utama:

1. **Mudah digunakan.**
2. **Mudah di-hosting.**
3. **Data aman.**
4. **Keuangan dapat diaudit.**
5. **Transparansi kepada warga.**
6. **Business logic mudah dipahami AI.**
7. **Mudah dikembangkan secara incremental.**
8. **Tidak over-engineering.**

---

# 79. KESIMPULAN TEKNIS

Gunakan:

```text
Laravel
Blade
Tailwind
Alpine.js
MySQL/MariaDB
Laravel Storage
Laravel Scheduler jika diperlukan
PHPUnit/Pest
```

Gunakan pola:

```text
Request
 ↓
Form Request
 ↓
Policy
 ↓
Controller
 ↓
Service
 ↓
Model
 ↓
Database
```

Untuk transaksi:

```text
Service
 ↓
DB Transaction
 ↓
Ledger/Allocation
 ↓
Audit Log
```

Jangan membuat sistem terlalu kompleks.

Fokus pada:

**Laravel monolith yang bersih, modular, aman, mudah di-hosting, mudah dites, dan mudah dipahami AI coding agent.**

# END OF MASTER PRD
