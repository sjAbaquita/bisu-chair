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
import { Plus, Edit, Trash2, Search, Building2 } from '@lucide/vue';
import building from '@/routes/admin/buildings';

interface Building {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    created_at: string;
    campus?: {
        name: string;
        code: string;
    };
}

const props = defineProps<{
    buildings: {
        data: Building[];
        links: Array<{ url: string; label: string }>;
        // meta: {
        //     per_page: number;
        //     current_page: number;
        //     from: number;
        //     to: number;
        //     total: number;
        // };
    };
    search?: string;
}>();

const search = ref(props.search || '');

const handleSearch = () => {
    router.get(
        building.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (buildingItem: Building) => {
    if (confirm(`Are you sure you want to delete "${buildingItem.name}"?`)) {
        router.delete(building.destroy(buildingItem.id));
    }
};
</script>

<template>
    <Head title="Buildings" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Buildings</h1>
        <Link :href="building.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Building
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Building Management</CardTitle>
                <CardDescription
                    >Manage BISU buildings per campus.</CardDescription
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
                            placeholder="Search buildings..."
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
                                <TableHead>Campus</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
								
							</TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="buildingItem in props.buildings.data"
                                :key="buildingItem.id"
                            >
                                <TableCell class="font-medium">
                                    <Badge variant="outline">{{
                                        buildingItem.code
                                    }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <Building2
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ buildingItem.name }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ buildingItem.campus?.name || '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            buildingItem.is_active
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            buildingItem.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link
                                        :href="building.edit(buildingItem.id)"
                                    >
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="buildingItem.is_active"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(buildingItem)"
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
                        {{ props.buildings.from }} to
                        {{ props.buildings.to }} of
                        {{ props.buildings.total }} buildings
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
