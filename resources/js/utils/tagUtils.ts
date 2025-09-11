import {RecipeTags} from "@/types/recipe.ts";

export interface FlattenedTag {
    text: string;
    severity: string;
    icon: string;
}

export const flattenRecipeTags = (tags: RecipeTags) => {
    const allTags: FlattenedTag[] = [];

    tags.recipe_diet?.forEach(tag =>
        allTags.push({text: tag, severity: 'success', icon: 'pi pi-heart'})
    );
    tags.recipe_cuisine?.forEach(tag =>
        allTags.push({text: tag, severity: 'secondary', icon: 'pi pi-globe'})
    );
    tags.recipe_category?.forEach(tag =>
        allTags.push({text: tag, severity: 'secondary', icon: 'pi pi-list'})
    );
    tags.recipe_keyword?.forEach(tag =>
        allTags.push({text: tag, severity: 'secondary', icon: 'pi pi-tag'})
    );

    return allTags;
};
