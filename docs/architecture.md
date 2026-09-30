# PermLib — projekt architektury

Status: propozycja do implementacji; aktualizacja kontekstu 30 września 2026. Przykłady kontraktów i konfiguracji są projektowane; nie stanowią istniejącego API.

## 1. Cel i granice odpowiedzialności

Firmowy chat lub agent jest stale zintegrowany z aplikacją wewnętrzną. Użytkownicy zlecają mu odczyty danych i wykonywanie zadań, które dotychczas realizowali przez interfejs aplikacji. Zarządzanie uprawnieniami jest jednym z dostępnych modułów tego połączenia.

Paczka PHP udostępnia operacje zarządzania dostępem istniejącemu asystentowi i mechanizmom aplikacji Laravel. Obsługuje trzy ścieżki:

- użytkownik wnioskuje o dostęp i, jeśli wymaga tego polityka, uzyskuje zgodę;
- uprawniony administrator zleca nadanie lub odebranie dostępu bez dodatkowego zatwierdzającego, o ile polityka na to pozwala;
- aplikacja wykonuje audyt na żądanie lub według harmonogramu i przedstawia ustalenia odpowiednim osobom.

Uniwersalny jest proces opisywania, uzgadniania, oceniania i wykonywania zmian. Znaczenie pojęć biznesowych dostarcza aplikacja. Paczka nie potrafi bez konfiguracji wywnioskować, czym są kraj, właściciel raportu, dział lub dopuszczalny konflikt ról.

AI rozpoznaje intencję, proponuje dopasowania i formułuje pytania. Serwer weryfikuje każde wejście oraz podejmuje egzekwowalne decyzje na podstawie danych i reguł aplikacji. Treść rozmowy, opis zasobu i odpowiedź modelu nie stanowią dowodu uprawnień ani zgody drugiej osoby.

### 1.1. Miejsce w środowisku firmowego asystenta

Docelowy przepływ: pracownik → istniejący chat/agent → narzędzia aplikacji → usługi i polityki Laravel. Narzędzia mogą dotyczyć raportów, klientów, zamówień lub uprawnień. PermLib dostarcza moduł uprawnień; integrator dostarcza pozostałe operacje biznesowe i obsługę rozmowy.

Stałe podłączenie oznacza dostępność integracji. Każde wywołanie ma aktualny, zweryfikowany kontekst użytkownika, organizacji i zakresu delegowanego agentowi. Połączenie techniczne nie zastępuje autoryzacji operacji. Dotyczy to także odczytów danych, wyszukiwania osób i katalogów.

Dwa podstawowe scenariusze:

1. Administrator mówi w zwykłej rozmowie: „Nadaj Annie podgląd raportów sprzedaży dla Polski”. Asystent korzysta z PermLib, doprecyzowuje brakujące dane i wykonuje dozwolony plan zgodnie z polityką. Wynik i powiadomienia wracają przez istniejące kanały.
2. Pracownik zleca: „Przygotuj raport sprzedaży dla Polski”. Narzędzie biznesowe sprawdza dostęp. Jeśli jest on niewystarczający, może zwrócić zdefiniowany przez aplikację opis brakującego zakresu oraz dopuszczalną ścieżkę wnioskowania. Asystent może zaproponować lub, przy odpowiednim upoważnieniu użytkownika, złożyć precyzyjny wniosek przez PermLib.

Sama odmowa operacji biznesowej nie upoważnia agenta do rozszerzania dostępu. Mapowanie odmowy na wniosek pochodzi z zaufanego kodu domenowego; model nie zgaduje nazwy roli administracyjnej. Informacja o brakującym dostępie podlega regułom widoczności i nie ujawnia niedostępnych zasobów.

