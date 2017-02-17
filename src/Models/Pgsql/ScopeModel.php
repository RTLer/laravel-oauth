<?php

namespace RTLer\Oauth2\Models\Pgsql;

use Illuminate\Database\Eloquent\Model;

class ScopeModel extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_scopes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'description',
    ];
}
