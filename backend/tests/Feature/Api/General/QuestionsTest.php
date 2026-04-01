<?php

namespace Tests\Feature\Api\General;

use Database\Seeders\QuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class QuestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(QuestionSeeder::class);
    }

    public function test_question_and_answers_are_displayed(): void
    {
        $response = $this->getJson(route('api.general.questions'));

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.0.title')
                ->has('data.0.answer')
                ->etc()
        );
    }
}
