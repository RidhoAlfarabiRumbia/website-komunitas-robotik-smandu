# Portal Robotik Agent Guide

## Project Shape

- Native PHP 8.3 OOP app using MySQL/MariaDB via PDO. Intentionally no framework, MVC, Composer, or frontend build step. No automated tests exist.
- `public/` is the document root (everything the browser hits): member-facing pages at `public/*.php`, admin pages at `public/admin/`, plus `assets/` and `uploads/`. Everything else lives in `app/`.
- `app/core/` = shared behavior (`Auth`, `Csrf`, `Model`, helpers, CRUD config); `app/models/` = entity DB behavior; `app/includes/` = bootstrap, layout, nav, reusable partials; `app/config/` = env loader, autoloader, PDO singleton.
- Keep business/data access in the model/core boundaries. Do not introduce a parallel framework or duplicate CRUD pages.

## Bootstrapping & Autoloading

- Member pages start `require_once __DIR__ . '/../app/includes/bootstrap.php';`; admin pages (one level deeper) use `__DIR__ . '/../../app/includes/bootstrap.php';`. Then enforce access with `Auth::requireRole('admin'|'anggota')` and set `$pageTitle`. `app/includes/bootstrap.php` loads config, autoload, and helpers — no other requires are needed.
- `app/config/autoload.php` only loads **classes** from `app/core/` and `app/models/`. `crud_config()` in `app/core/CrudConfig.php` is a function, so it is NOT autoloaded — `public/admin/crud.php` must keep its explicit `require_once __DIR__ . '/../../app/core/CrudConfig.php';`.
- `.env` lives at the project root (outside `public/`) and is required at runtime: `app/config/env.php` dies if it's missing. A safe placeholder `.env.example` is committed (no credentials). Keep the live `.env` and never commit or hard-code credentials.

## Page Anatomy (two distinct layouts)

- **Member pages** (`public/beranda.php`, `karya.php`, profil, dst.): `header.php` + `navbar_anggota.php` + content in `<div class="main">` + `footer.php`, all from `app/includes/`.
- **Admin pages** (`public/admin/*.php`): `app/includes/header.php` + inline `.topbar` + `<div class="admin-body">` with `_sidebar.php` (kept in `public/admin/`) + `app/includes/footer.php`. Do NOT include `navbar_anggota.php` here. Keep the `admin_link()` sidebar helper in `_sidebar.php` rather than hand-writing nav.
- `app/includes/partial_karya_card.php` and `partial_galeri_card.php` render one card from a loop variable already in scope (`$k`, `$g`). Include them *inside* a `foreach` that names that variable; they fall back to `public/assets/img/*.svg` when no photo.
- `base_url()` is the only way to build URLs + asset paths. It normalizes Windows backslashes from `dirname(SCRIPT_NAME)` and strips a trailing `/admin`, so admin pages can reach the project root too — never hard-code or hand-relative a link, and don't "fix" base_url back to the old implementation.

## Conventions & Gotchas

- Output with `e()` (including inside `base_url(e(...))` for image paths); use `redirect()`, `flash_set()`/`flash_get()`, `tanggal_indo()`, `trunc()`, and the `badge_*()` helpers already defined in `app/core/helpers.php`.
- `handle_upload($inputName, $subfolder)` accepts JPG/JPEG/PNG/WEBP ≤2MB, writes to `public/uploads/<subfolder>/`, and returns `uploads/<subfolder>/<file>` or `null`. On invalid input it already `flash_set`s a message — callers just branch on null. When editing via CRUD, an empty file field keeps the old stored path (only replaces when a new file is uploaded). Stored paths are relative to `public/` and rendered via `base_url()`.
- `Model::all($orderBy)` / `where($col, $val, $orderBy)` splice `$orderBy` into SQL verbatim; pass only internal constants, never user input. Values (`role`, `progres`, `publikasi`, select options in `CrudConfig.php`) are DB `ENUM`s/configured options — keep new values in sync with BOTH `database.sql` and `CrudConfig.php`.
- Session holds a snapshot: `Auth::anggota()` returns `$_SESSION['anggota']`, a copy of the logged-in member row made at login — it does not refresh after profile edits.
- `Auth::requireRole()` redirects the wrong role away (admin → `public/admin/dashboard.php`, anggota → `public/beranda.php`), so manually testing a page as the "wrong" role just lands elsewhere.

## Security Requirements

- Every state-changing POST must render `Csrf::field()` in the form and call `Csrf::verify()` before acting.
- Preserve role checks on protected pages; escape all HTML output including attributes/URLs; validate IDs, enum-like select values, links, and upload type/size at the handling boundary.
- Never commit credentials or replace the local `.env` with hard-coded connection settings.

## Database Access Rules (do not skip)

- **Never write to the database without asking the user first.** Any INSERT, UPDATE, DELETE, TRUNCATE, CREATE, ALTER or DROP — including indirect writes through a PHP script or a `Model::` call — requires explicit permission in the current conversation. Read-only queries are allowed without asking.
- When a task needs a data or schema change, **write a `.sql` file** (following the existing `migrate.sql` pattern), show its full contents, and let the user approve before anything is executed.
- Before running any PHP script, state whether it touches the database and whether it only reads or also writes.
- `.env` is blocked by permission rules. If credentials are genuinely needed, ask the user to paste the specific values. Do not try to reach the file through another tool, a helper script, or any indirect path.
- When a test needs fixtures, use an ID or row that cannot destroy real content, and verify row counts before and after. Never run a destructive command against real data to "see what happens".
- Note that permission rules are defense-in-depth, not a guarantee: this project's MySQL `root` account has an empty password, so the database is reachable without `.env`. These rules are therefore the primary control, not the config.

## Validation

- No test suite/build command. After changing PHP, run `php -l path/to/file.php` on each file, then exercise the affected flow in the XAMPP browser environment.
- Quick wiring smoke test without MySQL: `php -S 127.0.0.1:8123 -t public` and confirm protected pages `302` to `/login.php` while `login.php` is `200`. Logged-in DB flows (login, ajukan karya, CRUD) need MySQL running (start XAMPP's server; `Start-Service MySQL80` fails without admin).
- For DB/auth changes, use `database.sql` only when needed; verify both admin and anggota flows manually.
- Before changing product scope, check `PRD.md`; before changing setup or folder responsibilities, update or link `README.md` rather than duplicating docs here.

## Important Flows

- New member karya is created as `Pending` (see `public/ajukan-karya.php`); `public/admin/moderasi.php` moves it to `Approved`/`Rejected` with an optional `alasan_tolak` shown on the member's Profil Saya. Only `Karya::approved()` results are public.
- `public/admin/crud.php` behavior (fields, labels, options, upload subfolders, list columns) is configured per entity in `app/core/CrudConfig.php`; entity classes live in `app/models/` and extend `app/core/Model.php`.