<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResetPasswordToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'reset_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'role'
            ],
            'reset_expires' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'reset_token'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['reset_token', 'reset_expires']);
    }
}
