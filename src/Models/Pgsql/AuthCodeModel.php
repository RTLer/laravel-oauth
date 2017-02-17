<?php

namespace RTLer\Oauth2\Models\Pgsql;

use Illuminate\Database\Eloquent\Model;

class AuthCodeModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_auth_codes';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expire_time',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'client_id',
        'expire_time',
        'user_id',
    ];
}
