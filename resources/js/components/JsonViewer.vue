<script setup lang="ts">
defineProps<{
    value: Record<string, unknown> | unknown[];
    title?: string;
}>();
</script>

<template>
    <details class="group overflow-hidden rounded-xl border bg-card shadow-sm">
        <summary class="cursor-pointer list-none px-5 py-4 font-medium marker:hidden">
            <div class="flex items-center justify-between">
                <span>{{ title ?? 'Pełny payload źródłowy' }}</span>
                <span class="text-xs text-muted-foreground group-open:hidden">Rozwiń JSON</span>
                <span class="hidden text-xs text-muted-foreground group-open:inline">Zwiń JSON</span>
            </div>
        </summary>
        <pre class="max-h-[36rem] overflow-auto border-t bg-slate-950 p-5 text-xs leading-5 text-slate-200">{{ JSON.stringify(value, null, 2) }}</pre>
    </details>
</template>
