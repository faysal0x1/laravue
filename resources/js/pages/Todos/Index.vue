<script setup>
import { router, useForm } from '@inertiajs/vue3';
import DatePicker from 'primevue/datepicker';
import { computed, ref, watch } from 'vue';
import {
    destroy as todosDestroy,
    index as todosIndex,
    store as todosStore,
    toggle as todosToggle,
    update as todosUpdate,
} from '@/actions/App/Http/Controllers/TodoController';
import { dashboard } from '@/routes';
import { deleteItemWithConfirmation } from '@/utils/listingActions.js';

const props = defineProps({
    todos: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, active: 0, done: 0 }),
    },
});

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'To Do List', href: todosIndex() },
        ],
    }),
});

const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1);

const filter = ref(typeof props.filters?.filter === 'string' ? capitalize(props.filters.filter) : 'All');

const selected = ref(props.todos.data[0] ?? null);
const showModal = ref(false);
const editingId = ref(null);
const datePickerValue = ref(null);

const form = useForm({
    title: '',
    description: '',
    assignee: '',
    due_date: '',
});

const todos       = computed(() => props.todos.data ?? []);
const currentPage = computed(() => props.todos.current_page ?? 1);
const totalPages  = computed(() => props.todos.last_page ?? 1);

const doneCount      = computed(() => props.stats?.done ?? 0);
const activeCount    = computed(() => props.stats?.active ?? 0);
const totalCount     = computed(() => props.stats?.total ?? 0);
const progressPercent = computed(() =>
    totalCount.value ? Math.round((doneCount.value / totalCount.value) * 100) : 0,
);

watch(
    () => props.todos.data,
    (newTodos) => {
        if (selected.value) {
            const updated = newTodos.find((t) => t.id === selected.value.id);
            selected.value = updated ?? newTodos[0] ?? null;
        } else {
            selected.value = newTodos[0] ?? null;
        }
    },
);

watch(filter, (val) => {
    router.get(
        todosIndex().url,
        { ...props.filters, filter: val.toLowerCase(), page: 1 },
        { preserveScroll: true, preserveState: true, only: ['todos', 'filters', 'stats'], replace: true },
    );
});

watch(datePickerValue, (date) => {
    if (date instanceof Date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        form.due_date = `${y}-${m}-${d}`;
    } else {
        form.due_date = '';
    }
});

const goToPage = (page) => {
    router.get(
        todosIndex().url,
        { ...props.filters, filter: filter.value.toLowerCase(), page },
        { preserveScroll: true, preserveState: true, only: ['todos', 'filters', 'stats'], replace: false },
    );
};

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    datePickerValue.value = null;
    showModal.value = true;
};

const openEdit = (todo) => {
    editingId.value = todo.id;
    form.title = todo.title;
    form.description = todo.description;
    form.assignee = todo.assignee;
    form.due_date = todo.due_date;
    datePickerValue.value = todo.due_date ? new Date(todo.due_date) : null;
    form.clearErrors();
    showModal.value = true;
};

