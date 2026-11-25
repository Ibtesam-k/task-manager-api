<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    public function __construct(
        protected UserService $userService
    ) {}


    public function destroy(Request $request)
    {
        $user = $request->user();
        $this->userService->destroy($user);
        return response()->json([
            'message' => 'User Account deleted successfully',
        ]);    }
}
