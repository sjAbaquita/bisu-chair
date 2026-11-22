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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Plus, Edit, Search, BookOpen, Users, ExternalLink } from '@lucide/vue';
import classOffering from '@/routes/admin/class_offerings';

interface ClassOffering {
    id: number;
    academic_period: { academic_year: string; term: string };
    department: {
        name: string;
        code: string;
        college: { name: string; code: string };
    };
    subject: { code: string; title: string; units: number };
    section: { name: string } | null;
    delivery_mode: string;
    class_size: number | null;
    status: string;
    remarks: string | null;
    created_by: { name: string };
    faculty: Array<{
        id: number;
        pivot: {
            load_hours: number | null;
            load_units: number | null;
            role: string | null;
        };
    }>;
}

interface AcademicPeriod {
    id: number;
    academic_year: string;
    term: string;
    start_date: string;
    end_date: string;
    status: string;
}

interface Department {
    id: number;
    name: string;
    code: string;
    college: { name: string; code: string };
}

const props = defineProps<{
    classOfferings: {
        data: ClassOffering[];
        links: Array<{ url: string; label: string }>;
        meta: {
            per_page: number;
            current_page: number;
            from: number;
            to: number;
            total: number;
        };
    };
    academicPeriods?: AcademicPeriod[];
    departments?: Department[];
    filters?: {
        academic_period_id?: number;
        department_id?: number;
        status?: string;
        search?: string;
    };
}>();

const search = ref(props.filters?.search || '');
const academicPeriodFilter = ref(props.filters?.academic_period_id || '');
const departmentFilter = ref(props.filters?.department_id || '');
const statusFilter = ref(props.filters?.status || '');

const handleSearch = () => {
    router.get(
        classOffering.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleFilter = () => {
    router.get(
        classOffering.index(),
        {
            academic_period_id: academicPeriodFilter.value || undefined,
            department_id: departmentFilter.value || undefined,
            status: statusFilter.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const getStatusBadgeVariant = (status: string) => {
    const variants: Record<string, string> = {
        draft: 'outline',
        submitted: 'secondary',
        for_review: 'secondary',
        returned: 'destructive',
        approved: 'default',
        cancelled: 'outline',
        archived: 'outline',
    };
    return variants[status] || 'outline';
};

const getDeliveryModeBadge = (mode: string) => {
    const labels: Record<string, string> = {
        face_to_face: 'Face-to-Face',
        online: 'Online',
        hybrid: 'Hybrid',
    };
    return labels[mode] || mode;
};
</script>

<template>
    <Head title="Class Offerings" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Class Offerings</h1>
        <Link :href="classOffering.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Class Offering
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Class Offering Management</CardTitle>
                <CardDescription
                    >Manage BISU class offerings, faculty loads, and
                    schedules.</CardDescription
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
                            placeholder="Search by subject code or title..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="mb-4 flex items-center space-x-4">
                    <Select
                        v-model="academicPeriodFilter"
                        @change="handleFilter"
                    >
                        <SelectTrigger class="w-[200px]">
                            <SelectValue
                                placeholder="Filter by Academic Period"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="period in props.academicPeriods"
                                :key="period.id"
                                :value="period.id"
                            >
                                {{ period.academic_year }} - {{ period.term }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="departmentFilter" @change="handleFilter">
                        <SelectTrigger class="w-[200px]">
                            <SelectValue placeholder="Filter by Department" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="dept in props.departments"
                                :key="dept.id"
                                :value="dept.id"
                            >
                                {{ dept.code }} - {{ dept.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="statusFilter" @change="handleFilter">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="Filter by Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All Statuses</SelectItem>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="submitted">Submitted</SelectItem>
                            <SelectItem value="for_review"
                                >For Review</SelectItem
                            >
                            <SelectItem value="returned">Returned</SelectItem>
                            <SelectItem value="approved">Approved</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Subject</TableHead>
                                <TableHead>Section</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead>Delivery Mode</TableHead>
                                <TableHead>Class Size</TableHead>
                                <TableHead>Faculty Count</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in props.classOfferings.data"
                                :key="item.id"
                            >
                                <TableHead class="font-medium">
                                    <div class="flex items-center space-x-2">
                                        <BookOpen
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        <Badge variant="outline">{{
                                            item.subject.code
                                        }}</Badge>
                                    </div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ item.subject.title }}
                                    </div>
                                </TableHead>
                                <TableCell>
                                    {{ item.section?.name || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{
                                            item.department.code
                                        }}</span>
                                        <span
                                            class="text-sm text-muted-foreground"
                                            >{{
                                                item.department.college.code
                                            }}</span
                                        >
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            getStatusBadgeVariant(
                                                item.delivery_mode,
                                            )
                                        "
                                    >
                                        {{
                                            getDeliveryModeBadge(
                                                item.delivery_mode,
                                            )
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ item.class_size || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    <Users class="mr-1 inline h-4 w-4" />
                                    {{ item.faculty.length }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            getStatusBadgeVariant(item.status)
                                        "
                                    >
                                        {{
                                            item.status
                                                .replace('_', ' ')
                                                .toUpperCase()
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link :href="classOffering.show(item.id)">
                                        <Button variant="ghost" size="sm">
                                            <ExternalLink class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Link :href="classOffering.edit(item.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="mt-4 flex items-center justify-end space-x-2">
                    <p class="text-sm text-muted-foreground">
                        {{ props.classOfferings.meta.from }} to
                        {{ props.classOfferings.meta.to }} of
                        {{ props.classOfferings.meta.total }} class offerings
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
