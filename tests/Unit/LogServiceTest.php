<?php

namespace Tests\Unit;

use App\Services\LogService;
use Tests\TestCase;

class LogServiceTest extends TestCase
{
    private const TEST_LOG_FILE = 'test-log.log';

    private LogService $logService;

    public function test_log_file_is_created_and_message_written(): void
    {
        $this->logService->write(self::TEST_LOG_FILE, 'Test message');

        $path = storage_path('logs/' . self::TEST_LOG_FILE);
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('Test message', $content);
    }

    public function test_log_file_truncates_when_too_large(): void
    {
        $path = storage_path('logs/' . self::TEST_LOG_FILE);
        file_put_contents($path, str_repeat("Line\n", 12));

        $this->logService->write(self::TEST_LOG_FILE, 'New message', 10, 5);

        $lines = file($path);
        $this->assertLessThanOrEqual(10, count($lines));
        $this->assertStringContainsString('New message', implode('', $lines));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->logService = new LogService();
    }

    protected function tearDown(): void
    {
        $path = storage_path('logs/' . self::TEST_LOG_FILE);
        if (file_exists($path)) {
            unlink($path);
        }

        parent::tearDown();
    }
}
