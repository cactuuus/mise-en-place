<template>
    <div class="space-y-4">
        <Message v-if="modelValue.length === 0" severity="secondary">
            No instructions present
        </Message>

        <div
            v-for="(instruction, index) in modelValue"
            v-else
            :key="`instruction-${index}`"
        >
            <div
                v-if="instruction.type === 'section'"
                class="border rounded secondary-border space-y-4 pb-4"
            >
                <!-- Section Header -->
                <div
                    class="flex items-start p-3 border-b secondary-border secondary-bg justify-between rounded-sm w-full">
                    <p class="truncate font-semibold">
                        [{{ instruction.position }}] # {{ instruction.name }}
                    </p>

                    <div class="flex gap-2">
                        <Button
                            class="!p-0"
                            icon="pi pi-pencil"
                            severity="warn"
                            size="small"
                            text
                            @click="openSectionModal('edit', index, instruction.name)"
                        />
                        <Button
                            class="!p-0"
                            icon="pi pi-trash"
                            severity="danger"
                            size="small"
                            text
                            @click="removeInstruction(index)"
                        />
                    </div>
                </div>

                <!-- Section Steps -->
                <div class="space-y-4 px-1">

                    <div
                        v-for="(step, stepIndex) in instruction.steps"
                        :key="`step-${stepIndex}`"
                        class="p-3 space-y-2 border secondary-border cursor-pointer hover:border-primary-700 dark:hover:border-primary-400 rounded transition-colors contrast-bg"
                        @click="openStepModal('edit', index, stepIndex, step.name, step.text)"
                    >
                        <div class="flex items-start gap-2 justify-between w-full">
                            <p class="truncate font-medium">
                                [{{ instruction.position }}.{{ step.position }}] {{ step.name }}
                            </p>
                            <Button
                                class="!p-0"
                                icon="pi pi-trash"
                                severity="danger"
                                size="small"
                                text
                                @click.stop="removeStepFromSection(index, stepIndex)"
                            />
                        </div>
                        <p class="text-sm secondary-text">{{ step.text || '[no description]' }}</p>
                    </div>
                </div>

                <div class="flex justify-end px-1">
                    <Button
                        icon="pi pi-plus"
                        label="Add Step to Section"
                        size="small"
                        text
                        @click="openStepModal('create', index)"
                    />
                </div>
            </div>

            <!-- Standalone Step -->
            <div
                v-else
                class="p-3 space-y-2 border secondary-border cursor-pointer hover:border-primary-700 dark:hover:border-primary-400 rounded transition-colors contrast-bg"
                @click="openStepModal('edit', index, undefined, instruction.name, instruction.text)"
            >
                <div class="flex items-start gap-2 justify-between w-full">
                    <p class="truncate font-medium">
                        [{{ instruction.position }}] {{ instruction.name }}
                    </p>
                    <Button
                        class="!p-0"
                        icon="pi pi-trash"
                        severity="danger"
                        size="small"
                        text
                        @click.stop="removeInstruction(index)"
                    />
                </div>
                <p class="text-sm secondary-text">{{ instruction.text || '[no description]' }}</p>
            </div>
        </div>

        <div class="flex items-stretch justify-end pt-2">
            <Button
                icon="pi pi-bookmark"
                label="Add Section"
                size="small"
                text
                @click="openSectionModal('create')"
            />
            <Divider class="!mx-1" layout="vertical"/>
            <Button
                icon="pi pi-plus"
                label="Add Global Step"
                size="small"
                text
                @click="openStepModal('create')"
            />
        </div>

        <Message v-if="shouldShowError" severity="error" size="small" variant="simple">
            {{ error }}
        </Message>
    </div>

    <!-- Modals -->
    <RecipeSectionModal
        v-model:visible="showSectionModal"
        :initial-name="editingSectionName"
        :mode="sectionModalMode"
        @submit="handleSectionSubmit"
    />

    <RecipeStepModal
        v-model:visible="showStepModal"
        :initial-name="editingStepName"
        :initial-text="editingStepText"
        :mode="stepModalMode"
        @submit="handleStepSubmit"
    />
</template>

<script lang="ts" setup>
import {computed, readonly, ref, watch} from 'vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Divider from 'primevue/divider'
import RecipeSectionModal from "@/components/RecipeSectionModal.vue"
import RecipeStepModal from "@/components/RecipeStepModal.vue"
import type {RecipeInstruction, RecipeSection, RecipeStep} from '@/types/recipe'

