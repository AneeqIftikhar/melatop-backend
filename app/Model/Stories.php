<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $category
 * @property string $link
 * @property string $image
 * @property string $title
 * @property string $created_at
 * @property string $updated_at
 */
class Stories extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['category', 'link', 'image', 'title', 'created_at', 'updated_at'];

}
