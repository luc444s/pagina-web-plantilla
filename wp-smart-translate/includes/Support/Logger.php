<?php

declare(strict_types=1);

namespace WpSmartTranslate\Support;

final class Logger
{
    private bool $debug;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public function debug(string $message): void
    {
        if ($this->debug) {
            error_log('[WP Smart Translate][DEBUG] ' . $message); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        }
    }

    public function warning(string $message): void
    {
        error_log('[WP Smart Translate][WARN] ' . $message); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
    }
}
