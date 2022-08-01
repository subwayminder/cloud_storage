<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Directory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DirectoryController extends BaseController
{
    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function createDirectory(Request $request, User $user) : JsonResponse
    {
        $data = $request->validate([
            'name' => 'string|required',
        ]);
        $created = Storage::makeDirectory('cloud/'.$user->email.'/'.$data['name'], 644);

        if (Directory::where('name', $data['name'])->where('user_id', $user->id)->first())
            return response()->json(['success' => false, 'error' => 'Directory already exists']);

        if ($created){
            $directory = new Directory([
                'name' => $data['name']
            ]);
            $user->directories()->save($directory);
            $directory->save();
            return response()->json(['success' => true, 'message' => 'Directory '. $data['name'] . ' created']);
        }
        else{
            return response()->json(['success' => false, 'error' => 'Directory creation error']);
        }
    }

    public function directoryTotal(Request $request, User $user)
    {
        $data = $request->validate([
            'directory' => 'string|required',
        ]);

        $directory = Directory::where('name', \Arr::get($data, 'directory'))->where('user_id', $user->id)->first();
        if (!$directory) return response()->json(['success' => false, 'message' => 'Directory is not exists']);
        return response()->json(['total' => $directory->getTotalSize()/(1024 * 1024) . ' MB']);
    }
}
