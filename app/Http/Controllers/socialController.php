<?php

namespace App\Http\Controllers;


use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
class socialController extends Controller
{

    public function redirectToAuth(): JsonResponse
    {
        // Generate the Google login URL
        $loginUrl = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => $loginUrl,
        ]);
    }
    //facebook
  
    public function handleAuthCallback()
    {
        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver('google')->stateless()->user();
        } catch (ClientException $e) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }
    
        /** @var User $user */
        $user = User::query()
            ->firstOrCreate(
                [
                    'email' => $socialiteUser->getEmail(),
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $socialiteUser->getName(),
                    'social_id' => $socialiteUser->getId(),
                    'password' => Hash::make("my-google"),
                ]
            );
    
        $token = $user->createToken('google-token')->plainTextToken;
    
        return redirect()->away('http://localhost:5174/?user=' . $token);
    }
    
  
}
