<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class PdfScannerService
{
    private Parser $parser;

    private array $dangerousPatterns = [
        '/\.bat\b/i',
        '/\.cmd\b/i',
        '/\.exe\b/i',
        '/\.com\b/i',
        '/\.pif\b/i',
        '/\.scr\b/i',
        '/\.vbs\b/i',
        '/\.vbe\b/i',
        '/\.jse\b/i',
        '/\.wsf\b/i',
        '/\.wsh\b/i',
        '/\.msi\b/i',
        '/\.msp\b/i',
        '/\.mst\b/i',
        '/\.hta\b/i',
        '/\.cpl\b/i',
        '/\.lnk\b/i',
        '/\.inf\b/i',
        '/\.reg\b/i',
        '/\.ps1\b/i',
        '/\.psm1\b/i',
        '/\.psd1\b/i',
        '/\.sh\b/i',
        '/\.bash\b/i',
        '/\.py\b/i',
        '/\.rb\b/i',
        '/\.pl\b/i',
        '/\.php\b/i',
    ];

    private array $dangerousContentPatterns = [
        '/Shell\.Application/i',
        '/WScript\.Shell/i',
        '/Scripting\.FileSystemObject/i',
        '/ADODB\.Stream/i',
        '/PowerShell/i',
        '/cmd\.exe/i',
        '/command\.com/i',
        '/\/bin\/sh/i',
        '/\/bin\/bash/i',
        '/eval\s*\(/i',
        '/exec\s*\(/i',
        '/system\s*\(/i',
        '/passthru\s*\(/i',
        '/shell_exec\s*\(/i',
        '/proc_open\s*\(/i',
        '/popen\s*\(/i',
        '/curl\s+/i',
        '/wget\s+/i',
        '/Invoke-Expression/i',
        '/Invoke-Command/i',
        '/DownloadString/i',
        '/DownloadFile/i',
        '/Invoke-WebRequest/i',
        '/Net\.WebClient/i',
        '/URLDownloadToFile/i',
        '/CreateObject/i',
        '/regsvr32/i',
        '/rundll32/i',
    ];

    private array $embeddedFilePatterns = [
        '/\/EmbeddedFile/i',
        '/\/EF\b/i',
        '/\/FileAttachment/i',
        '/\/AssociatedFiles/i',
    ];

    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function scan(string $filePath): array
    {
        $issues = [];

        try {
            $pdf = $this->parser->parseFile($filePath);
            $fullText = $pdf->getText();
        } catch (\Exception $e) {
            return [
                'safe' => false,
                'issues' => ['PDF tidak dapat dibaca atau format tidak valid: ' . $e->getMessage()],
                'raw_scan' => [],
            ];
        }

        $rawContent = $this->getRawPdfContent($filePath);

        $issues = array_merge(
            $issues,
            $this->scanForDangerousFileExtensions($fullText),
            $this->scanForDangerousContent($fullText),
            $this->scanForEmbeddedFiles($rawContent),
            $this->scanRawContentForSuspicious($rawContent),
        );

        $issues = array_unique($issues);

        return [
            'safe' => empty($issues),
            'issues' => $issues,
            'raw_scan' => [
                'text_length' => strlen($fullText),
                'pages' => count($pdf->getPages()),
            ],
        ];
    }

    private function getRawPdfContent(string $filePath): string
    {
        $content = file_get_contents($filePath);

        return $content !== false ? $content : '';
    }

    private function scanForDangerousFileExtensions(string $text): array
    {
        $issues = [];
        $foundExtensions = [];

        foreach ($this->dangerousPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[0] as $match) {
                    $lower = strtolower($match);
                    if (!in_array($lower, $foundExtensions)) {
                        $foundExtensions[] = $lower;
                    }
                }
            }
        }

        if (!empty($foundExtensions)) {
            $issues[] = 'Ditemukan referensi file berbahaya: ' . implode(', ', $foundExtensions);
        }

        return $issues;
    }

    private function scanForDangerousContent(string $text): array
    {
        $issues = [];
        $foundPatterns = [];

        foreach ($this->dangerousContentPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $match = $matches[0];
                if (!in_array($match, $foundPatterns)) {
                    $foundPatterns[] = $match;
                }
            }
        }

        if (!empty($foundPatterns)) {
            $issues[] = 'Ditemukan konten berbahaya: ' . implode(', ', array_slice($foundPatterns, 0, 5));
        }

        return $issues;
    }

    private function scanForEmbeddedFiles(string $rawContent): array
    {
        $issues = [];

        foreach ($this->embeddedFilePatterns as $pattern) {
            if (preg_match($pattern, $rawContent)) {
                $issues[] = 'PDF memiliki embedded file/attachment yang terdeteksi';
                break;
            }
        }

        return $issues;
    }

    private function scanRawContentForSuspicious(string $rawContent): array
    {
        $issues = [];

        if (preg_match('/JavaScript\s*\/\s*true/i', $rawContent)) {
            $issues[] = 'PDF mengandung JavaScript yang diaktifkan';
        }

        if (preg_match('/OpenAction/i', $rawContent)) {
            $issues[] = 'PDF memiliki OpenAction yang bisa menjalankan kode otomatis';
        }

        if (preg_match('/AA\s*\/\s*(A|C|U|WC|WP|PO)\s/i', $rawContent)) {
            $issues[] = 'PDF memiliki Additional Actions yang mencurigakan';
        }

        if (preg_match('/Launch\b/i', $rawContent)) {
            $issues[] = 'PDF memiliki action Launch yang bisa menjalankan aplikasi eksternal';
        }

        return $issues;
    }
}
