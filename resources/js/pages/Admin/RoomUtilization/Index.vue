<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, reactive, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { CalendarDays, Filter, ChevronLeft, ChevronRight, Clock, MapPin, Users, BookOpen, Building, DoorOpen } from '@lucide/vue';
import { format, addDays, subDays, addWeeks, subWeeks, startOfWeek, addDays as dateAddDays } from 'date-fns';
import room from '@/routes/admin/rooms';

interface Campus {
    id: number;
    name: string;
    code: string;
}

interface Building {
    id: number;
    name: string;
    code: string;
    campus_id: number;
}

interface College {
    id: number;
    name: string;
    code: string;
    campus_id: number;
}

interface Room {
    id: number;
    code: string;
    name: string | null;
    room_type: string;
    capacity: number | null;
    status: string;
    building?: Building;
    college?: College;
}

interface AcademicPeriod {
    id: number;
    academic_year: string;
    term: string;
    start_date: string;
    end_date: string;
    status: string;
}

interface ClassMeetingOccurrence {
    id: number;
    meeting_date: string;
    start_time: string;
    end_time: string;
    room_id: number;
    status: string;
    class_meeting?: {
        delivery_mode: string;
        class_offering?: {
            subject?: { code: string; title: string };
            faculty?: Array<{ id: number; get_full_name: string }>;
            department?: { code: string; name: string };
        };
    };
}

const props = defineProps<{
    rooms: Room[];
    campuses: Campus[];
    buildings: Building[];
    colleges: College[];
    academicPeriods: AcademicPeriod[];
}>();

// Calendar state
const currentDate = ref(new Date());
const currentView = ref<'day' | 'week' | 'month'>('week');

// Filters
const filters = reactive({
    academic_period_id: '' as string,
    campus_id: '' as string,
    building_id: '' as string,
    room_id: '' as string,
    college_id: '' as string,
    delivery_mode: '' as string,
});

// Computed for filtered rooms
const filteredRooms = computed(() => {
    return props.rooms.filter(room => {
        if (filters.campus_id && room.building?.campus_id?.toString() !== filters.campus_id) return false;
        if (filters.building_id && room.building_id?.toString() !== filters.building_id) return false;
        if (filters.college_id && room.college_id?.toString() !== filters.college_id) return false;
        if (filters.room_id && room.id?.toString() !== filters.room_id) return false;
        if (filters.status && room.status !== filters.status) return false;
        return true;
    });
});

// Calendar dates for current view
const calendarDates = computed(() => {
    const dates: Date[] = [];
    const start = startOfWeek(currentDate.value, { weekStartsOn: 1 }); // Monday

    if (currentView.value === 'day') {
        dates.push(currentDate.value);
    } else if (currentView.value === 'week') {
        for (let i = 0; i < 7; i++) {
            dates.push(dateAddDays(start, i));
        }
    } else {
        for (let i = 0; i < 42; i++) {
            dates.push(dateAddDays(start, i));
        }
    }
    return dates;
});

// Time slots (8:00 - 18:00, 30 min increments)
const timeSlots = computed(() => {
    const slots: string[] = [];
    const startHour = 8;
    const endHour = 18;
    const slotMinutes = 30;

    for (let h = startHour; h < endHour; h++) {
        for (let m = 0; m < 60; m += slotMinutes) {
            slots.push(`${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:00`);
        }
    }
    return slots;
});

// Navigation functions
const previousPeriod = () => {
    if (currentView.value === 'day') {
        currentDate.value = subDays(currentDate.value, 1);
    } else if (currentView.value === 'week') {
        currentDate.value = subWeeks(currentDate.value, 1);
    } else {
        currentDate.value = subWeeks(currentDate.value, 4);
    }
};

const nextPeriod = () => {
    if (currentView.value === 'day') {
        currentDate.value = addDays(currentDate.value, 1);
    } else if (currentView.value === 'week') {
        currentDate.value = addWeeks(currentDate.value, 1);
    } else {
        currentDate.value = addWeeks(currentDate.value, 4);
    }
};

const goToToday = () => {
    currentDate.value = new Date();
};

