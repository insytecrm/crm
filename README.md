# InSyte CRM (monorepo)

| Folder | Stack | Purpose |
|--------|--------|---------|
| [`web/`](web/) | Laravel + Vite | Tenant CRM (primary backend & admin UI) |
| [`mobile/`](mobile/) | Flutter | Field / agent mobile app |

## Web (Laravel)

Open **`web/`** as your Cursor/IDE project root for day-to-day CRM work.

```powershell
cd web
composer install
npm install
cp .env.example .env   # if needed
php artisan serve
npm run dev
```

See [web/README.md](web/README.md) for Laravel details.

## Mobile (Flutter)

Requires [Flutter SDK](https://docs.flutter.dev/get-started/install).

```powershell
cd mobile
flutter pub get
# First-time only: generate platform folders if missing
flutter create . --org com.insyte.crm --project-name insyte_crm
flutter run
```

Configure API URL in `mobile/.env` (see `mobile/.env.example`).

## Git

Single repository at this root tracks both `web/` and `mobile/`.

## Workspace

Optional: open [`crm.code-workspace`](crm.code-workspace) in VS Code/Cursor to work on web and mobile together.
