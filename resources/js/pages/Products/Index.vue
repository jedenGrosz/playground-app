<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { PackageSearch, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface Product {
    id: number;
    title: string;
    description?: string;
    category: string;
    brand?: string;
    price: string;
    rating?: string;
    stock: number;
    thumbnail?: string;
    availability_status?: string;
}

const props = defineProps<{
    products: { data: Product[]; links: Array<{ url: string | null; label: string; active: boolean }>; from: number; to: number; total: number };
    categories: string[];
    filters: { search: string; category: string };
}>();

const search = ref(props.filters.search);
const category = ref(props.filters.category);
const money = (value: string) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value));
const filter = () =>
    router.get('/products', { search: search.value || undefined, category: category.value || undefined }, { preserveState: true, replace: true });
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Produkty', href: '/products' }];
</script>

<template>
    <Head title="Produkty" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-5 p-4 md:p-6">
            <div>
                <p class="text-sm font-medium text-primary">Katalog</p>
                <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">Produkty</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ products.total }} pełnych rekordów zapisanych z DummyJSON.</p>
            </div>

            <form class="flex flex-col gap-3 rounded-xl border bg-card p-4 shadow-sm sm:flex-row" @submit.prevent="filter">
                <label class="relative flex-1"
                    ><Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" /><input
                        v-model="search"
                        type="search"
                        placeholder="Szukaj po nazwie, marce lub SKU"
                        class="h-10 w-full rounded-lg border bg-background pl-9 pr-3 text-sm outline-none focus:ring-2 focus:ring-ring"
                /></label>
                <select
                    v-model="category"
                    class="h-10 rounded-lg border bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-ring"
                    @change="filter"
                >
                    <option value="">Wszystkie kategorie</option>
                    <option v-for="item in categories" :key="item" :value="item">{{ item.replaceAll('-', ' ') }}</option>
                </select>
                <button class="h-10 rounded-lg bg-primary px-5 text-sm font-medium text-primary-foreground">Filtruj</button>
            </form>

            <div v-if="products.data.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                <Link
                    v-for="product in products.data"
                    :key="product.id"
                    :href="`/products/${product.id}`"
                    class="group overflow-hidden rounded-xl border bg-card shadow-sm transition-shadow hover:shadow-md"
                >
                    <div class="relative aspect-[4/3] overflow-hidden bg-muted/60">
                        <img
                            :src="product.thumbnail"
                            :alt="product.title"
                            class="size-full object-contain p-5 transition-transform group-hover:scale-105"
                        /><span
                            class="absolute left-3 top-3 rounded-full bg-background/90 px-2.5 py-1 text-xs font-medium capitalize shadow-sm backdrop-blur"
                            >{{ product.category.replaceAll('-', ' ') }}</span
                        >
                    </div>
                    <div class="p-4">
                        <div class="mb-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ product.brand ?? 'Bez marki' }}</div>
                        <h2 class="line-clamp-1 font-semibold">{{ product.title }}</h2>
                        <p class="mt-1 line-clamp-2 min-h-10 text-sm text-muted-foreground">{{ product.description }}</p>
                        <div class="mt-4 flex items-end justify-between">
                            <div>
                                <div class="text-lg font-semibold">{{ money(product.price) }}</div>
                                <div class="text-xs text-amber-600">★ {{ product.rating ?? '—' }}</div>
                            </div>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="product.stock <= 10 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                                >{{ product.stock }} szt.</span
                            >
                        </div>
                    </div>
                </Link>
            </div>
            <div v-else class="flex min-h-64 flex-col items-center justify-center rounded-xl border border-dashed text-center">
                <PackageSearch class="mb-3 size-10 text-muted-foreground" />
                <h2 class="font-semibold">Brak produktów</h2>
                <p class="text-sm text-muted-foreground">Zmień filtry lub wykonaj synchronizację.</p>
            </div>
            <div class="text-center text-xs text-muted-foreground">
                Wyświetlono {{ products.from ?? 0 }}–{{ products.to ?? 0 }} z {{ products.total }}
            </div>
            <Pagination :links="products.links" />
        </div>
    </AppLayout>
</template>
