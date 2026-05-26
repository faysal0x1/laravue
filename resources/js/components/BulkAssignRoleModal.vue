<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import MultiSelect from '@/components/ui/multi-select/MultiSelect.vue';

const props = defineProps({
    selectedUsers: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    availableRoles: {
        type: Array,
        default: () => [],
    },
});

const isOpen = defineModel('isOpen');
const selectedUserIds = ref([]);

const form = useForm({
    user_ids: [],
    roles: [],
});

watch(isOpen, (open) => {
    if (open) {
        selectedUserIds.value = props.selectedUsers.map((u) => u.id);
        form.roles = [];
    }
});

const userOptions = computed(() =>
    props.users.map((u) => ({
        value: u.id,
        label: u.name,
        description: u.email,
    })),
);

const roleOptions = computed(() =>
    props.availableRoles.map((r) => ({
        value: r.name,
        label: r.name,
        description: `${r.permissions_count} permission${r.permissions_count !== 1 ? 's' : ''}`,
    })),
);

const submit = () => {
    form.user_ids = [...selectedUserIds.value];
    form.post('/users/bulk-assign-roles', {
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
                <DialogDescription>
                    Assign one or more roles to the selected users.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- User multi-select -->
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Users <span class="text-destructive">*</span>
                    </label>
                    <MultiSelect
                        v-model="selectedUserIds"
                        :options="userOptions"
                        option-value="value"
                        option-label="label"
                        option-description="description"
                        placeholder="Select users…"
                        search-placeholder="Search users…"
                    />
                </div>

                <!-- Role multi-select -->
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Roles <span class="text-destructive">*</span>
                    </label>
                    <MultiSelect
                        v-model="form.roles"
                        :options="roleOptions"
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
            </div>

            <div class="flex items-center justify-between border-t pt-4">
                <span v-if="form.roles.length > 0"
                    class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                    {{ form.roles.length }} role{{ form.roles.length !== 1 ? 's' : '' }} selected
                </span>
                <span v-else class="text-xs text-muted-foreground">No roles selected</span>

                <div class="flex gap-2">
                    <Button variant="outline" :disabled="form.processing" @click="isOpen = false">
                        Cancel
                    </Button>
                    <Button
                        :disabled="form.processing || form.roles.length === 0 || selectedUserIds.length === 0"
                        @click="submit"
                    >
                        {{ form.processing ? 'Assigning…' : `Assign to ${selectedUserIds.length} user${selectedUserIds.length !== 1 ? 's' : ''}` }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
