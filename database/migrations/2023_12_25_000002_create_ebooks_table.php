<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('author', 100);
            $table->string('title', 100);
            $table->string('publisher', 50);
            $table->unsignedInteger('price');
            $table->text('description');
            $table->string('isbn', 13)->unique();
            $table->date('published_date');
            $table->unsignedTinyInteger('format_id');
            $table->datetimes();

            $table->foreign('format_id')
                ->references('id')
                ->on('ebook_formats')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
};
