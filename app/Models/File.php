<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\File
 *
 * @property int $id
 * @property string $name
 * @property string $path
 * @property int $user_id
 * @property int|null $directory_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Directory|null $directories
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File query()
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereDirectoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUserId($value)
 * @mixin \Eloquent
 */
class File extends Model
{
    use HasFactory;

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function directories(): HasOne
    {
        return $this->hasOne(Directory::class);
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     * @param User $user
     * @param Directory|null $directory
     * @param string $filename
     * @return bool
     */
    public function saveFile(\Illuminate\Http\UploadedFile $file, User $user, Directory $directory = null, string $filename='')
    {
        $path = $user->email . '/' . ($directory ? $directory->name . '/' : '');
        $name = $filename ?: $file->getClientOriginalName();
        $uploaded = Storage::disk('cloud')->putFileAs($path, $file, $filename);
        if ($uploaded)
        {
            $this->name = $name;
            $this->path = $path;
            $this->size = $file->getSize();
            $user->files()->save($this);
            $directory?->files()->save($this);
            return $this->save();
        }
        return false;
    }

    /**
     * @param string $filename
     * @param User $user
     * @param Directory|null $directory
     * @return File|\Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getUserFile(string $filename, User $user, Directory $directory = null)
    {
        $path = $user->email . '/' . ($directory ? $directory->name . '/' : '');
        return File::where('path', $path)
            ->where('name', $filename)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * @param string $newName
     * @return bool
     */
    public function rename(string $newName): bool
    {
        Storage::disk('cloud')->move($this->path.$this->name, $this->path.$newName);
        $this->name = $newName;
        return $this->save();
    }

    /**
     * @return bool|null
     */
    public function delete(): ?bool
    {
        Storage::disk('cloud')->delete($this->path.$this->name);
        return parent::delete();
    }

    public static function totalSize(): float
    {
        $files = self::all()->toArray();
        $total = 0;
        foreach ($files as $file)
        {
            $total += $file->size;
        }
        return $total/(1024*1024);
    }
}
