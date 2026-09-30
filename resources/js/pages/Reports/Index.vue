<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, FileText, Filter, PackageCheck, ReceiptText, Search, WalletCards } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface OrderRow {
    id: number;
    dummyjson_id: number;
    total_products: number;
    total_quantity: number;
    total: string;
    discounted_total: string;
    external_user?: { id: number; first_name: string; last_name: string; email: string; address?: { country?: string } };
}

interface ProductRow {
    id: number;
    dummyjson_product_id: number;
    title: string;
    category: string;
    total_quantity: string;
    orders_count: string;
    total: string;
    discounted_total: string;
    average_price: string;
}

type Row = OrderRow & ProductRow;

const props = defineProps<{
    rows: {
        data: Row[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
    };
    summary: { records: number; quantity: number; total: number; discounted_total: number };
    filters: {
        type: 'orders' | 'products';
        search: string;
        country: string;
        category: string;
        price_min: number | null;
        price_max: number | null;
        per_page: number;
    };
    countries: string[];
    categories: string[];
}>();

const type = ref(props.filters.type);
const search = ref(props.filters.search);
const country = ref(props.filters.country);
const category = ref(props.filters.category);
const priceMin = ref(props.filters.price_min?.toString() ?? '');
const priceMax = ref(props.filters.price_max?.toString() ?? '');
const perPage = ref(props.filters.per_page.toString());

const params = () => ({
    type: type.value,
    search: search.value || undefined,
    country: country.value || undefined,
    category: type.value === 'products' ? category.value || undefined : undefined,
    price_min: priceMin.value || undefined,
    price_max: priceMax.value || undefined,
    per_page: perPage.value,
});
const apply = () => router.get('/reports', params(), { preserveState: true, replace: true });
const changeType = () => {
    category.value = '';
    apply();
};
const clear = () => {
    search.value = '';
    country.value = '';
    category.value = '';
    priceMin.value = '';
    priceMax.value = '';
    apply();
};
const pdfUrl = computed(() => {
    const query = new URLSearchParams();
    Object.entries(params()).forEach(([key, value]) => value !== undefined && query.set(key, String(value)));
    return `/reports/download?${query.toString()}`;
});
const money = (value: number | string) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value));
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Raporty', href: '/reports' }];
</script>

