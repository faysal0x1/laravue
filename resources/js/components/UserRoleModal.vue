<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import MultiSelect from '@/components/ui/multi-select/MultiSelect.vue';
import { role as usersRole } from '@/routes/users';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    availableRoles: {
        type: Array,
        default: () => [],
    },
});

const isOpen = defineModel('isOpen');

const form = useForm({
    roles: [],
});

watch(isOpen, (open) => {
    if (open && props.user) {
        form.roles = [...(props.user.roles ?? [])];
    }
});

const roleOptions = (roles) =>
    roles.map((r) => ({
        value: r.name,
        label: r.name,
        description: `${r.permissions_count} permission${r.permissions_count !== 1 ? 's' : ''}`,
    }));

const submit = () => {
    form.put(usersRole.update(props.user.id).url, {
        onSuccess: () => {
            isOpen.value = false;
        },
    });
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Assign Role</DialogTitle>
                <DialogDescription v-if="user">
                    Select roles for <span class="font-medium text-foreground">{{ user.name }}</span>
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">
                    Roles
                </label>
                <MultiSelect
                    v-model="form.roles"
                    :options="roleOptions(availableRoles)"
                    option-value="value"
                    option-label="label"
                    option-description="description"
                    placeholder="Select roles…"
                    search-placeholder="Search roles…"
                />
                <p v-if="availableRoles.length === 0" class="text-xs text-muted-foreground">
                    No roles available. Create roles first.
                </p>
            </div>

            <div class="flex items-center justify-between border-t pt-4">
                <span
                    v-if="form.roles.length > 0"
                    class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary"
                >
                    {{ form.roles.length }} selected
                </span>
                <span v-else class="text-xs text-muted-foreground">No roles selected</span>

                <div class="flex gap-2">
                    <Button variant="outline" :disabled="form.processing" @click="isOpen = false">
                        Cancel
                    </Button>
                    <Button :disabled="form.processing" @click="submit">
                        {{ form.processing ? 'Saving…' : 'Save Roles' }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
