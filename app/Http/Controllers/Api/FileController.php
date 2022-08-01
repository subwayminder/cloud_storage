<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Directory;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rules\File as FileValidation;

class FileController extends BaseController
{
    const SEPARATOR = '/';

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function upload(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'file' => [
                'required',
                FileValidation::types(config('cloud.allowed_types'))->max(1024 * 1024 * 2)
            ],
            'directory' => 'string',
            'filename' => 'string|required',
        ]);
        if ($user->overlimit()) return response()->json(['success' => false, 'error' => 'Cloud size overlimit']);
        $filename = Arr::get($data, 'filename') . '.' . $request->file('file')->getClientOriginalExtension();

        $directory = null;
        if (Arr::get($data, 'directory')) {
            $directory = Directory::where('name', Arr::get($data, 'directory'))
                ->where('user_id', $user->id)->first();
            if (!$directory) return response()->json(['success' => false, 'error' => 'Directory does not exists']);
        }

        $file = File::getUserFile($filename, $user, $directory);
        if (!$file) {
            if ((new File())->saveFile($request->file('file'), $user, $directory, $filename)) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'error' => 'File is not saved']);
            }
        }
        return response()->json([
            'success' => false,
            'error' => 'File is already exists. Upload another file, specify filename or choose another directory'
        ]);
    }

    /**
     * @param User $user
     * @param string $filename
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request, User $user): JsonResponse|StreamedResponse
    {
        $data = $request->validate([
            'directory' => 'string',
            'name' => 'string|required'
        ]);
        $path = $user->email . self::SEPARATOR . (Arr::get($data, 'directory') ? Arr::get($data, 'directory') . self::SEPARATOR : '');
        try {
            return Storage::disk('cloud')
                ->download($path . self::SEPARATOR . $data['name']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Download file error',
                'errors' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function rename(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'directory' => 'string',
            'name' => 'string|required',
            'new_name' => 'string|required',
        ]);
        $directory = null;
        if (Arr::get($data, 'directory')) {
            $directory = Directory::where('name', Arr::get($data, 'directory'))
                ->where('user_id', $user->id)->first();
            if (!$directory) return response()->json(['success' => false, 'error' => 'Directory does not exists']);
        }

        $file = File::getUserFile($data['name'], $user, $directory);
        if ($file && $file->rename($data['new_name']))
        {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'error' => 'File is not renamed. Probably wrong path',
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function delete(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'directory' => 'string',
            'name' => 'string|required'
        ]);
        $directory = null;
        if (Arr::get($data, 'directory')) {
            $directory = Directory::where('name', Arr::get($data, 'directory'))
                ->where('user_id', $user->id)->first();
            if (!$directory) return response()->json(['success' => false, 'error' => 'Directory does not exists']);
        }

        $file = File::getUserFile($data['name'], $user, $directory);
        if ($file && $file->delete())
        {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Deleting file error',
        ]);
    }

}
