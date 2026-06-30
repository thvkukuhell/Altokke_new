<?php

namespace App\Services;

class SimplePdfService
{
    public function generar(string $titulo, array $lineas): string
    {
        $contenido = "BT\n/F1 18 Tf\n50 780 Td\n(" . $this->pdfText($titulo) . ") Tj\n";
        $contenido .= "/F1 11 Tf\n0 -30 Td\n";

        foreach ($lineas as $linea) {
            $contenido .= "(" . $this->pdfText((string) $linea) . ") Tj\n0 -18 Td\n";
        }

        $contenido .= "ET";

        $objetos = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length " . strlen($contenido) . " >>\nstream\n" . $contenido . "\nendstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objetos as $objeto) {
            $offsets[] = strlen($pdf);
            $pdf .= $objeto;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objetos) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objetos); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objetos) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function pdfText(string $texto): string
    {
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
    }
}
