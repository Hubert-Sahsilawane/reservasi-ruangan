<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
{
    $search = $request->get('search');
    $role = $request->get('role');
    $page = (int) max(1, $request->get('page', 1)); // pagination manual
    $perPage = 10;

    // 🎭 Validasi role
    if (!empty($role)) {
        $validRoles = ['admin', 'superadmin', 'karyawan'];
        if (!in_array($role, $validRoles)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Role tidak ada.',
                'data'    => null,
            ], 400);
        }
    }

    // 🔍 Ambil data (urut berdasarkan id ASC)
    $filters = compact('search', 'role');
    $query = \App\Models\User::with('roles')
        ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
        ->when($role, fn($q) => $q->whereHas('roles', fn($q2) => $q2->where('name', $role)))
        ->orderBy('id', 'asc'); // 🔁 urutkan ID ASC

    $total = $query->count();
    $users = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

    if ($users->isEmpty()) {
        return response()->json([
            'status'  => 'success',
            'message' => 'Data tidak ditemukan.',
            'data'    => [],
        ]);
    }

    $from = ($page - 1) * $perPage + 1;
    $to = min($from + $users->count() - 1, $total);

    return response()->json([
        'status'  => 'success',
        'message' => "Daftar user berhasil diambil (halaman {$page}, data {$from}-{$to}).",
        'data'    => UserResource::collection($users),
    ]);
}




    // 📌 Admin membuat user baru
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated());
        return (new UserResource($user))
            ->additional(['message' => 'User created successfully']);
    }

    // 📌 Detail user
    public function show($id)
    {
        return new UserResource($this->userService->find($id));
    }

    // 📌 Admin update data user
    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userService->update($id, $request->validated());
        return (new UserResource($user))
            ->additional(['message' => 'User updated successfully']);
    }

    // 📌 Hapus user
    public function destroy($id): JsonResponse
    {
        $this->userService->delete($id);
        return response()->json(['message' => 'User deleted successfully']);
    }
}
