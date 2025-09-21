<?php

    namespace App\Http\Controllers\Api\User;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\User\UserRequest;
    use App\Http\Resources\User\UserResource;
    use App\Services\User\UserService;

    class UserController extends Controller
    {
        protected $userService;

        public function __construct(UserService $userService)
        {
            $this->userService = $userService;
        }

        public function index()
        {
            return UserResource::collection($this->userService->getAll());
        }

        public function store(UserRequest $request)
        {
            $user = $this->userService->create($request->validated());
            return new UserResource($user);
        }

        public function show($id)
        {
            return new UserResource($this->userService->find($id));
        }

        public function update(UserRequest $request, $id)
        {
            $user = $this->userService->update($id, $request->validated());
            return new UserResource($user);
        }

        public function destroy($id)
        {
            $this->userService->delete($id);
            return response()->json(['message' => 'User deleted successfully']);
        }
    }
