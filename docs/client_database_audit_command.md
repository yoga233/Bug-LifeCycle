# PERINTAH AUDIT DATABASE - ROLE Client (Portal)

---

## **TUGAS UTAMA**

Lakukan audit performa database secara menyeluruh untuk **SEMUA halaman yang bisa diakses oleh role Client**. Client adalah portal publik yang **TANPA login**. Audit mencakup halaman landing, pelaporan bug, dan tracking ticket.

---

## **LANGKAH 1: IDENTIFIKASI HALAMAN**

### Route yang diuji untuk Client:

| No | Nama Route | URI | Keterangan |
|----|------------|-----|------------|
| 1 | client.landing | /portal | Halaman landing portal |
| 2 | client.report | /report | Form pelaporan bug |
| 3 | client.report.store | /report (POST) | Simpan bug report |
| 4 | client.report.success | /report/success | Halaman sukses laporan |
| 5 | client.tracking | /track | Halaman tracking ticket |

---

## **LANGKAH 2: AUDIT QUERY SETIAP HALAMAN**

**Metrik Dasar:**
- Jumlah total query yang dijalankan
- Total waktu eksekusi semua query (dalam milliseconds)
- Waktu eksekusi query paling lambat (max single query time)
- Jumlah rows yang di-return setiap query

**Identifikasi Masalah:**
- Query yang duplikat/berulang (catat berapa kali muncul dan SQL-nya)
- Query yang lambat lebih dari 20ms (catat full SQL-nya)
- Query yang menggunakan SELECT * (catat table dan berapa kolom yang sebenarnya dibutuhkan)
- Query tanpa LIMIT pada data list
- Query dengan JOIN lebih dari 3 tabel

---

## **LANGKAH 3: EXPLAIN ANALYSIS**

Untuk setiap query yang waktu eksekusinya **lebih dari 20ms**, jalankan perintah EXPLAIN dan catat hasilnya. Perhatikan apakah ada **full table scan** (type = ALL), **missing index** (key = NULL), atau **slow sorting** (Using filesort). Identifikasi penyebab lambatnya dan index apa yang perlu ditambahkan.

---

## **LANGKAH 4: INDEX AUDIT**

Periksa semua tabel yang digunakan oleh halaman Client. Dokumentasikan index yang sudah ada dan index yang **seharusnya ada tapi belum dibuat**. Fokus pada kolom yang sering digunakan di WHERE clause, JOIN condition, dan ORDER BY.

---

## **LANGKAH 5: EDGE CASE TESTING**

Test setiap halaman dengan **data dalam jumlah besar**. Misalnya:
- Tracking dengan 500+ bugs
- Report form dengan banyak projects
- Status history yang panjang

Catat apakah ada degradasi performa signifikan dibanding data sedikit.

---

## **LANGKAH 6: IDENTIFIKASI N+1 PROBLEM**

Cari pattern dimana ada query yang dijalankan di dalam loop. Pada Client portal, ini jarang terjadi karena tidak ada relasi user yang kompleks.

---

## **OUTPUT YANG DIHARAPKAN**

**Summary per halaman:** nama halaman, jumlah query, total waktu, query terlambat, masalah yang ditemukan.

**Daftar query bermasalah:** full SQL, waktu eksekusi, penyebab lambat, solusi yang direkomendasikan, prioritas (HIGH/MEDIUM/LOW).

**Daftar missing index:** nama tabel, kolom yang perlu di-index, alasan kenapa perlu index.

**Prioritas perbaikan:** urutkan berdasarkan impact terbesar.

---

## **TARGET METRIK**

- Jumlah query: **maksimal 5 per halaman** (karena portal sangat sederhana)
- Total waktu query: **maksimal 30ms**
- Single query time: **maksimal 20ms**
- Duplicate query: **0**
- Query tanpa index: **0**
- SELECT *: **0**

---

## **CARA MENJALANKAN**

```bash
php scripts/client_database_audit.php
```

Output akan disimpan di: `storage/app/client_database_audit_report.json`

---

## **PERBEDAAN DENGAN ROLE PROGRAMMER**

| Aspek | Programmer | Client |
|-------|------------|--------|
| Tipe Akses | Dengan login | Tanpa login (publik) |
| Jumlah Route | 15-20 halaman | 5 halaman utama |
| Autentikasi | Perlu Auth | Tidak perlu |
| Data yang diakses | Bugs assigned, notifications, kinerja | Semua bugs公开 |
| Kompleksitas Query | Tinggi | Rendah |

---

## **LIST SCENARIO YANG DIUJI**

1. Client Landing Page
2. Client Bug Report Form
3. Client Bug Report Form (Multiple Projects)
4. Client Bug Report Store (Basic)
5. Client Bug Report Store (Full)
6. Client Report Success Page
7. Client Bug Tracking (No Ticket)
8. Client Bug Tracking (With Ticket)
9. Client Bug Tracking (With History)
10. Client Bug Tracking (Invalid Format)
11. Client Bug Tracking (Not Found)
12. Client Bug Report Store (Validation Error)
13. Client Bug Report Store (Large Description)
14. Client Bug Tracking (Resolved Bug)
15. Client Bug Tracking (Rejected Bug)
16. Client Bug Tracking (With Attachments)
17. Client Bug Report Store (All Frequencies)
18. Client Bug Report Store (Critical Severity)
19. Client Bug Tracking (Sequential Test)
20. Client Landing Page (Refresh)
