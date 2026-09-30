<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{ url: string | null; label: string; active: boolean }>;
}>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1 pt-4" aria-label="Paginacja">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="min-w-9 rounded-md border px-3 py-2 text-center text-sm transition-colors hover:bg-muted"
                :class="link.active ? 'border-primary bg-primary text-primary-foreground hover:bg-primary' : 'border-border bg-background'"
            >
                <span v-html="link.label" />
            </Link>
            <span v-else class="min-w-9 cursor-not-allowed rounded-md border border-border px-3 py-2 text-center text-sm text-muted-foreground/50">
                <span v-html="link.label" />
            </span>
        </template>
    </nav>
</template>
