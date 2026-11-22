<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
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
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import classOffering from '@/routes/admin/class_offerings';
import { ArrowLeft, Save } from '@lucide/vue';

interface AcademicPeriod {
    id: number;
    academic_year: string;
    term: string;
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
}

interface Faculty {
    id: number;
    first_name: string;
    last_name: string;
    middle_name: string | null;
    suffix: string | null;
    employee_no: string | null;
    email: string | null;
}

const props = defineProps<{
    academicPeriods?: AcademicPeriod[];
    departments?: Department[];
    subjects?: Subject[];
    faculty?: Faculty[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Class Offerings', href: classOffering.index() },
            { title: 'Create', href: classOffering.create() },
        ],
    },
});

const form = useForm({
    academic_period_id: '',
    department_id: '',
    section_id: '',
    subject_id: '',
    delivery_mode: 'face_to_face',
    class_size: '',
    remarks: '',
    faculty: [] as Array<{
        faculty_id: number;
        load_hours: number | string;
        load_units: number | string;
        role: string;
    }>,
});

const submitting = ref(false);

const filteredSubjects = computed(() => {
    if (!form.department_id) return [];
    return (
        props.subjects?.filter((s) => {
            const dept = props.departments?.find(
                (d) => d.id === form.department_id,
            );
            return dept;
        }) || []
    );
});

const availableFaculty = computed(() => {
    if (!form.department_id) return [];
    return props.faculty || [];
});

watch([form.academic_period_id, form.department_id], () => {
    form.section_id = '';
});

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

const submit = () => {
    submitting.value = true;
    router.post(classOffering.index(), form, {
        onSuccess: () => {
            submitting.value = false;
        },
        onError: (errors) => {
            submitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Create Class Offering" />

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
                <CardTitle>Create Class Offering</CardTitle>
                <CardDescription
                    >Enter class offering details for BISU academic
                    planning.</CardDescription
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
                                                v-for="f in availableFaculty"
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
