<script setup lang="ts">
import JsonViewer from '@/components/JsonViewer.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Building2, Mail, MapPin, Phone, ShoppingBag, UserRound } from 'lucide-vue-next';

interface CustomerOrder {
    id: number;
    dummyjson_id: number;
    total_products: number;
    total_quantity: number;
    total: string;
    discounted_total: string;
    status: string;
}
interface Customer {
    id: number;
    dummyjson_id: number;
    first_name: string;
    last_name: string;
    email: string;
    username?: string;
    phone?: string;
    gender?: string;
    age?: number;
    image?: string;
    company?: { name?: string; title?: string; department?: string; address?: Record<string, unknown> };
    address?: { address?: string; city?: string; state?: string; postalCode?: string; country?: string };
    raw_payload: Record<string, unknown>;
    synced_at: string;
    orders: CustomerOrder[];
}

const props = defineProps<{ customer: Customer; stats: { orders: number; quantity: number; total: number } }>();
const money = (value: number | string) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value));
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Klienci', href: '/customers' },
    { title: `${props.customer.first_name} ${props.customer.last_name}`, href: `/customers/${props.customer.id}` },
];
</script>

<template>
    <Head :title="`${customer.first_name} ${customer.last_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <Link href="/customers" class="text-sm font-medium text-primary hover:underline">← Wróć do klientów</Link>

            <section class="rounded-2xl border bg-card p-6 shadow-sm">
                <div class="flex flex-col gap-6 md:flex-row md:items-center">
                    <img
                        :src="customer.image"
                        :alt="`${customer.first_name} ${customer.last_name}`"
                        class="size-24 rounded-2xl bg-muted object-cover"
                    />
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-medium uppercase tracking-wide text-primary">Klient DummyJSON #{{ customer.dummyjson_id }}</div>
                        <h1 class="mt-1 text-3xl font-semibold tracking-tight">{{ customer.first_name }} {{ customer.last_name }}</h1>
                        <p class="mt-1 text-muted-foreground">
                            {{ customer.company?.title ?? 'Klient' }} · {{ customer.company?.department ?? '—' }}
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-5 rounded-xl bg-muted/50 p-4 text-center">
                        <div>
                            <div class="text-xs text-muted-foreground">Zamówienia</div>
                            <div class="mt-1 text-xl font-semibold">{{ stats.orders }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted-foreground">Sztuki</div>
                            <div class="mt-1 text-xl font-semibold">{{ stats.quantity }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted-foreground">Zakupy</div>
                            <div class="mt-1 text-xl font-semibold">{{ money(stats.total) }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-xl border bg-card p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 font-semibold"><UserRound class="size-5" />Dane kontaktowe</h2>
                    <dl class="space-y-4 text-sm">
                        <div class="flex gap-3">
                            <Mail class="mt-0.5 size-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">E-mail</dt>
                                <dd class="font-medium">{{ customer.email }}</dd>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Phone class="mt-0.5 size-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">Telefon</dt>
                                <dd class="font-medium">{{ customer.phone ?? '—' }}</dd>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <UserRound class="mt-0.5 size-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">Login / wiek / płeć</dt>
                                <dd class="font-medium">{{ customer.username ?? '—' }} · {{ customer.age ?? '—' }} · {{ customer.gender ?? '—' }}</dd>
                            </div>
                        </div>
                    </dl>
                </section>
                <section class="rounded-xl border bg-card p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 font-semibold"><MapPin class="size-5" />Adres i firma</h2>
                    <dl class="space-y-4 text-sm">
                        <div class="flex gap-3">
                            <MapPin class="mt-0.5 size-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">Adres</dt>
                                <dd class="font-medium">
                                    {{ customer.address?.address ?? '—' }}, {{ customer.address?.postalCode }} {{ customer.address?.city }},
                                    {{ customer.address?.state }}, {{ customer.address?.country }}
                                </dd>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Building2 class="mt-0.5 size-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">Firma</dt>
                                <dd class="font-medium">{{ customer.company?.name ?? '—' }}</dd>
                            </div>
                        </div>
                    </dl>
                </section>
            </div>

            <section class="overflow-hidden rounded-xl border bg-card shadow-sm">
                <div class="border-b px-5 py-4">
                    <h2 class="flex items-center gap-2 font-semibold"><ShoppingBag class="size-5" />Historia zamówień</h2>
                    <p class="text-xs text-muted-foreground">Wszystkie zamówienia powiązane z klientem</p>
                </div>
                <div v-if="customer.orders.length" class="divide-y">
                    <Link
                        v-for="order in customer.orders"
                        :key="order.id"
                        :href="`/orders/${order.id}`"
                        class="grid gap-3 px-5 py-4 transition-colors hover:bg-muted/40 sm:grid-cols-[1fr_repeat(3,auto)] sm:items-center sm:gap-8"
                    >
                        <div>
                            <div class="font-medium">Zamówienie #{{ order.dummyjson_id }}</div>
                            <div class="text-xs text-muted-foreground">{{ order.status }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted-foreground">Pozycje</div>
                            <div class="font-medium">{{ order.total_products }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted-foreground">Sztuki</div>
                            <div class="font-medium">{{ order.total_quantity }}</div>
                        </div>
                        <div class="sm:text-right">
                            <div class="text-xs text-muted-foreground">Wartość</div>
                            <div class="font-semibold">{{ money(order.discounted_total) }}</div>
                        </div>
                    </Link>
                </div>
                <div v-else class="p-8 text-center text-sm text-muted-foreground">Brak zamówień tego klienta.</div>
            </section>

            <JsonViewer :value="customer.raw_payload" />
        </div>
    </AppLayout>
</template>
