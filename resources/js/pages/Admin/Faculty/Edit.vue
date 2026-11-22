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
import faculty from '@/routes/admin/faculty';
import { ArrowLeft, Save } from '@lucide/vue';

interface Faculty {
    id: number;
    employee_no: string | null;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    suffix: string | null;
    email: string | null;
    status: string;
    highest_degree_earned: string | null;
    specialization: string | null;
}

const props = defineProps<{
    faculty: Faculty;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Faculty', href: faculty.index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    id: props.faculty.id,
    employee_no: props.faculty.employee_no || '',
    first_name: props.faculty.first_name,
    middle_name: props.faculty.middle_name || '',
    last_name: props.faculty.last_name,
    suffix: props.faculty.suffix || '',
    email: props.faculty.email || '',
    status: props.faculty.status,
    highest_degree_earned: props.faculty.highest_degree_earned || '',
    specialization: props.faculty.specialization || '',
});

const submitting = ref(false);

const statuses = ['active', 'inactive'];

const submit = () => {
    submitting.value = true;
    router.put(faculty.update(props.faculty.id), form, {
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
    <Head title="Edit Faculty" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="faculty.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Faculty
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Edit Faculty Member</CardTitle>
                <CardDescription
                    >Update faculty member details.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="employee_no">Employee No.</Label>
                            <Input
                                id="employee_no"
                                v-model="form.employee_no"
                                placeholder="e.g. BISU-0001"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status *</Label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                                required
                            >
                                <option
                                    v-for="status in statuses"
                                    :key="status"
                                    :value="status"
                                >
                                    {{
                                        status.charAt(0).toUpperCase() +
                                        status.slice(1)
                                    }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="first_name">First Name *</Label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                placeholder="e.g. Juan"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="middle_name">Middle Name</Label>
                            <Input
                                id="middle_name"
                                v-model="form.middle_name"
                                placeholder="e.g. Dela Cruz"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="last_name">Last Name *</Label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                placeholder="e.g. Santos"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="suffix">Suffix</Label>
                            <Input
                                id="suffix"
                                v-model="form.suffix"
                                placeholder="e.g. Jr."
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="e.g. juan.santos@bisu.edu.ph"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="highest_degree_earned"
                                >Highest Degree Earned</Label
                            >
                            <Input
                                id="highest_degree_earned"
                                v-model="form.highest_degree_earned"
                                placeholder="e.g. Master of Arts in Education"
                            />
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <Label for="specialization">Specialization</Label>
                            <Input
                                id="specialization"
                                v-model="form.specialization"
                                placeholder="e.g. Hospitality Management"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <Link :href="faculty.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Faculty Member
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
