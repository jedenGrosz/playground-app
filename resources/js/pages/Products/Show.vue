<script setup lang="ts">
import JsonViewer from '@/components/JsonViewer.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { BadgeDollarSign, Boxes, PackageCheck, ShoppingBag, Star } from 'lucide-vue-next';

interface Product {
    id: number;
    dummyjson_id: number;
    title: string;
    description?: string;
    category: string;
    brand?: string;
    sku?: string;
    price: string;
    discount_percentage: string;
    rating?: string;
    stock: number;
    availability_status?: string;
    thumbnail?: string;
    images?: string[];
    raw_payload: Record<string, unknown>;
    synced_at: string;
}

const props = defineProps<{
    product: Product;
    sales: { orders: number; quantity: number; revenue: number };
}>();

const money = (value: number | string) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value));
const label = (value?: unknown) => (value === undefined || value === null || value === '' ? '—' : String(value));
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Produkty', href: '/products' },
    { title: props.product.title, href: `/products/${props.product.id}` },
];
</script>

<template>
    <Head :title="product.title" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <Link href="/products" class="text-sm font-medium text-primary hover:underline">← Wróć do produktów</Link>

            <section class="grid gap-6 rounded-2xl border bg-card p-5 shadow-sm lg:grid-cols-[minmax(280px,0.8fr)_1.2fr] lg:p-7">
                <div>
                    <div class="aspect-square overflow-hidden rounded-xl bg-muted/50">
                        <img :src="product.thumbnail" :alt="product.title" class="size-full object-contain p-7" />
                    </div>
                    <div v-if="product.images?.length" class="mt-3 grid grid-cols-4 gap-2">
                        <div v-for="image in product.images" :key="image" class="aspect-square overflow-hidden rounded-lg border bg-muted/30">
                            <img :src="image" :alt="product.title" class="size-full object-contain p-2" />
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-primary">{{ product.category.replaceAll('-', ' ') }}</span>
                        <span class="rounded-full bg-muted px-3 py-1">DummyJSON #{{ product.dummyjson_id }}</span>
                    </div>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight">{{ product.title }}</h1>
                    <p class="mt-3 max-w-3xl text-muted-foreground">{{ product.description }}</p>

                    <div class="mt-6 flex flex-wrap items-end gap-x-5 gap-y-2">
                        <div class="text-3xl font-semibold">{{ money(product.price) }}</div>
                        <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">-{{ product.discount_percentage }}%</div>
                        <div class="flex items-center gap-1 text-sm text-amber-600">
                            <Star class="size-4 fill-current" />{{ product.rating ?? '—' }}
                        </div>
                    </div>

                    <dl class="mt-7 grid gap-x-6 gap-y-4 border-t pt-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Marka</dt>
                            <dd class="mt-1 font-medium">{{ product.brand ?? 'Bez marki' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">SKU</dt>
                            <dd class="mt-1 font-medium">{{ product.sku ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Stan magazynowy</dt>
                            <dd class="mt-1 font-medium">{{ product.stock }} szt.</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Dostępność</dt>
                            <dd class="mt-1 font-medium">{{ product.availability_status ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Waga</dt>
                            <dd class="mt-1 font-medium">{{ label(product.raw_payload.weight) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Minimalne zamówienie</dt>
                            <dd class="mt-1 font-medium">{{ label(product.raw_payload.minimumOrderQuantity) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Gwarancja</dt>
                            <dd class="mt-1 font-medium">{{ label(product.raw_payload.warrantyInformation) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Zwroty</dt>
                            <dd class="mt-1 font-medium">{{ label(product.raw_payload.returnPolicy) }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="item in [
                        { label: 'Zamówienia', value: sales.orders, icon: ShoppingBag },
                        { label: 'Sprzedane sztuki', value: sales.quantity, icon: PackageCheck },
                        { label: 'Sprzedaż po rabatach', value: money(sales.revenue), icon: BadgeDollarSign },
                        { label: 'Stan magazynowy', value: `${product.stock} szt.`, icon: Boxes },
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

            <JsonViewer :value="product.raw_payload" />
        </div>
    </AppLayout>
</template>
