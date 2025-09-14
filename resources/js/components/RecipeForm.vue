<template>
    <Form
        v-slot="$form"
        :initial-values="initialValues"
        :resolver="zodResolver(recipeSchema)"
        validate-on-blur
        @submit="handleSubmit"
        @keydown.enter.prevent
    >
        <div class="space-y-6">
            <!-- Grid Container for Image and Basic Info -->
            <div class="grid gap-6 sm:grid-cols-[1.5fr_2fr] lg:grid-cols-[1fr_2fr]">
                <!-- Recipe Image -->
                <Panel
                    class="min-w-40 w-full"
                    header="Recipe Image"
                >
                    <div class="flex flex-col w-full max-w-72 mx-auto gap-4">
                        <!-- Image Preview -->
                        <img
                            v-if="imageUrl"
                            :src="imageUrl"
                            alt="Recipe preview"
                            class="aspect-square object-cover rounded-lg border-4 border-dashed secondary-border"
                        />
                        <PlaceholderRecipeImage
                            v-else
                            class="aspect-square object-cover rounded-lg border-4 border-dashed secondary-border"
                        />

                        <Button
                            v-if="imagePreviewUrl"
                            icon="pi pi-times"
                            label="Cancel"
                            severity="secondary"
                            @click="handleImageRemove"
                        />
                        <!-- File Upload -->
                        <FileUpload
                            ref="fileUploadRef"
                            :disabled="loading"
                            :file-limit="1"
                            :max-file-size="MAX_FILESIZE"
                            :show-cancel-button="false"
                            :show-upload-button="false"
                            accept="image/*"
                            choose-icon="pi pi-upload"
                            choose-label="Upload new"
                            class="p-button-outlined"
                            mode="basic"
                            @select="handleImageSelect"
                        />
                    </div>
                </Panel>

                <!-- Basic Information -->
                <Panel header="Basic Information">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                        <!-- Title -->
                        <div class="lg:col-span-full">
                            <FloatLabel variant="on">
                                <InputText
                                    id="title"
                                    fluid
                                    name="title"
                                />
                                <label for="title">Recipe Title</label>
                            </FloatLabel>
                            <Message v-if="$form.title?.invalid" severity="error" size="small" variant="simple">
                                {{ $form.title.error?.message }}
                            </Message>
                        </div>

                        <!-- Difficulty -->
                        <div>
                            <SelectButton
                                id="difficulty_level"
                                :allow-empty="false"
                                :options="difficultyOptions"
                                fluid
                                name="difficulty_level"
                                option-label="label"
                                option-value="value"
                            />
                            <Message v-if="$form.difficulty_level?.invalid" severity="error" size="small"
                                     variant="simple">
                                {{ $form.difficulty_level.error?.message }}
                            </Message>
                        </div>

                        <!-- Serves -->
                        <div>
                            <InputGroup>
                                <InputGroupAddon>
                                    <i class="pi pi-bolt"></i>
                                </InputGroupAddon>
                                <FloatLabel variant="on">
                                    <InputText
                                        id="recipe_yield"
                                        class="w-full"
                                        name="recipe_yield"
                                    />
                                    <label for="recipe_yield">Yield</label>
                                </FloatLabel>
                            </InputGroup>
                            <Message v-if="$form.recipe_yield?.invalid" severity="error" size="small" variant="simple">
                                {{ $form.recipe_yield.error?.message }}
                            </Message>
                        </div>

                        <!-- Prep & Cook Time -->
                        <div class="col-span-full flex items-center gap-2">
                            <i class="pi pi-clock"></i>
                            <span>Time</span>
                            <Divider/>
                        </div>
                        <div>
                            <InputGroup>
                                <InputGroupAddon>
                                    <i class="pi pi-clock"></i>
                                </InputGroupAddon>
                                <FloatLabel variant="on">
                                    <InputNumber
                                        id="prep_time"
                                        :min="0"
                                        name="prep_time"
                                    />
                                    <label for="prep_time">Prep Time</label>
                                </FloatLabel>
                                <InputGroupAddon>
                                    <span class="min-w-14 text-center">mins</span>
                                </InputGroupAddon>
                            </InputGroup>
                            <Message v-if="$form.prep_time?.invalid" severity="error" size="small" variant="simple">
                                {{ $form.prep_time.error?.message }}
                            </Message>
                        </div>
                        <div>
                            <InputGroup>
                                <InputGroupAddon>
                                    <i class="pi pi-clock"></i>
                                </InputGroupAddon>
                                <FloatLabel variant="on">
                                    <InputNumber
                                        id="cook_time"
                                        :min="0"
                                        name="cook_time"
                                    />
                                    <label for="cook_time">Cook Time</label>
                                </FloatLabel>
                                <InputGroupAddon>
                                    <span class="min-w-14 text-center">mins</span>
                                </InputGroupAddon>
                            </InputGroup>
                            <Message v-if="$form.cook_time?.invalid" severity="error" size="small" variant="simple">
                                {{ $form.cook_time.error?.message }}
                            </Message>
                        </div>
                        <p class="secondary-text text-sm col-span-full">
                            Total time: {{ calculateTotalTime($form.prep_time?.value, $form.cook_time?.value) }} minutes
                        </p>

                        <!-- Tags -->
                        <div class="col-span-full">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-tags"></i>
                                <span>Tags</span>
                                <Divider/>
                            </div>
                            <p class="secondary-text text-sm col-span-full">
                                Select existing tags or create
                                new ones by typing and pressing enter.
                            </p>
                        </div>
                        <TagSelector
                            :available-tags="availableTags?.recipe_diet"
                            class="col-span-full"
                            icon="pi pi-heart"
                            label="Diet"
                            name="recipe_diet"
                        />
                        <TagSelector
                            :available-tags="availableTags?.recipe_cuisine"
                            class="col-span-full"
                            icon="pi pi-globe"
                            label="Cuisine type"
                            name="recipe_cuisine"
                        />
                        <TagSelector
                            :available-tags="availableTags?.recipe_category"
                            class="col-span-full"
                            icon="pi pi-list"
                            label="Category"
                            name="recipe_category"
                        />
                        <TagSelector
                            :available-tags="availableTags?.recipe_keyword"
                            class="col-span-full"
                            icon="pi pi-tag"
                            label="Additional Keywords"
                            name="recipe_keyword"
                        />
                    </div>
                </Panel>
            </div>

            <!-- Ingredients -->
            <Panel header="Ingredients" toggleable>
                <div class="space-y-3">
                    <div
                        v-for="(ingredient, index) in ingredientFields"
                        :key="ingredient.id"
                    >
                        <InputGroup>
                            <InputText
                                :model-value="ingredient.value"
                                :placeholder="`Ingredient ${index + 1}`"
                                @input="updateIngredient(index, ($event.target as HTMLInputElement)?.value)"
                            />
                            <InputGroupAddon v-if="ingredientFields.length > 1">
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    size="small"
                                    text
                                    @click="removeIngredient(index)"
                                />
                            </InputGroupAddon>
                        </InputGroup>
                    </div>
                </div>

                <div class="mt-2 flex justify-end">
                    <Button
                        icon="pi pi-plus"
                        label="Add Ingredient"
                        size="small"
                        text
                        @click="addIngredient"
                    />
                </div>
            </Panel>

            <!-- Instructions -->
            <Panel header="Instructions" toggleable>
                <RecipeInstructionEditor
                    ref="instructionsField"
                    v-model="instructionFields"
                />
            </Panel>

            <!-- Additional Information -->
            <Panel collapsed header="Additional Information" toggleable>
                <div class="space-y-4">
                    <!-- Notes -->
                    <div>
                        <FloatLabel variant="on">
                              <Textarea
                                  id="notes"
                                  auto-resize
                                  class="w-full"
                                  name="notes"
                                  rows="3"
                              />
                            <label for="notes">Notes</label>
                        </FloatLabel>
                    </div>

                    <!-- Source URL -->
                    <div>
                        <FloatLabel variant="on">
                            <InputText
                                id="source_url"
                                class="w-full"
                                name="source_url"
                                type="url"
                            />
                            <label for="source_url">Source URL</label>
                        </FloatLabel>
                        <Message v-if="$form.source_url?.invalid" severity="error" size="small" variant="simple">
                            {{ $form.source_url.error?.message }}
                        </Message>
                    </div>

                    <!-- Is Public -->
                    <div class="flex items-center gap-2">
                        <ToggleSwitch
                            id="is_public"
                            checked={isPublic}
                            name="is_public"
                        />
                        <label class="cursor-pointer" for="is_public">Make recipe public</label>
                    </div>
                </div>
            </Panel>

            <!-- Form Actions -->
            <div class="flex justify-end gap-2 pt-4 border-t">
                <Button
                    label="Cancel"
                    outlined
                    severity="secondary"
                    @click="$emit('abort')"
                />
                <Button
                    :disabled="!$form.valid || loading"
                    :label="mode === 'create' ? 'Create Recipe' : 'Update Recipe'"
                    :loading="loading"
                    icon="pi pi-save"
                    severity="success"
                    type="submit"
                />
            </div>
        </div>
    </Form>
