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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Plus, Edit, Trash2, User, X } from '@lucide/vue';
import classOffering from '@/routes/admin/class_offerings';
import faculty from '@/routes/admin/class_offerings/faculty';
import meetings from '@/routes/admin/class_offerings/meetings';

interface Faculty {
    id: number;
    employee_no: string | null;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    suffix: string | null;
    email: string | null;
}

interface FacultyAssignment {
    id: number;
    faculty_id: number;
    faculty: Faculty;
    load_hours: number | null;
    load_units: number | null;
    role: string | null;
}

interface ClassOffering {
    id: number;
    academic_period: { academic_year: string; term: string };
    department: { code: string; name: string; college: { name: string } };
    subject: { code: string; title: string };
    delivery_mode: string;
    status: string;
}

const props = defineProps<{
    classOffering: ClassOffering;
    facultyAssignments: FacultyAssignment[];
    faculty: Faculty[];
}>();

const showAddDialog = ref(false);
const newAssignment = ref({
    faculty_id: 0,
    load_hours: '',
    load_units: '',
    role: '',
});

const getDeliveryModeLabel = (mode: string): string => {
    const labels: Record<string, string> = {
        face_to_face: 'Face-to-Face',
        online: 'Online',
        hybrid: 'Hybrid',
    };
    return labels[mode] || mode;
};

const getStatusBadgeVariant = (status: string) => {
    if (status === 'approved') return 'default';
    if (status === 'cancelled') return 'outline';
    return 'secondary';
};

const handleAdd = () => {
    if (newAssignment.value.faculty_id === 0) return;

    router.post(
        faculty.store(props.classOffering.id),
        {
            faculty_id: newAssignment.value.faculty_id,
            load_hours: newAssignment.value.load_hours || null,
            load_units: newAssignment.value.load_units || null,
            role: newAssignment.value.role || null,
        },
        {
            onSuccess: () => {
                showAddDialog.value = false;
                newAssignment.value = {
                    faculty_id: 0,
                    load_hours: '',
                    load_units: '',
                    role: '',
                };
            },
        },
    );
};

const handleDelete = (assignment: FacultyAssignment) => {
    if (
        confirm(
            `Remove ${assignment.faculty.first_name} ${assignment.faculty.last_name} from this class offering?`,
        )
    ) {
        router.delete(
            faculty.destroy(
                props.classOffering.id,
                assignment.id,
            ),
        );
    }
};
</script>

<template>
    <Head :title="classOffering.subject.code + ' - Faculty'" />

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
                        <CardTitle>Faculty Assignments</CardTitle>
                        <CardDescription
                            >Manage faculty members assigned to
                            {{ classOffering.subject.code }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="mb-4 flex items-center justify-between">
                            <Link :href="meetings.index(classOffering.id)">
                                <Button variant="outline">
                                    <Edit class="mr-2 h-4 w-4" />
                                    View Schedule
                                </Button>
                            </Link>
                            <Button @click="showAddDialog = true">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Faculty
                            </Button>
                        </div>

                        <div
                            v-if="props.facultyAssignments.length === 0"
                            class="py-12 text-center"
                        >
                            <User
                                class="mx-auto mb-2 h-12 w-12 text-muted-foreground"
                            />
                            <p class="text-muted-foreground">
                                No faculty assigned yet.
                            </p>
                        </div>

                        <div v-else class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Faculty</TableHead>
                                        <TableHead>Employee No.</TableHead>
                                        <TableHead>Load Hours</TableHead>
                                        <TableHead>Load Units</TableHead>
                                        <TableHead>Role</TableHead>
                                        <TableHead class="text-right"
                                            >Actions</TableHead
                                        >
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="assignment in props.facultyAssignments"
                                        :key="assignment.id"
                                    >
                                        <TableCell>
                                            <div class="font-medium">
                                                {{
                                                    assignment.faculty
                                                        .first_name
                                                }}
                                                {{
                                                    assignment.faculty.last_name
                                                }}
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    assignment.faculty.email ||
                                                    '-'
                                                }}
                                            </div>
                                        </TableCell>
                                        <TableCell>{{
                                            assignment.faculty.employee_no ||
                                            '-'
                                        }}</TableCell>
                                        <TableCell>{{
                                            assignment.load_hours || '-'
                                        }}</TableCell>
                                        <TableCell>{{
                                            assignment.load_units || '-'
                                        }}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{
                                                assignment.role || '-'
                                            }}</Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="
                                                    handleDelete(assignment)
                                                "
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Class Information</CardTitle>
                        <CardDescription
                            >Subject and scheduling details.</CardDescription
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
                            <p class="text-sm text-muted-foreground">College</p>
                            <p class="font-medium">
                                {{ classOffering.department.college.name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Academic Period
                            </p>
                            <p class="font-medium">
                                {{
                                    classOffering.academic_period.academic_year
                                }}
                                - {{ classOffering.academic_period.term }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Delivery Mode
                            </p>
                            <Badge
                                :variant="
                                    getStatusBadgeVariant(
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
                        <div>
                            <p class="text-sm text-muted-foreground">Status</p>
                            <Badge
                                :variant="
                                    getStatusBadgeVariant(classOffering.status)
                                "
                            >
                                {{
                                    classOffering.status
                                        .replace('_', ' ')
                                        .toUpperCase()
                                }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

    <!-- Add Faculty Dialog -->
    <Dialog v-model:open="showAddDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add Faculty Member</DialogTitle>
                <DialogDescription
                    >Select a faculty member to assign to this class
                    offering.</DialogDescription
                >
            </DialogHeader>

            <div class="space-y-4">
                <select
                    v-model.number="newAssignment.faculty_id"
                    class="w-full rounded-md border px-3 py-2"
                >
                    <option :value="0">Select Faculty</option>
                    <option
                        v-for="f in props.faculty"
                        :key="f.id"
                        :value="f.id"
                    >
                        {{ f.first_name }} {{ f.last_name }} ({{
                            f.employee_no || 'No ID'
                        }})
                    </option>
                </select>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Load Hours</label>
                        <Input
                            type="number"
                            v-model.number="newAssignment.load_hours"
                            min="0"
                            step="0.25"
                            placeholder="e.g. 3.0"
                        />
                    </div>
                    <div>
                        <label class="text-sm font-medium">Load Units</label>
                        <Input
                            type="number"
                            v-model.number="newAssignment.load_units"
                            min="0"
                            step="0.25"
                            placeholder="e.g. 3"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium">Role</label>
                    <Input
                        v-model="newAssignment.role"
                        placeholder="e.g. Lead, Co-faculty"
                    />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="showAddDialog = false">
                    Cancel
                </Button>
                <Button
                    :disabled="newAssignment.faculty_id === 0"
                    @click="handleAdd"
                >
                    Add Faculty Member
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
