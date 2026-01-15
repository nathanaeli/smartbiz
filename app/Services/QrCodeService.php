<?php

namespace App\Services;

use App\Models\ProductItem;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Generate a unique QR code for product items.
     */
    public static function generateUniqueQrCode(): string
    {
        do {
            // Generate a QR code with format: ITEM-{timestamp}-{random}
            $timestamp = now()->format('ymdHis');
            $random = strtoupper(Str::random(4));
            $qrCode = "ITEM-{$timestamp}-{$random}";
        } while (ProductItem::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Generate multiple unique QR codes.
     */
    public static function generateUniqueQrCodes(int $count): array
    {
        $qrCodes = [];

        for ($i = 0; $i < $count; $i++) {
            $qrCodes[] = self::generateUniqueQrCode();
        }

        return $qrCodes;
    }

    /**
     * Validate QR code format.
     */
    public static function isValidQrCodeFormat(string $qrCode): bool
    {
        // QR code should match pattern: ITEM-{timestamp}-{random}
        return preg_match('/^ITEM-\d{12}-[A-Z0-9]{4}$/', $qrCode);
    }

    /**
     * Extract information from QR code.
     */
    public static function parseQrCode(string $qrCode): ?array
    {
        if (!self::isValidQrCodeFormat($qrCode)) {
            return null;
        }

        // Extract timestamp and random part
        $parts = explode('-', $qrCode);
        if (count($parts) !== 3) {
            return null;
        }

        return [
            'prefix' => $parts[0], // ITEM
            'timestamp' => $parts[1], // yymmddhhmmss
            'random' => $parts[2], // A-Z0-9
            'generated_at' => \Carbon\Carbon::createFromFormat('ymdHis', $parts[1]),
        ];
    }

    /**
     * Generate QR code data payload for mobile apps.
     */
    public static function generateQrPayload(ProductItem $item): array
    {
        return [
            'type' => 'product_item',
            'qr_code' => $item->qr_code,
            'product_id' => $item->product_id,
            'product_name' => $item->product->name,
            'product_sku' => $item->product->sku,
            'status' => $item->status,
            'selling_price' => $item->product->selling_price,
            'unit' => $item->product->unit,
            'generated_at' => now()->toISOString(),
        ];
    }
}
