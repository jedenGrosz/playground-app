<script setup lang="ts">
import JsonViewer from '@/components/JsonViewer.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Mail, MapPin, Package, ShoppingCart, UserRound } from 'lucide-vue-next';

interface OrderItem {
    id: number;
    product_id?: number;
    dummyjson_product_id: number;
    title: string;
    price: string;
    quantity: number;
    total: string;
    discount_percentage: string;
    discounted_total: string;
    thumbnail?: string;
    raw_payload: Record<string, unknown>;
    product?: { id: number; title: string; category: string; brand?: string; thumbnail?: string };
}

interface Customer {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    image?: string;
    address?: { address?: string; city?: string; state?: string; postalCode?: string; country?: string };
}

interface Order {
    id: number;
    dummyjson_id: number;
    total: string;
    discounted_total: string;
    total_products: number;
    total_quantity: number;
    status: string;
    synced_at: string;
    raw_payload: Record<string, unknown>;
    external_user?: Customer;
    items: OrderItem[];
}

const props = defineProps<{ order: Order }>();
const money = (value: string | number) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value));
const discount = Number(props.order.total) - Number(props.order.discounted_total);
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Zamówienia', href: '/orders' },
    { title: `#${props.order.dummyjson_id}`, href: `/orders/${props.order.id}` },
];
</script>

<template>
    <Head :title="`Zamówienie #${order.dummyjson_id}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <Link href="/orders" class="text-sm font-medium text-primary hover:underline">← Wróć do zamówień</Link>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-primary">Sprzedaż / szczegóły</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Zamówienie #{{ order.dummyjson_id }}</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Lokalny rekord #{{ order.id }} · status: {{ order.status }}</p>
                </div>
                <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-medium text-emerald-800">Zaimportowane</div>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="item in [
                        { label: 'Pozycje', value: order.total_products, icon: Package },
                        { label: 'Sztuki', value: order.total_quantity, icon: ShoppingCart },
                        { label: 'Wartość katalogowa', value: money(order.total), icon: ShoppingCart },
                        { label: 'Po rabatach', value: money(order.discounted_total), icon: ShoppingCart },
                    ]"
                    :key="item.label"
                    class="rounded-xl border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between text-sm text-muted-foreground">
                        <span>{{ item.label }}</span
                        ><component :is="item.icon" class="size-4" />
                    </div>
                    <div class="mt-3 text-2xl font-semibold">{{ item.value }}</div>
                </div>
            </section>

            <section v-if="order.external_user" class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex flex-col gap-5 md:flex-row md:items-center">
                    <img
                        :src="order.external_user.image"
                        :alt="`${order.external_user.first_name} ${order.external_user.last_name}`"
                        class="size-16 rounded-full bg-muted object-cover"
                    />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <UserRound class="size-4 text-muted-foreground" />
                            <h2 class="font-semibold">{{ order.external_user.first_name }} {{ order.external_user.last_name }}</h2>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-muted-foreground">
                            <span class="flex items-center gap-2"><Mail class="size-4" />{{ order.external_user.email }}</span>
                            <span class="flex items-center gap-2"
                                ><MapPin class="size-4" />{{ order.external_user.address?.city }}, {{ order.external_user.address?.country }}</span
                            >
                        </div>
                    </div>
                    <Link :href="`/customers/${order.external_user.id}`" class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted"
                        >Profil klienta</Link
                    >
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border bg-card shadow-sm">
                <div class="border-b px-5 py-4">
                    <h2 class="font-semibold">Pozycje zamówienia</h2>
                    <p class="text-xs text-muted-foreground">Pełne dane cenowe każdej pozycji</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                            <tr>
                                <th class="px-5 py-3">Produkt</th>
                                <th class="px-5 py-3 text-right">Cena</th>
                                <th class="px-5 py-3 text-right">Ilość</th>
                                <th class="px-5 py-3 text-right">Rabat</th>
                                <th class="px-5 py-3 text-right">Suma</th>
                                <th class="px-5 py-3 text-right">Po rabacie</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <img :src="item.thumbnail" :alt="item.title" class="size-11 rounded-md bg-muted object-contain" />
                                        <div>
                                            <Link
                                                v-if="item.product_id"
                                                :href="`/products/${item.product_id}`"
                                                class="font-medium hover:text-primary hover:underline"
                                                >{{ item.title }}</Link
                                            ><span v-else class="font-medium">{{ item.title }}</span>
                                            <div class="text-xs text-muted-foreground">DummyJSON #{{ item.dummyjson_product_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right">{{ money(item.price) }}</td>
                                <td class="px-5 py-3 text-right">{{ item.quantity }}</td>
                                <td class="px-5 py-3 text-right">{{ item.discount_percentage }}%</td>
                                <td class="px-5 py-3 text-right">{{ money(item.total) }}</td>
                                <td class="px-5 py-3 text-right font-semibold">{{ money(item.discounted_total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t-2">
                            <tr>
                                <td colspan="4" class="px-5 py-4 text-right text-muted-foreground">Oszczędność: {{ money(discount) }}</td>
                                <td class="px-5 py-4 text-right font-medium">Razem</td>
                                <td class="px-5 py-4 text-right text-lg font-semibold">{{ money(order.discounted_total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <JsonViewer :value="order.raw_payload" title="Pełny payload zamówienia" />
        </div>
    </AppLayout>
</template>
