<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Plus, Edit, Trash2, Search, DoorOpen } from '@lucide/vue';
import room from '@/routes/admin/rooms';

interface Room {
    id: number;
    code: string;
    name: string | null;
    room_type: string;
    capacity: number | null;
    status: string;
    notes: string | null;
    building?: {
        name: string;
        code: string;
    };
    college?: {
        name: string;
        code: string;
    } | null;
}

const props = defineProps<{
    rooms: {
        data: Room[];
        links: Array<{ url: string; label: string }>;
        // meta: {
        //     per_page: number;
        //     current_page: number;
        //     from: number;
        //     to: number;
        //     total: number;
        // };
    };
    search?: string;
}>();

const search = ref(props.search || '');

const handleSearch = () => {
    router.get(
        room.index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const handleDelete = (roomItem: Room) => {
    if (confirm(`Are you sure you want to delete room "${roomItem.code}"?`)) {
        router.delete(room.destroy(roomItem.id));
    }
};

const statusVariant = (status: string) => {
    switch (status) {
        case 'available':
            return 'default';
        case 'unavailable':
            return 'outline';
        case 'under_maintenance':
            return 'secondary';
        case 'retired':
            return 'outline';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head title="Rooms" />

    <div class="flex items-center justify-between px-4">
        <h1 class="text-2xl font-bold tracking-tight">Rooms</h1>
        <Link :href="room.create()">
            <Button>
                <Plus class="mr-2 h-4 w-4" />
                Add Room
            </Button>
        </Link>
    </div>

    <div class="px-4">
        <Card>
            <CardHeader>
                <CardTitle>Room Management</CardTitle>
                <CardDescription
                    >Manage BISU rooms across all buildings and
                    campuses.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="mb-4 flex items-center space-x-2">
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            type="search"
                            placeholder="Search rooms by code..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Building</TableHead>
                                <TableHead>College</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Capacity</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="roomItem in props.rooms.data"
                                :key="roomItem.id"
                            >
                                <TableCell class="font-medium">
                                    <Badge variant="outline">{{
                                        roomItem.code
                                    }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <DoorOpen
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        {{ roomItem.name || '-' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ roomItem.building?.name || '-' }}
                                </TableCell>
                                <TableCell>
                                    {{ roomItem.college?.code || '-' }}
                                </TableCell>
                                <TableCell>{{
                                    roomItem.room_type.replace(/_/g, ' ')
                                }}</TableCell>
                                <TableCell>{{
                                    roomItem.capacity ?? '-'
                                }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            statusVariant(roomItem.status)
                                        "
                                    >
                                        {{ roomItem.status.replace(/_/g, ' ') }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="space-x-2 text-right">
                                    <Link :href="room.edit(roomItem.id)">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="handleDelete(roomItem)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="mt-4 flex items-center justify-end space-x-2">
                    <p class="text-sm text-muted-foreground">
                        {{ props.rooms.from }} to
                        {{ props.rooms.to }} of
                        {{ props.rooms.total }} rooms
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
