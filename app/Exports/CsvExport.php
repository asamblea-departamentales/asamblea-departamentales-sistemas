<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class CsvExport
{
    public static function download(Collection $data, array $headers, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $callback = function () use ($data, $headers) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers, ';');

            foreach ($data as $row) {
                fputcsv($row->toArray(), $headers, ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