Wznowienie zadania po uzyskaniu zgody jest opcjonalną funkcją hosta agenta. Wymaga utrwalonej treści pierwotnego zadania i upoważnienia do jego kontynuowania, związku z konkretnym przydziałem oraz ponownego sprawdzenia aktualnego dostępu. Zadanie jest wykonywane jako pierwotny zlecający. Zgoda na dostęp nie zatwierdza automatycznie dodatkowych operacji biznesowych; zmiana celu, zakresu lub wymaganej intencji wymaga ponownej oceny.

PermLib udostępnia katalog, narzędzia, ustrukturyzowane wyniki i zdarzenia. Własny interfejs chata, ogólne planowanie zadań oraz funkcje raportów lub CRM nie należą do pierwszej wersji paczki. Powiadomienia i kontynuacje mają adaptery do istniejącego środowiska; nie zakładamy, że każdy klient MCP potrafi samodzielnie wznowić rozmowę lub wyświetlić wiadomość bez aktywnego użytkownika.

## 2. Model dostępu

Przydział opisujemy jako: podmiot + akcja + rodzaj zasobu + zakres + warunki + okres obowiązywania + pochodzenie.

| Pojęcie | Przykład | Znaczenie |
| --- | --- | --- |
| Podmiot | Anna, wewnętrzne ID użytkownika | Osoba otrzymująca dostęp |
| Akcja | `reports.view`, `reports.export` | Konkretna operacja |
| Rodzaj zasobu | Raport | Obiekt lub zbiór danych chroniony przez aplikację |
| Zakres | Kategoria sprzedaż, kraj danych PL | Ograniczenie tego, czego dotyczy operacja |
| Warunek | Tylko projekty, do których Anna należy | Reguła oceniana z autorytatywnych danych |
| Okres | Do określonego momentu | Czas obowiązywania przydziału |
| Pochodzenie | Wniosek, polecenie, rola i jej rewizja | Ślad uzasadniający przydział |

Organizacja jest obowiązkową granicą izolacji ustalaną i weryfikowaną przez backend. Nie jest dowolnym filtrem, który model może usunąć. Rozróżniamy wykonującego polecenie, beneficjenta, wnioskodawcę i zatwierdzającego; mogą to być różne osoby.

Posiadanie dostępu nie oznacza prawa do jego nadawania. Polityka delegowania określa, jakie akcje, zakresy, terminy i osoby mogą być objęte zmianą przez danego wykonującego.

Warstwy dostępu nie muszą tworzyć drzewa. Kraj, projekt, dział i poufność mogą być niezależnymi wymiarami. Aplikacja może również określić zależności, np. wybór oddziału zawęża listę dostępnych projektów.

## 3. Katalog możliwości dostarczany przez aplikację

Każda akcja ma stabilny identyfikator i wersjonowany opis. Opis zawiera:

- nazwę, znaczenie biznesowe i synonimy pomocne przy wyszukiwaniu;
- rodzaj zasobu oraz znaczenie każdego wymiaru zakresu;
- typy wartości, dozwolone operatory i wymagane informacje;
- identyfikatory zaufanych dostawców wartości i zależności między wymiarami;
- jawnie dopuszczalne wartości domyślne i ich pochodzenie;
- zależności od innych uprawnień, implikacje i konflikty;
- reguły czasu obowiązywania, zatwierdzania i dodatkowego potwierdzenia;
- obsługiwane sposoby egzekwowania oraz pokrycie telemetrią.

Przykład deklaracji jednej akcji, w formacie ilustracyjnym:

```json
{
  "id": "reports.view",
  "revision": "3",
  "label": "Podgląd raportów",
  "resourceType": "report",
  "scopeBasis": "dataset_rows",
  "dimensions": [
    {
      "key": "report_category",
      "label": "Rodzaj raportu",
      "type": "enum_set",
      "required": true,
      "operators": ["in"],
      "valuesProvider": "report_categories",
      "question": "Jakiego rodzaju raportów ma dotyczyć dostęp?"
    },
    {
      "key": "data_country",
      "label": "Kraj, którego dotyczą dane w raporcie",
      "type": "enum_set",
      "required": true,
      "operators": ["in"],
      "valuesProvider": "report_data_countries",
      "dependsOn": ["report_category"],
      "question": "Dane z których krajów mają być dostępne?"
    }
  ],
  "resourceSelectionModes": ["snapshot", "dynamic"],
  "validityPolicy": "report_access_duration",
  "grantPolicy": "report_access_delegation",
  "enforcementAdapter": "reports",
  "usageAdapter": "report_operations"
}
```

