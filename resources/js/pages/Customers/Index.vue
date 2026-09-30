<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Mail, Phone, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface Customer {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    image?: string;
    company?: { name?: string; title?: string; department?: string };
    address?: { city?: string; country?: string };
    orders_count: number;
    orders_sum_discounted_total?: string;
}

const props = defineProps<{
    customers: { data: Customer[]; links: Array<{ url: string | null; label: string; active: boolean }>; total: number };
    filters: { search: string };
}>();

const search = ref(props.filters.search);
const money = (value?: string) => new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'USD' }).format(Number(value ?? 0));
const filter = () => router.get('/customers', { search: search.value || undefined }, { preserveState: true, replace: true });
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Klienci', href: '/customers' }];
</script>

<template>
    <Head title="Klienci" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-5 p-4 md:p-6">
            <div>
                <p class="text-sm font-medium text-primary">CRM testowy</p>
                <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">Klienci zewnętrzni</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ customers.total }} profili z pełnym payloadem źródłowym.</p>
            </div>
            <form class="flex gap-3 rounded-xl border bg-card p-4 shadow-sm" @submit.prevent="filter">
                <label class="relative flex-1"
                    ><Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" /><input
                        v-model="search"
                        type="search"
                        placeholder="Imię, nazwisko, firma lub e-mail"
                        class="h-10 w-full rounded-lg border bg-background pl-9 pr-3 text-sm outline-none focus:ring-2 focus:ring-ring" /></label
                ><button class="h-10 rounded-lg bg-primary px-5 text-sm font-medium text-primary-foreground">Szukaj</button>
            </form>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="customer in customers.data"
                    :key="customer.id"
                    :href="`/customers/${customer.id}`"
                    class="rounded-xl border bg-card p-5 shadow-sm transition-colors hover:bg-muted/30"
                >
                    <div class="flex items-start gap-3">
                        <img
                            :src="customer.image"
                            :alt="`${customer.first_name} ${customer.last_name}`"
                            class="size-12 rounded-full bg-muted object-cover"
                        />
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate font-semibold">{{ customer.first_name }} {{ customer.last_name }}</h2>
                            <p class="truncate text-sm text-muted-foreground">{{ customer.company?.title ?? 'Klient' }}</p>
                        </div>
                        <span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">{{ customer.orders_count }} zam.</span>
                    </div>
                    <div class="mt-4 space-y-2 text-sm text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <Mail class="size-4 shrink-0" /><span class="truncate">{{ customer.email }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Phone class="size-4 shrink-0" /><span>{{ customer.phone ?? '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Building2 class="size-4 shrink-0" /><span class="truncate">{{ customer.company?.name ?? 'Brak firmy' }}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t pt-4 text-sm">
                        <span class="text-muted-foreground">Wartość zakupów</span><strong>{{ money(customer.orders_sum_discounted_total) }}</strong>
                    </div>
                </Link>
            </div>
            <Pagination :links="customers.links" />
        </div>
    </AppLayout>
</template>
