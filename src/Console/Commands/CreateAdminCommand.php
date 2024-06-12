<?php

namespace EscolaLms\Auth\Console\Commands;

use Carbon\Carbon;
use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Enums\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escolalms:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin account';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $email = $this->ask('Email', 'admin@escolalms.com');
        $data = [
            'email' => $email,
            'first_name' => 'Admin',
            'last_name' => 'A',
            'password' => $this->askUserPassword(true),
            'is_active' => 1,
            'email_verified_at' => Carbon::now(),
        ];
        try {
            $user = User::create($data);
            Role::findOrCreate(UserRole::ADMIN, 'api');
            $user->assignRole(UserRole::ADMIN);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->line(__(':admin account created and :role roles have been assigned.', [
            'admin' => $user->first_name,
            'role' => UserRole::ADMIN
        ]));
    }

    protected function askUserPassword(bool $required = false)
    {
        $default = $required ? Str::random() : '';
        while (1) {
            $password = $this->ask('Password', $default);
            if (!$required && empty($password)) {
                break;
            }
            $validator = Validator::make([
                'password' => $password
            ], ['password' => User::PASSWORD_RULES]);
            if (!$validator->fails()) {
                break;
            }
            $this->error('This password does not comply with the password security rules.');
        }
        return bcrypt($password);
    }
}