Identyfikatory dostawców i reguł wskazują kod zarejestrowany przez programistę. Model nie przekazuje klas PHP, SQL, kodu polityk ani adresów URL do wykonania. Wartości pochodzą z odczytu aplikacji ograniczonego prawami rozmówcy i organizacją. Schemat może być konfigurowalny przez administratora, ale jego zmiana jest osobną, autoryzowaną operacją zarządzania polityką.

Wyszukiwanie osób, raportów i wartości ma własną kontrolę dostępu. Prawo do wnioskowania o zasób może być inne niż prawo do oglądania jego danych. Odpowiedzi, liczby wyników i uzasadnienia nie mogą ujawniać zasobów, których rozmówca nie ma prawa odkryć. Duże katalogi są przeszukiwane i stronicowane zamiast przesyłania ich w całości do modelu.

## 4. Silnik doprecyzowania

Backend ocenia ustrukturyzowany szkic i zwraca stan oraz kody powodów. Przykładowe stany oceny: `needs_clarification`, `denied`, `requires_approval`, `ready`, `no_change`, `unsupported`. Stan `ready` oznacza możliwość wykonania w aktualnym kontekście, nie zastępuje walidacji przy zapisie.

Braki i niejednoznaczności są danymi: pole, powód, dozwolone odpowiedzi i zależności. Model może sformułować pytanie naturalnym językiem, ale nie może usunąć wymogu. Interfejs formularza powinien móc obsłużyć ten sam proces bez AI.

Reguły pytań:

1. Rozstrzygnij tożsamość beneficjenta i znaczenie żądanej operacji.
2. Ustal wymagane zakresy, zaczynając od tych, które zawężają dalsze odpowiedzi.
3. Wykorzystaj informacje już jawnie podane; nie pytaj o nie ponownie, jeśli pozostają aktualne.
4. Dopytaj o istotne niejednoznaczności, np. kraj danych kontra lokalizacja właściciela raportu.
5. Ustal termin i sposób objęcia przyszłych zasobów, gdy mają znaczenie dla tej akcji.
6. Pokaż wynik i istotne skutki uboczne. Wykonaj jednoznaczne, dozwolone polecenie albo uruchom potwierdzenie wymagane przez politykę.

Brak wartości nie oznacza „wszystkie”. Nieznany wymiar lub operator daje błąd. Wybór „wszystkie” jest jawny, dostępny wyłącznie dla akcji i osoby, którym polityka na to pozwala, oraz ograniczony organizacją. Samo to, że użytkownik widzi tylko jedną opcję, nie dowodzi, że miał ją na myśli. Zaufane, skonfigurowane wartości domyślne są prezentowane jako część wyniku.

Przykładowa rozmowa, przy założeniu, że wymienione kategorie są widoczne dla rozmówcy:

> Administrator: Daj Annie Bójko dostęp do raportów.
>
> Chat: Dla Anny Bójko z działu sprzedaży: chodzi o podgląd czy również eksport? Dostępne kategorie to sprzedaż, koszty i magazyn.
>
> Administrator: Podgląd sprzedaży, tylko Polska.
>
> Chat: Ograniczę dane w raportach do Polski. Dostęp ma dotyczyć obecnych raportów, czy również przyszłych raportów sprzedaży spełniających ten warunek?
>
> Administrator: Także przyszłych, do końca października.
>
> Chat po wykonaniu: Anna otrzymała podgląd raportów sprzedaży z danymi dotyczącymi Polski do wskazanego terminu. Zakres obejmuje przyszłe pasujące raporty. Eksport nie został dodany.