</template>

<script lang="ts" setup>
import {computed, nextTick, onMounted, ref} from 'vue'
import {Form} from '@primevue/forms'
import {zodResolver} from '@primevue/forms/resolvers/zod'
import {z} from 'zod'
import Panel from 'primevue/panel'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import FileUpload from 'primevue/fileupload'
import Button from 'primevue/button'
import Message from 'primevue/message'
import FloatLabel from 'primevue/floatlabel'
import ToggleSwitch from 'primevue/toggleswitch'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import Divider from 'primevue/divider'
import SelectButton from 'primevue/selectbutton'
import {DIFFICULTY_LEVELS, type Recipe, RecipeInstruction, RecipeTags} from '@/types/recipe'
import PlaceholderRecipeImage from "@/components/PlaceholderRecipeImage.vue"
import {fetchRecipeTags} from "@/services/recipeService.ts"
import {showError} from "@/services/toastService.ts"
import TagSelector from "@/components/TagSelector.vue"
import RecipeInstructionEditor from "@/components/RecipeInstructionEditor.vue"

const MAX_FILESIZE = 5242880 // 5MB

// Interface for initial data prop, can have any subset of Recipe fields, with the addition of imported_image_url
interface InitialRecipeData extends Partial<Recipe> {
    imported_image_data?: string
    imported_image_mime?: string
}

