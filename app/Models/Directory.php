<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Directory
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $files
 * @property-read int|null $files_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Directory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Directory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Directory query()
 * @method static \Illuminate\Database\Eloquent\Builder|Directory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Directory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Directory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Directory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Directory whereUserId($value)
 * @mixin \Eloquent
 */
class Directory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * @return int
     */
    public function getTotalSize(): int
    {
        $files = $this->files()->get()->toArray();
        $total = 0;
        foreach ($files as $file)
        {
            $total += $file['size'];
        }
        return $total;
    }
}
