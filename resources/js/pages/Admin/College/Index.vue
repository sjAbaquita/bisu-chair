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
import { Plus, Edit, Trash2, Search, Building } from '@lucide/vue';
import college from '@/routes/admin/colleges';

interface College {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    created_at: string;
    campus?: {
        name: string;
    };
}

const props = defineProps<{
    colleges: {
        data: College[];
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
        college.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (collegeItem: College) => {
    if (confirm(`Are you sure you want to delete "${collegeItem.name}"?`)) {
        router.delete(college.destroy(collegeItem.id));
    }
};
</script>

<template>
    <Head title="Colleges" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Colleges</h1>
        <Link :href="college.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add College
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>College Management</CardTitle>
                <CardDescription
                    >Manage BISU colleges and academic units.</CardDescription
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
                            placeholder="Search colleges..."
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
                                v-for="collegeItem in props.colleges.data"
                                :key="collegeItem.id"
                            >
                                <TableCell class="font-medium">
                                    <Badge variant="outline">{{
                                        collegeItem.code
                                    }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <Building
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ collegeItem.name }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ collegeItem.campus?.name || '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            collegeItem.is_active
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            collegeItem.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link :href="college.edit(collegeItem.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="collegeItem.is_active"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(collegeItem)"
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
                        {{ props.colleges.from }} to
                        {{ props.colleges.to }} of
                        {{ props.colleges.total }} colleges
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
