<template>
    <Popover ref="actionMenuRef">
        <Button
            icon="pi pi-pencil"
            label="Edit"
            severity="warn"
            text
            @click="editActiveRecipe"
        />
        <Button
            icon="pi pi-trash"
            label="Delete"
            severity="danger"
            text
            @click="deleteActiveRecipe"
        />
    </Popover>

    <!-- Delete Confirmation -->
    <ConfirmDialog/>
</template>

<script lang="ts" setup>

import Popover from "primevue/popover";
import Button from "primevue/button";
import {ref} from "vue";
import {deleteRecipe} from "@/services/recipeService.ts";
import {RecipePreview} from "@/types/recipe.ts";
import {useRouter} from "vue-router";
import ConfirmDialog from "primevue/confirmdialog";
import {useConfirm} from "primevue/useconfirm";

const router = useRouter()
const confirm = useConfirm()
const actionMenuRef = ref()
const activeRecipe = ref<RecipePreview | null>(null)


const toggle = (event: Event, recipe: RecipePreview) => {
    activeRecipe.value = recipe
    actionMenuRef.value?.toggle(event)
}

const editActiveRecipe = () => {
    if (!activeRecipe.value) {
        console.warn('No active recipe selected')
        return
    }
    router.push(`/cookbook/${activeRecipe.value.id}/edit`)
}

const deleteActiveRecipe = () => {
    const recipeToDelete = activeRecipe.value;

    if (!recipeToDelete) {
        console.warn('No active recipe selected');
        return;
    }

    confirm.require({
        message: 'Are you sure you want to delete this recipe?',
        header: 'Delete Confirmation',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        rejectLabel: 'Cancel',
        acceptLabel: 'Delete',
        acceptIcon: 'pi pi-trash',
        accept: async () => {
            const success = await deleteRecipe(recipeToDelete.id)
            if (success) await router.push('/cookbook')
        }
    })
}

defineExpose({toggle})
</script>