interface Props {
    mode?: 'create' | 'edit'
    initialData?: InitialRecipeData
}

interface Emits {
    'submit': [formData: FormData]
    'abort': []
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create'
})

const emit = defineEmits<Emits>()

const recipeSchema = z.object({
    title: z.string().min(1, 'Title is required').max(255, 'Title too long'),
    ingredients: z.array(z.string().min(1, 'Ingredient cannot be empty')).min(1, 'At least one ingredient is required'),
    notes: z.string().optional(),
    source_url: z.url('Invalid URL').optional().or(z.literal('')),
    is_public: z.boolean().default(false),
    prep_time: z.number().min(0, 'Prep time cannot be negative').optional(),
    cook_time: z.number().min(0, 'Cook time cannot be negative').optional(),
    recipe_yield: z.string().max(50, 'Invalid input, max 50 characters').optional(),
    difficulty_level: z.number().min(1).max(3, 'Invalid difficulty level')
})

const difficultyOptions = Object.values(DIFFICULTY_LEVELS).map(level => ({
    label: level.label,
    value: level.value
}))

// Form state
const loading = ref(false)

// Initial form values
const initialValues = computed(() => ({
    title: props.initialData?.title || '',
    notes: props.initialData?.notes || '',
    source_url: props.initialData?.source_url || '',
    is_public: props.initialData?.is_public || true,
    prep_time: props.initialData?.prep_time || undefined,
    cook_time: props.initialData?.cook_time || undefined,
    recipe_yield: props.initialData?.recipe_yield || undefined,
    difficulty_level: props.initialData?.difficulty_level?.value || DIFFICULTY_LEVELS.EASY.value,
    // Tags
    recipe_cuisine: props.initialData?.tags?.recipe_cuisine || [],
    recipe_category: props.initialData?.tags?.recipe_category || [],
    recipe_diet: props.initialData?.tags?.recipe_diet || [],
    recipe_keyword: props.initialData?.tags?.recipe_keyword || [],
    // ingredients, instructions, and image are handled separately
}))

// Ingredients management
const ingredientFields = ref<{ id: number, value: string }[]>([])
const nextIngredientId = ref(1)

// Instructions management
const instructionsField = ref()
const instructionFields = ref<RecipeInstruction[]>(props.initialData?.instructions || [])

// Image handling
const selectedImage = ref<File | null>(null)
const imagePreviewUrl = ref<string | null>(null)
const fileUploadRef = ref()

// Tags handling
const availableTags = ref<RecipeTags>()
const loadingTags = ref(true)

const initializeTags = async () => {
    availableTags.value = await fetchRecipeTags()
    loadingTags.value = false
}

const calculateTotalTime = (prepTime: number | undefined, cookTime: number | undefined): number => {
    return (prepTime || 0) + (cookTime || 0)
}

// Ingredient management
const initializeIngredients = () => {
    if (props.initialData?.ingredients?.length) {
        ingredientFields.value = props.initialData.ingredients.map((ingredient, index) => ({
            id: index + 1,
            value: ingredient
        }))
        nextIngredientId.value = ingredientFields.value.length + 1
    } else {
        ingredientFields.value = [{id: 1, value: ''}]
        nextIngredientId.value = 2
    }
}

const addIngredient = () => {
    ingredientFields.value.push({id: nextIngredientId.value++, value: ''})
}

