<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->call(AbsensiTableSeeder::class);
        $this->call(AbsensiUserTableSeeder::class);
        $this->call(BanksTableSeeder::class);
        $this->call(BarangHargasTableSeeder::class);
        $this->call(BarangsTableSeeder::class);
        $this->call(CacheTableSeeder::class);
        $this->call(CacheLocksTableSeeder::class);
        $this->call(CustomersTableSeeder::class);
        $this->call(DeliveryNoteDetailsTableSeeder::class);
        $this->call(DeliveryNotesTableSeeder::class);
        $this->call(FailedJobsTableSeeder::class);
        $this->call(IncomingBarangsTableSeeder::class);
        $this->call(InvoiceDeliveryNotesTableSeeder::class);
        $this->call(InvoiceDetailsTableSeeder::class);
        $this->call(InvoiceOngkirsTableSeeder::class);
        $this->call(InvoicesTableSeeder::class);
        $this->call(JobBatchesTableSeeder::class);
        $this->call(JobsTableSeeder::class);
        $this->call(KasTableSeeder::class);
        $this->call(MigrationsTableSeeder::class);
        $this->call(MutasiBarangsTableSeeder::class);
        $this->call(OrderDetailsTableSeeder::class);
        $this->call(OrdersTableSeeder::class);
        $this->call(PaymentDetailsTableSeeder::class);
        $this->call(PaymentsTableSeeder::class);
        $this->call(PremiHadirTableSeeder::class);
        $this->call(PremiUserTableSeeder::class);
        $this->call(SessionsTableSeeder::class);
        $this->call(SewaKendaraanTableSeeder::class);
        $this->call(SuppliersTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(VouchersTableSeeder::class);
    }
}
