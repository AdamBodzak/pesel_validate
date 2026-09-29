# PESEL Validator

Aplikacja do rejestracji osób z walidacją polskiego numeru PESEL. Formularz przyjmuje imię, nazwisko, PESEL, datę urodzenia i płeć, a następnie sprawdza poprawność numeru oraz jego zgodność z podaną datą urodzenia i płcią. Zarejestrowane osoby są widoczne na liście z zamaskowanym numerem PESEL.

**Stack:** PHP 8.2, Symfony 5.4, PostgreSQL 17, Doctrine ORM 3, Twig, Tailwind CSS 4, Alpine.js, Webpack Encore, PHPUnit 9, Docker.

## Szybki start

Wymagania: Docker z Docker Compose oraz `make`.

```bash
make build                                                    # obrazy Docker
make up                                                       # kontenery (Node od razu buduje assety w trybie watch)
make composer c="install"                                     # zależności PHP
make console c="doctrine:migrations:migrate --no-interaction" # schemat bazy
```

| Adres                                          | Co                                 |
|------------------------------------------------|------------------------------------|
| [http://localhost:8000](http://localhost:8000) | formularz rejestracji              |
| [http://localhost:8000/persons](http://localhost:8000/persons) | lista zarejestrowanych osób |
| [http://localhost:8081](http://localhost:8081) | Adminer (podgląd bazy, tylko dev)  |

Pierwsze budowanie assetów trwa kilkanaście sekund - postęp: `docker compose logs -f node`. Porty można zmienić zmiennymi `HTTP_PORT`, `ADMINER_PORT` i `DB_PORT`, np. `HTTP_PORT=8080 make up`.

### Rozwiązywanie problemów

| Objaw | Rozwiązanie |
|-------|-------------|
| `make up`: `port is already allocated` (np. lokalny PostgreSQL na 5432) | zmień port, np. `DB_PORT=5433 make up` (analogicznie `HTTP_PORT`, `ADMINER_PORT`) |
| strona zwraca błąd o brakującym `entrypoints.json` | assety jeszcze się budują - poczekaj na `webpack compiled successfully` w `docker compose logs -f node` |
| `permission denied` przy dostępie do Dockera | uruchamiaj komendy jako użytkownik z grupy `docker` (albo przez `sudo`) |
| brak `make` | wszystkie komendy z `Makefile` można wykonać bezpośrednio przez `docker compose`, np. `docker compose exec php composer install` |

## Komendy Makefile

| Komenda                 | Opis |
|-------------------------|------|
| `make` / `make help`    | lista komend |
| `make build`            | budowanie obrazów Docker |
| `make up` / `make down` | uruchomienie / zatrzymanie kontenerów |
| `make sh`               | powłoka w kontenerze PHP |
| `make composer c="…"`   | Composer, np. `make composer c="install"` |
| `make console c="…"`    | konsola Symfony, np. `make console c="debug:router"` |
| `make test c="…"`       | testy PHPUnit, np. `make test c="--testsuite Unit"` |
| `make test-db`          | utworzenie i migracja bazy testowej |
| `make npm c="…"`        | npm w jednorazowym kontenerze Node |
| `make assets`           | produkcyjne zbudowanie assetów |
| `make cs` / `make cs-fix` | sprawdzenie / poprawienie stylu kodu (PHP-CS-Fixer) |
| `make stan`             | analiza statyczna (PHPStan) |
| `make qa`               | wszystkie kontrole jakości: styl, analiza statyczna i testy |

## Jakość kodu

```bash
make qa   # styl kodu + analiza statyczna + testy
```

- **PHP-CS-Fixer** - standard `@Symfony` i `@PHP82Migration`, wymuszone `declare(strict_types=1)` oraz ścisłe porównania (`.php-cs-fixer.dist.php`).
- **PHPStan** - poziom 8 z rozszerzeniami dla Symfony (typy usług z kontenera), Doctrine (mapowanie encji) i PHPUnit (`phpstan.dist.neon`); bez pliku bazowego - cały kod przechodzi analizę bez wyjątków.

## Testy

```bash
make test-db   # jednorazowo: baza app_test + migracje
make test      # wszystkie testy
```

| Zestaw        | Zakres |
|---------------|--------|
| `Unit`        | domena (value objecty, encja), use case, typy Doctrine, walidatory, normalizer, ViewModel - bez bazy i frameworka |
| `Integration` | repozytorium i zapytanie listy na prawdziwym PostgreSQL |
| `Functional`  | pełne żądania HTTP: formularz, lista, endpoint AJAX (statusy, komunikaty, CSRF, prywatność) |

Testy integracyjne i funkcjonalne korzystają z osobnej bazy `app_test`; każdy test działa w transakcji wycofywanej po jego zakończeniu (`dama/doctrine-test-bundle`), więc baza pozostaje czysta. Przestarzałe wywołania (deprecations) w kodzie aplikacji powodują niepowodzenie testów.

## Walidacja numeru PESEL

Cała logika numeru PESEL znajduje się w jednym miejscu - `PeselValueObject`. Numer jest poprawny, gdy:

1. **Format** - dokładnie 11 cyfr. Białe znaki (także twarda spacja z kopiowania) są usuwane przed walidacją.
2. **Suma kontrolna** - wagi `1, 3, 7, 9, 1, 3, 7, 9, 1, 3` dla pierwszych 10 cyfr; ostatnia cyfra musi być równa `(10 − suma mod 10) mod 10`.
3. **Data urodzenia** - cyfry 1-6 to `RRMMDD`, a do miesiąca dodaje się przesunięcie zależne od stulecia:

   | Lata      | Miesiąc |
   |-----------|---------|
   | 1800-1899 | +80     |
   | 1900-1999 | +0      |
   | 2000-2099 | +20     |
   | 2100-2199 | +40     |
   | 2200-2299 | +60     |

   Data musi istnieć (z uwzględnieniem lat przestępnych) i nie może być z przyszłości. To ostatnie sprawdza `PeselConstraintValidator` - zależy od bieżącej daty (wstrzykiwany zegar), więc value object pozostaje od niej niezależny.
4. **Płeć** - 10. cyfra: parzysta - kobieta, nieparzysta - mężczyzna.

Formularz dodatkowo sprawdza, czy podana **data urodzenia i płeć zgadzają się z numerem PESEL** (błąd przy konkretnym polu), a przy zapisie - czy **numer nie jest już zarejestrowany** (sprawdzenie w use case oraz unikalny indeks w bazie na wypadek równoległych żądań). Zgodność danych z numerem jest też niezmiennikiem encji `PersonEntity`.

Po wpisaniu 11 cyfr formularz pokazuje podpowiedź z datą urodzenia i płcią odczytanymi z numeru oraz przycisk „Uzupełnij” (zapytanie AJAX do serwera - reguły nie są powielane w JavaScript). Formularz działa również bez JavaScriptu.

## Architektura

Projekt jest zorganizowany według DDD - bounded context `Person` z czterema warstwami:

```
src/
├── Person/
│   ├── Model/            # domena, czysty PHP: encja, value objecty, enumy, wyjątki, interfejs repozytorium
│   ├── Application/      # use case'y: RegisterPerson (komenda + handler), ListPersons (zapytanie odczytu)
│   ├── Infrastructure/   # Doctrine: mapowanie XML, typy DBAL, repozytorium, zapytanie SQL listy
│   └── UserInterface/    # HTTP: kontrolery, fabryki odpowiedzi, formularz, walidatory, ViewModel
└── Shared/
    └── Infrastructure/   # usługi techniczne wspólne dla kontekstów (zegar)
```

- **Domena bez zależności od frameworka** - encja nie ma atrybutów ORM (mapowanie XML w `Infrastructure`), nie ma setterów; powstaje przez `PersonEntity::register()`, który pilnuje zgodności danych z PESEL.
- **Identyfikatory** jako typowane value objecty (`PersonIdValueObject`, UUID v6) z własnymi typami DBAL; w PostgreSQL natywny typ `uuid`.
- **Zapis** - kontroler → `RegisterPersonHandler` → repozytorium (bez Messengera).
- **Odczyt (CQRS)** - lista pobierana czystym SQL (DBAL) do `ReadModel`, bez tworzenia encji; warstwa UI zamienia go na `ViewModel`.
- **Kontrolery jednoakcyjne i cienkie** - tylko tłumaczenie HTTP; odpowiedzi budują fabryki (`…ResponseFactory`).
- **Zegar** wstrzykiwany przez `Psr\Clock\ClockInterface` - testy dat są deterministyczne.
- **Zero tekstów w kodzie UI** - wszystkie komunikaty w `translations/*.pl.yaml`.
- **Konwencja nazw** - sufiks mówi, czym jest klasa: `…ValueObject`, `…Entity`, `…Enum`, `…Exception`, `…Interface`, `…Constraint`, `…Controller`, `…Handler`, `…FormType`, `…ViewModel` itd.

## Bezpieczeństwo i prywatność

PESEL jest daną osobową, dlatego:

- na liście osób jest **maskowany po stronie serwera** (`440514*****`) - pełny numer nie trafia do szablonu ani do HTML,
- **nie pojawia się w logach** - komunikaty wyjątków domenowych i błędów konwersji typów Doctrine nie zawierają numeru,
- **nigdy nie jest przesyłany w adresie URL** - endpoint podpowiedzi przyjmuje go wyłącznie w treści żądania JSON,
- endpoint podpowiedzi (`POST /pesel/decode`) **nie korzysta z bazy**, więc nie ujawnia, czy dany numer jest zarejestrowany.

Formularz i endpoint AJAX są chronione **tokenem CSRF** (endpoint - w nagłówku `X-CSRF-Token`).

## Konfiguracja Docker Compose

| Plik                    | Zawartość |
|-------------------------|-----------|
| `compose.yaml`          | usługi aplikacji: `php` (PHP 8.2-FPM), `nginx`, `database` (PostgreSQL 17) |
| `compose.override.yaml` | tylko dev: Adminer, port bazy (`5432`) wystawiony na hosta oraz Node (przebudowa assetów w trybie watch) |

`docker compose` (a więc i `make up`) automatycznie dołącza `compose.override.yaml`, dlatego lokalnie narzędzia deweloperskie działają bez dodatkowych flag. Kontenery PHP i Node działają z UID/GID użytkownika hosta, więc tworzone pliki nie należą do roota.

Na produkcji należy jawnie wskazać tylko plik bazowy, aby Adminer nie został uruchomiony, a baza nie była dostępna z zewnątrz:

```bash
docker compose -f compose.yaml up -d
```

### Podgląd bazy danych (Adminer, tylko dev)

Adminer jest dostępny pod adresem [http://localhost:8081](http://localhost:8081):

| Pole     | Wartość    |
|----------|------------|
| System   | PostgreSQL |
| Serwer   | `database` |
| Username | `app`      |
| Password | `app`      |
| Database | `app` (testy: `app_test`) |

## Produkcja

Pliki `.env` i `.env.dev` w repozytorium zawierają wyłącznie niesekretne wartości domyślne oraz konfigurację środowiska deweloperskiego. W `.env` `APP_SECRET` jest celowo pusty.

Na produkcji sekrety (`APP_SECRET`, dane dostępowe do bazy) muszą być dostarczone jako prawdziwe zmienne środowiskowe (np. Docker Secrets, sekrety CI/CD), które mają pierwszeństwo przed plikami `.env`. Następnie konfigurację kompiluje się poleceniem:

```bash
composer dump-env prod
```

Assety buduje się jednorazowo (`make assets`); na produkcji kontener Node nie jest potrzebny. Logi (Monolog) trafiają w formacie JSON na standardowe wyjście błędów kontenera.
