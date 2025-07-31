<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateMessagesTable
{
    public function up()
    {

        Capsule::schema()->create('messages', function ($table) {
            $table->id();
                        
            $table->timestamps();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('messages');
    }
}