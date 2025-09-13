<template>
    <InputGroup>
        <InputGroupAddon v-if="icon">
            <i :class="icon"></i>
        </InputGroupAddon>
        <FloatLabel variant="on">
            <AutoComplete
                :id="name"
                v-model="selectedTags"
                :class="icon ? 'with-icon' : ''"
                :loading="loading"
                :multiple="multiple"
                :name="name"
                :suggestions="filteredSuggestions"
                auto-option-focus
                fluid
                @complete="searchTags"
            />
            <label :for="name">{{ label }}</label>
        </FloatLabel>
    </InputGroup>
</template>

<script lang="ts" setup>
import {ref} from 'vue';
import AutoComplete from 'primevue/autocomplete';
import FloatLabel from "primevue/floatlabel";
import InputGroup from "primevue/inputgroup";
import InputGroupAddon from "primevue/inputgroupaddon";

interface Props {
    name: string
    icon?: string
    label?: string
    availableTags?: string[]
    multiple?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    multiple: true,
    availableTags: () => [],
});

const selectedTags = ref<string[]>();
const filteredSuggestions = ref<string[]>([])
const loading = ref(false)

const searchTags = (event: { query: string }) => {
    const query = event.query.toLowerCase().trim()

    if (!query) {
        filteredSuggestions.value = props.availableTags
        return
    }

    const matches = props.availableTags.filter(tag => tag.toLowerCase().includes(query))
    filteredSuggestions.value = [...new Set([...matches, query])]
};
</script>

<style>

/* workaround to remove left border radius when icon is present */
.with-icon > .p-autocomplete-input-multiple {
    @apply !rounded-tl-none !rounded-bl-none;
}
</style>
