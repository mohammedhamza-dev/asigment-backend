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
            // Validate user input including password confirmation
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
    
            // Sanitize inputs to prevent XSS (if needed)
            $cleanName = strip_tags($request->input('name'));
            $cleanEmail = filter_var($request->input('email'), FILTER_SANITIZE_EMAIL);
            $cleanPassword = htmlspecialchars($request->input('password'));
    
            // Create a new user
            $user = User::create([
                'name' => $cleanName,
                'email' => $cleanEmail,
                'password' => Hash::make($cleanPassword),
            ]);
    
            // Generate email verification token
            $verificationToken =  random_int(1000, 9999);// Adjust length as needed
    
            // Store the token in the database
            DB::table('email_verifications')->updateOrInsert(
                ['email' => $cleanEmail],
                ['token' => $verificationToken, 'created_at' => now()]
            );
    
           
            // Return success message
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
            // Validate user input with custom error messages
           // Validate user input with custom error messages
           $request->validate([
              'email' => 'required|email|exists:users,email',
              'password' => [
                  'required',
                  function ($attribute, $value, $fail) use ($request) {
                      // Get the user
                      $user = User::where('email', $request->email)->first();
  
                      // Check if the user exists and if the password is correct
                      if (!$user || !Hash::check($value, $user->password)) {
                          return $fail('Incorrect password.'); // Custom message for incorrect password
                      }
                  },
              ],
          ], [
              'email.required' => 'Please provide your email address.',
              'email.email' => 'Please enter a valid email address for the email field.',
              'email.exists' => 'Email not found.',
          ]);
            // Check if the user exists
            $user = User::where('email', $request->email)->first();
    
            if (!$user) {
                return response(['message' => 'Email not found.'], Response::HTTP_UNAUTHORIZED);
            }
    
            // Attempt authentication
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                // Create token with expiration
                $token = $user->createToken('token', ['*'], now()->addHours(24))->plainTextToken;
    
                // Create secure cookie
                return response([
                    'user' => $user,
                    'token' => $token,
                ])->cookie('jwt', $token, 1440, null, null, true, true); // 1440 minutes = 24 hours
            }
    
            // If authentication fails (incorrect password)
            return response(['message' => 'Invalid password.'], Response::HTTP_UNAUTHORIZED);
    
        } catch (ValidationException $e) {
            // Handle validation errors
            $errors = $e->errors();
            $errorMessages = [];
    
            // Check for email validation errors
            if (isset($errors['email'])) {
                $errorMessages['email'] = $errors['email'][0];
            }
    
            // Check for password validation errors
            if (isset($errors['password'])) {
                $errorMessages['password'] = $errors['password'][0];
            }
    
            // Return validation errors to the user
            return response(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
    
        } catch (\Exception $e) {
            // Handle other exceptions and return an appropriate response
            return response(['error' => 'An error occurred while processing your request. Please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function resendVerificationEmail(Request $request)
    {
        try {
            // Validate the input
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ], [
                'email.required' => 'email_required',
                'email.exists' => 'email_not_found',
            ]);
    
            // Sanitize email input
            $cleanEmail = filter_var($request->input('email'), FILTER_SANITIZE_EMAIL);
    
            // Find the user
            $user = User::where('email', $cleanEmail)->first();
    
            // Check if the email is already verified
            if ($user->email_verified_at !== null) {
                return response()->json(['message' => 'Email is already verified.'], 400);
            }
    
            // Generate a new email verification token
            $verificationToken = random_int(1000, 9999); // Adjust length as needed
    
            // Store the token in the database
            DB::table('email_verifications')->updateOrInsert(
                ['email' => $cleanEmail],
                ['token' => $verificationToken, 'created_at' => now()]
            );
    
            // Send the verification email directly
            Mail::to($cleanEmail)->send(new EmailVerificationMail($verificationToken));
    
            // Return success message
            return response()->json([
                'message' => 'Verification email resent. Please check your email.',
            ]);
    
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json(['errors' => $e->errors()], 422);
    
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['error' =>$e], 500);
        }
    }
   
    
    public function verifyEmail(Request $request)
    {


        try{
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
            ]);
    
            $emailVerification = EmailVerification::where('email', $request->email)
                                    ->where('token', $request->token)
                                    ->first();
    
            if (!$emailVerification) {
                return response()->json(['message' => 'Invalid token or email.'], 422);
            }
    
            // Find the user by email
            $user = User::where('email', $request->email)->first();
    
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }
    
            // Mark the email as verified
            $user->email_verified_at = now(); // Laravel's built-in timestamp for email verification
            $user->save();
    
            // Delete the email verification record
            EmailVerification::where('email', $request->email)->delete();
    
            return response()->json(['message' => 'Email has been verified.']);

        }
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Invalid input.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the exception message for debugging purposes
    
            return response()->json(['message' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }


 
}
