<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Boxes, RefreshCw, ShoppingCart, Users, WalletCards } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Order {
    id: number;
    dummyjson_id: number;
    discounted_total: string;
    total_quantity: number;
    external_user: { first_name: string; last_name: string; email: string } | null;
}

interface Category {
    category: string;
    products_count: number;
}

const props = defineProps<{
    stats: { products: number; customers: number; orders: number; revenue: number; lowStock: number };
    recentOrders: Order[];
    categories: Category[];
    lowStockProducts: Array<{ id: number; title: string; category: string; stock: number; thumbnail?: string }>;
    sync: { status: string; counts?: Record<string, number>; finishedAt?: string } | null;
}>();

const page = usePage<SharedData>();
const syncing = ref(false);
const maxCategory = computed(() => Math.max(...props.categories.map((item) => item.products_count), 1));
const money = (value: number | string) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value));
const date = (value?: string) =>
    value ? new Intl.DateTimeFormat('pl-PL', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : 'brak';

const synchronize = () => {
    syncing.value = true;
    router.post('/data/sync', {}, { preserveScroll: true, onFinish: () => (syncing.value = false) });
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-primary">Commerce sandbox</p>
                    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">Centrum danych testowych</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Produkty, klienci i koszyki zsynchronizowane z DummyJSON.</p>
                </div>
                <button
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm disabled:opacity-60"
                    :disabled="syncing"
                    @click="synchronize"
                >
                    <RefreshCw class="size-4" :class="{ 'animate-spin': syncing }" />
                    {{ syncing ? 'Synchronizacja…' : 'Synchronizuj dane' }}
                </button>
            </div>

            <div v-if="page.props.flash.success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ page.props.flash.success }}
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div
                    v-for="item in [
                        { label: 'Produkty', value: stats.products, icon: Boxes, tone: 'bg-violet-100 text-violet-700' },
                        { label: 'Klienci', value: stats.customers, icon: Users, tone: 'bg-sky-100 text-sky-700' },
                        { label: 'Zamówienia', value: stats.orders, icon: ShoppingCart, tone: 'bg-amber-100 text-amber-700' },
                        { label: 'Wartość zamówień', value: money(stats.revenue), icon: WalletCards, tone: 'bg-emerald-100 text-emerald-700' },
                        { label: 'Niski stan', value: stats.lowStock, icon: AlertTriangle, tone: 'bg-rose-100 text-rose-700' },
                    ]"
                    :key="item.label"
                    class="rounded-xl border bg-card p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">{{ item.label }}</div>
                        <div class="rounded-lg p-2" :class="item.tone"><component :is="item.icon" class="size-4" /></div>
                    </div>
                    <div class="mt-3 text-2xl font-semibold tracking-tight">{{ item.value }}</div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <section class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <div>
                            <h2 class="font-semibold">Ostatnie zamówienia</h2>
                            <p class="text-xs text-muted-foreground">Zaimportowane koszyki zakupowe</p>
                        </div>
                        <a href="/orders" class="text-sm font-medium text-primary hover:underline">Zobacz wszystkie</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                                <tr>
                                    <th class="px-5 py-3">ID</th>
                                    <th class="px-5 py-3">Klient</th>
                                    <th class="px-5 py-3">Sztuki</th>
                                    <th class="px-5 py-3 text-right">Wartość</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-muted/30">
                                    <td class="px-5 py-3 font-mono text-xs">#{{ order.dummyjson_id }}</td>
                                    <td class="px-5 py-3">
                                        <div class="font-medium">
                                            {{
                                                order.external_user
                                                    ? `${order.external_user.first_name} ${order.external_user.last_name}`
                                                    : 'Nieznany klient'
                                            }}
                                        </div>
                                        <div class="text-xs text-muted-foreground">{{ order.external_user?.email }}</div>
                                    </td>
                                    <td class="px-5 py-3">{{ order.total_quantity }}</td>
                                    <td class="px-5 py-3 text-right font-medium">{{ money(order.discounted_total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-xl border bg-card p-5 shadow-sm">
                    <h2 class="font-semibold">Największe kategorie</h2>
                    <p class="mb-5 text-xs text-muted-foreground">Liczba produktów w lokalnej bazie</p>
                    <div class="space-y-4">
                        <div v-for="category in categories" :key="category.category">
                            <div class="mb-1.5 flex justify-between text-sm">
                                <span class="capitalize">{{ category.category.replaceAll('-', ' ') }}</span
                                ><span class="font-medium">{{ category.products_count }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-primary" :style="{ width: `${(category.products_count / maxCategory) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <section class="rounded-xl border bg-card p-5 shadow-sm">
                    <div class="mb-4">
                        <h2 class="font-semibold">Produkty wymagające uwagi</h2>
                        <p class="text-xs text-muted-foreground">Stan magazynowy nie większy niż 10 sztuk</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div v-for="product in lowStockProducts" :key="product.id" class="flex items-center gap-3 rounded-lg border p-3">
                            <img :src="product.thumbnail" :alt="product.title" class="size-11 rounded-md bg-muted object-cover" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium">{{ product.title }}</div>
                                <div class="text-xs capitalize text-muted-foreground">{{ product.category.replaceAll('-', ' ') }}</div>
                            </div>
                            <span class="rounded-full bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700">{{ product.stock }} szt.</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border bg-slate-950 p-5 text-slate-100 shadow-sm">
                    <div class="text-sm font-medium text-slate-300">Stan integracji</div>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-emerald-400" /><span class="text-lg font-semibold">DummyJSON połączony</span>
                    </div>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-400">Ostatnia synchronizacja</dt>
                            <dd class="text-right">{{ date(sync?.finishedAt) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Status</dt>
                            <dd class="capitalize">{{ sync?.status ?? 'brak' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Pełne odpowiedzi API</dt>
                            <dd>Zapisane</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
