<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as usersIndex, role as usersRole } from '@/routes/users';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    availableRoles: {
        type: Array,
        default: () => [],
    },
    userRoles: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    roles: [...props.userRoles],
});

const isRoleChecked = (roleName) => form.roles.includes(roleName);

const toggleRole = (roleName) => {
    if (isRoleChecked(roleName)) {
        form.roles = form.roles.filter((r) => r !== roleName);
    } else {
        form.roles = [...form.roles, roleName];
    }
};

const submit = () => {
    form.put(usersRole.update(props.user.id).url);
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'User & Roles', href: usersIndex() },
            { title: 'Users', href: usersIndex() },
            { title: 'Assign Role', href: usersIndex() },
        ],
    }),
});
</script>

<template>
    <Head :title="`Assign Role — ${user.name}`" />

    <div class="mx-auto w-full max-w-3xl p-4">
        <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
            <!-- Header -->
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Assign Role</h1>
                    <p class="text-sm text-muted-foreground">{{ user.name }}</p>
                </div>
                <span
                    v-if="form.roles.length > 0"
                    class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary"
                >
                    {{ form.roles.length }} selected
                </span>
            </div>

            <!-- Role Cards -->
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <button
                    v-for="role in availableRoles"
                    :key="role.id"
                    type="button"
                    class="flex flex-col gap-2 rounded-xl border p-4 text-left transition-all"
                    :class="isRoleChecked(role.name)
                        ? 'border-primary/40 bg-primary/10 shadow-sm'
                        : 'border-border/60 bg-background hover:border-primary/20 hover:bg-muted/40'"
                    @click="toggleRole(role.name)"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span
                            class="font-semibold text-sm leading-tight"
                            :class="isRoleChecked(role.name) ? 'text-primary' : 'text-foreground'"
                        >
                            {{ role.name }}
                        </span>
                        <span
                            class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors"
                            :class="isRoleChecked(role.name)
                                ? 'border-primary bg-primary'
                                : 'border-muted-foreground/40'"
                        >
                            <svg v-if="isRoleChecked(role.name)" class="h-2.5 w-2.5 text-primary-foreground" fill="none" viewBox="0 0 12 12">
                                <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                    <span
                        class="text-xs"
                        :class="isRoleChecked(role.name) ? 'text-primary/70' : 'text-muted-foreground'"
                    >
                        {{ role.permissions_count }} permission{{ role.permissions_count !== 1 ? 's' : '' }}
                    </span>
                </button>

                <p v-if="availableRoles.length === 0" class="col-span-full py-10 text-center text-sm text-muted-foreground">
                    No roles available. Create roles first.
                </p>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-2">
                <Button as-child variant="outline">
                    <Link :href="usersIndex().url">Cancel</Link>
                </Button>
                <Button :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Roles' }}
                </Button>
            </div>
        </div>
    </div>
</template>
