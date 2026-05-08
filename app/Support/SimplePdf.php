<?php

namespace App\Support;

class SimplePdf
{
    public static function tableReport(string $title, array $summary, array $headers, array $rows): string
    {
        $pages = [];
        $current = [];

        $current[] = ['text' => $title, 'size' => 16];
        $current[] = ['text' => '', 'size' => 9];

        foreach ($summary as $label => $value) {
            $current[] = ['text' => "{$label}: {$value}", 'size' => 10];
        }

        $current[] = ['text' => '', 'size' => 9];
        $headerLine = implode(' | ', $headers);
        $separator = str_repeat('-', min(strlen($headerLine), 130));
        $current[] = ['text' => $headerLine, 'size' => 8];
        $current[] = ['text' => $separator, 'size' => 8];

        foreach ($rows as $row) {
            if (count($current) >= 37) {
                $pages[] = $current;
                $current = [
                    ['text' => $title . ' (continued)', 'size' => 14],
                    ['text' => '', 'size' => 9],
                    ['text' => $headerLine, 'size' => 8],
                    ['text' => $separator, 'size' => 8],
                ];
            }

            $current[] = ['text' => implode(' | ', $row), 'size' => 8];
        }

        if ($current !== []) {
            $pages[] = $current;
        }

        return self::render($pages);
    }

    private static function render(array $pages): string
    {
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';

        $kids = [];
        $objectId = 4;

        foreach ($pages as $page) {
            $pageObjectId = $objectId++;
            $contentObjectId = $objectId++;
            $kids[] = "{$pageObjectId} 0 R";

            $content = self::contentStream($page);
            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObjectId} 0 R >>";
            $objects[$contentObjectId] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private static function contentStream(array $lines): string
    {
        $content = '';
        $y = 555;

        foreach ($lines as $line) {
            $text = self::escape($line['text']);
            $size = $line['size'];
            $content .= "BT /F1 {$size} Tf 36 {$y} Td ({$text}) Tj ET\n";
            $y -= $size >= 14 ? 22 : 14;
        }

        return $content;
    }

    private static function escape(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