// Filter functions
const applyFilters = () => {
    router.get(route('admin.rooms.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filters.academic_period_id = '';
    filters.campus_id = '';
    filters.building_id = '';
    filters.room_id = '';
    filters.college_id = '';
    filters.delivery_mode = '';
};

// Room status badge variant
const getRoomStatusVariant = (status: string) => {
    switch (status) {
        case 'available': return 'default';
        case 'unavailable': return 'outline';
        case 'under_maintenance': return 'secondary';
        case 'retired': return 'destructive';
        default: return 'outline';
    }
};
</script>

<template>
    <Head title="Room Utilization Calendar" />

    <div class="px-4">
        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Room Utilization Calendar</h1>
                <p class="text-muted-foreground">
                    View and manage room schedules across BISU campuses and colleges.
                </p>
            </div>
            <Link :href="room.index()">
                <Button variant="outline" size="sm">
                    <DoorOpen class="mr-2 h-4 w-4" />
                    Manage Rooms
                </Button>
            </Link>
        </div>

        <!-- Filters -->
        <Card class="mb-6">
            <CardHeader>
                <CardTitle class="flex items-center">
                    <Filter class="mr-2 h-5 w-5" />
                    Filters
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
                    <div class="space-y-2">
                        <Label for="academic_period_id">Academic Period</Label>
                        <Select v-model="filters.academic_period_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Periods" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Periods</SelectItem>
                                <SelectItem v-for="period in props.academicPeriods" :key="period.id" :value="period.id">
                                    {{ period.academic_year }} - {{ period.term }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="campus_id">Campus</Label>
                        <Select v-model="filters.campus_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Campuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Campuses</SelectItem>
                                <SelectItem v-for="c in props.campuses" :key="c.id" :value="c.id">
                                    {{ c.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="building_id">Building</Label>
                        <Select v-model="filters.building_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Buildings" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Buildings</SelectItem>
                                <SelectItem v-for="b in props.buildings" :key="b.id" :value="b.id">
                                    {{ b.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="college_id">College</Label>
                        <Select v-model="filters.college_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Colleges" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Colleges</SelectItem>
                                <SelectItem v-for="c in props.colleges" :key="c.id" :value="c.id">
                                    {{ c.code }} - {{ c.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="room_id">Room</Label>
                        <Select v-model="filters.room_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Rooms" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Rooms</SelectItem>
                                <SelectItem v-for="r in filteredRooms" :key="r.id" :value="r.id">
                                    {{ r.code }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="delivery_mode">Delivery Mode</Label>
                        <Select v-model="filters.delivery_mode">
                            <SelectTrigger>
                                <SelectValue placeholder="All Modes" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Modes</SelectItem>
                                <SelectItem value="face_to_face">Face-to-Face</SelectItem>
                                <SelectItem value="online">Online</SelectItem>
                                <SelectItem value="hybrid">Hybrid</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="mt-4 flex space-x-2">
                    <Button size="sm" @click="applyFilters">Apply Filters</Button>
                    <Button variant="outline" size="sm" @click="clearFilters">Clear</Button>
                </div>
            </CardContent>
        </Card>

        <!-- Calendar View Controls -->
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <Button variant="outline" size="sm" @click="previousPeriod">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Popover>
                    <PopoverTrigger as-child>
                        <Button variant="outline" size="sm">
                            {{ format(currentDate, 'MMMM yyyy') }}
                            <ChevronRight class="ml-2 h-4 w-4" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent align="center">
                        <Calendar
                            mode="single"
                            :value="currentDate"
                            @update:value="currentDate = $event"
                        />
                    </PopoverContent>
                </Popover>
                <Button variant="outline" size="sm" @click="nextPeriod">
                    <ChevronRight class="h-4 w-4" />
                </Button>
                <Button variant="outline" size="sm" @click="goToToday">
                    Today
                </Button>
            </div>

            <div class="flex space-x-1 rounded-md bg-muted p-1">
                <Button
                    :variant="currentView === 'day' ? 'default' : 'ghost'"
                    size="sm"
                    @click="currentView = 'day'"
                >
                    Day
                </Button>
                <Button
                    :variant="currentView === 'week' ? 'default' : 'ghost'"
                    size="sm"
                    @click="currentView = 'week'"
                >
                    Week
                </Button>
                <Button
                    :variant="currentView === 'month' ? 'default' : 'ghost'"
                    size="sm"
                    @click="currentView = 'month'"
                >
                    Month
                </Button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center">
                    <CalendarDays class="mr-2 h-5 w-5" />
                    Schedule Overview
                </CardTitle>
                <CardDescription>
                    {{ filteredRooms.length }} rooms filtered
                </CardDescription>
            </CardHeader>
            <CardContent>
                <template v-if="filteredRooms.length === 0">
                    <div class="py-12 text-center">
                        <DoorOpen class="mx-auto mb-2 h-12 w-12 text-muted-foreground" />
                        <p class="text-muted-foreground">
                            No rooms match the current filters. Adjust your search criteria.
                        </p>
                    </div>
                </template>

                <div v-else>
                    <!-- Room Schedule Grid -->
                    <div class="space-y-4">
                        <div
                            v-for="room in filteredRooms"
                            :key="room.id"
                            class="rounded-lg border p-4"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium">
                                        <Badge variant="outline">{{ room.code }}</Badge>
                                        {{ room.name ? ` - ${room.name}` : '' }}
                                    </h3>
                                    <div class="mt-1 flex items-center text-sm text-muted-foreground">
                                        <MapPin class="mr-1 h-4 w-4" />
                                        <span class="mr-2">{{ room.building?.name || 'Building N/A' }}</span>
                                        <span class="mx-2">•</span>
                                        <span class="mr-2">{{ room.college?.code || 'College N/A' }}</span>
                                        <span class="mx-2">•</span>
                                        <span class="flex items-center">
                                            <DoorOpen class="mr-1 h-3 w-3" />
                                            {{ room.capacity || 'N/A' }} cap
                                        </span>
                                    </div>
                                </div>
                                <Badge :variant="getRoomStatusVariant(room.status)">
                                    {{ room.status.replace(/_/g, ' ') }}
                                </Badge>
                            </div>

                            <!-- Room Timetable -->
                            <div class="overflow-x-auto">
                                <table class="w-full table-auto border-collapse">
                                    <thead class="bg-muted">
                                        <tr>
                                            <th class="border p-2 text-left text-xs font-medium">Time</th>
                                            <th v-for="date in calendarDates" :key="date.toString()" class="border p-2 text-center text-xs font-medium">
                                                {{ format(date, 'EEE dd/MM') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="timeSlot in timeSlots" :key="timeSlot">
                                            <td class="border p-2 text-xs text-muted-foreground">
                                                {{ timeSlot.slice(0, 5) }}
                                            </td>
                                            <td v-for="date in calendarDates" :key="`${date}-${timeSlot}`" class="border p-1 align-top">
                                                <div
                                                    v-if="room.status !== 'available'"
                                                    class="rounded-md p-1 text-xs"
        >
                                                    <span class="block text-muted-foreground">
                                                        {{ room.status.replace(/_/g, ' ') }}
                                                    </span>
                                                </div>
                                                <div
                                                    v-else
                                                    class="cursor-pointer rounded-md p-1 text-xs transition-colors"
        >
                                                    <span class="block font-medium truncate">
                                                        {{ '--' }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>