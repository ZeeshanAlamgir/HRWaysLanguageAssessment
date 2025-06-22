<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TranslationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(), 'sanctum');
    }

    public function test_can_store_translation()
    {
        $payload = [
            'key' => 'welcome_text',
            'locale' => 'en',
            'tag' => 'web',
            'value' => 'Welcome!',
        ];

        $response = $this->postJson('/api/translation-store', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['value' => 'Welcome!']);

        $this->assertDatabaseHas('translations', $payload);
    }

    public function test_can_list_translations_with_filters()
    {
        Translation::factory()->create(['key' => 'home', 'locale' => 'en', 'tag' => 'web', 'value' => 'Home']);
        Translation::factory()->create(['key' => 'contact', 'locale' => 'fr', 'tag' => 'mobile', 'value' => 'Contact']);

        $response = $this->getJson('/api/translations?locale=en');

        $response->assertStatus(200)
                 ->assertJsonFragment(['locale' => 'en']);
    }

    public function test_can_show_grouped_translations()
    {
        Translation::factory()->create(['key' => 'home', 'locale' => 'en', 'tag' => 'web', 'value' => 'Home']);
        Translation::factory()->create(['key' => 'home', 'locale' => 'fr', 'tag' => 'web', 'value' => 'Accueil']);

        $response = $this->getJson('/api/translation-search?key=home');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_export_json_translations()
    {
        Translation::factory()->create(['key' => 'hello', 'locale' => 'en', 'tag' => 'web', 'value' => 'Hello']);
        Translation::factory()->create(['key' => 'hello', 'locale' => 'fr', 'tag' => 'web', 'value' => 'Bonjour']);

        $response = $this->getJson('/api/translations-export');

        $response->assertStatus(200)
                 ->assertJsonFragment(['hello' => 'Hello']);
    }

    public function test_can_update_translation()
    {
        $translation = Translation::factory()->create();

        $updatePayload = [
            'key' => 'greeting',
            'locale' => 'en',
            'tag' => 'web',
            'value' => 'Hello there!'
        ];

        $response = $this->putJson("/api/translation-update/{$translation->id}", $updatePayload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['value' => 'Hello there!']);
    }

    public function test_translation_index_performance()
    {
        Translation::factory()->count(1000)->create();

        $start = microtime(true);

        $response = $this->getJson('/api/translations');

        $duration = microtime(true) - $start;

        $response->assertStatus(200);

        $this->assertLessThan(0.5, $duration, 'API response time exceeded 500ms');
    }
}
