<?php

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Str;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    //  command name :
    // protected $signature = 'add:user {name} {email}';
    protected $signature = 'add:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $name = $this->ask("what is your name?");
        // $email = $this->ask("what is your email?");
        // $password = $this->secret("what is your password?");

        // if ($this->confirm("do you wish to continue?" . true)) {
        //     $user = new User();
        //     $user->name = $name;
        //     $user->email = $email;
        //     $user->password = $password;
        //     $user->email_verified_at = now();
        //     $user->remember_token = Str::random(10);
        //     $user->save();
        //     $this->info(" $user->name created successful");
        // } else {
        //     $this->warn("you cancceled to create user");
        // }

        // add the user
        $user = User::factory()->create();
        $this->info(" $user->name created successful!");

        // try {
        //     $user = new User();
        //     $user->name = $this->argument("name");
        //     $user->email = $this->argument("email");
        //     $user->password = bcrypt("123456");
        //     $user->email_verified_at = now();
        //     $user->remember_token = Str::random(10);
        //     $user->save();
        //     $this->info(" $user->name created successful");    
        // } catch (\Exception $e) {
        //     $this->error($e->getMessage());
        // }

        // info : return the some green color text
        // $this->info("The commant was successful!");
        // warn : return the some yelow color text
        // $this->warn("this is the warnning text");
        // error : return the some red text
        // $this->error("this is the error text");
        // line : return the some white text
        // $this->line("this is a line");
    }
}
