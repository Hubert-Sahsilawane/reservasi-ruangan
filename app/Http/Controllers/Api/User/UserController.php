<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    try {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Token tidak valid atau kadaluarsa.',
                'data'    => null,
            ], 422);
        }

        // ✅ Gunakan $filters seperti RoomController
        $filters = [
            'page'          => $request->query('page', 1),
            'per_page'      => $request->query('per_page', 10),
            'search'   => $request->query('search'),
            'role'     => $request->query('role'),
        ];

        // ✅ Validasi nilai per_page
        if ($filters['per_page'] <= 0) {
            $filters['per_page'] = 10;
        }

        // ✅ Validasi role
        if (!empty($filters['role'])) {
            $validRoles = ['admin', 'karyawan'];
            if (!in_array(strtolower($filters['role']), $validRoles)) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Role tidak ada. Gunakan "admin", atau "karyawan".',
                    'data'    => null,
                ], 422);
            }
        }

        // ✅ Query data user (urut ID ASC)
        $query = \App\Models\User::with('roles')
            ->when($filters['search'], fn($q) => $q->where('name', 'like', "%{$filters['search']}%"))
            ->when($filters['role'], fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $filters['role'])))
            ->orderBy('id', 'asc');

        $total = $query->count();
        $users = $query
            ->skip(($filters['page'] - 1) * $filters['per_page'])
            ->take($filters['per_page'])
            ->get();

        // ✅ Jika tidak ada data
        if ($users->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data tidak ditemukan.',
                'data'    => [],
                'meta'    => [
                    'current_page' => $filters['page'],
                    'per_page'     => $filters['per_page'],
                    'total'        => 0,
                    'last_page'    => 1,
                    'from'         => null,
                    'to'           => null,
                ],
            ], 200);
        }

        $from = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $to   = min($from + $users->count() - 1, $total);

        // ✅ Response sukses (format seragam)
        return response()->json([
            'status'  => 'success',
            'message' => 'Data user berhasil diambil.',
            'data'    => UserResource::collection($users),
            'meta'    => [
                'current_page' => $filters['page'],
                'per_page'     => $filters['per_page'],
                'total'        => $total,
                'last_page'    => (int) ceil($total / $filters['per_page']),
                'from'         => $from,
                'to'           => $to,
            ],
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
            'data'    => null,
        ], 500);
    }
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
