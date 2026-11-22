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
import academicPeriod from '@/routes/admin/academic_periods';
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

const props = defineProps<{
    period: AcademicPeriod;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Academic Periods', href: academicPeriod.index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    id: props.period.id,
    academic_year: props.period.academic_year,
    term: props.period.term,
    start_date: props.period.start_date,
    end_date: props.period.end_date,
    status: props.period.status,
    is_current: props.period.is_current,
});

const submitting = ref(false);

const terms = ['First Semester', 'Second Semester', 'Summer'];
const statuses = ['draft', 'open', 'locked', 'archived'];

const submit = () => {
    submitting.value = true;
    router.put(academicPeriod.update(props.period.id), form, {
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
    <Head title="Edit Academic Period" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="academicPeriod.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Academic Periods
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Edit Academic Period</CardTitle>
                <CardDescription
                    >Update academic period details.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="academic_year">Academic Year *</Label>
                            <Input
                                id="academic_year"
                                v-model="form.academic_year"
                                placeholder="e.g. 2026-2027"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="term">Term *</Label>
                            <select
                                id="term"
                                v-model="form.term"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                                required
                            >
                                <option value="">Select a term...</option>
                                <option
                                    v-for="term in terms"
                                    :key="term"
                                    :value="term"
                                >
                                    {{ term }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="start_date">Start Date *</Label>
                            <Input
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="end_date">End Date *</Label>
                            <Input
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                                required
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

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_current"
                                :checked="form.is_current"
                                @update:checked="form.is_current = $event"
                            />
                            <Label for="is_current"
                                >Mark as current period</Label
                            >
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <Link :href="academicPeriod.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Academic Period
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
