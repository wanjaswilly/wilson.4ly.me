<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class CreateProjectsTable
{
    public function up()
    {

        Capsule::schema()->create('projects', function ($table) {
            $table->id();
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('content')->nullable();
            $table->string('image_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('live_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('technologies')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('projects');
    }
}