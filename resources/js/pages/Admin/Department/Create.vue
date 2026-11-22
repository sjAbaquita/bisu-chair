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
import department from '@/routes/admin/departments';
import college from '@/routes/admin/colleges';
import { ArrowLeft, Save } from '@lucide/vue';

interface College {
    id: number;
    name: string;
    code: string;
}

const props = defineProps<{
    colleges: College[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Departments', href: department.index() },
            { title: 'Create', href: department.create() },
        ],
    },
});

const form = useForm({
    college_id: '',
    name: '',
    code: '',
    is_active: true,
});

const submitting = ref(false);

const submit = () => {
    submitting.value = true;
    router.post(department.index(), form, {
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
    <Head title="Create Department" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="department.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Departments
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Create Department</CardTitle>
                <CardDescription
                    >Enter department details for BISU academic
                    units.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="college_id">College *</Label>
                            <select
                                id="college_id"
                                v-model="form.college_id"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                                required
                            >
                                <option value="">Select a college...</option>
                                <option
                                    v-for="item in props.colleges"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.code }} - {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="code">Department Code *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="e.g. BSIT"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Department Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Information Technology"
                                required
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
                        <Link :href="department.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Department
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
