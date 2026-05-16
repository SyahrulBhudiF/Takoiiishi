# Plan Development Sistem Stok Takoyaki

Target: bangun aplikasi web Laravel versi terbaru + Filament untuk memenuhi kebutuhan fungsional Tabel 4.3.

## 1. Scope Wajib

Sistem harus punya fitur berikut:

| No | Fitur | Aktor | Hasil Wajib |
|---:|---|---|---|
| 1 | Login | Admin Pusat, Admin Cabang, Pemilik Pusat, Pemilik Cabang | User masuk dashboard sesuai role |
| 2 | Logout | Semua user | Session berakhir |
| 3 | Kelola Data Cabang | Admin Pusat | Data cabang tersimpan |
| 4 | Kelola Data Bahan Baku | Admin Pusat | Data bahan baku tersimpan |
| 5 | Input Pembelian Bahan | Admin Pusat | Data pembelian tersimpan, stok pusat bertambah |
| 6 | Distribusi Bahan ke Cabang | Admin Pusat | Stok pusat berkurang, stok cabang bertambah |
| 7 | Monitoring Stok | Admin Pusat, Admin Cabang | Stok tiap outlet tampil |
| 8 | Input Penjualan Harian | Admin Pusat, Admin Cabang | Data penjualan tersimpan |
| 9 | Perhitungan Stok Otomatis | Sistem | Stok bahan baku berkurang otomatis dari penjualan |
| 10 | Notifikasi Stok Minimum | Sistem, Admin Pusat | Peringatan stok minimum tampil |
| 11 | Laporan Stok | Admin Pusat, Pemilik Pusat, Pemilik Cabang | Laporan stok tampil + export CSV/Excel |
| 12 | Laporan Penjualan | Admin Pusat, Pemilik Pusat, Pemilik Cabang | Laporan penjualan tampil + export CSV/Excel |
| 13 | Laporan Pembelian | Admin Pusat, Pemilik Pusat | Laporan pembelian tampil + export CSV/Excel |

## 2. Stack Development

- Laravel versi terbaru
- Filament versi terbaru untuk admin panel
- MySQL via Docker container `ocean_mysql` (`mysql`, port `3306`, status up)
- Laravel Excel / Filament export action untuk CSV dan Excel
- Laravel auth + Filament panel access berdasarkan role

## 3. RBAC / Hak Akses

Role wajib mengikuti Tabel 4.2:

| Role | Deskripsi | Scope Data |
|---|---|---|
| Admin Pusat | Mengelola data cabang, bahan baku, pembelian, distribusi, monitoring semua stok, semua laporan | Semua cabang + pusat |
| Admin Cabang | Input penjualan harian cabang sendiri, monitoring stok cabang sendiri | Cabang miliknya saja |
| Pemilik Pusat | Melihat seluruh laporan stok, penjualan, pembelian, pengeluaran/distribusi bahan | Semua cabang + pusat |
| Pemilik Cabang | Melihat laporan stok dan penjualan cabang sendiri | Cabang miliknya saja |

Permission matrix:

| Fitur | Admin Pusat | Admin Cabang | Pemilik Pusat | Pemilik Cabang |
|---|---:|---:|---:|---:|
| Login/logout | ✅ | ✅ | ✅ | ✅ |
| Dashboard | ✅ semua data | ✅ cabang sendiri | ✅ semua data | ✅ cabang sendiri |
| Kelola cabang | ✅ create/read/update/delete | ❌ | ❌ | ❌ |
| Kelola bahan baku | ✅ create/read/update/delete | ❌ | ❌ | ❌ |
| Input pembelian | ✅ create/read/update/delete | ❌ | read only | ❌ |
| Distribusi bahan | ✅ create/read/update/delete | ❌ | read only | ❌ |
| Monitoring stok | ✅ semua outlet | ✅ cabang sendiri | ✅ semua outlet | ✅ cabang sendiri |
| Input penjualan harian | ✅ semua cabang | ✅ cabang sendiri | ❌ | ❌ |
| Notifikasi stok minimum | ✅ semua outlet | ✅ cabang sendiri | ✅ semua outlet | ✅ cabang sendiri |
| Laporan stok | ✅ semua outlet + export | ❌ | ✅ semua outlet + export | ✅ cabang sendiri + export |
| Laporan penjualan | ✅ semua outlet + export | ❌ | ✅ semua outlet + export | ✅ cabang sendiri + export |
| Laporan pembelian | ✅ semua data + export | ❌ | ✅ semua data + export | ❌ |
| Kelola user | ✅ create/read/update/delete | ❌ | ❌ | ❌ |

Aturan implementasi Filament:
- Pakai enum/constant role: `admin_pusat`, `admin_cabang`, `pemilik_pusat`, `pemilik_cabang`.
- `users.outlet_id` wajib untuk `admin_cabang` dan `pemilik_cabang`.
- `users.outlet_id` nullable untuk `admin_pusat` dan `pemilik_pusat`.
- Query data cabang wajib difilter `outlet_id = auth()->user()->outlet_id` untuk role cabang.
- Menu/resource Filament disembunyikan jika role tidak punya permission.
- Action create/edit/delete dimatikan untuk role read-only.

## 4. Struktur Modul

### Auth
- Login
- Logout
- Proteksi halaman berdasarkan role

### Master Data
- Cabang/outlet
- Bahan baku
- User sederhana jika dibutuhkan untuk akun role

### Transaksi
- Pembelian bahan
- Distribusi bahan ke cabang
- Penjualan harian