<template>
    <Head title="Raporty" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-primary">Analityka sprzedaży</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Raporty</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Filtruj dane, sprawdzaj podsumowania i pobieraj pełne zestawienia PDF.</p>
                </div>
                <a
                    :href="pdfUrl"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm"
                >
                    <Download class="size-4" />Pobierz wszystkie wyniki PDF
                </a>
            </div>

            <form class="rounded-xl border bg-card p-4 shadow-sm" @submit.prevent="apply">
                <div class="mb-3 flex items-center gap-2 text-sm font-medium"><Filter class="size-4" />Filtry raportu</div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <label class="space-y-1"
                        ><span class="text-xs text-muted-foreground">Rodzaj raportu</span
                        ><select v-model="type" class="h-10 w-full rounded-lg border bg-background px-3 text-sm" @change="changeType">
                            <option value="orders">Zamówienia</option>
                            <option value="products">Sprzedaż produktów</option>
                        </select></label
                    >
                    <label class="space-y-1"
                        ><span class="text-xs text-muted-foreground">Kraj</span
                        ><select v-model="country" class="h-10 w-full rounded-lg border bg-background px-3 text-sm">
                            <option value="">Wszystkie kraje</option>
                            <option v-for="item in countries" :key="item" :value="item">{{ item }}</option>
                        </select></label
                    >
                    <label v-if="type === 'products'" class="space-y-1"
                        ><span class="text-xs text-muted-foreground">Kategoria</span
                        ><select v-model="category" class="h-10 w-full rounded-lg border bg-background px-3 text-sm">
                            <option value="">Wszystkie kategorie</option>
                            <option v-for="item in categories" :key="item" :value="item">{{ String(item ?? '').replaceAll('-', ' ') }}</option>
                        </select></label
                    >
                    <label class="space-y-1"
                        ><span class="text-xs text-muted-foreground">Wartość od (USD)</span
                        ><input
                            v-model="priceMin"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0,00"
                            class="h-10 w-full rounded-lg border bg-background px-3 text-sm"
                    /></label>
                    <label class="space-y-1"
                        ><span class="text-xs text-muted-foreground">Wartość do (USD)</span
                        ><input
                            v-model="priceMax"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="bez limitu"
                            class="h-10 w-full rounded-lg border bg-background px-3 text-sm"
                    /></label>
                    <label class="space-y-1"
                        ><span class="text-xs text-muted-foreground">Wierszy w tabeli</span
                        ><select v-model="perPage" class="h-10 w-full rounded-lg border bg-background px-3 text-sm" @change="apply">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select></label
                    >
                </div>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                    <label class="relative flex-1"
                        ><Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" /><input
                            v-model="search"
                            type="search"
                            :placeholder="type === 'orders' ? 'ID, klient lub e-mail' : 'Nazwa produktu'"
                            class="h-10 w-full rounded-lg border bg-background pl-9 pr-3 text-sm"
                    /></label>
                    <button type="submit" class="h-10 rounded-lg bg-foreground px-5 text-sm font-medium text-background">Zastosuj filtry</button>
                    <button type="button" class="h-10 rounded-lg border px-5 text-sm font-medium hover:bg-muted" @click="clear">Wyczyść</button>
                </div>
            </form>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="item in [
                        { label: filters.type === 'orders' ? 'Zamówienia' : 'Produkty', value: summary.records, icon: FileText },
                        { label: 'Sprzedane sztuki', value: summary.quantity, icon: PackageCheck },
                        { label: 'Wartość katalogowa', value: money(summary.total), icon: ReceiptText },
                        { label: 'Wartość po rabatach', value: money(summary.discounted_total), icon: WalletCards },
                    ]"
                    :key="item.label"
                    class="rounded-xl border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between text-sm text-muted-foreground">
                        <span>{{ item.label }}</span
                        ><component :is="item.icon" class="size-4" />
                    </div>
                    <div class="mt-3 text-2xl font-semibold tracking-tight">{{ item.value }}</div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border bg-card shadow-sm">
                <div class="flex flex-col gap-2 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold">Podgląd raportu</h2>
                        <p class="text-xs text-muted-foreground">Tabela: {{ rows.from ?? 0 }}–{{ rows.to ?? 0 }} z {{ rows.total }} wyników</p>
                    </div>
                    <div class="text-xs text-muted-foreground">PDF obejmie wszystkie wyniki: {{ summary.records }} rekordów po filtrowaniu.</div>
                </div>
                <div class="overflow-x-auto">
                    <table v-if="rows.data.length" class="w-full min-w-[850px] text-sm">
                        <thead v-if="filters.type === 'orders'" class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                            <tr>
                                <th class="px-5 py-3">ID</th>
                                <th class="px-5 py-3">Klient</th>
                                <th class="px-5 py-3">Kraj</th>
                                <th class="px-5 py-3 text-right">Pozycje</th>
                                <th class="px-5 py-3 text-right">Sztuki</th>
                                <th class="px-5 py-3 text-right">Wartość</th>
                                <th class="px-5 py-3 text-right">Po rabatach</th>
                            </tr>
                        </thead>
                        <thead v-else class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                            <tr>
                                <th class="px-5 py-3">ID</th>
                                <th class="px-5 py-3">Produkt</th>
                                <th class="px-5 py-3">Kategoria</th>
                                <th class="px-5 py-3 text-right">Zamówienia</th>
                                <th class="px-5 py-3 text-right">Sztuki</th>
                                <th class="px-5 py-3 text-right">Śr. cena</th>
                                <th class="px-5 py-3 text-right">Po rabatach</th>
                            </tr>
                        </thead>
                        <tbody v-if="filters.type === 'orders'" class="divide-y">
                            <tr v-for="row in rows.data" :key="row.id" class="hover:bg-muted/30">
                                <td class="px-5 py-3">
                                    <Link :href="`/orders/${row.id}`" class="font-mono text-xs font-medium hover:text-primary hover:underline"
                                        >#{{ row.dummyjson_id }}</Link
                                    >
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-medium">
                                        {{ row.external_user ? `${row.external_user.first_name} ${row.external_user.last_name}` : 'Nieznany klient' }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">{{ row.external_user?.email }}</div>
                                </td>
                                <td class="px-5 py-3">{{ row.external_user?.address?.country ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">{{ row.total_products }}</td>
                                <td class="px-5 py-3 text-right">{{ row.total_quantity }}</td>
                                <td class="px-5 py-3 text-right">{{ money(row.total) }}</td>
                                <td class="px-5 py-3 text-right font-semibold">{{ money(row.discounted_total) }}</td>
                            </tr>
                        </tbody>
                        <tbody v-else class="divide-y">
                            <tr v-for="row in rows.data" :key="row.dummyjson_product_id" class="hover:bg-muted/30">
                                <td class="px-5 py-3">
                                    <Link
                                        v-if="row.id"
                                        :href="`/products/${row.id}`"
                                        class="font-mono text-xs font-medium hover:text-primary hover:underline"
                                        >#{{ row.dummyjson_product_id }}</Link
                                    ><span v-else>#{{ row.dummyjson_product_id }}</span>
                                </td>
                                <td class="px-5 py-3 font-medium">{{ row.title }}</td>
                                <td class="px-5 py-3 capitalize">{{ row.category?.replaceAll('-', ' ') ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">{{ row.orders_count }}</td>
                                <td class="px-5 py-3 text-right">{{ row.total_quantity }}</td>
                                <td class="px-5 py-3 text-right">{{ money(row.average_price) }}</td>
                                <td class="px-5 py-3 text-right font-semibold">{{ money(row.discounted_total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="p-12 text-center text-sm text-muted-foreground">Brak danych zgodnych z wybranymi filtrami.</div>
                </div>
            </section>
            <Pagination :links="rows.links" />
        </div>
    </AppLayout>
</template>
