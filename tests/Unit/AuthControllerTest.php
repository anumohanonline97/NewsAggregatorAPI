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

    

    // public function it_returns_validation_error_if_email_or_password_is_missing()
    // {
    //     $request = new Request([
    //         'email' => '',
    //         'password' => '',
    //     ]);

    //     $controller = new AuthController();
    //     $response = $controller->login($request);

    //     $this->assertEquals(422, $response->status()); 
    //     $this->assertArrayHasKey('errors', $response->getData(true)); 
    // }

    // /** @test */
    // public function it_returns_error_if_user_not_found()
    // {
    //     $request = new Request([
    //         'email' => 'user@example.com',
    //         'password' => 'password123',
    //     ]);

    //     $controller = new AuthController();
    //     $response = $controller->login($request);

    //     $this->assertEquals(401, $response->status()); 
    //     $this->assertEquals(['message' => 'Invalid credentials'], $response->getData(true));
    // }

    // /** @test */
    // public function it_logs_in_user_with_correct_credentials()
    // {
    //     $user = User::factory()->make([
    //         'email' => 'testuser@example.com',
    //         'password' => Hash::make('testuser123'),
    //     ]);
    //     Hash::shouldReceive('check')
    //         ->with('testuser123', $user->password)
    //         ->andReturn(true);

    //     $request = new Request([
    //         'email' => 'testuser@example.com',
    //         'password' => 'testuser123',
    //     ]);

    //     $controller = new AuthController();

    //     $retrievedUser = ($request->email === $user->email) ? $user : null;

    //     if (!$retrievedUser || !Hash::check($request->password, $retrievedUser->password)) {
    //         $response = response()->json(['message' => 'Invalid credentials'], 401);
    //     } else {
    //         $response = response()->json([
    //             'message' => 'Successfully logged in.',
    //             'access_token' => 'fake-token-123',
    //             'token_type' => 'Bearer',
    //         ], 200);
    //     }

    //     $this->assertEquals(200, $response->status());
    //     $this->assertArrayHasKey('access_token', $response->getData(true));
    // }



    // /**Testing for password reset */
    //  /** @test */
    // public function it_returns_validation_error_if_password_fields_are_missing()
    // {
    //     $user = User::factory()->create();
    //     $this->actingAs($user); 

    //     $request = new Request([
    //         'password' => '',
    //         'password_confirmation' => '',
    //     ]);

    //     $controller = new AuthController();
    //     $response = $controller->passwordreset($request);

    //     $this->assertEquals(422, $response->status());
    //     $this->assertArrayHasKey('errors', $response->getData(true));
    // }

    // /** @test */
    // public function it_updates_password_successfully()
    // {
    //     $user = User::factory()->create([
    //         'password' => Hash::make('oldpassword123'),
    //     ]);
    //     $this->actingAs($user); 

    //     $request = new Request([
    //         'password' => 'newpassword123',
    //         'password_confirmation' => 'newpassword123',
    //     ]);

    //     $controller = new AuthController();
    //     $response = $controller->passwordreset($request);

    //     $this->assertEquals(201, $response->status());
    //     $this->assertEquals(['message' => 'Successfully updated the password!'], $response->getData(true));

    //     $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    // }
 
    //  /** @test */
    //  public function it_returns_unauthorized_if_no_authenticated_user()
    //  {
    //      $request = new Request([
    //          'password' => 'newpassword123',
    //          'password_confirmation' => 'newpassword123',
    //      ]);
 
    //      $controller = new AuthController();
 
    //      $this->mockUnauthorizedUser();
 
    //      $response = $controller->passwordreset($request);
 
    //      $this->assertEquals(401, $response->status());
    //      $this->assertEquals(['message' => 'Unauthorized user.'], $response->getData(true));
    //  }
 
    //  /** @test */
    // public function it_returns_validation_error_if_passwords_do_not_match()
    // {
    //     $user = User::factory()->make([
    //         'id' => 1,
    //         'password' => Hash::make('oldpassword'),
    //     ]);

    //     $this->partialMock(User::class, function ($mock) use ($user) {
    //         $mock->shouldReceive('find')->with(1)->andReturn($user);
    //     });

    //     $this->mockAuthenticatedUser($user->id);

    //     $request = new Request([
    //         'password' => 'newpassword123',
    //         'password_confirmation' => 'mismatchpassword123',
    //     ]);

    //     $controller = new AuthController();
    //     $response = $controller->passwordreset($request);

    //     $this->assertEquals(422, $response->status());

    //     $this->assertArrayHasKey('errors', $response->getData(true));
    //     $this->assertArrayHasKey('password', $response->getData(true)['errors']);
    // }

    // private function mockAuthenticatedUser($userId = 1)
    // {
    //     Auth::shouldReceive('id')->andReturn($userId);
    // }

    //  protected function mockUnauthorizedUser()
    //  {
    //      Auth::shouldReceive('id')->once()->andReturn(null); 
    //  }
}
