<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast channel for real-time bookings (accessible by internal panels and outside projects)
Broadcast::channel('bookings', function ($user = null) {
    return true;
});
