<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GeneralSettingController from '@/actions/App/Http/Controllers/Settings/GeneralSettingController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit as editGeneralSettings } from '@/routes/general-settings';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'General settings',
                href: editGeneralSettings(),
            },
        ],
    },
});

const form = useForm({
    application_name: props.settings.application_name ?? '',
    phone: props.settings.phone ?? '',
    email: props.settings.email ?? '',
    address: props.settings.address ?? '',
    parcel_tracking_prefix: props.settings.parcel_tracking_prefix ?? '',
    invoice_prefix: props.settings.invoice_prefix ?? '',
    copyright: props.settings.copyright ?? '',
    primary_color: props.settings.primary_color || '#7c3aed',
    text_color: props.settings.text_color || '#111827',
    logo: null,
    light_logo: null,
    favicon: null,
});

const logoPreview = ref(props.settings.logo_url ?? '');
const lightLogoPreview = ref(props.settings.light_logo_url ?? '');
const faviconPreview = ref(props.settings.favicon_url ?? '');

const revokePreview = (previewRef) => {
    if (previewRef.value && previewRef.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewRef.value);
    }
};

const updatePreview = (event, field, previewRef, fallback) => {
    const file = event.target.files?.[0] ?? null;

    form[field] = file;
    revokePreview(previewRef);

    if (file) {
        previewRef.value = URL.createObjectURL(file);

        return;
    }

    previewRef.value = fallback ?? '';
};

const onLogoChange = (event) => updatePreview(event, 'logo', logoPreview, props.settings.logo_url);
const onLightLogoChange = (event) => updatePreview(event, 'light_logo', lightLogoPreview, props.settings.light_logo_url);
const onFaviconChange = (event) => updatePreview(event, 'favicon', faviconPreview, props.settings.favicon_url);

const submit = () => {
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(GeneralSettingController.update().url, {
            preserveScroll: true,
            forceFormData: true,
        });
};
</script>

