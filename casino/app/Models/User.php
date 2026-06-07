<?php

/**
 * NOTE: This file is intentionally kept for Laravel Cashier compatibility.
 * The primary User model for this project is VanguardLTE\User (app/User.php).
 * This file aliases it so that Laravel Cashier (which expects App\Models\User) works correctly.
 */

namespace App\Models;

// Alias to the real VanguardLTE User model
class User extends \VanguardLTE\User
{
    // Inherits everything from VanguardLTE\User
}
