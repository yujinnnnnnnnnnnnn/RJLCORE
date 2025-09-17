<?php

declare(strict_types=1);

namespace App;

use FPDF;

final class PdfService
{
    public static function receipt(array $sale, array $items): string
    {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 10, 'Appliances Store - Receipt', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(190, 8, 'Sale ID: ' . ($sale['id'] ?? ''), 0, 1);
        $pdf->Cell(190, 8, 'Customer: ' . ($sale['customer_name'] ?? ''), 0, 1);
        $pdf->Ln(4);
        // Table header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 8, 'Product', 1);
        $pdf->Cell(30, 8, 'Qty', 1, 0, 'R');
        $pdf->Cell(30, 8, 'Price', 1, 0, 'R');
        $pdf->Cell(30, 8, 'Subtotal', 1, 1, 'R');
        $pdf->SetFont('Arial', '', 12);
        foreach ($items as $it) {
            $pdf->Cell(100, 8, $it['name'] ?? '', 1);
            $pdf->Cell(30, 8, (string)($it['quantity'] ?? 0), 1, 0, 'R');
            $pdf->Cell(30, 8, number_format((float)($it['unit_price'] ?? 0), 2), 1, 0, 'R');
            $pdf->Cell(30, 8, number_format((float)($it['subtotal'] ?? 0), 2), 1, 1, 'R');
        }
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(160, 8, 'Total', 1);
        $pdf->Cell(30, 8, number_format((float)($sale['total_amount'] ?? 0), 2), 1, 1, 'R');

        $outputPath = dirname(__DIR__) . '/storage/receipts';
        if (!is_dir($outputPath)) {
            @mkdir($outputPath, 0775, true);
        }
        $file = $outputPath . '/receipt_' . ($sale['id'] ?? 'temp') . '.pdf';
        $pdf->Output('F', $file);
        return $file;
    }
}

