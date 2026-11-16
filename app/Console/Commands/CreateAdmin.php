<?php

namespace App\Console\Commands;

use App\Actions\Admin\CreateAdmin as ProvisionAdmin;
use App\Support\AdminAccess;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CreateAdmin extends Command
{
    protected $signature = 'trivo:admin-create {--from-env : Read ADMIN_INITIAL_* from the process environment once}';

    protected $description = 'Explicitly provision an admin account';

    public function handle(ProvisionAdmin $provision): int
    {
        $this->call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
        $fromEnv = (bool) $this->option('from-env');
        // These bootstrap secrets intentionally never enter Laravel's cached configuration.
        $name = (string) ($fromEnv ? getenv('ADMIN_INITIAL_NAME') : $this->ask('Name'));
        $email = (string) ($fromEnv ? getenv('ADMIN_INITIAL_EMAIL') : $this->ask('Email address'));
        $password = (string) ($fromEnv ? getenv('ADMIN_INITIAL_PASSWORD') : $this->secret('Password (12+ characters, mixed case, number and symbol)'));
        $role = (string) ($fromEnv ? (getenv('ADMIN_INITIAL_ROLE') ?: 'Super Admin') : $this->choice('Role', AdminAccess::ROLES));
        if (! $fromEnv && $password !== (string) $this->secret('Confirm password')) {
            $this->error('Passwords do not match. No account created.');

            return self::FAILURE;
        }
        try {
            $provision->handle($name, $email, $password, $role);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $errors) {
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }

            return self::FAILURE;
        } finally {
            unset($password);
        }
        $this->info('Admin created. Sign in with your email and password at the configured admin sign-in page.');
        if ($fromEnv) {
            $this->warn('Remove ADMIN_INITIAL_* credentials from the environment before caching configuration.');
        }

        return self::SUCCESS;
    }
}
