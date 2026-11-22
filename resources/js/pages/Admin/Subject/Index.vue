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
import subject from '@/routes/admin/subjects';

interface Subject {
    id: number;
    code: string;
    title: string;
    units: number;
    lecture_hours: number;
    laboratory_hours: number | null;
    is_active: boolean;
}

const props = defineProps<{
    subjects: {
        data: Subject[];
        links: Array<{ url: string; label: string }>;
        meta: {
            per_page: number;
            current_page: number;
            from: number;
            to: number;
            total: number;
        };
    };
    search?: string;
}>();

const search = ref(props.search || '');

const handleSearch = () => {
    router.get(
        subject.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (subjectItem: Subject) => {
    if (
        confirm(
            `Are you sure you want to delete subject "${subjectItem.code}"?`,
        )
    ) {
        router.delete(subject.destroy(subjectItem.id));
    }
};
</script>

<template>
    <Head title="Subjects" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Subjects</h1>
        <Link :href="subject.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Subject
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Subject Management</CardTitle>
                <CardDescription
                    >Manage BISU subjects and courses.</CardDescription
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
                            placeholder="Search by code or title..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Title</TableHead>
                                <TableHead>Units</TableHead>
                                <TableHead>Lab Hours</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="subjectItem in props.subjects.data"
                                :key="subjectItem.id"
                            >
                                <TableHead class="font-medium">
                                    <Badge variant="outline">{{
                                        subjectItem.code
                                    }}</Badge>
                                </TableHead>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <BookOpen
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ subjectItem.title }}
                                    </div>
                                </TableCell>
                                <TableCell>{{ subjectItem.units }}</TableCell>
                                <TableCell>{{
                                    subjectItem.laboratory_hours || 0
                                }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            subjectItem.is_active
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            subjectItem.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link :href="subject.edit(subjectItem.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="subjectItem.is_active"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(subjectItem)"
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
                        {{ props.subjects.meta.from }} to
                        {{ props.subjects.meta.to }} of
                        {{ props.subjects.meta.total }} subjects
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
