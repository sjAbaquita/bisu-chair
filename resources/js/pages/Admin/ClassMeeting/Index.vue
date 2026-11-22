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
    Calendar,
    Clock,
    MapPin,
    ExternalLink,
} from '@lucide/vue';
import classOffering from '@/routes/admin/class_offerings';
import class_meetings from '@/routes/admin/class_meetings';
import faculty from '@/routes/admin/class_offerings/faculty';

interface ClassOffering {
    id: number;
    subject: { code: string; title: string };
    department: { code: string; name: string };
    delivery_mode: string;
    status: string;
}

interface Room {
    id: number;
    code: string;
    name: string | null;
    room_type: string;
}

interface ClassMeeting {
    id: number;
    delivery_mode: string;
    room: Room | null;
    day_of_week: number | null;
    start_time: string;
    end_time: string;
    effective_start_date: string;
    effective_end_date: string;
    notes: string | null;
}

const props = defineProps<{
    classOffering: ClassOffering;
    meetings: ClassMeeting[];
    rooms: Room[];
    campuses: { id: number; name: string; code: string }[];
    deliveryModes: string[];
}>();

const getDayOfWeek = (dayNumber: number | null): string => {
    if (dayNumber === null) return 'One-time';
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

const getDeliveryModeLabel = (mode: string): string => {
    const labels: Record<string, string> = {
        face_to_face: 'Face-to-Face',
        synchronous_online: 'Sync Online',
        asynchronous_online: 'Async Online',
    };
    return labels[mode] || mode;
};

const getDeliveryModeBadgeVariant = (mode: string) => {
    if (mode === 'face_to_face') return 'default';
    return 'outline';
};

const handleDelete = (meeting: ClassMeeting) => {
    if (confirm('Delete this class meeting?')) {
        router.delete(
            class_meetings.destroy(meeting.id),
            {
                onSuccess: () => {
                    // Refresh the page
                },
            },
        );
    }
};
</script>

<template>
    <Head :title="classOffering.subject.code + ' - Schedule'" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="classOffering.show(classOffering.id)">
                <Button variant="ghost" size="sm">
                    <span class="mr-2">←</span>
                    Back to Class Offering
                </Button>
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Class Schedule</CardTitle>
                        <CardDescription
                            >Manage meeting patterns for
                            {{ classOffering.subject.code }} -
                            {{ classOffering.subject.title }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="mb-4 flex items-center justify-between">
                            <Button
                                as="a"
                                :href="classOffering.edit(classOffering.id)"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Edit Class Offering
                            </Button>
                            <Link :href="faculty.index(classOffering.id)">
                                <Button variant="outline">
                                    <ExternalLink class="mr-2 h-4 w-4" />
                                    View Faculty Assignments
                                </Button>
                            </Link>
                        </div>

                        <div
                            v-if="props.meetings.length === 0"
                            class="py-12 text-center"
                        >
                            <Calendar
                                class="mx-auto mb-2 h-12 w-12 text-muted-foreground"
                            />
                            <p class="text-muted-foreground">
                                No meetings defined yet.
                            </p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="meeting in props.meetings"
                                :key="meeting.id"
                                class="rounded-md border p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <Badge
                                            :variant="
                                                getDeliveryModeBadgeVariant(
                                                    meeting.delivery_mode,
                                                )
                                            "
                                        >
                                            {{
                                                getDeliveryModeLabel(
                                                    meeting.delivery_mode,
                                                )
                                            }}
                                        </Badge>
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
                                                            null &&
                                                        meeting.start_time
                                                    "
                                                >
                                                    • {{ meeting.start_time }} -
                                                    {{ meeting.end_time }}
                                                </span>
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                <span v-if="meeting.room"
                                                    >{{ meeting.room.code }} -
                                                </span>
                                                {{
                                                    meeting.effective_start_date
                                                }}
                                                to
                                                {{ meeting.effective_end_date }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Button variant="ghost" size="sm">
                                            <ExternalLink class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="handleDelete(meeting)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Class Information</CardTitle>
                        <CardDescription
                            >Subject and delivery details.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div>
                            <p class="text-sm text-muted-foreground">Subject</p>
                            <p class="font-medium">
                                {{ classOffering.subject.code }} -
                                {{ classOffering.subject.title }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Department
                            </p>
                            <p class="font-medium">
                                {{ classOffering.department.code }} -
                                {{ classOffering.department.name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Delivery Mode
                            </p>
                            <Badge
                                :variant="
                                    getDeliveryModeBadgeVariant(
                                        classOffering.delivery_mode,
                                    )
                                "
                            >
                                {{
                                    getDeliveryModeLabel(
                                        classOffering.delivery_mode,
                                    )
                                }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