Daty względne rozstrzyga backend z uwzględnieniem strefy czasowej i pokazuje jednoznaczny termin. Jeśli nie ma pewnego kontekstu, wymagane jest doprecyzowanie.

## 5. Semantyka zakresu i rzeczywiste egzekwowanie

Przykład znormalizowanego fragmentu przydziału po rozstrzygnięciu tożsamości i organizacji:

```json
{
  "subjectId": "user_anna",
  "tenantId": "tenant_demo",
  "capabilityId": "reports.view",
  "capabilityRevision": "3",
  "scope": {
    "mode": "dynamic",
    "allOf": [
      {"dimension": "report_category", "operator": "in", "values": ["sales"]},
      {"dimension": "data_country", "operator": "in", "values": ["PL"]}
    ]
  },
  "validUntil": "2026-10-31T23:00:00Z"
}
```

To ilustracyjny wynik backendu, nie zestaw zaufanych parametrów przyjmowanych od modelu. Powyższy termin odpowiada końcowi 31 października w strefie Europe/Warsaw; granica jest wyłączna.

W MVP wszystkie ograniczenia wewnątrz jednego zakresu łączymy przez AND; lista wartości operatora `in` oznacza wybór dowolnej wartości z tej listy. Obsługa bardziej złożonych alternatyw wymaga osobnych kompletnych zakresów, bez łączenia wymiarów, które przypadkiem tworzyłoby dodatkowe kombinacje.

Przydziały dodają dostęp zgodnie z jawną semantyką adaptera i polityką aplikacji. Globalne ograniczenia organizacji i twarde zakazy pozostają nadrzędne. Jeśli aplikacja ma inny model łączenia polityk, adapter musi go obsłużyć; rdzeń nie może zgadywać.

Węższy przydział nie zawęża automatycznie istniejącego szerszego dostępu. Jeśli Anna już ma wszystkie raporty przez inną rolę, polecenie „tylko Polska” może wymagać odebrania lub zastąpienia wcześniejszego dostępu. System pokazuje tę różnicę i wymaga jednoznacznej intencji zmiany. Efektywny dostęp obejmuje wszystkie role, grupy, przydziały i wyjątki, które aplikacja uznaje za źródła uprawnień.

- `snapshot`: dostęp do utrwalonego zbioru identyfikatorów wybranych zasobów; nowe zasoby nie wchodzą do niego automatycznie. Ograniczenia danych i twarde polityki nadal są oceniane przy użyciu.
- `dynamic`: dostęp do zasobów spełniających zapisany warunek w chwili użycia, także przyszłych. Zmiana atrybutów zasobu może włączyć go do zakresu lub wyłączyć. Musi to być ujawnione przy przydziale.

Trzeba rozróżnić dostęp do całego obiektu od filtrowania danych wewnątrz obiektu. Gotowy PDF zawierający kilka krajów nie może być uznany za bezpieczny tylko dlatego, że jego właściciel jest z Polski. Jeśli aplikacja nie potrafi wygenerować lub udostępnić odpowiednio ograniczonej wersji, operacja zwraca `unsupported`.

Kontrola zakresu musi działać przy odczycie pojedynczego zasobu, list, agregacji, eksporcie, zadaniach w tle i korzystaniu z pamięci podręcznej. Sam zapis kraju w metadanych przydziału niczego nie ogranicza. Backend uwzględnia czas wygaśnięcia przy autoryzacji; scheduler porządkuje dane, ale nie jest jedyną barierą dostępu po terminie.

