<?php

namespace App\Services;

use DateTime;

class LogService
{
    public function write(string $fileName, string $data): void
    {
        $filePath = storage_path('logs/' . $fileName);

        $this->ensureLogDirectoryExists();
        $this->ensureLogFileExists($filePath);
        $this->truncateIfTooLarge($filePath);

        $message = (new DateTime())->format('Y:m:d H:i:s') . $data;
        file_put_contents($filePath, $message . "\n\n", FILE_APPEND);
    }

    protected function ensureLogDirectoryExists(): void
    {
        $logDir = storage_path('logs');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    protected function ensureLogFileExists(string $filePath): void
    {
        if (!file_exists($filePath)) {
            touch($filePath);
        }
    }

    protected function truncateIfTooLarge(string $filePath, int $maxLines = 10000, int $trimLines = 5000): void
    {
        $rows = file($filePath);
        if (count($rows) >= $maxLines) {
            array_splice($rows, 0, $trimLines);
            file_put_contents($filePath, implode('', $rows));
        }
    }
}
