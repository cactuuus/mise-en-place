<template>
    <div class="max-w-4xl mx-auto space-y-4">
        <!-- Profile Information Display -->
        <Panel id="user-settings-panel">
            <template #header>
                <div class="panel-header">
                    <i class="pi pi-user"></i>
                    Profile Information
                </div>
            </template>

            <div class="space-y-4">
                <!-- Avatar Section -->
                <Fieldset legend="Profile Picture">
                    <SmartImage
                        :alt="authStore.user?.name"
                        :src="authStore.user?.avatar_urls?.large || '/images/avatar-placeholder.svg'"
                        image-class="w-[90px] rounded-full object-cover aspect-square"
                    />
                    <div class="flex flex-col gap-2">
                        <Button
                            icon="pi pi-pencil"
                            label="Edit"
                            outlined
                            severity="secondary"
                            size="small"
                            @click="showAvatarModal = true"
                        />
                        <Button
                            v-if="authStore.user?.avatar_urls?.small"
                            icon="pi pi-trash"
                            label="Delete"
                            severity="danger"
                            size="small"
                            @click="showDeleteAvatarModal = true"
                        />
                    </div>
                </Fieldset>

                <!-- Name Section -->
                <Fieldset legend="Name">
                    <p>{{ authStore.user?.name }}</p>
                    <Button
                        icon="pi pi-pencil"
                        label="Edit"
                        outlined
                        severity="secondary"
                        size="small"
                        @click="showNameModal = true"
                    />
                </Fieldset>

                <!-- Email Section -->
                <Fieldset legend="Email">
                    <p>{{ authStore.user?.email }}</p>
                    <Button
                        disabled
                        icon="pi pi-lock"
                        label="Edit"
                        outlined
                        severity="secondary"
                        size="small"
                    />
                </Fieldset>

                <!-- Password Section -->
                <Fieldset legend="Password">
                    <p>••••••••••••</p>
                    <Button
                        icon="pi pi-pencil"
                        label="Edit"
                        outlined
                        severity="secondary"
                        size="small"
                        @click="showPasswordModal = true"
                    />
                </Fieldset>
            </div>
        </Panel>

        <!-- Danger Zone -->
        <Panel id="danger-zone-panel" collapsed toggleable>
            <template #header>
                <div class="panel-header text-red-500">
                    <i class="pi pi-exclamation-triangle "></i>
                    <div class="flex items-center gap-2">
                        Danger Zone
                    </div>
                </div>
            </template>

            <Fieldset legend="Delete Account">
                <p class="text-sm font-semibold">
                    Once you delete your account, all of your data will be permanently removed.
                </p>
                <Button
                    icon="pi pi-trash"
                    label="Delete Account"
                    severity="danger"
                    @click="showDeleteAccountModal = true"
                />
            </Fieldset>
        </Panel>
    </div>

    <!-- Modals -->
    <EditNameModal
        v-model:visible="showNameModal"
        :initial-name="authStore.user?.name"
    />

    <EditPasswordModal
        v-model:visible="showPasswordModal"
    />

    <EditAvatarModal
        v-model:visible="showAvatarModal"
        :current-avatar-url="authStore.user?.avatar_urls?.large"
    />

    <DeleteAvatarModal
        v-model:visible="showDeleteAvatarModal"
    />

    <DeleteAccountModal
        v-model:visible="showDeleteAccountModal"
    />
</template>

<script lang="ts" setup>
import {ref} from 'vue'
import {useAuthStore} from '@/stores/auth'
import Panel from 'primevue/panel'
import Button from 'primevue/button'
import Fieldset from 'primevue/fieldset'
import EditNameModal from '@/components/EditNameModal.vue'
import EditPasswordModal from '@/components/EditPasswordModal.vue'
import EditAvatarModal from '@/components/EditAvatarModal.vue'
import DeleteAvatarModal from "@/components/DeleteAvatarModal.vue";
import DeleteAccountModal from "@/components/DeleteAccountModal.vue";
import SmartImage from "@/components/SmartImage.vue";

// Stores and composables
const authStore = useAuthStore()

// Modal visibility
const showNameModal = ref(false)
const showPasswordModal = ref(false)
const showAvatarModal = ref(false)
const showDeleteAvatarModal = ref(false)
const showDeleteAccountModal = ref(false)

</script>
