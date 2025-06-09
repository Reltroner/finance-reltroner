<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attachment;
use App\Models\Transaction;

class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = Transaction::pluck('id')->toArray();
        foreach ($transactions as $id) {
            Attachment::factory()->create(['transaction_id' => $id]);
        }
    }
}
