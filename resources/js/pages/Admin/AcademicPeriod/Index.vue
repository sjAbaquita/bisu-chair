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
import { Plus, Edit, Trash2, Search, CalendarDays } from '@lucide/vue';
import academicPeriod from '@/routes/admin/academic_periods';

interface AcademicPeriod {
    id: number;
    academic_year: string;
    term: string;
    start_date: string;
    end_date: string;
    status: string;
    is_current: boolean;
}

const props = defineProps<{
    periods: {
        data: AcademicPeriod[];
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
        academicPeriod.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (period: AcademicPeriod) => {
    if (
        confirm(
            `Are you sure you want to delete "${period.academic_year} ${period.term}"?`,
        )
    ) {
        router.delete(academicPeriod.destroy(period.id));
    }
};
</script>

<template>
    <Head title="Academic Periods" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Academic Periods</h1>
        <Link :href="academicPeriod.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Period
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Academic Period Management</CardTitle>
                <CardDescription
                    >Manage academic years, semesters and
                    terms.</CardDescription
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
                            placeholder="Search by academic year..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Academic Year</TableHead>
                                <TableHead>Term</TableHead>
                                <TableHead>Start Date</TableHead>
                                <TableHead>End Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Current</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="period in props.periods.data"
                                :key="period.id"
                            >
                                <TableCell class="font-medium">
                                    {{ period.academic_year }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <CalendarDays
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ period.term }}
                                    </div>
                                </TableCell>
                                <TableCell>{{ period.start_date }}</TableCell>
                                <TableCell>{{ period.end_date }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            period.status === 'locked'
                                                ? 'secondary'
                                                : period.status === 'open'
                                                  ? 'default'
                                                  : 'outline'
                                        "
                                    >
                                        {{
                                            period.status
                                                .charAt(0)
                                                .toUpperCase() +
                                            period.status.slice(1)
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            period.is_current
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{ period.is_current ? 'Yes' : 'No' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link
                                        :href="academicPeriod.edit(period.id)"
                                    >
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(period)"
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
                        {{ props.periods.from }} to
                        {{ props.periods.to }} of
                        {{ props.periods.total }} periods
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
