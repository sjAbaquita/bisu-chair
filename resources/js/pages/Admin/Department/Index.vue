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
import { Plus, Edit, Trash2, Search, BookOpen } from '@lucide/vue';
import department from '@/routes/admin/departments';

interface Department {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    created_at: string;
    college?: {
        name: string;
        code: string;
    };
}

const props = defineProps<{
    departments: {
        data: Department[];
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
        department.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (dept: Department) => {
    if (confirm(`Are you sure you want to delete "${dept.name}"?`)) {
        router.delete(department.destroy(dept.id));
    }
};
</script>

<template>
    <Head title="Departments" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Departments</h1>
        <Link :href="department.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Department
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Department Management</CardTitle>
                <CardDescription
                    >Manage BISU departments and academic
                    programs.</CardDescription
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
                            placeholder="Search departments..."
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
                                <TableHead>College</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
							</TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="dept in props.departments.data"
                                :key="dept.id"
                            >
                                <TableCell class="font-medium">
                                    <Badge variant="outline">{{
                                        dept.code
                                    }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <BookOpen
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ dept.name }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium">
                                            {{ dept.college?.name || '-' }}
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            class="text-xs"
                                        >
                                            {{ dept.college?.code || '-' }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            dept.is_active
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            dept.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link :href="department.edit(dept.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="dept.is_active"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(dept)"
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
                        {{ props.departments.from }} to
                        {{ props.departments.to }} of
                        {{ props.departments.total }} departments
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
