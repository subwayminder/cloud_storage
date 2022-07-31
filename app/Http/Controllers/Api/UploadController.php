<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends BaseController
{
    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function uploadFile(Request $request, User $user)
    {
        $result = null;
        if ($request->hasFile('file')) {
            $result = $user->addMedia($request->file('file'))->toMediaCollection();
        }
        return response()->json(['id' => $user->id, 'result' => $result]);
    }

}
