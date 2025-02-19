<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**Testing for login */
    /** @test */
    public function test_api_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('test1234')
        ]);
    
        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'test1234'
        ]);
    
        $response->assertStatus(200)->assertJsonStructure(['access_token']);
    
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }


    public function test_api_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'nouser@example.com',
            'password' => bcrypt('test1234')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'incorrectpassword'
        ]);

        $response->assertStatus(401);

        $response->assertJson(['message' => 'Invalid credentials']); 
    }

    /**Testing for password reset */
    /** @test */
    public function test_api_user_can_reset_password()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('test1234')
        ]);
    
        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'test1234'
        ]);
    
        $response->assertStatus(200)->assertJsonStructure(['access_token']);
    
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }


    public function test_api_user_cannot_reset_password()
    {
        $user = User::factory()->create([
            'email' => 'nouser@example.com',
            'password' => bcrypt('test1234')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'incorrectpassword'
        ]);

        $response->assertStatus(401);

        $response->assertJson(['message' => 'Invalid credentials']); 
    }

    /**Testing for user registeration */
    /** @test */

    public function test_api_user_can_signup()
    {
        $response = $this->postJson('/api/signup', [
            'first_name' => 'David',
            'last_name' => 'Doe',
            'email' => 'david@example.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
            'phone' => '1234567890',
            'address' => '123 Street, City, Country'
        ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'User registered successfully!']);

        $this->assertDatabaseHas('users', ['email' => 'david@example.com']);
    }

    /**
     * Test for user cannot sign up with invalid data
     */
    public function test_api_user_cannot_signup_with_invalid_data()
    {
        $response = $this->postJson('/api/signup', [
            'first_name' => '',
            'last_name' => 'Doe',
            'email' => 'invalid-email', 
            'password' => 'short', 
            'password_confirmation' => 'short',
            'phone' => '123', 
            'address' => 'Test Address'
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    /**
     * Test that user cannot sign up if passwords do not match
     */
    public function test_api_user_cannot_signup_with_mismatched_passwords()
    {
        $response = $this->postJson('/api/signup', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'password' => 'test1234',
            'password_confirmation' => 'wrongpassword', 
            'phone' => '1234567890',
            'address' => '123 Street, City, Country'
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Password mismatch!']);
    }

    /**
     * Test that user cannot sign up with an already existing email
     */
    public function test_api_user_cannot_signup_with_existing_email()
    {
        User::factory()->create([
            'email' => 'existinguser@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/signup', [
            'first_name' => 'David',
            'last_name' => 'Doe',
            'email' => 'david@example.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
            'phone' => '1234567890',
            'address' => '123 Street, City, Country'
        ]);

        $response->assertStatus(422) 
                 ->assertJsonStructure(['errors']);
    }

     /**
     * Test that an authenticated user can successfully log out
     */
    public function test_api_user_can_logout()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully logged out']);

        $this->assertEquals(0, $user->tokens()->count());
    }

    /**
     * Test that an unauthenticated user cannot log out
     */
    public function test_api_unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}