const removeIngredient = (index: number) => {
    if (ingredientFields.value.length > 1) {
        ingredientFields.value.splice(index, 1)
    }
}

const updateIngredient = (index: number, value: string) => {
    ingredientFields.value[index].value = value
}

// Image handling
const generateTimestampId = (): string => {
    const timestamp = Date.now()
    const random = Math.random().toString(36).substring(2, 8)
    return `${timestamp}_${random}`
}

const getExtensionFromMime = (mimeType: string): string => {
    const extensions = {
        'image/jpeg': 'jpg',
        'image/png': 'png',
        'image/gif': 'gif',
        'image/webp': 'webp'
    }
    return extensions[mimeType as keyof typeof extensions] || 'jpg'
}

const initializeImage = async () => {
    // Clear the component first
    fileUploadRef.value?.clear()
    await nextTick()

    if (props.initialData?.imported_image_data && props.initialData?.imported_image_mime) {
        try {
            const bytes = Uint8Array.from(atob(props.initialData.imported_image_data), c => c.charCodeAt(0))
            const blob = new Blob([bytes], {type: props.initialData.imported_image_mime})
            const filename = `imported_${generateTimestampId()}.${getExtensionFromMime(blob.type)}`
            const file = new File([blob], filename, {type: blob.type})

            // Try to access the underlying input element and simulate file selection
            if (fileUploadRef.value?.$el) {
                const inputElement = fileUploadRef.value?.$el?.querySelector?.('input[type="file"]') ||
                    fileUploadRef.value?.$refs?.fileInput
                if (inputElement) {
                    const dataTransfer = new DataTransfer()
                    dataTransfer.items.add(file)
                    inputElement.files = dataTransfer.files

                    // Trigger the change event to notify PrimeVue
                    const changeEvent = new Event('change', {bubbles: true})
                    inputElement.dispatchEvent(changeEvent)
                    return
                }
            }

            // Fallback: Handle manually if the above doesn't work
            selectedImage.value = file
            imagePreviewUrl.value = URL.createObjectURL(file)
        } catch (error) {
            console.error('Failed to process imported image:', error)
        }
    }
}

const imageUrl = computed(() => {
    return imagePreviewUrl.value ?? props.initialData?.image_urls?.medium
})

const handleImageSelect = (event: { files: File[] }) => {
    const file = event.files[0]
    if (!file) return

    selectedImage.value = file
    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value)
    }
    imagePreviewUrl.value = URL.createObjectURL(file)
}

const handleImageRemove = () => {
    selectedImage.value = null
    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value)
        imagePreviewUrl.value = null
    }
    if (fileUploadRef.value) {
        fileUploadRef.value.clear()
    }
}

// Form submission
const handleSubmit = async (event: { valid: boolean, states: Record<string, any> }) => {
    // Validate form
    instructionsField.value?.onSubmit()
    if (!instructionsField.value?.valid || !event.valid) {
        showError('Please fix the errors in the form before submitting.')
        return
    }

    loading.value = true

    try {
        const formData = new FormData()

        // Add all form fields
        formData.append('title', event.states.title.value)
        formData.append('notes', event.states.notes?.value || '')
        formData.append('source_url', event.states.source_url?.value || '')
        formData.append('is_public', event.states.is_public.value ? '1' : '0')
        formData.append('difficulty_level', event.states.difficulty_level.value.toString())
        formData.append('tags',
            JSON.stringify({
                recipe_diet: event.states.recipe_diet?.value || [],
                recipe_cuisine: event.states.recipe_cuisine?.value || [],
                recipe_category: event.states.recipe_category?.value || [],
                recipe_keyword: event.states.recipe_keyword?.value || []
            })
        )

        if (event.states.prep_time?.value) {
            formData.append('prep_time', event.states.prep_time.value.toString())
        }
        if (event.states.cook_time?.value) {
            formData.append('cook_time', event.states.cook_time.value.toString())
        }
        if (event.states.serves?.value) {
            formData.append('recipe_yield', event.states.serves.value.toString())
        }

        // Add ingredients
        const ingredients = ingredientFields.value.map(f => f.value).filter(v => v.trim())
        ingredients.forEach((ingredient, index) => {
            formData.append(`ingredients[${index}]`, ingredient)
        })

        // Add instructions
        formData.append('instructions', JSON.stringify(instructionFields.value))

        // Add image if selected
        if (selectedImage.value) {
            formData.append('image', selectedImage.value)
        }

        emit('submit', formData)
    } catch (error) {
        showError('An error occurred during form submission. Please try again.')
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    // initialises custom fields, technically present in the form
    initializeImage()
    initializeIngredients()

    initializeTags()
})
</script>
