<?php

namespace RTLer\Oauth2\Models\Pgsql;

use Illuminate\Database\Eloquent\Model;

class RefreshTokenModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_refresh_tokens';

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
        'access_token_id',
        'expire_time',
    ];
}
