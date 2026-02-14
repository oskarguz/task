# Kalkulator kosztu dostawy

## Wymagania techniczne

### Otwartość na nowe reguły (OCP)

Dodanie nowej reguły (np. „Dostawa gabarytowa”, „Rabat dla stałych klientów”) wymaga **tylko** utworzenia nowej klasy w `src/` implementującej `App\Service\DeliveryCostRule\DeliveryCostRuleInterface`.  
Kalkulator (`CalculateDeliveryCost`) i konfiguracja nie wymagają zmian — reguły są rejestrowane przez `_instanceof` i `tagged_iterator` w `config/services.yaml`.

### Pokrycie testami

Wszystkie reguły biznesowe są pokryte testami jednostkowymi (katalog `tests/Unit/Service/DeliveryCostRule/`) oraz testem integracyjnym (`tests/Integration/Service/CalculateDeliveryCostTest.php`).

### Wynik na konsoli

Komenda `app:calculate-delivery-cost` wypisuje na stdout:

- opis użycia i dostępne opcje;
- **dane zamówienia** — wczytywane z pliku `src/Command/order.json`. Waga, wartość koszyka, datę zamówienia oraz kod kraju można nadpisać opcjami komendy;
- **podsumowanie zamówienia** — waga, wartość koszyka, kraj, data zamówienia (oraz dzień tygodnia);
- **wynik kalkulacji** — koszt dostawy w PLN.

Opcjonalne opcje komendy:

| Opcja | Skrót | Opis |
|-------|-------|------|
| `--weight` | `-w` | Waga przesyłki w kg (nadpisuje wartość z JSON) |
| `--totalPrice` | `-p` | Wartość koszyka w PLN (nadpisuje wartość z JSON) |
| `--countryCode` | `-cc` | Kod kraju, np. PL, DE, USA (nadpisuje wartość z JSON) |
| `--createdAt` | `-ca` | Data i czas zamówienia w formacie ISO 8601 (nadpisuje wartość z JSON) |

Szczegóły w sekcji „Usage” wyświetlanej przez komendę.

**Uwaga:** Pozycje zamówienia w JSON służą wyłącznie do prezentacji i nie są wykorzystywane w obliczeniach kosztu dostawy.

### Docker

Aplikacja uruchamiana jest w kontenerze. Poniższe komendy zakładają, że kontenery są włączone (`docker compose up -d`).

| Działanie | Komenda |
|-----------|---------|
| Uruchomienie kalkulatora | `docker compose exec php php bin/console app:calculate-delivery-cost` |
| Uruchomienie testów | `docker compose exec php php bin/phpunit` |
| Uruchomienie PHPStan | `docker compose exec php composer phpstan` |

Przykład z nadpisaniem danych z pliku:

```bash
docker compose exec php php bin/console app:calculate-delivery-cost --countryCode=USA --totalPrice=500
```
