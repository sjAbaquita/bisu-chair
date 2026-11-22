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
import { Textarea } from '@/components/ui/textarea';
import room from '@/routes/admin/rooms';
import { ArrowLeft, Save } from '@lucide/vue';

interface Building {
    id: number;
    name: string;
    code: string;
    campus_id: number;
    campus?: {
        name: string;
    };
}

interface College {
    id: number;
    name: string;
    code: string;
}

const props = defineProps<{
    buildings: Building[];
    colleges: College[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Rooms', href: room.index() },
            { title: 'Create', href: room.create() },
        ],
    },
});

const form = useForm({
    building_id: '' as string,
    college_id: '' as string,
    code: '',
    name: '',
    room_type: 'lecture_room',
    capacity: '' as unknown as number,
    features: [] as string[],
    status: 'available',
    notes: '',
});

const submitting = ref(false);

const roomTypes = [
    'lecture_room',
    'laboratory',
    'computer_laboratory',
    'conference_room',
    'auditorium',
    'library',
    'gymnasium',
    'other',
];

const roomStatuses = [
    'available',
    'unavailable',
    'under_maintenance',
    'retired',
];

const submit = () => {
    submitting.value = true;
    router.post(room.index(), form, {
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
    <Head title="Create Room" />

    <div class="px-4">
        <div class="mb-4 flex items-center space-x-2">
            <Link :href="room.index()">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Rooms
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Create Room</CardTitle>
                <CardDescription
                    >Enter room details for BISU facilities.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="building_id">Building *</Label>
                            <select
                                id="building_id"
                                v-model="form.building_id"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                                required
                            >
                                <option value="">Select a building...</option>
                                <option
                                    v-for="item in props.buildings"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.code }} - {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="college_id">Owning College</Label>
                            <select
                                id="college_id"
                                v-model="form.college_id"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                            >
                                <option value="">
                                    Not assigned to a college
                                </option>
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
                            <Label for="code">Room Code *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="e.g. 101"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Room Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g. Lecture Room 101"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="room_type">Room Type *</Label>
                            <select
                                id="room_type"
                                v-model="form.room_type"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm transition-colors"
                                required
                            >
                                <option
                                    v-for="type in roomTypes"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ type.replace(/_/g, ' ') }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="capacity">Capacity</Label>
                            <Input
                                id="capacity"
                                v-model.number="form.capacity"
                                type="number"
                                min="1"
                                placeholder="e.g. 40"
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
                                    v-for="status in roomStatuses"
                                    :key="status"
                                    :value="status"
                                >
                                    {{ status.replace(/_/g, ' ') }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Additional notes about the room..."
                            />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <Link :href="room.index()">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="submitting">
                            <Save class="mr-2 h-4 w-4" />
                            Save Room
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
