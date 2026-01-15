<?php

namespace App\Console\Commands;

use App\Models\ProductItem;
use App\Services\QrCodeService;
use Illuminate\Console\Command;

class RegenerateQrCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr-codes:regenerate {--tenant_id= : Regenerate only for specific tenant} {--force : Force regeneration even if QR code already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate QR codes for existing product items to use the new format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant_id');
        $force = $this->option('force');

        $query = ProductItem::query();

        if ($tenantId) {
            $query->whereHas('product', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
            $this->info("Regenerating QR codes for tenant ID: {$tenantId}");
        } else {
            $this->info('Regenerating QR codes for all tenants');
        }

        if (!$force) {
            // Only regenerate items that don't have the new format
            $query->where(function($q) {
                $q->whereNot('qr_code', 'like', 'ITEM-%')
                  ->orWhereRaw('LENGTH(qr_code) != 21'); // ITEM- + 12 digits + - + 4 chars = 21 chars
            });
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->info('No items found that need QR code regeneration.');
            return;
        }

        $this->info("Found {$items->count()} items to regenerate QR codes for.");
        $this->newLine();

        if (!$this->confirm('Do you want to proceed with regenerating QR codes?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        $regenerated = 0;
        $errors = 0;

        foreach ($items as $item) {
            try {
                $oldQrCode = $item->qr_code;
                $newQrCode = QrCodeService::generateUniqueQrCode();

                $item->update(['qr_code' => $newQrCode]);

                $this->newLine();
                $this->info("Updated: {$oldQrCode} → {$newQrCode} (Product: {$item->product->name})");

                $regenerated++;
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Failed to update item ID {$item->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("QR code regeneration completed!");
        $this->info("Regenerated: {$regenerated} items");
        if ($errors > 0) {
            $this->error("Errors: {$errors} items");
        }

        return Command::SUCCESS;
    }
}
