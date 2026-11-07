<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Hash; // Import the Hash facade

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     * The signature defines how you run the command (e.g., php artisan make:admin).
     *
     * @var string
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user with the "admin" role.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Get User Input
        $name = $this->ask('Enter Admin Name');
        $email = $this->ask('Enter Admin Email');
        
        // Use secret() for password input so it's not visible
        $password = $this->secret('Enter Admin Password');

        // 2. Validate Input (Simple check)
        if (User::where('email', $email)->exists()) {
            $this->error('A user with that email already exists!');
            return Command::FAILURE;
        }

        // 3. Create the Admin User
        try {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password), // Hashing the password is essential
                'role' => 'admin', // Set the role explicitly
            ]);

            $this->info("Admin user '{$name}' created successfully!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Email', $email],
                    ['Role', 'admin']
                ]
            );
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}