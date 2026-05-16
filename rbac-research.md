# Research: Laravel + Filament RBAC for admin panels

## Summary
Use **Filament Shield + Spatie Laravel Permission** as the default RBAC stack for current Filament panels. Shield gives maintainable Filament-aware permission generation for resources/pages/widgets and policy scaffolding; Spatie remains the underlying roles/permissions engine, including team/tenant-scoped permissions. For exports, prefer Filament's built-in queued `ExportAction` unless you specifically need Laravel Excel features like advanced styling/multiple sheets.

## Findings
1. **Recommended stack: Filament Shield on top of Spatie Permission.** Shield is explicitly built to add access management to Filament panels using `spatie/laravel-permission`, supports Filament 4.x and 5.x, and covers resource/page/widget permissions, custom permissions, automatic policies, super-admin handling, tenancy support, discovery, localization, and seeders. [Shield docs](https://filamentphp.com/plugins/bezhansalleh-shield)

2. **Compatibility caution: Shield 4 is a rewrite.** Latest Shield docs mark the current iteration as not backward-compatible with Shield 3.x / 4 beta. For greenfield use current Shield. For existing apps, backup DB/config/resources and follow upgrade steps before regenerating permissions. [Shield docs](https://filamentphp.com/plugins/bezhansalleh-shield)

3. **Baseline setup steps.** Install Shield with `composer require bezhansalleh/filament-shield`, publish config, set `auth_provider_model`, add Spatie `HasRoles` to `User`, then run `php artisan shield:setup`. Spatie itself requires publishing config/migrations, enabling `teams` before migration if needed, clearing config cache, migrating, and adding `HasRoles` to the auth model. [Shield docs](https://filamentphp.com/plugins/bezhansalleh-shield), [Spatie install docs](https://spatie.be/docs/laravel-permission/v7/installation-laravel)

4. **Permission naming should be designed early.** Shield generates permission keys from entity/action/subject with configurable separator/case. Default config uses `separator: ':'`, `case: 'pascal'`, and generated permissions. If multiple resources share the same model/class basename, customize `FilamentShield::buildPermissionKeyUsing()` to include module/navigation group/outlet context to prevent collisions. [Shield permissions docs](https://filamentphp.com/plugins/bezhansalleh-shield)

5. **Resource permissions map to policies.** Shield derives resource permissions from configured policy methods and generates policies for resource models. Defaults include `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, `restore`, `forceDelete`, `forceDeleteAny`, `restoreAny`, `replicate`, and `reorder`. Keep global methods small; add resource-specific methods under `resources.manage`. [Shield resource/policy docs](https://filamentphp.com/plugins/bezhansalleh-shield)

6. **Policy enforcement remains Laravel-native.** Filament automatically checks Laravel model policies for standard resource CRUD operations, but custom actions/pages/Livewire/API logic must be explicitly authorized. Use Laravel policies for model/resource authorization; use gates/custom permissions for non-model abilities like `Export:Order`, `Impersonate:User`, or dashboard access. [Filament security docs](https://filamentphp.com/docs/5.x/advanced/security), [Laravel authorization docs](https://laravel.com/docs/13.x/authorization)

7. **Register/resolve policies deliberately.** Laravel auto-discovers policies by convention; for non-standard namespaces or third-party models, register with `Gate::policy()` or configure `Gate::guessPolicyNamesUsing()`. Shield docs also recommend this for nested models/third-party resources. [Laravel authorization docs](https://laravel.com/docs/13.x/authorization), [Shield policy docs](https://filamentphp.com/plugins/bezhansalleh-shield)

8. **Pages and widgets need Shield traits for enforcement.** Shield can generate page/widget view permissions, but enforcement requires `HasPageShield` on custom pages and `HasWidgetShield` on widgets. Exclude always-visible items like dashboard/account/system info widgets in config. [Shield pages/widgets docs](https://filamentphp.com/plugins/bezhansalleh-shield)

9. **Role assignment UI is not bundled.** Shield provides a Role resource but not user role assignment UI. Add a `Select`/`CheckboxList` relationship field on your User resource. In tenancy mode, save role relationship pivot values with Spatie's team foreign key and current `getPermissionsTeamId()`. [Shield users docs](https://filamentphp.com/plugins/bezhansalleh-shield)

10. **Multi-tenant/outlet scoping has two layers.** Use Filament tenancy for data scoping when users can belong to many outlets/teams; configure `->tenant(Outlet::class)` and implement `HasTenants::getTenants()` plus `canAccessTenant()` to prevent URL tenant guessing. Filament scopes tenant-aware resource queries and auto-associates new records, but custom queries/actions/pages still need manual scoping. [Filament tenancy docs](https://filamentphp.com/docs/5.x/users/tenancy), [Filament security docs](https://filamentphp.com/docs/5.x/advanced/security)

11. **Spatie team permissions fit outlet-specific roles.** Enable `'teams' => true` before Spatie migrations, or use `php artisan permission:setup-teams` if retrofitting. Set current team/outlet via middleware with `setPermissionsTeamId($id)`, and ensure middleware priority before route model binding; for Livewire, persist the middleware. When switching outlets, unset cached `roles`/`permissions` relations before authorization checks. [Spatie teams docs](https://spatie.be/docs/laravel-permission/v7/basic-usage/teams-permissions)

12. **Shield tenancy settings can scope the Role resource.** Configure `FilamentShieldPlugin::make()->scopeToTenant(true)` and tenant relationship names where roles should be outlet/tenant-specific. Use global roles sparingly, e.g. platform super-admin, and be explicit about whether roles are shared or per-outlet. [Shield tenancy docs](https://filamentphp.com/plugins/bezhansalleh-shield)

13. **Super-admin best practice: central bypass, limited assignment.** Shield supports super-admin role or gate interception. Laravel supports `Gate::before()`/policy `before()` for admin bypass. Prefer one consistent mechanism, audit who has it, and avoid assigning it per outlet unless Spatie teams semantics require it. [Shield docs](https://filamentphp.com/plugins/bezhansalleh-shield), [Laravel authorization docs](https://laravel.com/docs/13.x/authorization)

14. **Exports: use Filament built-in export first.** Filament 5 has `ExportAction`/`ExportBulkAction` for CSV/XLSX, exporter classes, generated columns, selected visible columns, formats, query modification, queued jobs, notifications, chunk size, max rows, private disks, filenames, CSV delimiters, XLSX styling, and queue customization. It uses job batches and database notifications, so publish queue batches, notifications, and Filament action migrations. [Filament export docs](https://filamentphp.com/docs/5.x/actions/export)

15. **Export security/RBAC.** Treat export as a permissioned custom action: add custom permissions like `Export:Orders`, call `->authorize()`/visibility checks on export actions, and ensure `modifyQueryUsing()` or exporter `modifyQuery()` preserves tenant/outlet scope and filters. Store export files on private storage such as S3/private policy; Filament warns public disks can expose export files and does not delete created files automatically. [Filament export docs](https://filamentphp.com/docs/5.x/actions/export), [Filament security docs](https://filamentphp.com/docs/5.x/advanced/security)

16. **Use pxlrbt/filament-excel only for advanced Excel needs.** `pxlrbt/filament-excel` supports Filament 4.x/5.x, resolves fields from table/form, Laravel Excel formatting/styling, CSV settings, queued exports, multiple export classes, multiple sheets, and custom download URLs. It is useful when business users need rich Excel output beyond Filament's built-in exporter. [Filament Excel plugin docs](https://filamentphp.com/plugins/pxlrbt-excel)

## Actionable recommendation
1. Install and configure **Spatie Permission + Shield** now; do not hand-roll RBAC tables.
2. Decide whether `Outlet` is a Filament tenant. If yes, enable Filament tenancy and Spatie teams before migrations.
3. Use policies for all resources; generate with Shield; register non-standard policies via `Gate::policy()`/`guessPolicyNamesUsing()`.
4. Use Shield page/widget traits and custom permissions for non-CRUD actions: exports, approvals, impersonation, settings.
5. Add a User resource role picker; in tenant mode, sync roles with team/outlet pivot values.
6. Put tenant/team ID setup in persistent middleware; test outlet switching and relation cache clearing.
7. Use Filament built-in exports by default; add private disk, max rows, chunk size, queue, and `Export:*` permission checks.
8. Use pxlrbt/filament-excel only if needing multiple sheets, Laravel Excel styling/formatting, or highly customized spreadsheet behavior.

## Sources
- Kept: Shield - Filament (https://filamentphp.com/plugins/bezhansalleh-shield) — official plugin docs for setup, compatibility, resource/page/widget generation, policies, tenancy.
- Kept: Spatie Laravel Permission install (https://spatie.be/docs/laravel-permission/v7/installation-laravel) — official install/migration/config basics.
- Kept: Spatie teams permissions (https://spatie.be/docs/laravel-permission/v7/basic-usage/teams-permissions) — official tenant/team-scoped roles and permissions.
- Kept: Filament tenancy (https://filamentphp.com/docs/5.x/users/tenancy) — official multi-tenant/outlet scoping guidance.
- Kept: Filament security (https://filamentphp.com/docs/5.x/advanced/security) — official authorization caveats for policies, custom actions, custom queries.
- Kept: Laravel authorization (https://laravel.com/docs/13.x/authorization) — official gates/policies behavior.
- Kept: Filament export action (https://filamentphp.com/docs/5.x/actions/export) — official CSV/XLSX export best practices.
- Kept: Filament Excel plugin (https://filamentphp.com/plugins/pxlrbt-excel) — plugin docs for advanced Excel exports.
- Dropped: FilamentMastery multi-tenant article — useful practical example, but secondary source; official Spatie/Filament docs were enough.
- Dropped: Blog comparisons/tutorials for exports — secondary/SEO; official Filament and plugin docs were more authoritative.

## Gaps
- Exact versions in the local project were not inspected; verify installed Laravel/Filament versions before choosing Shield/Excel plugin major versions.
- Need project decision: single-outlet users with simple global scopes vs full Filament tenancy with outlet switcher.
- Need domain decision: global roles plus outlet-scoped roles, or all roles outlet-scoped.
