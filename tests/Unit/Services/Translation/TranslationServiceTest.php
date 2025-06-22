<?php

namespace Tests\Unit\Services\Translation;

use App\Models\Translation;
use App\Services\Translation\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TranslationService $translationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translationService = new TranslationService();
    }

    public function it_can_get_paginated_translations_without_filters()
    {
        Translation::factory()->count(15)->create();
        $request = new Request();

        $result = $this->translationService->index($request);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
    }

    public function it_can_filter_translations_by_key()
    {
        Translation::factory()->create(['key' => 'welcome.message']);
        Translation::factory()->create(['key' => 'goodbye.message']);
        Translation::factory()->create(['key' => 'hello.world']);

        $request = new Request(['key' => 'welcome']);

        $result = $this->translationService->index($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('welcome.message', $result->items()[0]->key);
    }

    public function it_can_filter_translations_by_tag()
    {
        Translation::factory()->create(['tag' => 'frontend']);
        Translation::factory()->create(['tag' => 'backend']);
        Translation::factory()->create(['tag' => 'frontend']);

        $request = new Request(['tag' => 'frontend']);

        $result = $this->translationService->index($request);

        $this->assertEquals(2, $result->total());
        $result->items()->each(function ($item) {
            $this->assertEquals('frontend', $item->tag);
        });
    }

    public function it_can_filter_translations_by_locale()
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'es']);
        Translation::factory()->create(['locale' => 'en']);

        $request = new Request(['locale' => 'en']);

        $result = $this->translationService->index($request);

        $this->assertEquals(2, $result->total());
        $result->items()->each(function ($item) {
            $this->assertEquals('en', $item->locale);
        });
    }

    public function it_can_filter_translations_by_value()
    {
        Translation::factory()->create(['value' => 'Hello World']);
        Translation::factory()->create(['value' => 'Goodbye World']);
        Translation::factory()->create(['value' => 'Welcome']);

        $request = new Request(['value' => 'Hello World']);

        $result = $this->translationService->index($request);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Hello World', $result->items()[0]->value);
    }

    public function it_can_create_new_translation()
    {
        $validated = [
            'key' => 'test.key',
            'locale' => 'en',
            'tag' => 'test',
            'value' => 'Test Value'
        ];

        $result = $this->translationService->store($validated);

        $this->assertInstanceOf(Translation::class, $result);
        $this->assertEquals('test.key', $result->key);
        $this->assertEquals('en', $result->locale);
        $this->assertEquals('test', $result->tag);
        $this->assertEquals('Test Value', $result->value);
        $this->assertDatabaseHas('translations', $validated);
    }

    public function it_can_update_existing_translation_on_store()
    {
        $existing = Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'en',
            'tag' => 'test',
            'value' => 'Old Value'
        ]);

        $validated = [
            'key' => 'test.key',
            'locale' => 'en',
            'tag' => 'test',
            'value' => 'New Value'
        ];

        $result = $this->translationService->store($validated);

        $this->assertEquals($existing->id, $result->id);
        $this->assertEquals('New Value', $result->value);
        $this->assertDatabaseHas('translations', $validated);
        $this->assertEquals(1, Translation::count());
    }

    public function it_can_show_translations_by_key()
    {
        Translation::factory()->create([
            'key' => 'welcome.message',
            'locale' => 'en',
            'value' => 'Welcome'
        ]);
        Translation::factory()->create([
            'key' => 'welcome.message',
            'locale' => 'es',
            'value' => 'Bienvenido'
        ]);

        $request = new Request(['key' => 'welcome.message']);

        $result = $this->translationService->show($request);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        [$groupedData, $translations] = $result;

        $this->assertInstanceOf(LengthAwarePaginator::class, $translations);
        $this->assertEquals(2, $translations->total());
        $this->assertTrue($groupedData->has('en'));
        $this->assertTrue($groupedData->has('es'));
    }

    public function it_can_show_translations_by_locale()
    {
        Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'English'
        ]);

        $request = new Request(['key' => 'en']);

        $result = $this->translationService->show($request);

        $this->assertIsArray($result);
        [$groupedData, $translations] = $result;
        $this->assertEquals(1, $translations->total());
        $this->assertTrue($groupedData->has('en'));
    }

    public function it_returns_null_when_no_translations_found_in_show()
    {
        $request = new Request(['key' => 'nonexistent.key']);
        $result = $this->translationService->show($request);
        $this->assertNull($result);
    }

    public function it_can_export_translations_as_json_without_filters()
    {
        Translation::factory()->create([
            'key' => 'hello',
            'locale' => 'en',
            'value' => 'Hello'
        ]);
        Translation::factory()->create([
            'key' => 'hello',
            'locale' => 'es',
            'value' => 'Hola'
        ]);

        $request = new Request();
        $result = $this->translationService->exportJson($request);
        $this->assertTrue($result->has('en'));
        $this->assertTrue($result->has('es'));
        $this->assertEquals('Hello', $result['en']['hello']);
        $this->assertEquals('Hola', $result['es']['hello']);
    }
    public function it_can_export_translations_filtered_by_locale()
    {
        Translation::factory()->create([
            'key' => 'hello',
            'locale' => 'en',
            'value' => 'Hello'
        ]);
        Translation::factory()->create([
            'key' => 'hello',
            'locale' => 'es',
            'value' => 'Hola'
        ]);

        $request = new Request(['locale' => 'en']);

        $result = $this->translationService->exportJson($request);

        $this->assertTrue($result->has('en'));
        $this->assertFalse($result->has('es'));
        $this->assertEquals('Hello', $result['en']['hello']);
    }

    public function it_can_export_translations_filtered_by_tag()
    {
        Translation::factory()->create([
            'key' => 'hello',
            'locale' => 'en',
            'tag' => 'frontend',
            'value' => 'Hello'
        ]);
        Translation::factory()->create([
            'key' => 'goodbye',
            'locale' => 'en',
            'tag' => 'backend',
            'value' => 'Goodbye'
        ]);

        $request = new Request(['tag' => 'frontend']);

        $result = $this->translationService->exportJson($request);

        $this->assertTrue($result->has('en'));
        $this->assertTrue($result['en']->has('hello'));
        $this->assertFalse($result['en']->has('goodbye'));
    }

    public function it_can_delete_existing_translation()
    {
        $translation = Translation::factory()->create();

        $result = $this->translationService->delete($translation->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function it_returns_false_when_deleting_nonexistent_translation()
    {
        $result = $this->translationService->delete(999);

        $this->assertFalse($result);
    }

    public function it_can_update_existing_translation()
    {
        $translation = Translation::factory()->create([
            'key' => 'old.key',
            'locale' => 'en',
            'tag' => 'old',
            'value' => 'Old Value'
        ]);

        $validated = [
            'key' => 'new.key',
            'locale' => 'es',
            'tag' => 'new',
            'value' => 'New Value'
        ];

        $result = $this->translationService->update($validated, $translation->id);

        $this->assertEquals($translation->id, $result->id);
        $this->assertEquals('new.key', $result->key);
        $this->assertEquals('es', $result->locale);
        $this->assertEquals('new', $result->tag);
        $this->assertEquals('New Value', $result->value);
    }

    public function it_can_create_new_translation_when_updating_nonexistent_id()
    {
        $validated = [
            'key' => 'new.key',
            'locale' => 'en',
            'tag' => 'new',
            'value' => 'New Value'
        ];

        $result = $this->translationService->update($validated, 999);

        $this->assertInstanceOf(Translation::class, $result);
        $this->assertEquals('new.key', $result->key);
        $this->assertEquals('en', $result->locale);
        $this->assertEquals('new', $result->tag);
        $this->assertEquals('New Value', $result->value);
        $this->assertDatabaseHas('translations', $validated);
    }
}
