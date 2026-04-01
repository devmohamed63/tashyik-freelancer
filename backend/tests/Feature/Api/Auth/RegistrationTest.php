<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function get_basic_information_for_service_provider(string $entity_type = User::INDIVIDUAL_ENTITY_TYPE): array
    {
        Storage::fake('images');

        return [
            'account_type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'entity_type' => $entity_type,
            'city' => City::factory()->create()?->id,
            'name' => 'User',
            'phone' => '1020304050',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'categories' => Category::factory(2)->create()->pluck('id')->toArray(),
            'residence_name' => 'test',
            'residence_number' => 'test',
            'residence_image' => UploadedFile::fake()->image('photo2.jpg'),
            'bank_name' => 'test bank',
            'iban' => '127459us',
        ];
    }

    public function test_user_registration(): void
    {
        $response = $this->postJson(route('api.register'), [
            'city' => City::factory()->create()?->id,
            'name' => 'User',
            'phone' => '1020304050',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'account_type' => User::USER_ACCOUNT_TYPE,
        ]);

        $response->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('token')->etc()
            );
    }

    public function test_individual_registration(): void
    {
        $response = $this->postJson(route('api.register'), [
            ...$this->get_basic_information_for_service_provider(),
            'personal_picture' => UploadedFile::fake()->image('doc.jpg'),
        ]);

        $response->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('token')->etc()
            );
    }

    public function test_institution_or_company_registration(): void
    {
        $response = $this->postJson(route('api.register'), [
            ...$this->get_basic_information_for_service_provider(User::INSTITUTION_ENTITY_TYPE),
            'commercial_registration_number' => 'aaa1245125',
            'commercial_registration_image' => UploadedFile::fake()->image('doc.jpg'),
            'national_address_image' => UploadedFile::fake()->image('nat.jpg'),
            'tax_registration_number' => '35566',
        ]);

        $response->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('token')->etc()
            );
    }
}
