<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GlobalFormFields from '@/components/forms/GlobalFormFields.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as usersIndex, update as usersUpdate } from '@/routes/users';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
});

const fields = [
    { name: 'name', label: 'Name', type: 'text', autocomplete: 'name', required: true },
    { name: 'email', label: 'Email', type: 'email', autocomplete: 'email', required: true },
];

const submit = () => {
    form.put(usersUpdate(props.user.id).url);
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'User & Roles', href: usersIndex() },
            { title: 'Users', href: usersIndex() },
            { title: 'Edit', href: usersIndex() },
        ],
    }),
});
</script>

<template>
    <Head title="Edit User" />

    <div class="mx-auto w-full max-w-2xl p-4">
        <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
            <h1 class="mb-5 text-lg font-semibold">Edit User</h1>

            <form class="space-y-4" @submit.prevent="submit">
                <GlobalFormFields :form="form" :fields="fields" />

                <div class="flex justify-end gap-2 pt-2">
                    <Button as-child variant="outline">
                        <Link :href="usersIndex().url">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