Jeżeli istniejący mechanizm ról obsługuje tylko globalne role, adapter nie może twierdzić, że nadał rolę ograniczoną do kraju. Musi wspierać faktyczne ograniczenie przez polityki aplikacji lub odmówić takiej operacji. Integracja ze Spatie nie zastępuje implementacji zakresów w logice dostępu do danych.

## 6. Plan zmiany i wykonanie

Każda zmiana przechodzi przez utrwalony plan obejmujący aktora, beneficjenta, organizację, dokładną treść operacji, zakresy, terminy oraz wersje definicji i polityk. Backend zapisuje również:

- stan przed zmianą i przewidywany dostęp po zmianie;
- uprawnienia pochodzące z roli i osobne przydziały;
- zależności wymagające dodatkowej operacji oraz konflikty;
- różnicę między intencją rozmówcy a rzeczywistym skutkiem;
- wymagane zgody, ich zakres, ważność i pochodzenie;
- identyfikator do bezpiecznego ponawiania i wynik wykonania.

Model nie może zatwierdzić planu w imieniu innego użytkownika przez podanie jego ID. Zgoda pochodzi z uwierzytelnionej operacji zatwierdzającego i jest związana z konkretną wersją planu. Przy wrażliwych zmianach polityka może wymagać zaufanego widoku potwierdzenia lub dodatkowego uwierzytelnienia. Zwykłe, jednoznaczne polecenie uprawnionego administratora nie wymaga automatycznie drugiego pytania o zgodę.

Zmiana treści planu unieważnia dotychczasowe zgody na zmienioną treść. Przy wykonaniu ponownie sprawdzamy uprawnienia aktora i zatwierdzającego, zakres, stan beneficjenta, ważność planu, wersje ról i polityk. Porównanie z zapisanym stanem oraz zapis muszą być chronione przed równoległą zmianą, np. transakcją i kontrolą rewizji. Nieaktualny plan wraca do oceny; nie wykonujemy go ze starym wynikiem walidacji.

MVP wykonuje wieloelementową zmianę atomowo w jednej aplikacji i bazie, jeśli adapter zapewnia tę właściwość. Nieobsługiwany element blokuje cały plan, zamiast powodować nieujawniony częściowy sukces. Zewnętrzne systemy wymagają osobnego projektu koordynacji i jawnych stanów częściowego wykonania.

Powiadomienia powstają jako trwałe zdarzenia/outbox w tej samej transakcji co wynik zmiany. Kolejka wysyła je po zatwierdzeniu transakcji do zlecającego, beneficjenta i uprawnionego wnioskodawcy. Treść jest dostosowana do uprawnień odbiorcy. Powtórne dostarczenie zdarzenia nie wykonuje przydziału ponownie; identyfikator zdarzenia ogranicza duplikaty wiadomości. Nie obiecujemy dokładnie jednego dostarczenia w zewnętrznym kanale bez odpowiedniego wsparcia tego kanału.

## 7. Zmiany definicji, ról i aplikacji

Schemat katalogu, definicje akcji, role i reguły mają rewizje. Zmiana etykiety może zachować znaczenie; zmiana interpretacji zakresu wymaga jawnej migracji. Usunięcie wymiaru nie może zamienić starego ograniczenia w dostęp globalny. Nierozpoznawalny lub nieobsługiwany przydział nie daje dostępu i trafia do przeglądu.

Rola jest nazwanym zestawem uprawnień. Adapter określa, czy członkostwo śledzi bieżącą zawartość roli, czy jest utrwalonym zestawem przydziałów. Nie wolno udawać utrwalonego zestawu, gdy użyty silnik realizuje role dynamiczne.

Zmiana zawartości roli dynamicznej jest zmianą dostępu wszystkich jej posiadaczy. Musi mieć analizę wpływu, właściwą autoryzację, historię i wymagane zatwierdzenie. Samo wersjonowanie dokumentacji nie zapobiega rozszerzeniu roli przez zewnętrzny panel. Integracja musi objąć również te ścieżki zapisu, a audyt uzgadniać rzeczywisty stan z historią paczki.

