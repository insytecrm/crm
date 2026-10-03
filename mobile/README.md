# InSyte CRM — Mobile (Flutter)

Agent-focused mobile client for the Laravel CRM in `../web`.

## Prerequisites

- [Flutter SDK](https://docs.flutter.dev/get-started/install) (stable channel)
- Android Studio / Xcode / device emulator as needed

## Setup

```powershell
cd E:\crm\mobile
copy .env.example .env
flutter pub get
```

If `android/` and `ios/` folders are missing (this repo ships app code only):

```powershell
flutter create . --org com.insyte.crm --project-name insyte_crm
flutter pub get
```

## Run

```powershell
flutter run
```

## API

Point `API_BASE_URL` and `TENANT_SLUG` in `.env` at your local or staging web app. Mobile auth and Work API endpoints will be wired to match `web/routes` as the app grows.

## Structure

- `lib/main.dart` — app entry
- `lib/config/` — environment & API base URL
- `lib/features/` — auth, work queue, leads (stubs to expand)
