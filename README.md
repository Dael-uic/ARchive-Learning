# Laravel + MySQL version — ARchive Learning

This mirrors `/backend` (Python) exactly — same endpoints, same request/response
shapes — so the same static frontend (already copied into `public/`) works
against either backend unchanged.

---

## How to run / simulate the app

This section is written from an actual first-time setup on Windows + XAMPP,
including every real error that came up and how it was fixed — not a generic
tutorial. Follow it in order.

### 1. Prerequisites

- PHP 8.2+ (XAMPP's bundled PHP works)
- Composer
- MySQL running (XAMPP's MySQL, or MySQL Workbench's server)
- Node.js (only needed if you want `npm run dev`/Vite — this project's frontend
  is plain static HTML/JS, so this is optional)

### 2. Scaffold Laravel and copy in the project files

A full Laravel app has hundreds of framework files (`vendor/`, bootstrap
scaffolding, etc.) that aren't useful to hand-copy — so create a real Laravel
project first, then drop this project's specific files into it:

```powershell
composer create-project laravel/laravel archive-learning
cd archive-learning
```

Copy these from this repo into the new project (overwrite when prompted):

```
app/Models/                  → archive-learning/app/Models/
app/Http/Controllers/        → archive-learning/app/Http/Controllers/
database/migrations/         → archive-learning/database/migrations/
routes/api.php               → archive-learning/routes/api.php
routes/web.php                → archive-learning/routes/web.php
config/cors.php                → archive-learning/config/cors.php
public/*.html, *.js, *.json     → archive-learning/public/
```

**⚠️ Delete Laravel's own default users migration** — it conflicts with this
project's custom one (both try to create a `users` table):
```powershell
del database\migrations\0001_01_01_000000_create_users_table.php
```
Leave the default `cache` and `jobs` migrations alone — those don't conflict.

### 3. Configure `.env`

Laravel's `composer create-project` already generates a working `.env` with an
`APP_KEY` and SQLite by default. Edit it to match this project's needs —
**every line below matters, each one caused a real failure when wrong**:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arch-learn
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
FILESYSTEM_DISK=public
```

- **`SESSION_DRIVER=file`, not `database`** — the default tries to query a
  `sessions` table that this project never creates (we don't use Laravel's
  built-in session auth; our own login talks straight to `/api/login`).
  Leaving this on `database` throws `Base table or view not found: sessions`.
- **`FILESYSTEM_DISK=public`, not `local`** — Laravel 11+ changed the
  default `local` disk's root to `storage/app/private`, separate from
  `storage/app/public`. Without this, uploaded `.glb` files silently save to
  the wrong folder with **no error at all** (that disk's config has
  `'throw' => false` and `'report' => false`), and the AR viewer shows a
  blank stage because the file the database points to doesn't exist where
  expected.

If you ever see `No application encryption key has been specified`, run:
```powershell
php artisan key:generate
```

### 4. Enable API routing (Laravel 11+ specific gotcha)

Unlike older Laravel versions, `routes/api.php` isn't loaded automatically.
Check:
```powershell
php artisan route:list --path=api
```
If nothing shows up, register it:
```powershell
php artisan install:api
```
Then confirm `bootstrap/app.php` has an `api:` line:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',   // must be present
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```
If `install:api` overwrote `routes/api.php` with its own boilerplate,
re-copy this project's version back in afterward.

**Symptom if this is missed:** every registration/login attempt fails with a
generic "Could not create account" message in the UI — the frontend shows
that same message for a 404 as it would for a genuinely duplicate email, so
this is easy to misdiagnose. Check `php artisan route:list --path=api` first
whenever auth "just doesn't work."

### 5. Create the MySQL database

The database itself does **not** get created automatically — `php artisan
migrate` only creates tables *inside* an existing database.

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE arch-learn CHARACTER SET utf8mb4;"
```
(If `mysql.exe` isn't on that path, search for it with
`Get-ChildItem -Path C:\ -Filter mysql.exe -Recurse`, or create the database
via MySQL Workbench's GUI instead — same SQL statement works in a query tab.)

**Symptom if this is skipped:** `SQLSTATE[HY000] [1049] Unknown database
'arch-learn'` on every command that touches the database.

### 6. Migrate and link storage

```powershell
php artisan migrate
php artisan storage:link
```
`storage:link` creates a symlink so uploaded files at
`storage/app/public/models/...` are reachable over HTTP at
`/storage/models/...`. If it says the link already exists but uploads still
404, delete and recreate it:
```powershell
Remove-Item public\storage -Force
php artisan storage:link
```

### 7. Run it

```powershell
php artisan serve
```
Open **http://localhost:8000/login.html**.

> Don't use `composer run dev` on Windows — it also tries to run `php artisan
> pail` (live log viewer), which depends on the `pcntl` PHP extension, which
> doesn't exist on Windows. It'll crash and kill the whole process group,
> including the server. Plain `php artisan serve` is all this project needs.

### 8. Create accounts and try the app

Register a **teacher** account and a **student** account from the login page.
Log in as teacher → upload a `.glb`/`.gltf` file → add a quiz question. Log in
as student → open the model → rotate/zoom it → take the quiz.

**To see who's registered** (passwords are bcrypt-hashed and cannot be
recovered — reset them instead of trying to "look them up"):
```powershell
php artisan tinker
```
```php
\App\Models\User::all(['id', 'name', 'email', 'role']);

// to reset a forgotten password:
$user = \App\Models\User::where('email', 'teacher@test.com')->first();
$user->password = \Illuminate\Support\Facades\Hash::make('newpassword123');
$user->save();
exit
```

---

## Testing AR on a phone

The AR button only appears/works on a real phone — desktop browsers have no
equivalent OS-level AR system to hand off to.

### Same Wi-Fi network (try this first)
1. Get your laptop's LAN IP: `ipconfig` → note the IPv4 address
2. Run the server so it's reachable from other devices:
   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```
3. Allow the port through Windows Firewall (as Administrator):
   ```powershell
   New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -Profile Any
   ```
4. On your phone, same Wi-Fi, browse to `http://<laptop-IP>:8000/login.html`
   — **not** `http://0.0.0.0:8000`, that address isn't a real destination,
   it only means "listen on every interface" to the server itself.

If this still fails even with the firewall rule confirmed active, many
mobile hotspots and public/campus Wi-Fi networks enable **client isolation**,
which blocks devices on the same network from reaching each other. No
firewall fix solves this — use the tunnel option below instead.

### Tunnel option (works regardless of network setup — most reliable for demos)
```powershell
php artisan serve
```
In a second terminal:
```powershell
ngrok http 8000
```
Use the `https://...ngrok-free.dev` forwarding URL it prints, on any device,
over any network (including mobile data) — e.g.
`https://your-tunnel.ngrok-free.dev/login.html`. First visit may show ngrok's
free-tier interstitial warning page — click **Visit Site** to continue.

### AR device limitations (hardware, not fixable in code)
- **Android** needs **ARCore** support — not every Android device has it.
  Check https://developers.google.com/ar/devices if AR fails with "Google
  Play Services for AR is not supported on this device."
- **iOS** uses Quick Look AR, which requires a **`.usdz`** file — this
  project only ships `.glb`/`.gltf` per the requirements doc. Converting a
  model to `.usdz` (via Reality Converter or Blender's USDZ exporter) is a
  separate step if iOS AR specifically is needed for a demo.
- The non-AR 3D viewer (rotate/pan/zoom) works on any device/browser
  regardless of AR hardware support, and already demonstrates the core FR3
  requirement on its own.

---

## Troubleshooting quick reference

| Symptom | Cause | Fix |
|---|---|---|
| "Could not create account" on every registration attempt | `routes/api.php` not registered | `php artisan install:api`, confirm `api:` line in `bootstrap/app.php` |
| `Unknown database 'arch-learn'` | Database was never created | `CREATE DATABASE arch-learn CHARACTER SET utf8mb4;` |
| `table "users" already exists` during migrate | Laravel's default users migration conflicts with this project's | Delete `0001_01_01_000000_create_users_table.php` |
| `No application encryption key has been specified` | `.env` reset/missing `APP_KEY` | `php artisan key:generate` |
| `Table 'sessions' doesn't exist` | `SESSION_DRIVER=database` (this app doesn't use Laravel sessions) | Set `SESSION_DRIVER=file` in `.env`, then `php artisan config:clear` |
| Quiz loads but 3D model area is blank | Either the `<model-viewer>` CDN script is blocked, or `FILESYSTEM_DISK` wasn't `public` when uploading | Check browser DevTools Console for a blocked-script error; set `FILESYSTEM_DISK=public` and re-upload |
| Uploaded file not found in `storage/app/public/models/` | `FILESYSTEM_DISK=local` (or unset) at time of upload → file silently saved to `storage/app/private/public/models/` instead | Fix `.env`, confirm `ModelController::upload()` uses `$file->storeAs('models', $name, 'public')` explicitly, delete the stale DB row, re-upload |
| `php artisan serve --host=0.0.0.0` "doesn't load" | Usually a stale process already holding port 8000, or trying to *browse to* `0.0.0.0` itself (not a real address) | `netstat -ano | findstr :8000` → kill stale PID; browse to your actual LAN IP, not `0.0.0.0` |
| Phone can't reach laptop's IP even with firewall rule confirmed | Network-level client isolation (common on hotspots/campus Wi-Fi) | Use an ngrok tunnel instead — see AR testing section above |
| "Google Play Services for AR is not supported on this device" | Phone's hardware/OS doesn't support ARCore | Try a different Android device, or check against Google's supported-devices list |

---

## File map

```
app/Models/               Eloquent models (User, Model3D, QuizQuestion, QuizResult, ViewSession)
app/Http/Controllers/     AuthController, ModelController, QuizController, ProgressController
database/migrations/      Table definitions — same shape as mysql/schema.sql
routes/api.php            /api/* endpoints, same shape as backend/main.py
routes/web.php            Redirects "/" to the login page
public/*.html/.js         The tested frontend from /web, with asset paths pointed
                           at Laravel's /storage/models/ instead of FastAPI's /uploads/
config/cors.php           Allows the Flutter app / any separate frontend origin to reach the API
```
