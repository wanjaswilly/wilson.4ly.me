<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateMessagesTable
{
    public function up()
    {

        Capsule::schema()->create('messages', function ($table) {
            $table->id();
            
            $table->string('name', 100);
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('messages');
    }
}