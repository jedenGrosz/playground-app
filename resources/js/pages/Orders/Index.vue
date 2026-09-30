<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, ShoppingBag } from 'lucide-vue-next';
import { ref } from 'vue';

interface Order {
    id: number;
    dummyjson_id: number;
    total: string;
    discounted_total: string;
    total_products: number;
    total_quantity: number;
    status: string;
    external_user: { first_name: string; last_name: string; email: string; image?: string } | null;
    items: Array<{ id: number; title: string; quantity: number; discounted_total: string }>;
}

const props = defineProps<{
    orders: { data: Order[]; links: Array<{ url: string | null; label: string; active: boolean }>; total: number };
    filters: { search: string };
}>();

const search = ref(props.filters.search);
const money = (value: string) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value));
const filter = () => router.get('/orders', { search: search.value || undefined }, { preserveState: true, replace: true });
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Zamówienia', href: '/orders' }];
</script>

<template>
    <Head title="Zamówienia" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-5 p-4 md:p-6">
            <div>
                <p class="text-sm font-medium text-primary">Sprzedaż</p>
                <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">Zamówienia</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ orders.total }} koszyków traktowanych jako zamówienia testowe.</p>
            </div>
            <form class="flex gap-3 rounded-xl border bg-card p-4 shadow-sm" @submit.prevent="filter">
                <label class="relative flex-1"
                    ><Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" /><input
                        v-model="search"
                        type="search"
                        placeholder="ID, klient lub e-mail"
                        class="h-10 w-full rounded-lg border bg-background pl-9 pr-3 text-sm outline-none focus:ring-2 focus:ring-ring" /></label
                ><button class="h-10 rounded-lg bg-primary px-5 text-sm font-medium text-primary-foreground">Szukaj</button>
            </form>

            <div class="space-y-3">
                <Link
                    v-for="order in orders.data"
                    :key="order.id"
                    :href="`/orders/${order.id}`"
                    class="block rounded-xl border bg-card p-4 shadow-sm transition-colors hover:bg-muted/30 md:p-5"
                >
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <ShoppingBag class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="font-semibold">Zamówienie #{{ order.dummyjson_id }}</h2>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">zaimportowane</span>
                                </div>
                                <p class="truncate text-sm text-muted-foreground">
                                    {{
                                        order.external_user
                                            ? `${order.external_user.first_name} ${order.external_user.last_name} · ${order.external_user.email}`
                                            : 'Klient niedostępny'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-5 text-sm lg:text-right">
                            <div>
                                <div class="text-xs text-muted-foreground">Pozycje</div>
                                <div class="font-medium">{{ order.total_products }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-muted-foreground">Sztuki</div>
                                <div class="font-medium">{{ order.total_quantity }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-muted-foreground">Wartość</div>
                                <div class="font-semibold">{{ money(order.discounted_total) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 border-t pt-4">
                        <span v-for="item in order.items" :key="item.id" class="rounded-md bg-muted px-2.5 py-1 text-xs text-muted-foreground"
                            >{{ item.title }} × {{ item.quantity }}</span
                        >
                    </div>
                </Link>
            </div>
            <Pagination :links="orders.links" />
        </div>
    </AppLayout>
</template>
