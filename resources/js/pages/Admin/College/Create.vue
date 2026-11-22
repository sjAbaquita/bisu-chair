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
import college from '@/routes/admin/colleges';
import campus from '@/routes/admin/campuses';
import { ArrowLeft, Save } from '@lucide/vue';

interface Campus {
    id: number;
    name: string;
    code: string;
}

const props = defineProps<{
    campuses: Campus[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Colleges', href: college.index() },
            { title: 'Create', href: college.create() },
        ],
    },
});

const form = useForm({
    campus_id: '',
    name: '',
    code: '',
    is_active: true,
});

const submitting = ref(false);

const submit = () => {
    submitting.value = true;
    router.post(college.index(), form, {
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
    <Head title="Create College" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="college.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Colleges
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Create College</CardTitle>
                <CardDescription
                    >Enter college details for BISU academic
                    units.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="campus_id">Campus *</Label>
                            <select
                                id="campus_id"
                                v-model="form.campus_id"
                                class="disabled:operations-ever-disabled flex h-9 w-full rounded-md border border-border bg-card bg-transparent px-3 py-2 text-sm text-card-foreground transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30 dark:focus-visible:ring-ring"
                                required
                            >
                                <option value="">Select a campus...</option>
                                <option
                                    v-for="item in props.campuses"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="code">College Code *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="e.g. COEd"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="name">College Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="College of Education"
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
                        <Link :href="college.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save College
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
