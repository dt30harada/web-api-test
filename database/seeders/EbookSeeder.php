<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ebook\Ebook;
use Illuminate\Database\Seeder;

final class EbookSeeder extends Seeder
{
    public function run()
    {
        Ebook::unguard();
        Ebook::factory()->count(30)->create();
        Ebook::reguard();
    }
}
