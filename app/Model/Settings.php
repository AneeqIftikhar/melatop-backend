<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = ['beginner_rate', 'intermediate_rate', 'expert_rate','min_payment','beginner_threshold','intermediate_threshold','expert_threshold', 'created_at', 'updated_at'];
}
