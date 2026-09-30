<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { FileText, LayoutDashboard, Package, ShoppingCart, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Produkty',
        href: '/products',
        icon: Package,
    },
    {
        title: 'Zamówienia',
        href: '/orders',
        icon: ShoppingCart,
    },
    {
        title: 'Klienci',
        href: '/customers',
        icon: Users,
    },
    {
        title: 'Raporty',
        href: '/reports',
        icon: FileText,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <div class="mx-2 rounded-lg border border-sidebar-border/70 bg-sidebar-accent/40 px-3 py-2 text-xs text-sidebar-foreground/70">
                <div class="font-medium text-sidebar-foreground">Źródło danych</div>
                <div>DummyJSON · lokalna kopia</div>
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
