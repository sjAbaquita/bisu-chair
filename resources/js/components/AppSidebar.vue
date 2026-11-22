<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    Building2,
    MapPin,
    Users,
    CalendarDays,
    DoorOpen,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import roomUtilization from '@/routes/admin/room-utilization';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Campuses',
        href: '/admin/campuses',
        icon: MapPin,
    },
    {
        title: 'Colleges',
        href: '/admin/colleges',
        icon: Building2,
    },
    {
        title: 'Departments',
        href: '/admin/departments',
        icon: BookOpen,
    },
    {
        title: 'Buildings',
        href: '/admin/buildings',
        icon: Building2,
    },
    {
        title: 'Rooms',
        href: '/admin/rooms',
        icon: DoorOpen,
    },
    {
        title: 'Room Utilization',
        href: roomUtilization.index.url(),
        icon: Users,
    },
    {
        title: 'Academic Periods',
        href: '/admin/academic_periods',
        icon: CalendarDays,
    },
    {
        title: 'Faculty',
        href: '/admin/faculty',
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