Zmiana zależności lub konfliktów uruchamia ponowną ocenę oczekujących planów i odpowiedni przegląd aktywnych przydziałów. Twarde zakazy obowiązują w czasie rzeczywistym. Rozszerzenia istniejącego zakresu nie są migrowane automatycznie bez jawnej polityki i autoryzacji.

## 8. Audyty i użycie

Harmonogram aplikacji uruchamia miesięczny audyt niezależnie od aktywności chata. Rdzeń wylicza ustalenia, AI je objaśnia. Raport jest przechowywany i udostępniany zgodnie z uprawnieniami do audytu; dostawca modelu otrzymuje tylko niezbędne dane.

Telemetria udanych operacji zawiera co najmniej organizację, podmiot, akcję, zasób lub bezpieczny opis zakresu, czas i rewizję polityki. Sprawdzenie uprawnienia przy rysowaniu menu nie jest użyciem. Należy rejestrować wykonanie chronionej operacji, z jasno zdefiniowanym znaczeniem dla danego adaptera.

Przy nakładających się rolach często nie da się dowieść, z którego przydziału użytkownik faktycznie skorzystał. Raport może stwierdzić, że uprawnienie było używane lub że przydział nie wnosi unikalnego dostępu według obecnej polityki; nie powinien arbitralnie oznaczać pozostałych ról jako nieużywanych.

Każde ustalenie ma okres obserwacji, pokrycie telemetrią, dowody, ograniczenia wnioskowania i proponowane działanie. Brak danych oznacza „nie wiadomo”. Reguły uwzględniają sezonowość, dostęp awaryjny i skonfigurowane wyjątki.

Audyty obejmują nieaktywne konta, potencjalnie nieużywane dostępy, redundancje, konflikty, nieskuteczne wygaśnięcia i zmiany efektywnego dostępu po modyfikacji ról. Globalnie nieprzypisana definicja uprawnienia jest innym przypadkiem niż niewykorzystywany dostęp jednej osoby. Audyt może dotyczyć węższego zakresu, np. eksportu dla jednego kraju, jeśli telemetria na to pozwala.

W MVP audyt proponuje odebranie dostępu do zatwierdzenia. Automatyczne wygaśnięcie wcześniej ograniczonego czasowo przydziału wynika z jego warunków. Analiza statyczna kodu może być dodatkowym sygnałem, ale brak tekstowego odwołania nie dowodzi, że definicja jest nieużywana.

## 9. Kontrakty i moduły PHP

Na początek jedna paczka Composer z oddzielnymi modułami i opcjonalnymi adapterami; bez przedwczesnego dzielenia na wiele paczek.

| Projektowany kontrakt | Zadanie |
| --- | --- |
| `CapabilityCatalog` | Definicje akcji, wymiarów, ról i ich rewizje |
| `SubjectResolver` | Wyszukiwanie i jednoznaczne rozstrzyganie osób w dopuszczalnym zakresie |
| `ScopeProvider` | Dostępne wartości, zależności i walidacja zakresów |
| `AccessAdapter` | Efektywny dostęp, różnica przed/po i obsługiwane możliwości egzekwowania |
| `GrantPolicy` | Dopuszczalność nadania, delegowania, konfliktów i wymaganych zgód |
| `ChangeExecutor` | Transakcyjne wykonanie wersjonowanego planu i bezpieczne ponawianie |
| `UsageProvider` | Dane o faktycznym wykorzystaniu i ich pokryciu |
| `AuditRule` | Ustalenia oparte na danych i skonfigurowanych regułach |
| `NotificationDispatcher` | Powiadomienia z utrwalonych zdarzeń po wykonaniu |

Laravel dostarcza integrację z kontenerem, migracjami, politykami, kolejkami i harmonogramem. Adapter MCP oraz ewentualne HTTP API wywołują ten sam rdzeń. Wersje wspieranego PHP, Laravel i zależności należy ustalić przed implementacją na podstawie aplikacji docelowej.

