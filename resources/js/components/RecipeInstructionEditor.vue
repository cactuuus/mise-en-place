<template>
    <div class="space-y-2">
        <Message v-if="modelValue.length === 0" severity="secondary">
            No instructions present
        </Message>

        <div
            v-for="(instruction, index) in modelValue"
            v-else
            :key="`instruction-${index}`"
            class="pb-2 border-b-2 secondary-border border-dashed"
        >
            <div v-if="instruction.type === 'section'">
                <div class="flex items-center gap-1 mb-2">
                    <i class="pi pi-bookmark-fill !text-sm"/>
                    <span class="shrink-0 font-semibold">
                    {{ instruction.position }} #
                    </span>
                    <InputText
                        :model-value="instruction.name"
                        :placeholder="`Section Title`"
                        fluid
                        @input="updateSectionName(index, ($event.target as HTMLInputElement)?.value)"
                    />
                    <Button
                        icon="pi pi-trash"
                        severity="danger"
                        size="small"
                        text
                        @click="removeInstruction(index)"
                    />
                </div>
                <div class="space-y-2">
                    <div v-for="(step, stepIndex) in instruction.steps" :key="`step-${stepIndex}`">
                        <div class="flex relative items-center justify-between">
                            <span class="text-sm font-semibold">
                                {{ instruction.position }}.{{ step.position }} - {{
                                    step.name || '[unnamed step]'
                                }}
                            </span>
                            <Button
                                icon="pi pi-trash"
                                rounded
                                severity="danger"
                                size="small"
                                text
                                @click="removeStepFromSection(index, stepIndex)"
                            />
                        </div>
                        <Textarea
                            :model-value="step.text"
                            :placeholder="`Step ${step.position}`"
                            auto-resize
                            fluid
                            rows="2"
                            @input="updateStepText(index, stepIndex, ($event.target as HTMLInputElement)?.value)"
                        />
                    </div>
                </div>
                <div class="mt-2 flex justify-end">
                    <Button
                        icon="pi pi-plus"
                        label="Add Step to Section"
                        size="small"
                        text
                        @click="addStepToSection(index)"
                    />
                </div>
            </div>

            <div v-else>
                <div class="flex items-center gap-2 justify-between">
                    <!-- todo - allow editing of step name -->
                    <span class="text-sm font-semibold">
                        {{ instruction.position }}  - {{ instruction.name || '[unnamed step]' }}
                    </span>
                    <Button
                        icon="pi pi-trash"
                        rounded
                        severity="danger"
                        size="small"
                        text
                        @click="removeInstruction(index)"
                    />
                </div>
                <Textarea
                    :model-value="instruction.text"
                    :placeholder="`Description of step ${instruction.position}`"
                    auto-resize
                    fluid
                    rows="2"
                    @input="updateInstruction(index, ($event.target as HTMLInputElement)?.value)"
                />
            </div>
        </div>

        <div class="flex items-stretch justify-end pt-2">
            <Button
                icon="pi pi-bookmark"
                label="Add Section"
                size="small"
                text
                @click="addInstruction('section')"
            />
            <Divider class="!mx-1" layout="vertical"/>
            <Button
                icon="pi pi-plus"
                label="Add Global Step"
                size="small"
                text
                @click="addInstruction('step')"
            />
        </div>

        <Message v-if="shouldShowError" severity="error" size="small" variant="simple">
            {{ error }}
        </Message>
    </div>
</template>

<script lang="ts" setup>
import {computed, readonly, ref, watch} from 'vue';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Divider from 'primevue/divider';
import type {RecipeInstruction, RecipeSection, RecipeStep} from '@/types/recipe';

interface Props {
    modelValue: RecipeInstruction[]
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: RecipeInstruction[]]
}>();

// Internal validation state
const error = ref<string | null>(null);
const hasBlurred = ref(false);
const hasAttemptedSubmit = ref(false);

// Computed properties for the field interface
const valid = computed(() => !error.value);
const shouldShowError = computed(() => error.value && (hasBlurred.value || hasAttemptedSubmit.value));

