<?php

namespace App\Models;

use Common\Auth\BaseUser;
use Common\Workspaces\Workspace;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class User
 *
 * @property int    $id
 * @property string $email
 *
 * @package App\Models
 * @date    02/01/2026
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class User extends BaseUser
{
    use HasApiTokens;

    protected bool $gravatarEnabled = false;

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function routeNotificationForFcm(): string|array|null
    {
        return $this->fcmTokens()
                    ->get()
                    ->pluck('token')
                    ->toArray();
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    public function loadFcmToken(): ?string
    {
        if ($this->currentAccessToken()) {
            $token = $this->fcmTokens()
                          ->where('device_id',
                              $this->currentAccessToken()->name)
                          ->first()->token ?? null;
            $this['fcm_token'] = $token;
            return $token;
        }
        return null;
    }
}
