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
import { Checkbox } from '@/components/ui/checkbox';
import section from '@/routes/admin/sections';
import { ArrowLeft, Save } from '@lucide/vue';

interface AcademicPeriod {
    id: number;
    academic_year: string;
    term: string;
    start_date: string;
    end_date: string;
    status: string;
    is_current: boolean;
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
    department_id: number;
    academic_period_id: number;
    department?: Department;
    academic_period?: AcademicPeriod;
}

const props = defineProps<{
    section: SectionItem;
    academicPeriods: AcademicPeriod[];
    departments: Department[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Sections', href: section.index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    id: props.section.id,
    department_id: props.section.department_id,
    academic_period_id: props.section.academic_period_id,
    program_name: props.section.program_name || '',
    year_level: props.section.year_level || '',
    name: props.section.name,
    expected_enrollment: props.section.expected_enrollment || '',
    is_active: props.section.is_active,
});

const submitting = ref(false);

const submit = () => {
    submitting.value = true;
    router.put(section.update(props.section.id), form, {
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
    <Head title="Edit Section" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="section.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Sections
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Edit Section</CardTitle>
                <CardDescription
                    >Update section details for BISU academic
                    units.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="academic_period_id"
                                >Academic Period *</Label
                            >
                            <select
                                id="academic_period_id"
                                v-model="form.academic_period_id"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                                required
                            >
                                <option value="">
                                    Select an academic period...
                                </option>
                                <option
                                    v-for="item in props.academicPeriods"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.academic_year }} - {{ item.term }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="department_id">Department *</Label>
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                                required
                            >
                                <option value="">Select a department...</option>
                                <option
                                    v-for="item in props.departments"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.code }} - {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Section Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g. BSIT 2A"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="program_name">Program Name</Label>
                            <Input
                                id="program_name"
                                v-model="form.program_name"
                                placeholder="Bachelor of Science in Information Technology"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="year_level">Year Level</Label>
                            <Input
                                id="year_level"
                                v-model="form.year_level"
                                placeholder="2nd Year"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="expected_enrollment"
                                >Expected Enrollment</Label
                            >
                            <Input
                                id="expected_enrollment"
                                v-model.number="form.expected_enrollment"
                                type="number"
                                min="0"
                                placeholder="e.g. 40"
                            />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_active"
                                :checked="form.is_active"
                                @update:checked="form.is_active = $event"
                            />
                            <Label for="is_active">Active</Label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <Link :href="section.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Section
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
