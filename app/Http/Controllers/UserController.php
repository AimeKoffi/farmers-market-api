<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Admin : liste tous les supervisors
    // Supervisor : liste ses operators
    public function index(): JsonResponse
    {
        $user  = request()->user();
        $query = User::with('supervisor');

        if ($user->isAdmin()) {
            $users = $query->where('role', 'supervisor')->get();
        } else {
            $users = $query->where('supervisor_id', $user->id)->get();
        }

        return response()->json(['success' => true, 'data' => UserResource::collection($users)]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $currentUser = $request->user();

        // Admin crée des supervisors, Supervisor crée des operators
        $role = $currentUser->isAdmin() ? 'supervisor' : 'operator';

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => $role,
            'supervisor_id' => $currentUser->id,
        ]);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource($user->load('supervisor')),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Utilisateur supprimé.']);
    }
}