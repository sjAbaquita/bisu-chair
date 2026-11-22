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
import subject from '@/routes/admin/subjects';
import { ArrowLeft, Save } from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Subjects', href: subject.index() },
            { title: 'Create', href: subject.create() },
        ],
    },
});

const form = useForm({
    code: '',
    title: '',
    units: '',
    lecture_hours: 0,
    laboratory_hours: '',
    is_active: true,
});

const submitting = ref(false);

const submit = () => {
    submitting.value = true;
    router.post(subject.index(), form, {
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
    <Head title="Create Subject" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="subject.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Subjects
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Create Subject</CardTitle>
                <CardDescription
                    >Enter subject details for BISU courses.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="code">Subject Code *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="e.g. CS-101"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="title">Title *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="e.g. Introduction to Computer Science"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="units">Units *</Label>
                            <Input
                                id="units"
                                v-model="form.units"
                                type="number"
                                step="0.25"
                                min="0.25"
                                max="10"
                                placeholder="e.g. 3.00"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="lecture_hours">Lecture Hours *</Label>
                            <Input
                                id="lecture_hours"
                                v-model.number="form.lecture_hours"
                                type="number"
                                min="0"
                                placeholder="e.g. 3"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="laboratory_hours"
                                >Laboratory Hours</Label
                            >
                            <Input
                                id="laboratory_hours"
                                v-model.number="form.laboratory_hours"
                                type="number"
                                min="0"
                                placeholder="e.g. 2"
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
                        <Link :href="subject.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Subject
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
