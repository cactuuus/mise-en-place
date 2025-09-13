<template>
    <div class="space-y-4">
        <Message v-if="localInstructions.length === 0" severity="secondary" size="small">
            No instructions present
        </Message>

        <!-- Draggable container for all instructions -->
        <Draggable
            :component-data="{ class: 'space-y-4' }"
            :group="{ name: 'instructions', pull: true, put: true }"
            :list="localInstructions"
            item-key="id"
            v-bind="draggableOptions"
        >
            <template #item="{ element: instruction, index }">
                <div :key="instruction.id">
                    <!-- Section -->
                    <div
                        v-if="instruction.type === 'section'"
                        class="border rounded secondary-border pb-4"
                    >
                        <!-- Section Header with drag handle -->
                        <div
                            class="flex items-center p-3 gap-2 border-b secondary-border secondary-bg justify-between rounded-sm w-full">
                            <Button
                                class="drag-handle cursor-move"
                                icon="pi pi-bars"
                                severity="secondary"
                                text
                            />

                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <p class="truncate font-semibold">
                                    [{{ instruction.position }}] # {{ instruction.name }}
                                </p>
                            </div>

                            <div class="flex gap-2 shrink-0">
                                <Button
                                    icon="pi pi-pencil"
                                    severity="warn"
                                    text
                                    @click="openSectionModal('edit', index, instruction.name)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click="removeInstruction(index)"
                                />
                            </div>
                        </div>


                        <!-- Section Steps - Nested Draggable -->
                        <div class="px-1">
                            <Draggable
                                :component-data="{ class: 'space-y-4 py-4' }"
                                :group="{ name: 'steps', pull: true, put: true }"
                                :list="instruction.steps"
                                item-key="id"
                                v-bind="draggableOptions"
                            >
                                <template v-if="instruction.steps.length === 0" #header>
                                    <Message class="mx-2" severity="secondary" size="small">
                                        This section is empty
                                    </Message>
                                </template>
                                <template #item="{ element: step, index: stepIndex }">
                                    <div :key="step.id">
                                        <div
                                            class="p-3 space-y-2 border secondary-border cursor-pointer hover:border-primary-700 dark:hover:border-primary-400 rounded transition-colors contrast-bg"
                                        >
                                            <div class="flex items-centre gap-2 justify-between w-full">
                                                <Button
                                                    class="drag-handle cursor-move"
                                                    icon="pi pi-bars"
                                                    severity="secondary"
                                                    text
                                                    @click.stop
                                                />
                                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                                    <p class="truncate font-medium">
                                                        [{{ instruction.position }}.{{ step.position }}] {{ step.name }}
                                                    </p>
                                                </div>
                                                <div class="flex gap-2 shrink-0">
                                                    <Button
                                                        icon="pi pi-pencil"
                                                        severity="warn"
                                                        text
                                                        @click.stop="openStepModal('edit', index, stepIndex, step.name, step.text)"
                                                    />
                                                    <Button
                                                        icon="pi pi-trash"
                                                        severity="danger"
                                                        text
                                                        @click.stop="removeStepFromSection(index, stepIndex)"
                                                    />
                                                </div>
                                            </div>
                                            <p class="text-sm secondary-text">{{ step.text || '[no description]' }}</p>
                                        </div>
                                    </div>
                                </template>
                            </Draggable>
                        </div>

                        <div class="flex justify-end px-1">
                            <Button
                                icon="pi pi-plus"
                                label="Add Step Inside Section"
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
                    >
                        <div class="flex items-center gap-2 justify-between w-full">
                            <Button
                                class="drag-handle cursor-move"
                                icon="pi pi-bars"
                                severity="secondary"
                                text
                                @click.stop
                            />
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <p class="truncate font-medium">
                                    [{{ instruction.position }}] {{ instruction.name }}
                                </p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <Button
                                    icon="pi pi-pencil"
                                    severity="warn"
                                    text
                                    @click.stop="openStepModal('edit', index, undefined, instruction.name, instruction.text)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click.stop="removeInstruction(index)"
                                />
                            </div>
                        </div>
                        <p class="text-sm secondary-text">{{ instruction.text || '[no description]' }}</p>
                    </div>
                </div>
            </template>
        </Draggable>

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
                label="Add Step"
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
import {computed, readonly, ref} from 'vue'
import Draggable from 'vuedraggable'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Divider from 'primevue/divider'
import RecipeSectionModal from "@/components/RecipeSectionModal.vue"
import RecipeStepModal from "@/components/RecipeStepModal.vue"
import type {RecipeInstruction, RecipeSection, RecipeStep} from '@/types/recipe'

type DraggableRecipeStep = RecipeStep & { id?: string }
type DraggableRecipeSection = Omit<RecipeSection, 'steps'> & { id?: string, steps: DraggableRecipeStep[] }
type DraggableRecipeInstruction = (DraggableRecipeStep | DraggableRecipeSection) & { id?: string }

const props = defineProps({
    modelValue: {
        type: Array as () => RecipeInstruction[],
        default: () => []
    }
})

const emit = defineEmits<{
    'update:modelValue': [value: RecipeInstruction[]]
}>()


const draggableOptions = {
    bubbleScroll: true,
    forceFallback: true,
    ghostClass: 'ghost',
    scroll: true,
    scrollSensitivity: 100,
    scrollSpeed: 10,
    handle: '.drag-handle',
    tag: 'div',
    delay: 200,
    delayOnTouchOnly: true,
    onStart: () => {
        if (navigator.vibrate) navigator.vibrate(50)
    },
    onChange: () => localInstructions.value = localInstructions.value // Trigger reactivity on change,
}

