<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;

class UserController extends BaseController
{
    public function total(User $user)
    {
        return response()->json(['total' => ($user->getTotalStorageSize() / (1024 * 1024)) . ' MB']);
    }
}
