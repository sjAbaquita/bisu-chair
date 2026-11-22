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
import { Badge } from '@/components/ui/badge';
import classOffering from '@/routes/admin/class_offerings';
import {
    ArrowLeft,
    Edit,
    ExternalLink,
    Users,
    BookOpen,
    GraduationCap,
    Calendar,
} from '@lucide/vue';

interface ClassOffering {
    id: number;
    academic_period: {
        academic_year: string;
        term: string;
        start_date: string;
        end_date: string;
        status: string;
    };
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
    submitted_by: { name: string } | null;
    approved_by: { name: string } | null;
    submitted_at: string | null;
    approved_at: string | null;
    faculty: Array<{
        id: number;
        first_name: string;
        last_name: string;
        employee_no: string | null;
        pivot: {
            load_hours: number | null;
            load_units: number | null;
            role: string | null;
        };
    }>;
    class_meetings: Array<{
        id: number;
        delivery_mode: string;
        room: { code: string; name: string } | null;
        day_of_week: number | null;
        start_time: string;
        end_time: string;
        effective_start_date: string;
        effective_end_date: string;
        notes: string | null;
    }>;
}

const props = defineProps<{
    classOffering: ClassOffering;
}>();

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

const getDayOfWeek = (dayNumber: number | null) => {
    if (dayNumber === null) return 'N/A';
    const days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];
    return days[dayNumber];
};
</script>

<template>
    <Head title="Class Offering Details" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="classOffering.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Class Offerings
                </Button>
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Class Offering Details</CardTitle>
                        <CardDescription
                            >View class offering information and
                            schedule.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div
                            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div class="space-y-2">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Subject
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <BookOpen
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="font-medium">{{
                                        props.classOffering.subject.code
                                    }}</span>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ props.classOffering.subject.title }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Section
                                </h3>
                                <p class="font-medium">
                                    {{
                                        props.classOffering.section?.name ||
                                        'N/A'
                                    }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Department
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <GraduationCap
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="font-medium">{{
                                        props.classOffering.department.code
                                    }}</span>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ props.classOffering.department.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    College
                                </h3>
                                <p class="font-medium">
                                    {{
                                        props.classOffering.department.college
                                            .name
                                    }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Academic Period
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <Calendar
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="font-medium">{{
                                        props.classOffering.academic_period
                                            .academic_year
                                    }}</span>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        props.classOffering.academic_period.term
                                    }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Delivery Mode
                                </h3>
                                <Badge
                                    :variant="
                                        getStatusBadgeVariant(
                                            props.classOffering.delivery_mode,
                                        )
                                    "
                                >
                                    {{
                                        getDeliveryModeBadge(
                                            props.classOffering.delivery_mode,
                                        )
                                    }}
                                </Badge>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-md border p-4">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Class Size
                                </h3>
                                <p class="text-2xl font-bold">
                                    {{
                                        props.classOffering.class_size || 'N/A'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-md border p-4">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Status
                                </h3>
                                <Badge
                                    :variant="
                                        getStatusBadgeVariant(
                                            props.classOffering.status,
                                        )
                                    "
                                >
                                    {{
                                        props.classOffering.status
                                            .replace('_', ' ')
                                            .toUpperCase()
                                    }}
                                </Badge>
                            </div>

                            <div class="rounded-md border p-4">
                                <h3
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Faculty Count
                                </h3>
                                <div class="flex items-center space-x-1">
                                    <Users
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-2xl font-bold">{{
                                        props.classOffering.faculty.length
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="props.classOffering.remarks"
                            class="rounded-md border p-4"
                        >
                            <h3
                                class="mb-2 text-sm font-medium text-muted-foreground"
                            >
                                Remarks
                            </h3>
                            <p class="text-sm">
                                {{ props.classOffering.remarks }}
                            </p>
                        </div>

                        <div>
                            <h3 class="mb-4 text-lg font-medium">
                                Faculty Members
                            </h3>
                            <div class="rounded-md border">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted">
                                        <tr>
                                            <th class="px-4 py-2 text-left">
                                                Name
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Employee No
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Load Hours
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Load Units
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Role
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="f in props.classOffering
                                                .faculty"
                                            :key="f.id"
                                        >
                                            <td class="px-4 py-2">
                                                {{ f.first_name }}
                                                {{ f.last_name }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ f.employee_no || '-' }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ f.pivot.load_hours || '-' }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ f.pivot.load_units || '-' }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ f.pivot.role || '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div v-if="props.classOffering.class_meetings.length">
                            <h3 class="mb-4 text-lg font-medium">
                                Class Meetings
                            </h3>
                            <div class="space-y-3">
                                <div
                                    v-for="meeting in props.classOffering
                                        .class_meetings"
                                    :key="meeting.id"
                                    class="rounded-md border p-4"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div
                                            class="flex items-center space-x-4"
                                        >
                                            <Badge variant="outline">{{
                                                getDeliveryModeBadge(
                                                    meeting.delivery_mode,
                                                )
                                            }}</Badge>
                                            <div class="space-y-1">
                                                <div class="font-medium">
                                                    {{
                                                        getDayOfWeek(
                                                            meeting.day_of_week,
                                                        )
                                                    }}
                                                    <span
                                                        v-if="
                                                            meeting.day_of_week !==
                                                            null
                                                        "
                                                    >
                                                        •
                                                        {{ meeting.start_time }}
                                                        - {{ meeting.end_time }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{
                                                        meeting.effective_start_date
                                                    }}
                                                    to
                                                    {{
                                                        meeting.effective_end_date
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="meeting.room">
                                            <Badge>{{
                                                meeting.room.code
                                            }}</Badge>
                                        </div>
                                    </div>
                                    <p
                                        v-if="meeting.notes"
                                        class="mt-2 text-sm text-muted-foreground"
                                    >
                                        {{ meeting.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Actions</CardTitle>
                        <CardDescription
                            >Manage this class offering.</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Link
                                :href="
                                    classOffering.edit(props.classOffering.id)
                                "
                            >
                                <Button class="w-full justify-start">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Class Offering
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
