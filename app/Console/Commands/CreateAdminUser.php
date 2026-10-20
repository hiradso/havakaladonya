<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Console\Command;

/**
 * Creates (or updates) a full-access admin of the /admin panel:
 *   php artisan user:admin hiradso@gmail.com --name=hiradso
 *
 * The password is asked for on the screen (not shown, not saved in history).
 */
class CreateAdminUser extends Command
{
    protected $signature = 'user:admin {email} {--name= : Display name}';

    protected $description = 'Create or update a full-access admin user';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (min 8 characters)');
        if (mb_strlen($password) < 8) {
            $this->error('Password is too short.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $this->option('name') ?: ($user->name ?: strstr($email, '@', true)),
            'password' => $password,
            'role' => Roles::ADMIN,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated')." admin: {$email}");

        return self::SUCCESS;
    }
}
