# playground-app

Testowa aplikacja oparta na Laravel 12, Vue 3, TypeScript i Inertia. Środowisko developerskie działa w Docker Compose z PHP-FPM, Nginx, PostgreSQL i Vite.

## Uruchomienie

Wymagany jest Docker Desktop lub Docker Engine z obsługą Compose.

```bash
docker compose up --build
```

Po uruchomieniu:

- aplikacja: http://localhost:8080
- Vite HMR: http://localhost:5173
- PostgreSQL: `localhost:5432`

Migracje oraz seed podstawowych użytkowników wykonują się automatycznie przy starcie kontenera aplikacji.

## Konta testowe

Każde konto ma hasło `password`:

| Użytkownik | E-mail |
| --- | --- |
| Admin Testowy | `admin@example.com` |
| Anna Bójko | `anna@example.com` |
| Jan Kowalski | `jan@example.com` |

Można również utworzyć konto przez formularz rejestracji.

## Przydatne polecenia

```bash
docker compose exec app php artisan test
docker compose exec app php artisan migrate:fresh --seed
docker compose exec vite npm run lint
docker compose down
```

Dane PostgreSQL oraz zależności kontenerów są zapisane w nazwanych wolumenach. Aby usunąć także dane testowe, użyj świadomie `docker compose down -v`.

## Dokumentacja koncepcji PermLib

- [Projekt architektury i zakres pierwszej wersji](docs/architecture.md)
- [Research bibliotek, konkurencji i sensu produktu — 29.09.2026](docs/research-2026-09-29.md)
