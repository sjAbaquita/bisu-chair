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
import {
    Plus,
    Edit,
    Trash2,
    Search,
    BookOpen,
    GraduationCap,
} from '@lucide/vue';
import section from '@/routes/admin/sections';

interface AcademicPeriod {
    id: number;
    academic_year: string;
    term: string;
    start_date: string;
    end_date: string;
    status: string;
}

interface College {
    id: number;
    name: string;
    code: string;
}

interface Department {
    id: number;
    name: string;
    code: string;
    college: College;
}

interface SectionItem {
    id: number;
    name: string;
    program_name: string | null;
    year_level: string | null;
    expected_enrollment: number | null;
    is_active: boolean;
    created_at: string;
    academic_period?: AcademicPeriod;
    department?: Department;
}

const props = defineProps<{
    sections: {
        data: SectionItem[];
        links: Array<{ url: string; label: string }>;
        meta: {
            per_page: number;
            current_page: number;
            from: number;
            to: number;
            total: number;
        };
    };
    academicPeriods: AcademicPeriod[];
    filters?: {
        academic_period_id?: number;
        search?: string;
    };
}>();

const search = ref(props.filters?.search || '');
const academicPeriodFilter = ref(
    props.filters?.academic_period_id?.toString() || '',
);

const handleSearch = () => {
    router.get(
        section.index(),
        {
            search: search.value,
            academic_period_id: academicPeriodFilter.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const handleFilterChange = () => {
    router.get(
        section.index(),
        {
            search: search.value || undefined,
            academic_period_id: academicPeriodFilter.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (sect: SectionItem) => {
    if (confirm(`Are you sure you want to delete section "${sect.name}"?`)) {
        router.delete(section.destroy(sect.id));
    }
};
</script>

<template>
    <Head title="Sections" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Sections</h1>
        <Link :href="section.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Section
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Section Management</CardTitle>
                <CardDescription
                    >Manage BISU academic sections and
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
                            placeholder="Search sections..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="mb-4 flex items-center space-x-2">
                    <select
                        v-model="academicPeriodFilter"
                        @change="handleFilterChange"
                        class="h-9 rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                    >
                        <option value="">All Academic Periods</option>
                        <option
                            v-for="period in props.academicPeriods"
                            :key="period.id"
                            :value="period.id"
                        >
                            {{ period.academic_year }} - {{ period.term }}
                        </option>
                    </select>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Program</TableHead>
                                <TableHead>Year Level</TableHead>
                                <TableHead>Period</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead>College</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="sect in props.sections.data"
                                :key="sect.id"
                            >
                                <TableHead class="font-medium">
                                    <Badge variant="outline">{{
                                        sect.name
                                    }}</Badge>
                                </TableHead>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <BookOpen
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ sect.department?.name || '-' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ sect.program_name || '-' }}
                                </TableCell>
                                <TableCell>
                                    <GraduationCap
                                        class="mr-1 inline h-4 w-4 text-muted-foreground"
                                    />
                                    {{ sect.year_level || '-' }}
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">
                                        {{
                                            sect.academic_period
                                                ?.academic_year || '-'
                                        }}<br />
                                        <small class="text-muted-foreground">{{
                                            sect.academic_period?.term || '-'
                                        }}</small>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm font-medium">
                                        {{ sect.department?.code || '-' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm font-medium">
                                        {{
                                            sect.department?.college?.name ||
                                            '-'
                                        }}
                                    </div>
                                    <Badge variant="secondary" class="text-xs">
                                        {{
                                            sect.department?.college?.code ||
                                            '-'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            sect.is_active
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            sect.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link :href="section.edit(sect.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="sect.is_active"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(sect)"
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
                        {{ props.sections.meta.from }} to
                        {{ props.sections.meta.to }} of
                        {{ props.sections.meta.total }} sections
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
