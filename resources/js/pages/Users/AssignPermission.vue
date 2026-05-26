<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as usersIndex, permissions as usersPermissions } from '@/routes/users';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    permissionModules: {
        type: Array,
        default: () => [],
    },
    userPermissions: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    permissions: [...props.userPermissions],
});

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

const allPermissionNames = computed(() =>
    modules.value.flatMap((m) => m.permissions.map((p) => p.name)),
);

const isAllSelected = computed(
    () =>
        allPermissionNames.value.length > 0 &&
        allPermissionNames.value.every((name) => form.permissions.includes(name)),
);

const isGlobalIndeterminate = computed(
    () => form.permissions.length > 0 && !isAllSelected.value,
);

const toggleAllPermissions = () => {
    if (isAllSelected.value) {
        form.permissions = [];
    } else {
        form.permissions = [...allPermissionNames.value];
    }
};

const isModuleIndeterminate = (module) => {
    const { selected, total } = moduleCounts.value[module.key] ?? { selected: 0, total: 0 };
    return selected > 0 && selected < total;
};

const clearAllPermissions = () => {
    form.permissions = [];
};

const submit = () => {
    form.put(usersPermissions.update(props.user.id).url);
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'User & Roles', href: usersIndex() },
            { title: 'Users', href: usersIndex() },
            { title: 'Assign Permission', href: usersIndex() },
        ],
    }),
});
</script>

<template>
    <Head :title="`Assign Permission — ${user.name}`" />

    <div class="mx-auto w-full max-w-5xl p-4">
        <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
            <!-- Header -->
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold">Direct Permissions</h1>
                    <p class="text-xs text-muted-foreground">
                        Assigning to <span class="font-medium text-foreground">{{ user.name }}</span>
                        &mdash; {{ form.permissions.length }} permission{{ form.permissions.length !== 1 ? 's' : '' }} selected
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium transition-colors hover:bg-muted/50"
                        :class="isAllSelected ? 'text-primary' : 'text-muted-foreground hover:text-foreground'"
                        @click="toggleAllPermissions"
                    >
                        <span
                            class="relative flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors"
                            :class="isAllSelected
                                ? 'border-primary bg-primary'
                                : isGlobalIndeterminate
                                    ? 'border-primary bg-primary'
                                    : 'border-muted-foreground/50'"
                        >
                            <svg v-if="isAllSelected" class="h-2.5 w-2.5 text-primary-foreground" fill="none" viewBox="0 0 12 12">
                                <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span v-else-if="isGlobalIndeterminate" class="h-0.5 w-2 rounded-full bg-primary-foreground" />
                        </span>
                        Select all
                    </button>
                    <button
                        v-if="form.permissions.length > 0"
                        type="button"
                        class="text-xs text-destructive hover:underline"
                        @click="clearAllPermissions"
                    >
                        Clear all
                    </button>
                </div>
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
                                class="flex items-center gap-1.5 text-xs font-medium transition-colors"
                                :class="isModuleAllSelectedFor(module) ? 'text-primary' : 'text-muted-foreground hover:text-foreground'"
                                @click.stop="toggleModulePermissionsFor(module)"
                            >
                                <span
                                    class="flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded border transition-colors"
                                    :class="isModuleAllSelectedFor(module)
                                        ? 'border-primary bg-primary'
                                        : isModuleIndeterminate(module)
                                            ? 'border-primary bg-primary'
                                            : 'border-muted-foreground/40'"
                                >
                                    <svg v-if="isModuleAllSelectedFor(module)" class="h-2 w-2 text-primary-foreground" fill="none" viewBox="0 0 12 12">
                                        <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span v-else-if="isModuleIndeterminate(module)" class="h-0.5 w-1.5 rounded-full bg-primary-foreground" />
                                </span>
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

            <!-- Footer Actions -->
            <div class="mt-5 flex justify-end gap-2">
                <Button as-child variant="outline">
                    <Link :href="usersIndex().url">Cancel</Link>
                </Button>
                <Button :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Permissions' }}
                </Button>
            </div>
        </div>
    </div>
</template>
