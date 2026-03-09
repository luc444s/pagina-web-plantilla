<?php

declare(strict_types=1);

namespace WpSmartTranslate\Cache;

use wpdb;
use WpSmartTranslate\Core\Config;

final class DatabaseCache implements CacheInterface
{
    private Config $config;
    private CacheKeyGenerator $keyGenerator;
    private string $table;

    public function __construct(Config $config, ?CacheKeyGenerator $keyGenerator = null)
    {
        global $wpdb;

        $this->config = $config;
        $this->keyGenerator = $keyGenerator ?: new CacheKeyGenerator();
        $this->table = $wpdb->prefix . 'wst_translations';

        $this->maybeCreateTable($wpdb);
    }

    public function get(string $text, string $source, string $target): ?string
    {
        global $wpdb;

        $hash = $this->keyGenerator->generate($text, $source, $target);
        $sql = $wpdb->prepare(
            "SELECT translated_text FROM {$this->table} WHERE text_hash = %s LIMIT 1",
            $hash
        );
        $result = $wpdb->get_var($sql);

        return is_string($result) ? $result : null;
    }

    public function set(string $text, string $source, string $target, string $translation): bool
    {
        global $wpdb;

        $hash = $this->keyGenerator->generate($text, $source, $target);

        $data = [
            'text_hash' => $hash,
            'source_lang' => $source,
            'target_lang' => $target,
            'original_text' => $text,
            'translated_text' => $translation,
            'updated_at' => current_time('mysql', true),
        ];

        $formats = ['%s', '%s', '%s', '%s', '%s', '%s'];

        $result = $wpdb->replace($this->table, $data, $formats);

        return is_int($result) && $result > 0;
    }

    private function maybeCreateTable(wpdb $wpdb): void
    {
        $installed = get_option('wst_cache_table_installed', '0') === '1';
        if ($installed) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            text_hash CHAR(64) NOT NULL,
            source_lang VARCHAR(10) NOT NULL,
            target_lang VARCHAR(10) NOT NULL,
            original_text LONGTEXT NOT NULL,
            translated_text LONGTEXT NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY text_hash (text_hash),
            KEY lang_pair (source_lang, target_lang)
        ) {$charset};";

        dbDelta($sql);

        update_option('wst_cache_table_installed', '1', false);
    }
}
