<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Log the request and valid roles to debug
            \Illuminate\Support\Facades\Log::info('Registration attempt with data', [
                'request_data' => $request->all(),
                'valid_roles' => [User::ROLE_STUDENT, User::ROLE_SUPERVISOR, User::ROLE_COMMITTEE_HEAD]
            ]);

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', Password::defaults()],
                'role' => ['required', 'string', Rule::in([User::ROLE_STUDENT, User::ROLE_SUPERVISOR, User::ROLE_COMMITTEE_HEAD])],
                'department' => 'required|string|max:255',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $user
            ], 201);
        } catch (ValidationException $e) {
            // Add detailed debugging information to the response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'debug' => [
                    'provided_role' => $request->input('role'),
                    'valid_roles' => [
                        'STUDENT' => User::ROLE_STUDENT,
                        'SUPERVISOR' => User::ROLE_SUPERVISOR,
                        'COMMITTEE_HEAD' => User::ROLE_COMMITTEE_HEAD
                    ]
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $user = User::where('email', $credentials['email'])->first();
            if()
            // $user = User::first();
            // dd(Hash::make($credentials['password']) .'      '. $user->password);
            if ($user && Hash::check($credentials['password'], $user->password)){
                Auth::login($user);
                $request->session()->regenerate();
                // dd('aa');
                return redirect()->intended(route('dashboard'));
            }


        return back()->withErrors(provider: [
            'email' => 'Invalid credentialsss.',
        ])->onlyInput('email');

        } catch (\Exception $e) {
            return response()->view('errors.500', [], 404);


        }
    }

    /**
     * Logout user (revoke token).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
             // Log out the user
            Auth::logout();


            $request->session()->invalidate();


            $request->session()->regenerateToken();

            return redirect()->route('login');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = User::all();

            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', Password::defaults()],
                'role' => ['required', Rule::in([User::ROLE_STUDENT, User::ROLE_SUPERVISOR, User::ROLE_COMMITTEE_HEAD])],
                'department' => 'required|string|max:255',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'full_name' => 'sometimes|required|string|max:255',
                'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
                'role' => ['sometimes', 'required', Rule::in([User::ROLE_STUDENT, User::ROLE_SUPERVISOR, User::ROLE_COMMITTEE_HEAD])],
                'department' => 'sometimes|required|string|max:255',
            ]);

            if ($request->filled('password')) {
                $request->validate([
                    'password' => ['required', Password::defaults()],
                ]);
                $validated['password'] = Hash::make($request->password);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudents()
    {
        try {
            $students = User::where('role', User::ROLE_STUDENT)->get();

            return response()->json([
                'success' => true,
                'data' => $students
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all supervisors.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupervisors()
    {
        try {
            $supervisors = User::where('role', User::ROLE_SUPERVISOR)->get();

            return response()->json([
                'success' => true,
                'data' => $supervisors
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve supervisors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all committee heads.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommitteeHeads()
    {
        try {
            $committeeHeads = User::where('role', User::ROLE_COMMITTEE_HEAD)->get();

            return response()->json([
                'success' => true,
                'data' => $committeeHeads
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve committee heads',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all departments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartments()
    {
        try {
            $departments = User::select('department')->distinct()->pluck('department');

            return response()->json([
                'success' => true,
                'data' => $departments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