### Stok
- Stok pusat
- Stok cabang
- Mutasi stok otomatis
- Notifikasi stok minimum

### Laporan
- Laporan stok
- Laporan penjualan
- Laporan pembelian
- Export CSV
- Export Excel

## 5. Database Minimal

### users
- id
- name
- username
- password
- role
- outlet_id nullable
- created_at
- updated_at

### outlets
- id
- name
- address
- type: pusat/cabang
- created_at
- updated_at

### ingredients
- id
- name
- unit
- minimum_stock
- usage_per_portion
- created_at
- updated_at

### stocks
- id
- outlet_id
- ingredient_id
- quantity
- created_at
- updated_at

### purchases
- id
- purchase_date
- created_by
- total
- created_at
- updated_at

### purchase_items
- id
- purchase_id
- ingredient_id
- quantity
- price
- subtotal

### distributions
- id
- distribution_date
- from_outlet_id
- to_outlet_id
- created_by
- created_at
- updated_at

### distribution_items
- id
- distribution_id
- ingredient_id
- quantity

### sales
- id
- sale_date
- outlet_id
- portion_qty
- created_by
- created_at
- updated_at

### stock_movements
- id
- outlet_id
- ingredient_id
- type
- qty_in
- qty_out
- reference
- created_at

## 6. Alur Development

### Phase 1 — Setup Project
1. Buat project Laravel versi terbaru.
2. Install dan setup Filament.
3. Pakai MySQL Docker container `ocean_mysql`.
4. Buat database.
5. Setup `.env` koneksi database.
6. Buat panel Filament dan dashboard awal.

### Phase 2 — Auth & RBAC
1. Buat tabel users dengan field `role` dan `outlet_id`.
2. Buat seed akun awal untuk 4 role Tabel 4.2.
3. Buat login/logout Filament.
4. Buat enum/constant role.
5. Batasi menu/resource berdasarkan permission matrix.
6. Batasi query data berdasarkan scope role pusat/cabang.
7. Matikan create/edit/delete untuk role read-only.

### Phase 3 — Master Data
1. CRUD cabang/outlet.
2. CRUD bahan baku.
3. Isi stok minimum dan kebutuhan bahan per porsi.
4. CRUD user jika diperlukan.

### Phase 4 — Stok Dasar
1. Buat tabel stocks.
2. Buat fungsi tambah stok.
3. Buat fungsi kurangi stok.
4. Buat validasi stok cukup.
5. Buat catatan mutasi stok.

### Phase 5 — Pembelian
1. Buat form pembelian.
2. Admin pusat input bahan, jumlah, harga.
3. Simpan pembelian.
4. Tambah stok pusat otomatis.
5. Simpan mutasi `purchase_in`.

### Phase 6 — Distribusi
1. Buat form distribusi.
2. Admin pusat pilih cabang tujuan.
3. Admin pusat pilih bahan dan jumlah.
4. Sistem cek stok pusat.
5. Jika cukup: kurangi stok pusat.
6. Tambah stok cabang.
7. Simpan mutasi distribusi.

### Phase 7 — Penjualan Harian
1. Buat form input jumlah porsi terjual.
2. Sistem hitung kebutuhan bahan = porsi x `usage_per_portion`.
3. Sistem cek stok cabang.
4. Jika cukup: simpan penjualan.
5. Kurangi stok bahan otomatis.
6. Simpan mutasi `sale_out`.

### Phase 8 — Monitoring & Notifikasi
1. Buat halaman stok pusat.
2. Buat halaman stok cabang.
3. Admin pusat lihat semua stok.
4. Admin cabang lihat stok cabangnya.
5. Tampilkan warning jika stok <= minimum_stock.

### Phase 9 — Laporan
1. Laporan stok per outlet.
2. Laporan penjualan per tanggal/outlet.
3. Laporan pembelian per tanggal.
4. Export CSV.
5. Export Excel.

### Phase 10 — Testing
1. Test login/logout.
2. Test CRUD cabang.
3. Test CRUD bahan baku.
4. Test pembelian menambah stok pusat.
5. Test distribusi mengurangi pusat dan menambah cabang.
6. Test penjualan mengurangi stok otomatis.
7. Test notifikasi stok minimum.
8. Test laporan tampil dan PDF bisa dibuka.

## 7. Acceptance Criteria

- Semua fitur pada Tabel 4.3 tersedia.
- Semua role hanya melihat fitur yang sesuai.
- Pembelian selalu menambah stok pusat.
- Distribusi selalu mengurangi stok pusat dan menambah stok cabang.
- Penjualan harian selalu mengurangi stok bahan otomatis.
- Stok tidak boleh minus.
- Notifikasi muncul saat stok minimum.
- Laporan stok, penjualan, pembelian bisa difilter dan export CSV/Excel.
- Sistem berjalan di Laravel + Filament dengan MySQL Docker `ocean_mysql`.

## 8. Concrete Steps

1. Buat project Laravel terbaru.
2. Install Filament terbaru.
3. Hubungkan `.env` ke Docker MySQL `ocean_mysql`.
4. Buat database dan tabel minimal.
5. Implement login/logout + RBAC Filament sesuai Tabel 4.2.
6. Implement CRUD cabang dan bahan baku via Filament Resource.
7. Implement stok dasar + mutasi.
8. Implement pembelian.
9. Implement distribusi.
10. Implement penjualan harian + stok otomatis.
11. Implement monitoring + notifikasi stok minimum.
12. Implement laporan stok, penjualan, pembelian + CSV/Excel.
13. Test semua fitur sesuai acceptance criteria.
