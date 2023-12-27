<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ebook\EbookFormat;
use Illuminate\Database\Seeder;

final class EbookFormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EbookFormat::insert([
            ['id' => 1, 'name' => 'PDF'],
            ['id' => 2, 'name' => 'EPUB'],
        ]);
    }
}