const validate = () => {
    // Check for at least one instruction
    if (props.modelValue.length === 0) {
        error.value = 'At least one instruction is required';
        return;
    }

    // Iterate through all instructions to check for empty content or missing section names
    for (const instruction of props.modelValue) {
        if (instruction.type === 'section') {
            // Check that sections have a name
            if (!instruction.name?.trim()) {
                error.value = 'Each section must have a name.';
                return;
            }
            // Check that sections have at least one step
            if (instruction.steps.length === 0) {
                error.value = 'Each section must contain at least one step.';
                return;
            }
            // Check that each step within a section has content
            for (const step of instruction.steps) {
                if (!step.text?.trim()) {
                    error.value = `Step ${step.position} in section "${instruction.name}" cannot be empty.`;
                    return;
                }
            }
        } else if (instruction.type === 'step') {
            // Check that standalone steps have content
            if (!instruction.text?.trim()) {
                error.value = `Instruction ${instruction.position} cannot be empty.`;
                return;
            }
        }
    }

    // If all checks pass, clear the error
    error.value = null;
};

const onSubmit = () => {
    hasAttemptedSubmit.value = true;
    validate();
};

// Auto-validate on changes if needed
watch(() => props.modelValue, () => {
    if (hasBlurred.value || hasAttemptedSubmit.value) {
        validate();
    }
}, {deep: true});

// Helper to reposition instructions after a change
const repositionInstructions = (instructions: RecipeInstruction[]) => {
    let position = 1;
    return instructions.map(instruction => {
        if (instruction.type === 'step') {
            return {...instruction, position: position++};
        } else if (instruction.type === 'section') {
            const repositionedSteps = instruction.steps.map((step, i) => ({...step, position: i + 1}));
            // Since a section itself takes up a position, we don't increment it by the number of steps inside.
            return {...instruction, position: position++, steps: repositionedSteps};
        }
        return instruction;
    });
};


// Main instruction management methods
const addInstruction = (type: 'step' | 'section') => {
    let newInstruction: RecipeInstruction;
    if (type === 'section') {
        newInstruction = {
            type: 'section',
            position: props.modelValue.length + 1,
            name: '',
            steps: [{type: 'step', position: 1, text: ''}]
        };
    } else {
        newInstruction = {
            type: 'step',
            position: props.modelValue.length + 1,
            text: ''
        };
    }
    const updated = [...props.modelValue, newInstruction];
    emit('update:modelValue', repositionInstructions(updated));
};

const removeInstruction = (index: number) => {
    const updated = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', repositionInstructions(updated));
};

const updateInstruction = (index: number, text: string) => {
    const updated = [...props.modelValue];
    updated[index] = {
        ...(updated[index] as RecipeStep),
        text
    };
    emit('update:modelValue', updated);
};

// Section-specific methods
const updateSectionName = (index: number, name: string) => {
    const updated = [...props.modelValue];
    const section = updated[index] as RecipeSection;
    updated[index] = {...section, name};
    emit('update:modelValue', updated);
};

const addStepToSection = (sectionIndex: number) => {
    const updated = [...props.modelValue];
    const section = updated[sectionIndex] as RecipeSection;
    const newStep: RecipeStep = {
        type: 'step',
        position: section.steps.length + 1,
        text: ''
    };
    section.steps.push(newStep);
    emit('update:modelValue', repositionInstructions(updated));
};

const updateStepText = (sectionIndex: number, stepIndex: number, text: string) => {
    const updated = [...props.modelValue];
    const section = updated[sectionIndex] as RecipeSection;
    const step = section.steps[stepIndex];
    section.steps[stepIndex] = {...step, text};
    emit('update:modelValue', updated);
};

const removeStepFromSection = (sectionIndex: number, stepIndex: number) => {
    const updated = [...props.modelValue];
    const section = updated[sectionIndex] as RecipeSection;
    section.steps = section.steps.filter((_, i) => i !== stepIndex);
    emit('update:modelValue', repositionInstructions(updated));
};

// Expose the field interface
defineExpose({
    valid: readonly(valid),
    error: readonly(error),
    onSubmit,
    validate
});
</script>
