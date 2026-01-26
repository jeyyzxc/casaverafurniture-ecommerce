<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {email=admin@casavera.com} {password=password}';
    protected $description = 'Reset admin password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $admin = DB::table('admins')->where('email', $email)->first();

        if (!$admin) {
            $this->error("Admin with email {$email} not found!");
            return 1;
        }

        DB::table('admins')
            ->where('email', $email)
            ->update(['password' => Hash::make($password)]);

        $this->info("Password for {$email} has been reset to: {$password}");
        return 0;
    }
}
