<?php

use Illuminate\Support\Facades\Broadcast;
use App\Broadcasting\UserChannel;

Broadcast::routes(['middleware' => ['auth:api']]);
Broadcast::channel('users.{userId}', UserChannel::class);
