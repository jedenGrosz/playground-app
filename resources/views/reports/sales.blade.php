<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 34px 36px 42px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font-family: "DejaVu Sans", sans-serif; font-size: 9px; }
        h1 { margin: 0 0 5px; font-size: 22px; color: #111827; }
        .subtitle { color: #667085; font-size: 9px; }
        .header { border-bottom: 2px solid #7c3aed; padding-bottom: 14px; margin-bottom: 14px; }
        .brand { color: #7c3aed; font-size: 10px; font-weight: bold; margin-bottom: 5px; }
        .filters { margin: 10px 0 13px; padding: 8px 10px; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 6px; color: #4b5563; }
        .filters span { margin-right: 18px; }
        .stats { width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 6px 0; }
        .stats td { width: 25%; padding: 9px 10px; border: 1px solid #e5e7eb; border-radius: 6px; }
        .stat-label { color: #667085; font-size: 8px; text-transform: uppercase; }
        .stat-value { margin-top: 3px; color: #111827; font-size: 15px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data thead { display: table-header-group; }
        table.data tr { page-break-inside: avoid; }
        table.data th { padding: 7px 6px; background: #111827; color: white; text-align: left; font-size: 7.5px; text-transform: uppercase; }
        table.data td { padding: 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f9fafb; }
        .right { text-align: right; }
        .muted { color: #667085; font-size: 8px; }
        .footer { position: fixed; left: 0; right: 100px; bottom: -24px; color: #98a2b3; font-size: 8px; }
    </style>
</head>
<body>
    <div class="footer">playground-app · wygenerowano {{ $generatedAt->format('d.m.Y H:i') }}</div>

    <header class="header">
        <div class="brand">PLAYGROUND-APP / RAPORTY</div>
        <h1>{{ $title }}</h1>
        <div class="subtitle">Raport zawiera wszystkie rekordy zgodne z filtrami, niezależnie od ustawień tabeli w aplikacji.</div>
    </header>

    <div class="filters">
        <strong>Filtry:</strong>
        <span>Kraj: {{ $filters['country'] ?: 'wszystkie' }}</span>
        @if ($filters['type'] === 'products')
            <span>Kategoria: {{ $filters['category'] ?: 'wszystkie' }}</span>
        @endif
        <span>Wartość od: {{ $filters['price_min'] !== null ? number_format($filters['price_min'], 2, ',', ' ') . ' USD' : 'bez limitu' }}</span>
        <span>Wartość do: {{ $filters['price_max'] !== null ? number_format($filters['price_max'], 2, ',', ' ') . ' USD' : 'bez limitu' }}</span>
        @if ($filters['search'])
            <span>Szukaj: {{ $filters['search'] }}</span>
        @endif
    </div>

    <table class="stats">
        <tr>
            <td><div class="stat-label">Rekordy</div><div class="stat-value">{{ number_format($summary['records'], 0, ',', ' ') }}</div></td>
            <td><div class="stat-label">Sprzedane sztuki</div><div class="stat-value">{{ number_format($summary['quantity'], 0, ',', ' ') }}</div></td>
            <td><div class="stat-label">Wartość katalogowa</div><div class="stat-value">{{ number_format($summary['total'], 2, ',', ' ') }} USD</div></td>
            <td><div class="stat-label">Wartość po rabatach</div><div class="stat-value">{{ number_format($summary['discounted_total'], 2, ',', ' ') }} USD</div></td>
        </tr>
    </table>

    @if ($filters['type'] === 'products')
        <table class="data">
            <thead><tr><th>ID</th><th>Produkt</th><th>Kategoria</th><th class="right">Zamówienia</th><th class="right">Sztuki</th><th class="right">Śr. cena</th><th class="right">Wartość</th><th class="right">Po rabatach</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>#{{ $row->dummyjson_product_id }}</td>
                    <td><strong>{{ $row->title }}</strong></td>
                    <td>{{ str_replace('-', ' ', $row->category) }}</td>
                    <td class="right">{{ number_format($row->orders_count, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row->total_quantity, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row->average_price, 2, ',', ' ') }} USD</td>
                    <td class="right">{{ number_format($row->total, 2, ',', ' ') }} USD</td>
                    <td class="right"><strong>{{ number_format($row->discounted_total, 2, ',', ' ') }} USD</strong></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <table class="data">
            <thead><tr><th>ID</th><th>Klient</th><th>Kraj</th><th class="right">Pozycje</th><th class="right">Sztuki</th><th class="right">Wartość</th><th class="right">Po rabatach</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>#{{ $row->dummyjson_id }}</td>
                    <td>
                        <strong>{{ $row->externalUser ? $row->externalUser->first_name . ' ' . $row->externalUser->last_name : 'Nieznany klient' }}</strong>
                        <div class="muted">{{ $row->externalUser?->email }}</div>
                    </td>
                    <td>{{ data_get($row->externalUser?->address, 'country', '—') }}</td>
                    <td class="right">{{ number_format($row->total_products, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row->total_quantity, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($row->total, 2, ',', ' ') }} USD</td>
                    <td class="right"><strong>{{ number_format($row->discounted_total, 2, ',', ' ') }} USD</strong></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
