<?php

namespace App\Containers\Authentication\UI\API\Tests\Functional;

use App\Containers\Authentication\Tests\ApiTestCase;
use Illuminate\Support\Facades\Config;

class ApiLoginProxyTest extends ApiTestCase
{
    protected string $endpoint = 'post@v1/clients/web/admin/login';

    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    public function test_client_web_admin_proxy_login(): void
    {
        $data = [
            'email' => 'testing@mail.com',
            'password' => 'testingpass'
        ];

        $user = $this->getTestingUser($data);
        $this->actingAs($user, 'web');

        $response = $this->makeCall($data);

        $response->assertStatus(200);
        $response->assertCookie('refreshToken');
        $this->assertResponseContainKeyValue([
            'token_type' => 'Bearer',
        ]);

        $this->assertResponseContainKeys(['expires_in', 'access_token']);
    }

    public function test_client_web_admin_proxy_unconfirmed_login(): void
    {
        $data = [
            'email' => 'testing2@mail.com',
            'password' => 'testingpass',
            'confirmed' => false,
        ];

        $user = $this->getTestingUser($data);
        $this->actingAs($user, 'web');

        $response = $this->makeCall($data);

        if (Config::get('authentication-container.require_email_confirmation')) {
            $response->assertStatus(409);
        } else {
            $response->assertStatus(200);
        }
    }

    public function test_login_with_name_attribute(): void
    {
        $data = [
            'email' => 'testing@mail.com',
            'password' => 'testingpass',
            'name' => 'username',
        ];

        $user = $this->getTestingUser($data);
        $this->actingAs($user, 'web');

        $this->setLoginAttributes([
            'email' => [],
            'name' => []
        ]);

        $request = [
            'password' => 'testingpass',
            'name' => 'username',
        ];

        $response = $this->makeCall($request);

        $response->assertStatus(200);

        $this->assertResponseContainKeyValue([
            'token_type' => 'Bearer',
        ]);

        $this->assertResponseContainKeys(['expires_in', 'access_token', 'refresh_token']);
    }

    private function setLoginAttributes(array $attributes): void
    {
        Config::set('authentication-container.login.attributes', $attributes);
    }

    public function test_given_only_one_login_attribute_is_set_then_it_should_be_required(): void
    {
        $this->setLoginAttributes([
            'email' => []
        ]);

        $data = [
            'password' => 'so-secret',
        ];

        $this->makeCall($data);

        $this->assertValidationErrorContain([
            'email' => 'The email field is required.',
        ]);
    }

    public function test_given_multiple_login_attribute_is_set_then_at_least_one_should_be_required(): void
    {
        $this->setLoginAttributes([
            'email' => [],
            'name' => []
        ]);

        $data = [
            'password' => 'so-secret',
        ];

        $this->makeCall($data);

        $this->assertValidationErrorContain([
            'email' => 'The email field is required when none of name are present.',
            'name' => 'The name field is required when none of email are present.',
        ]);
    }
}