const submitForm = () => {
    if (editingId.value) {
        form.put(todosUpdate(editingId.value).url, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                editingId.value = null;
                form.reset();
            },
        });
    } else {
        form.post(todosStore().url, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const toggleTodo = (todo) => {
    router.put(todosToggle(todo.id).url, {}, { preserveScroll: true });
};

const removeTodo = async (todo) => {
    await deleteItemWithConfirmation({
        title: 'Delete task?',
        text: `This will permanently delete "${todo.title}".`,
        confirmButtonText: 'Delete',
        url: todosDestroy(todo.id).url,
    });
};
</script>

<template>
    <div class="space-y-6">

        <!-- ── Top header ── -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                    Task Board
                </h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    {{ doneCount }} of {{ totalCount }} tasks completed
                </p>
            </div>
            <button
                class="flex items-center gap-2 rounded-xl bg-linear-to-r from-violet-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-200 transition-all hover:shadow-purple-300 hover:brightness-110 active:scale-95 dark:shadow-purple-900/40"
                @click="openCreate"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Add Task
            </button>
        </div>

        <!-- ── Stat cards + progress ── -->
        <div class="grid grid-cols-4 gap-4">
            <!-- Total -->
            <div class="col-span-1 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700">
                    <svg class="h-5 w-5 text-slate-500 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke-linecap="round" stroke-linejoin="round" />
                        <rect height="4" rx="1" width="6" x="9" y="3" />
                        <path d="M9 12h6M9 16h4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-700 dark:text-slate-100">{{ totalCount }}</p>
                    <p class="text-xs font-medium text-slate-400">Total Tasks</p>
                </div>
            </div>

            <!-- Active -->
            <div class="col-span-1 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/40">
                    <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4l3 3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ activeCount }}</p>
                    <p class="text-xs font-medium text-slate-400">In Progress</p>
                </div>
            </div>

            <!-- Done -->
            <div class="col-span-1 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ doneCount }}</p>
                    <p class="text-xs font-medium text-slate-400">Completed</p>
                </div>
            </div>

            <!-- Progress card -->
            <div class="col-span-1 flex flex-col justify-center rounded-2xl bg-linear-to-br from-violet-600 to-purple-700 p-4 shadow-lg shadow-purple-200 dark:shadow-purple-900/40">
                <div class="mb-2 flex items-end justify-between">
                    <p class="text-xs font-semibold text-purple-200">Overall Progress</p>
                    <p class="text-2xl font-bold text-white">{{ progressPercent }}%</p>
                </div>
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                    <div
                        class="h-1.5 rounded-full bg-white transition-all duration-700"
                        :style="{ width: `${progressPercent}%` }"
                    />
                </div>
            </div>
        </div>

        <!-- ── Main layout ── -->
        <div class="flex items-start gap-5">

            <!-- ── Left: task list ── -->
            <div class="w-80 shrink-0 self-start overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">

                <!-- Filter tabs -->
                <div class="flex bg-slate-50 dark:bg-slate-900/50">
                    <button
                        v-for="f in ['All', 'Active', 'Done']"
                        :key="f"
                        class="flex flex-1 flex-col items-center py-3 text-xs font-semibold transition-all"
                        :class="
                            filter === f
                                ? 'bg-white text-violet-600 shadow-sm dark:bg-slate-800 dark:text-violet-400'
                                : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
                        "
                        @click="filter = f"
                    >
                        <span class="text-base font-bold" :class="filter === f ? 'text-violet-600 dark:text-violet-400' : 'text-slate-400'">
                            <template v-if="f === 'All'">{{ totalCount }}</template>
                            <template v-else-if="f === 'Active'">{{ activeCount }}</template>
                            <template v-else>{{ doneCount }}</template>
                        </span>
                        {{ f }}
                    </button>
                </div>

                <!-- Divider -->
                <div class="h-px bg-slate-100 dark:bg-slate-700" />

                <!-- List -->
                <div class="divide-y divide-slate-50 dark:divide-slate-700/60">
                    <div v-if="todos.length === 0" class="flex flex-col items-center py-12 text-slate-400">
                        <svg class="mb-3 h-10 w-10 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke-linecap="round" stroke-linejoin="round" />
                            <rect height="4" rx="1" width="6" x="9" y="3" />
                        </svg>
                        <p class="text-sm">No tasks found</p>
                    </div>

                    <div
                        v-for="todo in todos"
                        :key="todo.id"
                        class="group relative cursor-pointer px-4 py-3.5 transition-colors hover:bg-violet-50/60 dark:hover:bg-violet-900/10"
                        :class="selected?.id === todo.id ? 'bg-violet-50/80 dark:bg-violet-900/20' : ''"
                        @click="selected = todo"
                    >
                        <!-- Active indicator bar -->
                        <div
                            class="absolute inset-y-0 left-0 w-0.5 rounded-r transition-all"
                            :class="selected?.id === todo.id ? 'bg-violet-600' : 'bg-transparent group-hover:bg-violet-300'"
                        />

                        <div class="flex items-start gap-3 pl-1.5">
                            <!-- Check button -->
                            <button class="mt-0.5 shrink-0" @click.stop="toggleTodo(todo)">
                                <span
                                    v-if="todo.done"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500"
                                >
                                    <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span
                                    v-else
                                    class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-slate-200 transition-colors group-hover:border-violet-400 dark:border-slate-600"
                                />
                            </button>

                            <!-- Content -->
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-medium leading-snug"
                                    :class="todo.done ? 'text-slate-400 line-through dark:text-slate-500' : 'text-slate-700 dark:text-slate-200'"
                                >
                                    {{ todo.title }}
                                </p>
                                <div class="mt-1.5 flex items-center gap-2">
                                    <!-- Assignee avatar -->
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-violet-100 text-[9px] font-bold uppercase text-violet-700 dark:bg-violet-900/50 dark:text-violet-300">
                                        {{ todo.assignee.charAt(0) }}
                                    </span>
                                    <span class="truncate text-xs text-slate-400">{{ todo.due_date }}</span>
                                </div>
                            </div>

                            <!-- Hover actions -->
                            <div class="flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100">
                                <button
                                    class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-violet-100 hover:text-violet-600 dark:hover:bg-violet-900/40"
                                    @click.stop="openEdit(todo)"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.83a2 2 0 01-.897.522l-3.293.823.823-3.293a2 2 0 01.522-.897z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button
                                    class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-red-100 hover:text-red-500 dark:hover:bg-red-900/40"
                                    @click.stop="removeTodo(todo)"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="totalPages > 1"
                    class="flex items-center justify-between border-t border-slate-100 px-4 py-2.5 dark:border-slate-700"
                >
                    <span class="text-xs text-slate-400">{{ currentPage }} / {{ totalPages }}</span>
                    <div class="flex items-center gap-1">
                        <button
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-violet-50 hover:text-violet-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-violet-900/20"
                            :disabled="currentPage === 1"
                            @click="goToPage(currentPage - 1)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button
                            v-for="p in totalPages"
                            :key="p"
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold transition-colors"
                            :class="p === currentPage ? 'bg-violet-600 text-white shadow-sm' : 'text-slate-500 hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-900/20'"
                            @click="goToPage(p)"
                        >
                            {{ p }}
                        </button>
                        <button
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-violet-50 hover:text-violet-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-violet-900/20"
                            :disabled="currentPage === totalPages"
                            @click="goToPage(currentPage + 1)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Right: detail panel ── -->
            <div class="flex-1 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 dark:bg-slate-800 dark:ring-slate-700">

                <template v-if="selected">
                    <!-- Gradient header banner -->
                    <div
                        class="relative overflow-hidden px-7 py-6"
                        :class="selected.done
                            ? 'bg-linear-to-r from-emerald-500 to-teal-500'
                            : 'bg-linear-to-r from-violet-600 to-purple-600'"
                    >
                        <!-- Decorative circles -->
                        <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10" />
                        <div class="absolute -bottom-4 right-12 h-16 w-16 rounded-full bg-white/10" />

                        <div class="relative flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <!-- Big toggle -->
                                <button
                                    class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-white/60 transition-all hover:border-white hover:bg-white/20"
                                    @click="toggleTodo(selected)"
                                >
                                    <svg v-if="selected.done" class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <div>
                                    <h2
                                        class="text-lg font-bold leading-snug text-white"
                                        :class="{ 'line-through opacity-70': selected.done }"
                                    >
                                        {{ selected.title }}
                                    </h2>
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-0.5 text-xs font-semibold text-white backdrop-blur-sm">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="selected.done ? 'bg-emerald-200' : 'bg-yellow-300'" />
                                        {{ selected.done ? 'Completed' : 'In Progress' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-1">
                                <button
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-white backdrop-blur-sm transition-colors hover:bg-white/30"
                                    @click="openEdit(selected)"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.83a2 2 0 01-.897.522l-3.293.823.823-3.293a2 2 0 01.522-.897z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-white backdrop-blur-sm transition-colors hover:bg-red-400/60"
                                    @click="removeTodo(selected)"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Detail body -->
                    <div class="p-6">
                        <!-- Info grid -->
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Assignee -->
                            <div class="rounded-xl border border-slate-100 p-4 dark:border-slate-700">
                                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/40">
                                    <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </div>
                                <p class="text-xs font-medium text-slate-400">Assigned To</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ selected.assignee }}</p>
                            </div>

                            <!-- Due Date -->
                            <div class="rounded-xl border border-slate-100 p-4 dark:border-slate-700">
                                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect height="18" rx="2" width="18" x="3" y="4" />
                                        <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <p class="text-xs font-medium text-slate-400">Due Date</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ selected.due_date }}</p>
                            </div>

                            <!-- Created -->
                            <div class="rounded-xl border border-slate-100 p-4 dark:border-slate-700">
                                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40">
                                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 8v4l3 3" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <p class="text-xs font-medium text-slate-400">Created At</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ selected.created_at }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="selected.description" class="mt-4 rounded-xl border border-slate-100 p-4 dark:border-slate-700">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Description</p>
                            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ selected.description }}</p>
                        </div>

                        <!-- Toggle CTA -->
                        <div class="mt-5">
                            <button
                                class="flex w-full items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold text-white shadow-md transition-all active:scale-[.98]"
                                :class="selected.done
                                    ? 'bg-linear-to-r from-amber-500 to-orange-500 shadow-amber-200 hover:brightness-105 dark:shadow-amber-900/30'
                                    : 'bg-linear-to-r from-emerald-500 to-teal-500 shadow-emerald-200 hover:brightness-105 dark:shadow-emerald-900/30'"
                                @click="toggleTodo(selected)"
                            >
                                <svg v-if="!selected.done" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M4 4l16 16M4 20L20 4" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                {{ selected.done ? 'Mark as Active' : 'Mark as Done' }}
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Empty state -->
                <div v-else class="flex flex-col items-center justify-center py-20 text-slate-300 dark:text-slate-600">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-50 dark:bg-slate-700/50">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke-linecap="round" stroke-linejoin="round" />
                            <rect height="4" rx="1" width="6" x="9" y="3" />
                            <path d="M9 12h6M9 16h4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-400 dark:text-slate-500">Select a task to see details</p>
                    <p class="mt-1 text-xs text-slate-300 dark:text-slate-600">Click any task from the list</p>
                </div>
            </div>
        </div>

        <!-- ── Create / Edit Modal ── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showModal = false" />

                    <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
                        <!-- Modal header with gradient -->
                        <div
                            class="flex items-center justify-between px-7 py-5"
                            :class="editingId ? 'bg-linear-to-r from-violet-600 to-purple-600' : 'bg-linear-to-r from-violet-600 to-purple-700'"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20">
                                    <svg v-if="editingId" class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.83a2 2 0 01-.897.522l-3.293.823.823-3.293a2 2 0 01.522-.897z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <svg v-else class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h2 class="text-base font-bold text-white">
                                    {{ editingId ? 'Edit Task' : 'New Task' }}
                                </h2>
                            </div>
                            <button
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-white transition-colors hover:bg-white/30"
                                @click="showModal = false"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal body -->
                        <div class="grid grid-cols-2 gap-x-6 gap-y-5 px-7 py-6">
                            <!-- Title -->
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Title <span class="text-violet-500">*</span>
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Enter task title…"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 transition focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-200 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100 dark:focus:bg-slate-700"
                                    :class="{ 'border-red-400! focus:ring-red-200!': form.errors.title }"
                                />
                                <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                            </div>

                            <!-- Description spans 2 rows -->
                            <div class="row-span-3">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="7"
                                    placeholder="Add more details about this task…"
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 transition focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-200 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100 dark:focus:bg-slate-700"
                                />
                            </div>

                            <!-- Assign -->
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Assign To <span class="text-violet-500">*</span>
                                </label>
                                <select
                                    v-model="form.assignee"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 transition focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-200 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100"
                                    :class="{ 'border-red-400! focus:ring-red-200!': form.errors.assignee }"
                                >
                                    <option value="">— Select user —</option>
                                    <option v-for="user in users" :key="user" :value="user">{{ user }}</option>
                                </select>
                                <p v-if="form.errors.assignee" class="mt-1 text-xs text-red-500">{{ form.errors.assignee }}</p>
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Due Date <span class="text-violet-500">*</span>
                                </label>
                                <DatePicker
                                    v-model="datePickerValue"
                                    date-format="yy-mm-dd"
                                    placeholder="Pick a date"
                                    show-icon
                                    fluid
                                    :invalid="!!form.errors.due_date"
                                />
                                <p v-if="form.errors.due_date" class="mt-1 text-xs text-red-500">{{ form.errors.due_date }}</p>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-7 py-4 dark:border-slate-700">
                            <button
                                class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700"
                                @click="showModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                class="flex items-center gap-2 rounded-xl bg-linear-to-r from-violet-600 to-purple-600 px-6 py-2 text-sm font-semibold text-white shadow-md shadow-purple-200 transition-all hover:brightness-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-purple-900/30"
                                :disabled="form.processing"
                                @click="submitForm"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" />
                                </svg>
                                {{ form.processing ? 'Saving…' : editingId ? 'Update Task' : 'Create Task' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