// Modal state & data
const showSectionModal = ref(false)
const showStepModal = ref(false)
const sectionModalMode = ref<'create' | 'edit'>('create')
const stepModalMode = ref<'create' | 'edit'>('create')
const editingSectionName = ref<string>()
const editingStepName = ref<string>()
const editingStepText = ref<string>()
const editingInstructionIndex = ref<number>()
const editingStepIndex = ref<number>()

// Computed property for local instructions with drag IDs
const localInstructions = computed<DraggableRecipeInstruction[]>({
    get: () => addDraggableIds(props.modelValue),
    set: (value) => emit('update:modelValue', repositionInstructions(removeDraggableIds(value)))
})

// Internal validation state
const error = ref<string | null>(null)
const hasBlurred = ref(false)
const hasAttemptedSubmit = ref(false)
const valid = computed(() => !error.value)
const shouldShowError = computed(() => error.value && (hasBlurred.value || hasAttemptedSubmit.value))

// Add/remove IDs for draggable functionality
const addDraggableIds = (instructions: DraggableRecipeInstruction[]): DraggableRecipeInstruction[] => {
    const flattened = flattenInstructions(instructions)
    return flattened.map((instruction, index) => ({
        ...instruction,
        id: `inst-${index}`,
        ...(instruction.type === 'section' && {
            steps: instruction.steps.map((step, stepIndex) => ({
                ...step,
                id: `step-${index}-${stepIndex}`
            }))
        })
    }))
}

const removeDraggableIds = (instructions: DraggableRecipeInstruction[]): DraggableRecipeInstruction[] => {
    const flattened = flattenInstructions(instructions)
    return flattened.map(({id, ...instruction}) => ({
        ...instruction,
        ...(instruction.type === 'section' && {
            steps: instruction.steps.map(({id, ...step}) => step)
        })
    }))
}

// Flatten nested sections for easier manipulation
const flattenSection = (section: DraggableRecipeSection): DraggableRecipeInstruction => {
    return {
        ...section,
        steps: section.steps.flatMap((child) => {
            if (child.type === "step") {
                return [child]
            }
            if (child.type === "section") {
                // @ts-expect-error We know this will be steps only after flattening
                return flattenSection(child).steps
            }
            return []
        }),
    }
}

// Flatten instructions to avoid nested sections (sections with sections), leaving at most one level of nesting (sections with steps)
const flattenInstructions = (instructions: DraggableRecipeInstruction[]): DraggableRecipeInstruction[] => {
    return instructions.map((instruction) => {
        if (instruction.type === "section") {
            return flattenSection(instruction)
        }
        return instruction
    })
}

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
        localInstructions.value = [...localInstructions.value, {...newSection, id: 'temp'}]
    } else if (editingInstructionIndex.value !== undefined) {
        const updated = [...localInstructions.value]
        const section = updated[editingInstructionIndex.value]
        if (section.type === 'section') {
            section.name = data.name
        }
        localInstructions.value = updated
    }
}

const handleStepSubmit = (data: { name?: string, text: string }) => {
    if (stepModalMode.value === 'create') {
        if (editingInstructionIndex.value !== undefined) {
            // Adding to section
            const updated = [...localInstructions.value]
            const section = updated[editingInstructionIndex.value]
            if (section.type === 'section') {
                const newStep: RecipeStep = {
                    type: 'step',
                    position: section.steps.length + 1,
                    text: data.text,
                    name: data.name,
                }
                section.steps = [...section.steps, newStep]
            }
            localInstructions.value = updated
        } else {
            // Adding global step
            const newStep: RecipeStep = {
                type: 'step',
                position: localInstructions.value.length + 1,
                text: data.text,
                name: data.name,
            }
            localInstructions.value = [...localInstructions.value, newStep]
        }
    } else if (stepModalMode.value === 'edit' && editingInstructionIndex.value !== undefined) {
        const updated = [...localInstructions.value]

        if (editingStepIndex.value !== undefined) {
            // Editing step in section
            const section = updated[editingInstructionIndex.value]
            if (section.type === 'section') {
                const step = section.steps[editingStepIndex.value]
                step.text = data.text
                step.name = data.name
            }
        } else {
            // Editing global step
            const step = updated[editingInstructionIndex.value]
            if (step.type === 'step') {
                step.text = data.text
                step.name = data.name
            }
        }

        localInstructions.value = updated
    }
}

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

// Helper to reposition instructions after a change
const repositionInstructions = (instructions: RecipeInstruction[]) => {
    let position = 1
    return instructions.map(instruction => {
        if (instruction.type === 'step') {
            return {...instruction, position: position++}
        } else if (instruction.type === 'section') {
            const repositionedSteps = instruction.steps.map((step, i) => ({...step, position: i + 1}))
            return {...instruction, position: position++, steps: repositionedSteps}
        }
        return instruction
    })
}

const removeInstruction = (index: number) => {
    localInstructions.value = localInstructions.value.filter((_, i) => i !== index)
}

const removeStepFromSection = (sectionIndex: number, stepIndex: number) => {
    const updated = [...localInstructions.value]
    const section = updated[sectionIndex] as RecipeSection
    section.steps = section.steps.filter((_, i) => i !== stepIndex)
    localInstructions.value = updated
}

// Expose the field interface
defineExpose({
    valid: readonly(valid),
    error: readonly(error),
    onSubmit,
    validate
})
</script>

<style scoped>
.ghost {
    @apply border-t-4 border-primary-700 dark:border-primary-400 h-0 p-0 overflow-hidden;
}

.drag-handle:active,
.drag-handle:active,
.chosen {
    cursor: grabbing !important;
}
</style>
