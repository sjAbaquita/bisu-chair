<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
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
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import classOffering from '@/routes/admin/class_offerings';
import { ArrowLeft, Save } from '@lucide/vue';

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

interface Subject {
    id: number;
    code: string;
    title: string;
    units: number;
    lecture_hours: number;
    laboratory_hours: number | null;
    is_active: boolean;
}

interface Faculty {
    id: number;
    first_name: string;
    last_name: string;
    middle_name: string | null;
    suffix: string | null;
    employee_no: string | null;
    email: string | null;
    status: string;
}

interface ClassOffering {
    id: number;
    academic_period_id: number;
    department_id: number;
    subject_id: number;
    section_id: number | null;
    delivery_mode: string;
    class_size: number | null;
    status: string;
    remarks: string | null;
    academic_period: AcademicPeriod;
    department: Department;
    subject: Subject;
    faculty: Array<{
        id: number;
        pivot: {
            load_hours: number | null;
            load_units: number | null;
            role: string | null;
        };
    }>;
}

const props = defineProps<{
    classOffering: ClassOffering;
    academicPeriods?: AcademicPeriod[];
    departments?: Department[];
    subjects?: Subject[];
    faculty?: Faculty[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Class Offerings', href: classOffering.index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    id: props.classOffering.id,
    academic_period_id: props.classOffering.academic_period_id,
    department_id: props.classOffering.department_id,
    section_id: props.classOffering.section_id,
    subject_id: props.classOffering.subject_id,
    delivery_mode: props.classOffering.delivery_mode,
    class_size: props.classOffering.class_size,
    remarks: props.classOffering.remarks,
    faculty: props.classOffering.faculty.map((f) => ({
        faculty_id: f.id,
        load_hours: f.pivot.load_hours ?? '',
        load_units: f.pivot.load_units ?? '',
        role: f.pivot.role ?? '',
    })),
});

const submitting = ref(false);

const submit = () => {
    submitting.value = true;
    router.put(classOffering.update(props.classOffering.id), form, {
        onSuccess: () => {
            submitting.value = false;
        },
        onError: (errors) => {
            submitting.value = false;
        },
    });
};

const addFacultyMember = () => {
    form.faculty.push({
        faculty_id: 0,
        load_hours: '',
        load_units: '',
        role: '',
    });
};

const removeFacultyMember = (index: number) => {
    form.faculty.splice(index, 1);
};
</script>

<template>
    <Head title="Edit Class Offering" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="classOffering.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Class Offerings
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Edit Class Offering</CardTitle>
                <CardDescription
                    >Update class offering details for
                    {{
                        form.subject_id ? 'subject selection' : 'subject'
                    }}.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="academic_period_id"
                                >Academic Period *</Label
                            >
                            <Select v-model="form.academic_period_id" required>
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select academic period"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="period in props.academicPeriods"
                                        :key="period.id"
                                        :value="period.id"
                                    >
                                        {{ period.academic_year }} -
                                        {{ period.term }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="department_id">Department *</Label>
                            <Select v-model="form.department_id" required>
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select department"
                                    />
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
                        </div>

                        <div class="space-y-2">
                            <Label for="subject_id">Subject *</Label>
                            <Select v-model="form.subject_id" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select subject" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="subject in props.subjects"
                                        :key="subject.id"
                                        :value="subject.id"
                                    >
                                        {{ subject.code }} - {{ subject.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="section_id">Section</Label>
                            <Select v-model="form.section_id">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select section (optional)"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="section in []"
                                        :key="section?.id"
                                        :value="section?.id"
                                    >
                                        No sections available
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="delivery_mode">Delivery Mode *</Label>
                            <Select v-model="form.delivery_mode">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select delivery mode"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="face_to_face"
                                        >Face-to-Face</SelectItem
                                    >
                                    <SelectItem value="online"
                                        >Online</SelectItem
                                    >
                                    <SelectItem value="hybrid"
                                        >Hybrid</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="class_size">Class Size</Label>
                            <Input
                                id="class_size"
                                v-model.number="form.class_size"
                                type="number"
                                min="1"
                                placeholder="e.g. 40"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="remarks">Remarks</Label>
                            <Input
                                id="remarks"
                                v-model="form.remarks"
                                type="text"
                                placeholder="Additional notes..."
                            />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <Label>Faculty Members *</Label>
                        <div class="space-y-3">
                            <div
                                v-for="(facultyMember, index) in form.faculty"
                                :key="index"
                                class="flex items-center space-x-3 rounded-md border p-3"
                            >
                                <div class="flex-1 space-y-2">
                                    <Select
                                        v-model="facultyMember.faculty_id"
                                        required
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select faculty"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="f in props.faculty"
                                                :key="f.id"
                                                :value="f.id"
                                            >
                                                {{ f.first_name }}
                                                {{ f.last_name }} ({{
                                                    f.employee_no || 'No ID'
                                                }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="w-24 space-y-2">
                                    <Input
                                        type="number"
                                        v-model.number="
                                            facultyMember.load_hours
                                        "
                                        placeholder="Hours"
                                        min="0"
                                    />
                                </div>

                                <div class="w-24 space-y-2">
                                    <Input
                                        type="number"
                                        v-model.number="
                                            facultyMember.load_units
                                        "
                                        placeholder="Units"
                                        min="0"
                                    />
                                </div>

                                <div class="w-32 space-y-2">
                                    <Input
                                        type="text"
                                        v-model="facultyMember.role"
                                        placeholder="Role (e.g., Lead)"
                                    />
                                </div>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeFacultyMember(index)"
                                >
                                    Remove
                                </Button>
                            </div>
                        </div>

                        <Button
                            type="button"
                            variant="outline"
                            @click="addFacultyMember"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            Add Faculty Member
                        </Button>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <Link :href="classOffering.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Class Offering
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
