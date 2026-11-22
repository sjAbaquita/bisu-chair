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
import campus from '@/routes/admin/campuses';
import { ArrowLeft, Save } from '@lucide/vue';

interface Campus {
    id: number;
    name: string;
    code: string;
    address: string | null;
    is_active: boolean;
}

const props = defineProps<{
    campus: Campus;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Campuses', href: campus.index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    id: props.campus.id,
    name: props.campus.name,
    code: props.campus.code,
    address: props.campus.address || '',
    is_active: props.campus.is_active,
});

const submitting = ref(false);

const submit = () => {
    submitting.value = true;
    router.put(campus.update(props.campus.id), form, {
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
    <Head title="Edit Campus" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="campus.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Campuses
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Edit Campus</CardTitle>
                <CardDescription
                    >Update campus details for BISU.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Campus Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Bohol Island State University"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="code">Campus Code *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="e.g. Main Campus"
                                required
                            />
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <Label for="address">Address</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                placeholder="Main Campus address..."
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
                        <Link :href="campus.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Campus
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
