# playground-app

Testowa aplikacja commerce oparta na Laravel 12, Vue 3, TypeScript i Inertia. Środowisko developerskie działa w Docker Compose z PHP-FPM, Nginx, PostgreSQL i Vite.

Aplikacja zawiera logowanie, dashboard, listy i szczegóły produktów, zamówień i klientów oraz lokalną kopię danych pobranych z DummyJSON. Pełne odpowiedzi API i surowy payload każdego rekordu są przechowywane w PostgreSQL, dzięki czemu projekt można swobodnie rozszerzać i używać do dalszych testów.

Moduł raportów udostępnia zestawienie zamówień i zagregowanej sprzedaży produktów. Dane można filtrować po kraju, kategorii, nazwie i zakresie wartości, zmieniać liczbę wierszy podglądu oraz pobierać wszystkie przefiltrowane rekordy do wielostronicowego PDF.

## Uruchomienie

Wymagany jest Docker Desktop lub Docker Engine z obsługą Compose.

```bash
docker compose up --build
```

Po uruchomieniu:

- aplikacja: http://localhost:8081
- Vite HMR: http://localhost:5173
- PostgreSQL: `localhost:5432`

Migracje, seed podstawowych użytkowników i pierwszy import z DummyJSON wykonują się automatycznie przy starcie kontenera aplikacji.

## Konto administratora

| Użytkownik | E-mail | Hasło |
| --- | --- | --- |
| admin | `admin@admin.com` | `admin` |

## Dane testowe

Koszyki DummyJSON są w aplikacji traktowane jako zamówienia. Import tworzy lokalne rekordy produktów, klientów, zamówień i pozycji zamówień oraz zapisuje kompletne odpowiedzi API jako snapshoty.

## Przydatne polecenia

```bash
docker compose exec app php artisan test
docker compose exec app php artisan dummyjson:sync --force
docker compose exec app php artisan migrate:fresh --seed
docker compose exec vite npm run lint
docker compose down
```

Dane PostgreSQL oraz zależności kontenerów są zapisane w nazwanych wolumenach. Aby usunąć także dane testowe, użyj świadomie `docker compose down -v`.

## Dokumentacja koncepcji PermLib

- [Projekt architektury i zakres pierwszej wersji](docs/architecture.md)
- [Research bibliotek, konkurencji i sensu produktu — 29.09.2026](docs/research-2026-09-29.md)
