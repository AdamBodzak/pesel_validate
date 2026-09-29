# PESEL Validator

Aplikacja sprawdzająca poprawność polskiego numeru PESEL.

Stack: PHP 8.2, Symfony 5.4, PostgreSQL, Docker.

> Dokumentacja w przygotowaniu.

## Produkcja

Pliki `.env` i `.env.dev` w repozytorium zawierają wyłącznie niesekretne wartości domyślne oraz konfigurację środowiska deweloperskiego. W `.env` `APP_SECRET` jest celowo pusty.

Na produkcji sekrety (`APP_SECRET`, dane dostępowe do bazy) muszą być dostarczone jako prawdziwe zmienne środowiskowe (np. Docker Secrets, sekrety CI/CD), które mają pierwszeństwo przed plikami `.env`. Następnie konfigurację kompiluje się poleceniem:

```bash
composer dump-env prod
```