<template>

    <Head title="General settings" />

    <h1 class="sr-only">General settings</h1>

    <!-- Page card -->
    <div
        class="w-8xl overflow-hidden rounded-8xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-slate-950">


        <!-- Card header -->
        <div
            class="border-b border-sidebar-border/60 bg-linear-to-r from-slate-50 to-white px-6 py-5 dark:border-sidebar-border dark:from-slate-900/60 dark:to-slate-950">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-slate-900 dark:text-slate-50">General
                        Settings</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Update your application identity, contact
                        details, and branding assets.</p>
                </div>
            </div>
        </div>

        <!-- Card body -->
        <form @submit.prevent="submit">
            <div
                class="grid divide-y divide-sidebar-border/60 lg:grid-cols-2 lg:divide-x lg:divide-y-0 dark:divide-sidebar-border">

                <!-- ── Left column ── -->
                <div class="space-y-5 p-6">

                    <!-- Section: Identity -->
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        Identity</p>

                    <div class="grid gap-2">
                        <Label for="application_name">Application Name</Label>
                        <Input id="application_name" v-model="form.application_name" type="text"
                            autocomplete="organization" placeholder="Enter Application Name" />
                        <InputError :message="form.errors.application_name" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input id="phone" v-model="form.phone" type="text" autocomplete="tel"
                                placeholder="Enter Phone" />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" autocomplete="email"
                                placeholder="Enter Email" />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="address">Address</Label>
                        <textarea id="address" v-model="form.address" rows="3"
                            class="w-full resize-none rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm text-on-surface outline-none transition focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/20 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Enter Address" />
                        <InputError :message="form.errors.address" />
                    </div>

                    <!-- Section: Prefixes -->
                    <p class="pt-1 text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        Prefixes</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="parcel_tracking_prefix">Parcel Tracking</Label>
                            <Input id="parcel_tracking_prefix" v-model="form.parcel_tracking_prefix" type="text"
                                placeholder="Enter Parcel Tracking Prefix" />
                            <InputError :message="form.errors.parcel_tracking_prefix" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="invoice_prefix">Invoice</Label>
                            <Input id="invoice_prefix" v-model="form.invoice_prefix" type="text" placeholder="Enter Invoice Prefix" />
                            <InputError :message="form.errors.invoice_prefix" />
                        </div>
                    </div>

                    <!-- Section: Appearance -->
                    <p class="pt-1 text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        Appearance</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="primary_color">Primary Color</Label>
                            <div
                                class="flex h-10 overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 dark:bg-slate-900">
                                <input id="primary_color" v-model="form.primary_color" type="color"
                                    class="h-full w-12 shrink-0 cursor-pointer border-0 bg-transparent p-1" />
                                <span class="self-center border-l border-outline-variant" />
                                <input v-model="form.primary_color" type="text" placeholder="#7c3aed"
                                    class="min-w-0 flex-1 bg-transparent px-3 text-sm text-on-surface outline-none dark:text-slate-100" />
                            </div>
                            <InputError :message="form.errors.primary_color" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="text_color">Text Color</Label>
                            <div
                                class="flex h-10 overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 dark:bg-slate-900">
                                <input id="text_color" v-model="form.text_color" type="color"
                                    class="h-full w-12 shrink-0 cursor-pointer border-0 bg-transparent p-1" />
                                <span class="self-center border-l border-outline-variant" />
                                <input v-model="form.text_color" type="text" placeholder="#111827"
                                    class="min-w-0 flex-1 bg-transparent px-3 text-sm text-on-surface outline-none dark:text-slate-100" />
                            </div>
                            <InputError :message="form.errors.text_color" />
                        </div>
                    </div>
                </div>

                <!-- ── Right column ── -->
                <div class="space-y-5 p-6">

                    <!-- Section: Legal -->
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Legal
                    </p>

                    <div class="grid gap-2">
                        <Label for="copyright">Copyright</Label>
                        <textarea id="copyright" v-model="form.copyright" rows="2"
                            class="w-full resize-none rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm text-on-surface outline-none transition focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/20 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Copyright © All rights reserved." />
                        <InputError :message="form.errors.copyright" />
                    </div>

                    <!-- Section: Brand Assets -->
                    <p class="pt-1 text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        Brand Assets</p>

                    <!-- Logo -->
                    <div class="grid gap-2">
                        <Label for="logo">Logo <span class="text-xs font-normal text-slate-400">(PNG, SVG, max 4
                                MB)</span></Label>
                        <label for="logo"
                            class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-outline-variant bg-slate-50/50 px-4 py-4 text-center transition hover:border-primary hover:bg-primary/5 dark:bg-slate-900/40 dark:hover:bg-primary/10">
                            <template v-if="logoPreview">
                                <img :src="logoPreview" alt="Logo preview" class="max-h-14 w-auto object-contain" />
                                <span class="text-xs text-slate-400 group-hover:text-primary">Click to replace</span>
                            </template>
                            <template v-else>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-8 w-8 text-slate-300 group-hover:text-primary" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <span class="text-xs text-slate-400 group-hover:text-primary">Click to upload
                                    logo</span>
                            </template>
                            <input id="logo" type="file" accept=".png,.jpg,.jpeg,.webp,.svg,.ico,image/*"
                                class="sr-only" @change="onLogoChange" />
                        </label>
                        <InputError :message="form.errors.logo" />
                    </div>

                    <!-- Light Logo -->
                    <div class="grid gap-2">
                        <Label for="light_logo">Light Logo <span class="text-xs font-normal text-slate-400">(for dark
                                backgrounds)</span></Label>
                        <label for="light_logo"
                            class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-outline-variant px-4 py-4 text-center transition hover:border-primary dark:hover:bg-primary/10"
                            :class="lightLogoPreview ? 'bg-slate-800' : 'bg-slate-50/50 dark:bg-slate-900/40'">
                            <template v-if="lightLogoPreview">
                                <img :src="lightLogoPreview" alt="Light logo preview"
                                    class="max-h-14 w-auto object-contain" />
                                <span class="text-xs text-slate-400 group-hover:text-primary">Click to replace</span>
                            </template>
                            <template v-else>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-8 w-8 text-slate-300 group-hover:text-primary" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <span class="text-xs text-slate-400 group-hover:text-primary">Click to upload light
                                    logo</span>
                            </template>
                            <input id="light_logo" type="file" accept=".png,.jpg,.jpeg,.webp,.svg,.ico,image/*"
                                class="sr-only" @change="onLightLogoChange" />
                        </label>
                        <InputError :message="form.errors.light_logo" />
                    </div>

                    <!-- Favicon -->
                    <div class="grid gap-2">
                        <Label for="favicon">Favicon <span class="text-xs font-normal text-slate-400">(32×32
                                recommended, max 1
                                MB)</span></Label>
                        <div class="flex items-center gap-3">
                            <label for="favicon"
                                class="group flex flex-1 cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-outline-variant bg-slate-50/50 px-4 py-3 transition hover:border-primary hover:bg-primary/5 dark:bg-slate-900/40 dark:hover:bg-primary/10">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 shrink-0 text-slate-300 group-hover:text-primary" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <span class="text-xs text-slate-400 group-hover:text-primary">
                                    {{ faviconPreview ? 'Click to replace favicon' : 'Click to upload favicon' }}
                                </span>
                                <input id="favicon" type="file" accept=".png,.jpg,.jpeg,.webp,.svg,.ico,image/*"
                                    class="sr-only" @change="onFaviconChange" />
                            </label>
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-outline-variant bg-white dark:bg-slate-800">
                                <img v-if="faviconPreview" :src="faviconPreview" alt="Favicon preview"
                                    class="h-10 w-10 object-contain" />
                                <svg v-else xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-slate-200 dark:text-slate-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                        </div>
                        <InputError :message="form.errors.favicon" />
                    </div>
                </div>
            </div>

            <!-- Card footer / actions -->
            <div
                class="flex items-center justify-end border-t border-sidebar-border/60 bg-slate-50/80 px-6 py-4 dark:border-sidebar-border dark:bg-slate-900/40">
                <Button type="submit" :disabled="form.processing" class="min-w-32">
                    <svg v-if="form.processing" xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 animate-spin"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>
