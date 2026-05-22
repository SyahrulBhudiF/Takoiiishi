# Warehouse Location Domain Implementation Plan

> **IMPORTANT**: Use plan-execute skill to implement this plan task-by-task.

**Goal:** Separate warehouse (`gudang`) from main outlet (`pusat`) so purchases enter warehouse and distributions leave warehouse.
**Architecture:** Keep using `outlets` as location table with `type` values `gudang`, `pusat`, `cabang`. Add warehouse lookup on `Outlet`, update forms/seeders/stock movements to treat warehouse as stock source while pusat remains sellable outlet.
**Tech Stack:** Laravel 13, Filament, Eloquent, PHP enums/strings for outlet type.

---

## Tasks

1. Update `app/Models/Outlet.php`
   - Add `warehouse()` method returning first outlet where `type = gudang`.
   - Keep `pusat()` for main outlet lookup.

2. Update outlet form/table labels
   - Add `gudang => Gudang` to `app/Filament/Resources/Outlets/Schemas/OutletForm.php`.
   - Improve type display in `app/Filament/Resources/Outlets/Tables/OutletsTable.php` if needed.

3. Update user outlet requirement
   - In `app/Enums/UserRole.php`, make `StaffGudang` require outlet too.
   - `isOutletScoped()` stays only `KaryawanOutlet`, so staff gudang still sees all stock.

4. Update user outlet selection
   - In `app/Filament/Resources/Users/Schemas/UserForm.php`, filter outlet options:
     - `staff_gudang` => only `gudang`
     - `karyawan_outlet` => `pusat` or `cabang`

5. Update purchase stock destination
   - In `app/Filament/Resources/Purchases/Pages/CreatePurchase.php`, replace `Outlet::pusat()` with `Outlet::warehouse()`.
   - Purchase stock adds to warehouse.

6. Update distribution source
   - In `app/Filament/Resources/Distributions/Pages/CreateDistribution.php`, replace `Outlet::pusat()` with `Outlet::warehouse()`.
   - Distribution subtracts from warehouse.

7. Update distribution target options
   - In `app/Filament/Resources/Distributions/Schemas/DistributionForm.php`, allow targets with `type in ['pusat', 'cabang']`.
   - Update labels/copy from cabang-only to outlet tujuan.

8. Update sales outlet options
   - In `app/Filament/Resources/Sales/Schemas/SaleForm.php`, allow sales at `pusat` and `cabang`.
   - Exclude `gudang`.

9. Update seeders
   - `database/seeders/RoleAndUserSeeder.php`:
     - Seed `Pusat Sumberpucung` type `pusat`.
     - Seed `Gudang Sukun` type `gudang`.
     - Assign staff gudang `outlet_id` to Gudang Sukun.
   - `database/seeders/DemoDataSeeder.php`:
     - Purchases add stock to gudang.
     - Distributions source from gudang.
     - Add distribution to pusat as main outlet if demo needs pusat stock.

10. Update distribution infolist copy
    - Replace “dari pusat ke cabang” with “dari gudang ke outlet”.

11. Verify references
    - Search `Outlet::pusat()` and `where('type', 'cabang')`.
    - Ensure no stock-source logic still uses pusat.

12. Run checks
    - `php artisan test` or targeted relevant tests if suite stale.
    - If DB dev safe: `php artisan migrate:fresh --seed`.