Projektowane operacje dostępne dla AI: wyszukiwanie katalogu, wyszukiwanie podmiotów, pobieranie dozwolonych wartości, ocena szkicu, przygotowanie planu, złożenie wniosku, zatwierdzenie/odrzucenie, wykonanie gotowego planu oraz odczyt statusu i audytu. Każda ma odrębną autoryzację. Wykonanie wskazuje utrwalony plan i rewizję; nie jest ogólnym poleceniem SQL ani dowolnym `assignRole` omijającym plan.

## 10. Zakres pierwszej wersji i kryteria odbioru

Pierwsza wersja obejmuje integrację z jedną aplikacją Laravel, podstawowe role oraz zakresy `enum`, zbiory ID i termin obowiązywania. Raporty są przykładem domeny dostarczanej przez aplikację, a nie wbudowanym w rdzeń pojęciem. Relacje złożone i dowolny język polityk są rozszerzeniami, nie warunkiem pierwszego wdrożenia.

Przed wydaniem implementacja powinna przejść scenariusze:

1. Niejasne „raporty” wymaga ustalenia akcji i zakresu; nie nadaje dostępu globalnego.
2. Dwie osoby o tej samej nazwie wymagają rozstrzygnięcia bez wycieku danych z innych organizacji.
3. Bezpośrednie, jednoznaczne polecenie uprawnionego administratora wykonuje się bez zbędnej drugiej akceptacji.
4. Osoba posiadająca dostęp bez prawa delegowania nie może go nadać.
5. Znane odpowiedzi nie wywołują powtórnych pytań; zmiana odpowiedzi nadrzędnej ponownie weryfikuje zależne wartości.
6. Ograniczenie kraju działa na listach, szczegółach, agregatach i eksporcie; nie jest tylko metadanymi przydziału.
7. Nieobsługiwany zakres lub nieznany operator blokuje zmianę.
8. Dodanie węższego przydziału przy istniejącym szerszym dostępie pokazuje rzeczywisty efekt i nie udaje odebrania dostępu.
9. Rozróżniamy obecne zasoby i przyszłe zasoby pasujące do warunku.
10. Zmiana roli, polityki lub aktora między planem a wykonaniem wymusza aktualną ocenę.
11. Równoległe wykonanie i ponowienie nie powodują podwójnych zmian; awaria powiadomień nie powtarza nadania.
12. Wygaśnięcie jest egzekwowane mimo opóźnienia schedulera; zmiana nie przechodzi między organizacjami.
13. Instrukcje ukryte w opisie zasobu lub treści wniosku nie zmieniają polityki i nie tworzą cudzej zgody.
14. Audyt odróżnia brak użycia od braku obserwacji i uwzględnia nakładające się źródła dostępu.
15. Ta sama ścieżka planowania i wykonania działa z formularza bez udziału modelu.

## 11. Źródła i odniesienia

Poniższe materiały opisują mechanizmy, na których można oprzeć integrację. Szczegóły modelu i kontraktów powyżej są propozycją dla PermLib.

- [Laravel — Authorization](https://laravel.com/docs/13.x/authorization): Gates, Policies i przekazywanie kontekstu do autoryzacji.
- [Laravel — MCP](https://laravel.com/docs/13.x/mcp): warstwa integracji narzędzi z klientami AI.
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission): role i uprawnienia jako potencjalny adapter istniejącego systemu.
- [Open Policy Agent — Access Control Systems](https://www.openpolicyagent.org/docs/comparisons/access-control-systems): modele wykorzystujące role i atrybuty; odniesienie koncepcyjne, bez wymagania instalacji OPA.
- [OpenAI — Build an MCP server](https://developers.openai.com/plugins/build/mcp-server): walidacja wejść i autoryzacja na serwerze.
