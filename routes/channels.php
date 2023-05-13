<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Session;

Broadcast::channel(Session::getId(), function ($user, $id) {
    return (int) $user->id === (int) $id;
});
