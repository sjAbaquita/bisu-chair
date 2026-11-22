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
import { Plus, Edit, Trash2, Search, User } from '@lucide/vue';
import faculty from '@/routes/admin/faculty';

interface Faculty {
    id: number;
    employee_no: string | null;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    suffix: string | null;
    email: string | null;
    status: string;
    highest_degree_earned: string | null;
    specialization: string | null;
}

const props = defineProps<{
    faculty: {
        data: Faculty[];
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
        faculty.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (member: Faculty) => {
    if (
        confirm(
            `Are you sure you want to delete "${member.first_name} ${member.last_name}"?`,
        )
    ) {
        router.delete(faculty.destroy(member.id));
    }
};
</script>

<template>
    <Head title="Faculty" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Faculty</h1>
        <Link :href="faculty.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Faculty
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Faculty Management</CardTitle>
                <CardDescription
                    >Manage BISU faculty members and their
                    profiles.</CardDescription
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
                            placeholder="Search by last name..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Employee No.</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Degree</TableHead>
                                <TableHead>Specialization</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="member in props.faculty.data"
                                :key="member.id"
                            >
                                <TableCell class="font-medium">
                                    <Badge variant="outline">{{
                                        member.employee_no || '-'
                                    }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <User
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ member.first_name }}
                                        {{
                                            member.middle_name
                                                ? member.middle_name + ' '
                                                : ''
                                        }}{{ member.last_name
                                        }}{{
                                            member.suffix
                                                ? ' ' + member.suffix
                                                : ''
                                        }}
                                    </div>
                                </TableCell>
                                <TableCell>{{ member.email || '-' }}</TableCell>
                                <TableCell>{{
                                    member.highest_degree_earned || '-'
                                }}</TableCell>
                                <TableCell>{{
                                    member.specialization || '-'
                                }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            member.status === 'active'
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            member.status
                                                .charAt(0)
                                                .toUpperCase() +
                                            member.status.slice(1)
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link :href="faculty.edit(member.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(member)"
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
                        {{ props.faculty.from }} to
                        {{ props.faculty.to }} of
                        {{ props.faculty.total }} faculty
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
