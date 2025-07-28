<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CreateBlogPostsTable
{
    public function up()
    {
        Capsule::schema()->create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('featured_image')->nullable();
            $table->text('excerpt')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index('slug');
            $table->index('is_published');
            $table->index('published_at');
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('blog_posts');
    }
}