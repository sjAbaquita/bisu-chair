<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Plus, Edit, Trash2, Search, MapPin } from '@lucide/vue';
import campus from '@/routes/admin/campuses';

interface Campus {
    id: number;
    name: string;
    code: string;
    address: string | null;
    is_active: boolean;
    created_at: string;
}

const props = defineProps<{
    campuses: {
        data: Campus[];
        links: Array<{ url: string; label: string }>;
    };
    search?: string;
}>();

const search = ref(props.search || '');

const handleSearch = () => {
    router.get(
        campus.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (campusItem: Campus) => {
    if (confirm(`Are you sure you want to delete "${campusItem.name}"?`)) {
        router.delete(campus.destroy(campusItem.id));
    }
};
</script>

<template>
    <Head title="Campuses" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Campuses</h1>
        <Link :href="campus.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Campus
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Campus Management</CardTitle>
                <CardDescription
                    >Manage BISU campus locations and
                    information.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="mb-4 flex items-center space-x-2">
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            type="search"
                            placeholder="Search campuses..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Address</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
							</TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="campusItem in props.campuses.data"
                                :key="campusItem.id"
                            >
                                <TableCell class="font-medium">
                                    <Badge variant="outline">{{
                                        campusItem.code
                                    }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <MapPin
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ campusItem.name }}
                                    </div>
                                </TableCell>
                                <TableCell class="max-w-xs truncate">
                                    {{ campusItem.address || '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            campusItem.is_active
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            campusItem.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link :href="campus.edit(campusItem.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="campusItem.is_active"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(campusItem)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="mt-4 flex items-center justify-end space-x-2">
                    <p class="text-sm text-muted-foreground">
                        {{ props.campuses.from }} to
                        {{ props.campuses.to }} of
                        {{ props.campuses.total }} campuses
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
