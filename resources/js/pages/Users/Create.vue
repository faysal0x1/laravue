<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GlobalFormFields from '@/components/forms/GlobalFormFields.vue';
import { dashboard } from '@/routes';
import { create as usersCreate, index as usersIndex, store as usersStore } from '@/routes/users';
import { User, Mail, Lock, KeyRound } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const fields = [
    { name: 'name', label: 'NAME', type: 'text', autocomplete: 'name', required: true, icon: User },
    { name: 'email', label: 'EMAIL ADDRESS', type: 'email', autocomplete: 'email', required: true, icon: Mail },
    { name: 'password', label: 'PASSWORD', type: 'password', autocomplete: 'new-password', required: true, icon: Lock },
    {
        name: 'password_confirmation',
        label: 'CONFIRM PASSWORD',
        type: 'password',
        autocomplete: 'new-password',
        required: true,
        icon: KeyRound,
    },
];

const submit = () => {
    form.post(usersStore().url);
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'User & Roles', href: usersIndex() },
            { title: 'Users', href: usersIndex() },
            { title: 'Create', href: usersCreate() },
        ],
    }),
});
</script>

<template>
    <Head title="Create User" />

    <div class="mx-auto w-full max-w-[560px] pt-8 pb-16 px-4 md:px-0 relative overflow-hidden">
        <!-- Decorative background elements -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary/10 blur-[128px] rounded-full -z-10 pointer-events-none"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-secondary/10 blur-[128px] rounded-full -z-10 pointer-events-none"></div>
        
        <div class="w-full bg-surface-container border border-outline-variant rounded-xl overflow-hidden shadow-2xl z-10 relative">
            <!-- Form Header -->
            <div class="p-8 pb-0">
                <h1 class="font-headline-lg text-3xl font-semibold text-on-surface mb-2">Create User</h1>
                <p class="font-body-sm text-sm text-on-surface-variant">
                    Configure a new user account and set initial access permissions.
                </p>
            </div>

            <form class="p-8 space-y-6" @submit.prevent="submit">
                <GlobalFormFields :form="form" :fields="fields" gapClass="gap-6" />

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-outline-variant mt-8">
                    <Link
                        :href="usersIndex().url"
                        class="px-6 py-2.5 font-medium text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/30 rounded-lg transition-all active:scale-95"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-2.5 bg-primary-container text-on-primary-container font-bold rounded-lg shadow-lg shadow-primary-container/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50"
                    >
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

