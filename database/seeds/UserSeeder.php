<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'password' => password_hash('password', PASSWORD_BCRYPT)
            ]
        ];

        $this->table('users')->insert($data)->save();
    }
}
