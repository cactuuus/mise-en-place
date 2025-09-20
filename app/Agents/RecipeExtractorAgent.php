<?php

namespace App\Agents;

use Exception;
use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;

class RecipeExtractorAgent extends Agent
{
    private string $url;

    public function withUrl(string $url): RecipeExtractorAgent
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Extract recipe data from the URL
     */
    public function extractRecipe(): array
    {
        $response     = $this->chat(new UserMessage("Can you please extract this recipe for me? {$this->url}"));
        $responseText = $response->getContent();

        // Clean response - remove any markdown formatting
        $cleanResponse = trim($responseText);
        $cleanResponse = preg_replace('/^```json\s*/', '', $cleanResponse);
        $cleanResponse = preg_replace('/\s*```$/', '', $cleanResponse);

        $data = json_decode($cleanResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse recipe data from AI response');
        }

        Log::info("AI successfully processed recipe", [
            'title'             => $data['title'],
            'tokens spent'      => $response->getUsage(),
            'ingredient_count'  => count($data['ingredients']),
            'instruction_count' => count($data['instructions']),
        ]);

        // Fill in any missing fields with defaults
        return [
            'title'            => $data['title'] ?? 'Untitled Recipe',
            'tags'             => $data['tags'] ?? [],
            'ingredients'      => $data['ingredients'] ?? [],
            'instructions'     => $data['instructions'] ?? [],
            'prep_time'        => (int) ($data['prep_time'] ?? 0),
            'cook_time'        => (int) ($data['cook_time'] ?? 0),
            'serves'           => (int) ($data['serves'] ?? 1),
            'difficulty_level' => $data['difficulty_level'] ?? 'medium',
        ];
    }

    public function instructions(): string
    {
        return "
**IMPORTANT INSTRUCTIONS:**
Structure it like the example below:

```json
{
  \"title\": \"Recipe Name\",
  \"tags\": [\"vegetarian\", \"soup\", \"healthy\"],
  \"ingredients\": [
    {\"amount\": \"2 cups\", \"item\": \"flour\"},
    {\"amount\": \"1 tsp\", \"item\": \"honey\"}
    {\"amount\": \"-\", \"item\": \"salt to taste\"}
  ],
  \"instructions\": [
    {\"instruction\": \"Step 1 description\"},
    {\"instruction\": \"Step 2 description\"}
  ],
  \"prep_time\": 15,
  \"cook_time\": 30,
  \"serves\": 4,
  \"difficulty_level\": \"easy\"
}
```
";
    }

    /**
     * Configure the AI provider (Gemini)
     */
    protected function provider(): AIProviderInterface
    {
        return new Gemini(
            key: config('services.gemini.api_key'),
            model: 'gemini-2.5-pro',
        );
    }

}
