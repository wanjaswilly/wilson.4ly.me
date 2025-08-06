<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateProjectsTable
{
    public function up()
    {

        Capsule::schema()->create('projects', function ($table) {
            $table->id();
                        
            $table->timestamps();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('projects');
    }
}