<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationMail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/', $value)) {
                            return $fail('Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, and one digit.');
                        }
                    },
            ],
            ], [
                'password.min' => 'Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, and one digit.', // Custom error message for min:8

                'name.required' => 'Name Is Required',
                'email.unique' => 'Email Is Unique',
                'email.required' => 'Email Is Required',
            ]);
    
        $cleanName = strip_tags($request->input('name')); // Removes <script> tags
        $cleanEmail = filter_var($request->input('email'), FILTER_SANITIZE_EMAIL);
        $cleanPassword = htmlspecialchars($request->input('password'), ENT_QUOTES, 'UTF-8');
    
            $user = User::create([
                'name' => $cleanName,
                'email' => $cleanEmail,
                'password' => Hash::make($cleanPassword),
            ]);
    
            // Generate email verification token
            $verificationToken =  random_int(1000, 9999);
    
            // Store the token in the database
            DB::table('email_verifications')->updateOrInsert(
                ['email' => $cleanEmail],
                ['token' => $verificationToken, 'created_at' => now()]
            );
    
           
            return response()->json([
                'message' => 'Registration successful.',
            ]);
    
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json(['errors' => $e->errors()], 422);
    
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['error' => $e], 500);
        }
    }
    
    public function login(Request $request)
    {
        try {
        
           $request->validate([
              'email' => 'required|email|exists:users,email',
              'password' => [
                  'required',
                  function ($attribute, $value, $fail) use ($request) {
                      $user = User::where('email', $request->email)->first();
  
                      if (!$user || !Hash::check($value, $user->password)) {
                          return $fail('Incorrect password.'); 
                      }
                  },
              ],
          ], [
              'email.required' => 'Please provide your email address.',
              'email.email' => 'Please enter a valid email address for the email field.',
              'email.exists' => 'Email not found.',
          ]);
            $user = User::where('email', $request->email)->first();
    
            if (!$user) {
                return response(['message' => 'Email not found.'], Response::HTTP_UNAUTHORIZED);
            }
    
            // Attempt authentication
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                $token = $user->createToken('token', ['*'], now()->addHours(24))->plainTextToken;
    
                return response([
                    'user' => $user,
                    'token' => $token,
                ])->cookie('jwt', $token, 1440, null, null, true, true); // 1440 minutes = 24 hours
            }
    
            return response(['message' => 'Invalid password.'], Response::HTTP_UNAUTHORIZED);
    
        } catch (ValidationException $e) {
            // Handle validation errors
            $errors = $e->errors();
            $errorMessages = [];
    
            if (isset($errors['email'])) {
                $errorMessages['email'] = $errors['email'][0];
            }
    
            if (isset($errors['password'])) {
                $errorMessages['password'] = $errors['password'][0];
            }
    
            return response(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
    
        } catch (\Exception $e) {
            // Handle other exceptions and return an appropriate response
            return response(['error' => 'An error occurred while processing your request. Please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
   
    


 
}
