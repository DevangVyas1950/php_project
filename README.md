# FitLife (merged with Activitar template)

Your FitLife PHP/MySQL fitness app, restyled with the free **Activitar** template
(bundled locally under `assets/activitar/`), with the two projects fully merged
into one working codebase.

## Full site-wide theme (v2)

Every module — admin dashboard, user dashboard, diet chart, exercise library,
food log, daily tasks, profile, weight tracker, progress, login, and register —
now shares Activitar's actual design system, not just its color:

- **Typography**: Oswald for headings/buttons/labels, Nunito Sans for body
  text, loaded on every page via `assets/css/activitar-dashboard-theme.css`
- **Buttons**: every primary button (submit buttons, `.btn-primary`,
  `.btn-weight`, register/login CTAs) uses Activitar's signature orange
  gradient, uppercase lettering, and hover lift
- **Sidebar**: admin + user sidebars are now a dark charcoal panel with an
  orange active-link accent, matching the marketing site's header
- **Cards & stat cards**: consistent radius, shadow, and hover-lift across
  every dashboard
- **Forms**: focus states use the orange brand ring instead of default blue

This was done as a single additive stylesheet (`activitar-dashboard-theme.css`)
loaded after each page's existing CSS, so none of the original layout classes,
IDs, or JavaScript hooks were touched — every AJAX call and interaction still
works exactly as before, just restyled.

## What changed from your original FitLife project

1. **New public homepage** (`index.php`) — built from Activitar's `index.html`
   (hero slider, features, about, program cards, footer), wired to your real
   `login.php` / `register.php` instead of dummy links.
2. **Login moved** — your original `index.php` login/register card is now
   `login.php`. `register.php` is unchanged in logic, just recolored.
3. **Theme merge** — every FitLife green accent (`#1a7a4a` and its shades) was
   replaced project-wide with Activitar's orange brand color (`#e4381c`), so the
   admin panel, user dashboard, login, and register pages all match the new
   landing page. Status colors (success/error/warning badges) were left as-is.
4. **Activitar assets copied in** — `assets/activitar/css`, `js`, `fonts`, `img`
   so the template works fully offline, no dependency on the original zip.
5. **Two real bugs fixed** (pre-existing in your original FitLife code, found
   during testing — not related to the merge):
   - `admin/dashboard.php` and `includes/functions.php` queried a table/column
     that didn't exist (`food_logs.calories_consumed`) — corrected to the real
     table/column (`food_log.calories`, `food_log.logged_date`).
   - `user/progress.php` called an undefined function `bmiCategory()` — fixed
     to call the real function `bmi_category()`.

Every other module — auth, admin (users/food/exercise/diet management), user
dashboard, diet chart, exercise library, food log, daily tasks, weight tracker,
progress, profile — is untouched in logic and was tested end-to-end.

## How I tested it

I actually ran this: installed PHP + MariaDB, imported `sql/fitlife.sql`,
started `php -S` and used curl (with real session cookies) to:
- Load every public/admin/user page → all return HTTP 200
- Log in as admin and as a normal user
- Register a brand-new user through the real form
- Write to the database through the API (add a weight entry, log a food item,
  add a daily task) and confirmed the rows actually landed in MySQL
- Checked `php -l` on every `.php` file (zero syntax errors) and watched the
  PHP error log through the whole test run (zero fatal errors)

## How to run it yourself

You'll need PHP 8+ and MySQL/MariaDB (XAMPP, WAMP, Laragon, or MAMP all work).

1. Drop this folder into your web root (e.g. `htdocs/fitlife` in XAMPP).
2. Start Apache + MySQL.
3. Either:
   - Import `sql/fitlife.sql` into a database named `fitlife`, **or**
   - Do nothing — `config/db.php` will auto-create the database and tables
     (with sample exercises) the first time any page connects.
4. Visit `http://localhost/fitlife/` — you'll land on the new homepage.
5. Demo logins (from the seed data, password is `password` for all):
   - Admin: `admin@fitlife.com`
   - User: `rahul@example.com` or `priya@example.com`

If your MySQL root user has a password, update `config/db.php`
(`DB_USER` / `DB_PASS`). If you're not running from `/fitlife/` at your web
root, update `BASE_URL` in `config/constants.php`.

## Credit

The visual template is **Activitar** by Colorlib, used under its CC BY 3.0
license — attribution is kept in the homepage footer as required. Don't remove
that footer credit link.
