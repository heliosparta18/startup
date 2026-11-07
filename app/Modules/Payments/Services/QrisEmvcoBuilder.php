<?php

namespace App\Modules\Payments\Services;

class QrisEmvcoBuilder
{
    public static function build(string $nmid, string $orderNumber, float $amount): string
    {
        $payload = '000201'; // Payload Format Indicator
        $payload .= '010212'; // Dynamic QR code
        $payload .= '26'.sprintf('%02d', strlen($nmid) + 8).'0004ID.CO'.'01'.sprintf('%02d', strlen($nmid)).$nmid;
        $payload .= '52045912'; // Merchant Category Code (MCC 5912: Apotek)
        $payload .= '5303360';  // Currency Code 360 (IDR)
        $amountFormatted = number_format($amount, 2, '.', '');
        $payload .= '54'.sprintf('%02d', strlen($amountFormatted)).$amountFormatted;
        $payload .= '5802ID';   // Country Code ID
        $payload .= '5916OMNIPHARMA APOTEK'; // Merchant Name
        $payload .= '6011BANYUWANGI'; // Merchant City
        $payload .= '62'.sprintf('%02d', strlen($orderNumber) + 4).'01'.sprintf('%02d', strlen($orderNumber)).$orderNumber;

        // Hitung Checksum CRC16-CCITT (Tag 6304)
        $crcData = $payload.'6304';
        $checksum = strtoupper(self::crc16Ccitt($crcData));

        return $crcData.$checksum;
    }

    public static function crc16Ccitt(string $data): string
    {
        $crc = 0xFFFF;
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return sprintf('%04X', $crc);
    }
}
