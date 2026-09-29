# PESEL Validator

Aplikacja sprawdzająca poprawność polskiego numeru PESEL.

Stack: PHP 8.2, Symfony 5.4, PostgreSQL, Docker.

> Dokumentacja w przygotowaniu.

## Konfiguracja Docker Compose

| Plik                    | Zawartość                                                       |
|-------------------------|-----------------------------------------------------------------|
| `compose.yaml`          | usługi aplikacji: `php`, `nginx`, `database`                    |
| `compose.override.yaml` | tylko dev: Adminer oraz port bazy (`5432`) wystawiony na hosta  |

`docker compose` (a więc i `make up`) automatycznie dołącza `compose.override.yaml`, dlatego lokalnie narzędzia deweloperskie działają bez dodatkowych flag.

Na produkcji należy jawnie wskazać tylko plik bazowy, aby Adminer nie został uruchomiony, a baza nie była dostępna z zewnątrz:

```bash
docker compose -f compose.yaml up -d
```

## Podgląd bazy danych (Adminer, tylko dev)

Po uruchomieniu kontenerów (`make up`) Adminer jest dostępny pod adresem [http://localhost:8081](http://localhost:8081).

Dane logowania:

| Pole     | Wartość    |
|----------|------------|
| System   | PostgreSQL |
| Serwer   | `database` |
| Username | `app`      |
| Password | `app`      |
| Database | `app`      |

Port można zmienić zmienną `ADMINER_PORT`, np. `ADMINER_PORT=8082 make up`.

## Produkcja

Pliki `.env` i `.env.dev` w repozytorium zawierają wyłącznie niesekretne wartości domyślne oraz konfigurację środowiska deweloperskiego. W `.env` `APP_SECRET` jest celowo pusty.

Na produkcji sekrety (`APP_SECRET`, dane dostępowe do bazy) muszą być dostarczone jako prawdziwe zmienne środowiskowe (np. Docker Secrets, sekrety CI/CD), które mają pierwszeństwo przed plikami `.env`. Następnie konfigurację kompiluje się poleceniem:

```bash
composer dump-env prod
```
