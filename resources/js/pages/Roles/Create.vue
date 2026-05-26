<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GlobalFormFields from '@/components/forms/GlobalFormFields.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { create as rolesCreate, index as rolesIndex, store as rolesStore } from '@/routes/roles';
import { index as usersIndex } from '@/routes/users';

const props = defineProps({
    permissionModules: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    status: 'active',
    permissions: [],
});

const fields = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    {
        name: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        options: [
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' },
        ],
    },
];

const modules = computed(() => props.permissionModules ?? []);

const expandedModules = ref(props.permissionModules.map((m) => m.key));

const isExpanded = (moduleKey) => expandedModules.value.includes(moduleKey);

const toggleExpanded = (moduleKey) => {
    if (isExpanded(moduleKey)) {
        expandedModules.value = expandedModules.value.filter((k) => k !== moduleKey);
    } else {
        expandedModules.value = [...expandedModules.value, moduleKey];
    }
};

const isPermissionChecked = (permissionName) => form.permissions.includes(permissionName);

const togglePermission = (permissionName) => {
    if (isPermissionChecked(permissionName)) {
        form.permissions = form.permissions.filter((v) => v !== permissionName);
    } else {
        form.permissions = [...form.permissions, permissionName];
    }
};

const moduleCounts = computed(() => {
    const counts = {};

    modules.value.forEach((module) => {
        const names = module.permissions.map((p) => p.name);

        counts[module.key] = {
            selected: form.permissions.filter((name) => names.includes(name)).length,
            total: names.length,
        };
    });

    return counts;
});

const isModuleAllSelectedFor = (module) => {
    if (module.permissions.length === 0) {
        return false;
    }

    return module.permissions.every((p) => form.permissions.includes(p.name));
};

const toggleModulePermissionsFor = (module) => {
    const modulePermNames = module.permissions.map((p) => p.name);

    if (isModuleAllSelectedFor(module)) {
        form.permissions = form.permissions.filter((name) => !modulePermNames.includes(name));
    } else {
        const existing = form.permissions.filter((name) => !modulePermNames.includes(name));
        form.permissions = [...existing, ...modulePermNames];
    }
};

const clearAllPermissions = () => {
    form.permissions = [];
};

const submit = () => {
    form.post(rolesStore().url);
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'User & Roles', href: usersIndex() },
            { title: 'Roles', href: rolesIndex() },
            { title: 'Create', href: rolesCreate() },
        ],
    }),
});
</script>

<template>
    <Head title="Create Role" />

    <div class="mx-auto w-full max-w-5xl p-4">
        <div class="grid gap-4 lg:grid-cols-[1.1fr,2fr]">
            <!-- Form Card -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <h1 class="mb-5 text-lg font-semibold">Create Role</h1>

                <form class="space-y-4" @submit.prevent="submit">
                    <GlobalFormFields :form="form" :fields="fields" />

                    <div class="flex justify-end gap-2 pt-2">
                        <Button as-child variant="outline">
                            <Link :href="rolesIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating…' : 'Create Role' }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Permissions Card -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">Permissions</h2>
                        <p class="text-xs text-muted-foreground">
                            {{ form.permissions.length }} permission{{ form.permissions.length !== 1 ? 's' : '' }} selected
                        </p>
                    </div>
                    <button
                        v-if="form.permissions.length > 0"
                        type="button"
                        class="text-xs text-destructive hover:underline"
                        @click="clearAllPermissions"
                    >
                        Clear all
                    </button>
                </div>

                <!-- Accordion Modules -->
                <div class="space-y-2">
                    <div
                        v-for="module in modules"
                        :key="module.key"
                        class="overflow-hidden rounded-lg border border-border/60"
                    >
                        <!-- Module Header Row -->
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-4 py-3 text-left transition-colors hover:bg-muted/30"
                            :class="isExpanded(module.key) ? 'bg-muted/20' : ''"
                            @click="toggleExpanded(module.key)"
                        >
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm font-semibold">{{ module.label }}</span>
                                <span
                                    class="rounded-full px-1.5 py-0.5 text-xs font-semibold tabular-nums"
                                    :class="moduleCounts[module.key]?.selected > 0
                                        ? 'bg-primary/15 text-primary'
                                        : 'bg-muted text-muted-foreground'"
                                >
                                    {{ moduleCounts[module.key]?.selected ?? 0 }}/{{ moduleCounts[module.key]?.total ?? 0 }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    v-if="isExpanded(module.key) && module.permissions.length > 0"
                                    class="text-xs font-medium text-muted-foreground hover:text-foreground"
                                    @click.stop="toggleModulePermissionsFor(module)"
                                >
                                    {{ isModuleAllSelectedFor(module) ? 'Deselect all' : 'Select all' }}
                                </span>
                                <svg
                                    class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200"
                                    :class="isExpanded(module.key) ? 'rotate-180' : ''"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Permission Cards (expanded) -->
                        <div
                            v-if="isExpanded(module.key)"
                            class="grid gap-2 border-t border-border/60 bg-muted/5 p-3 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <button
                                v-for="permission in module.permissions"
                                :key="permission.name"
                                type="button"
                                class="flex items-center gap-3 rounded-lg border px-3 py-2.5 text-left text-sm transition-all"
                                :class="isPermissionChecked(permission.name)
                                    ? 'border-primary/40 bg-primary/10 text-primary shadow-sm'
                                    : 'border-border/60 bg-card hover:border-primary/20 hover:bg-muted/40'"
                                @click="togglePermission(permission.name)"
                            >
                                <span
                                    class="flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors"
                                    :class="isPermissionChecked(permission.name)
                                        ? 'border-primary bg-primary'
                                        : 'border-muted-foreground/40'"
                                >
                                    <svg v-if="isPermissionChecked(permission.name)" class="h-2.5 w-2.5 text-primary-foreground" fill="none" viewBox="0 0 12 12">
                                        <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span class="font-medium" :class="isPermissionChecked(permission.name) ? '' : 'text-foreground/70'">
                                    {{ permission.label }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <p v-if="modules.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                        No permissions available.
                    </p>
                </div>

                <InputError class="mt-2" :message="form.errors.permissions" />
            </div>
        </div>
    </div>
</template>