interface Props {
    modelValue: RecipeInstruction[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [value: RecipeInstruction[]]
}>()

// Modal state
const showSectionModal = ref(false)
const showStepModal = ref(false)
const sectionModalMode = ref<'create' | 'edit'>('create')
const stepModalMode = ref<'create' | 'edit'>('create')

// Modal data
const editingSectionName = ref<string>()
const editingStepName = ref<string>()
const editingStepText = ref<string>()
const editingInstructionIndex = ref<number>()
const editingStepIndex = ref<number>()

// Modal handlers
const openSectionModal = (mode: 'create' | 'edit', instructionIndex?: number, name?: string) => {
    sectionModalMode.value = mode
    editingInstructionIndex.value = instructionIndex
    editingSectionName.value = name
    showSectionModal.value = true
}

const openStepModal = (mode: 'create' | 'edit', instructionIndex?: number, stepIndex?: number, name?: string, text?: string) => {
    stepModalMode.value = mode
    editingInstructionIndex.value = instructionIndex
    editingStepIndex.value = stepIndex
    editingStepName.value = name
    editingStepText.value = text
    showStepModal.value = true
}

const handleSectionSubmit = (data: { name: string }) => {
    if (sectionModalMode.value === 'create') {
        const newSection: RecipeSection = {
            type: 'section',
            position: props.modelValue.length + 1,
            name: data.name,
            steps: []
        }
        const updated = [...props.modelValue, newSection]
        emit('update:modelValue', repositionInstructions(updated))
    } else if (sectionModalMode.value === 'edit' && editingInstructionIndex.value !== undefined) {
        const updated = [...props.modelValue]
        const section = updated[editingInstructionIndex.value] as RecipeSection
        updated[editingInstructionIndex.value] = {...section, name: data.name}
        emit('update:modelValue', updated)
    }
}

const handleStepSubmit = (data: { name?: string, text: string }) => {
    if (stepModalMode.value === 'create') {
        if (editingInstructionIndex.value !== undefined) {
            // Adding to section
            const updated = [...props.modelValue]
            const section = updated[editingInstructionIndex.value] as RecipeSection
            const newStep: RecipeStep = {
                type: 'step',
                position: section.steps.length + 1,
                text: data.text,
                name: data.name
            }
            section.steps.push(newStep)
            emit('update:modelValue', repositionInstructions(updated))
        } else {
            // Adding global step
            const newStep: RecipeStep = {
                type: 'step',
                position: props.modelValue.length + 1,
                text: data.text,
                name: data.name
            }
            const updated = [...props.modelValue, newStep]
            emit('update:modelValue', repositionInstructions(updated))
        }
    } else if (stepModalMode.value === 'edit' && editingInstructionIndex.value !== undefined) {
        const updated = [...props.modelValue]

        if (editingStepIndex.value !== undefined) {
            // Editing step in section
            const section = updated[editingInstructionIndex.value] as RecipeSection
            const step = section.steps[editingStepIndex.value]
            section.steps[editingStepIndex.value] = {...step, text: data.text, name: data.name}
        } else {
            // Editing global step
            const step = updated[editingInstructionIndex.value] as RecipeStep
            updated[editingInstructionIndex.value] = {...step, text: data.text, name: data.name}
        }

        emit('update:modelValue', updated)
    }
}

// Internal validation state
const error = ref<string | null>(null)
const hasBlurred = ref(false)
const hasAttemptedSubmit = ref(false)

// Computed properties for the field interface
const valid = computed(() => !error.value)
const shouldShowError = computed(() => error.value && (hasBlurred.value || hasAttemptedSubmit.value))

const validate = () => {
    // Check for at least one instruction
    if (props.modelValue.length === 0) {
        error.value = 'At least one instruction is required'
        return
    }

    // Iterate through all instructions to check for empty content or missing section names
    for (const instruction of props.modelValue) {
        if (instruction.type === 'section') {
            // Check that sections have a name
            if (!instruction.name?.trim()) {
                error.value = 'Each section must have a name.'
                return
            }
            // Check that sections have at least one step
            if (instruction.steps.length === 0) {
                error.value = 'Each section must contain at least one step.'
                return
            }
            // Check that each step within a section has content
            for (const step of instruction.steps) {
                if (!step.text?.trim()) {
                    error.value = `Step ${step.position} in section "${instruction.name}" cannot be empty.`
                    return
                }
            }
        } else if (instruction.type === 'step') {
            // Check that standalone steps have content
            if (!instruction.text?.trim()) {
                error.value = `Instruction ${instruction.position} cannot be empty.`
                return
            }
        }
    }

    // If all checks pass, clear the error
    error.value = null
}

const onSubmit = () => {
    hasAttemptedSubmit.value = true
    validate()
}

// Auto-validate on changes if needed
watch(() => props.modelValue, () => {
    if (hasBlurred.value || hasAttemptedSubmit.value) {
        validate()
    }
}, {deep: true})

// Helper to reposition instructions after a change
const repositionInstructions = (instructions: RecipeInstruction[]) => {
    let position = 1
    return instructions.map(instruction => {
        if (instruction.type === 'step') {
            return {...instruction, position: position++}
        } else if (instruction.type === 'section') {
            const repositionedSteps = instruction.steps.map((step, i) => ({...step, position: i + 1}))
            // Since a section itself takes up a position, we don't increment it by the number of steps inside.
            return {...instruction, position: position++, steps: repositionedSteps}
        }
        return instruction
    })
}

const removeInstruction = (index: number) => {
    const updated = props.modelValue.filter((_, i) => i !== index)
    emit('update:modelValue', repositionInstructions(updated))
}

const removeStepFromSection = (sectionIndex: number, stepIndex: number) => {
    const updated = [...props.modelValue]
    const section = updated[sectionIndex] as RecipeSection
    section.steps = section.steps.filter((_, i) => i !== stepIndex)
    emit('update:modelValue', repositionInstructions(updated))
}

// Expose the field interface
defineExpose({
    valid: readonly(valid),
    error: readonly(error),
    onSubmit,
    validate
})
</script>
