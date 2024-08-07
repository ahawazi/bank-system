<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:data {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'user data command';

    public function handle()
    {
        if ($this->argument('id')) {
            
            $user = User::whereId($this->argument('id'))
                ->get(['id', 'name', 'email']); //array

            if (count($user) > 0) {
                $this->table(['id', 'name', 'email'], $user);
            }else{
                $this->error('user not found');
            }

        }else {
            $users = User::get(['id', 'name', 'email']);

            $this->table(['id', 'name', 'email'], $users);

            $this->info('User Data');
            }
        }
}
