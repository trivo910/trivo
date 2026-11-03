<?php

/** Import only the allowlisted audit export into a new, isolated database. */
declare(strict_types=1);

$source = dirname(__DIR__).'/.audit/legacy-public.json';
$payload = json_decode(file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
$host = getenv('MIGRATION_DB_HOST') ?: '127.0.0.1';
$port = getenv('MIGRATION_DB_PORT') ?: '3306';
$username = getenv('MIGRATION_DB_USERNAME') ?: 'root';
$password = getenv('MIGRATION_DB_PASSWORD') ?: '';
$created = false;

try {
    $connection = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $exists = $connection->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?');
    $exists->execute(['wp_legacy']);
    if ($exists->fetchColumn()) {
        fwrite(STDERR, "wp_legacy already exists. Refusing to overwrite or merge any existing database.\n");
        exit(1);
    }
    $connection->exec('CREATE DATABASE wp_legacy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    $connection->exec('USE wp_legacy');
    $connection->exec('CREATE TABLE audit_import (source VARCHAR(255) NOT NULL, imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
    $connection->exec('CREATE TABLE wp_posts (ID BIGINT UNSIGNED PRIMARY KEY, post_name VARCHAR(255), post_title TEXT, post_parent BIGINT UNSIGNED, post_type VARCHAR(50), post_date DATETIME, post_modified DATETIME, post_content LONGTEXT)');
    $connection->exec('CREATE TABLE wp_attachments (ID BIGINT UNSIGNED PRIMARY KEY, post_title TEXT, post_excerpt TEXT, post_mime_type VARCHAR(100), post_parent BIGINT UNSIGNED)');
    $connection->exec('CREATE TABLE wp_options (option_name VARCHAR(191) PRIMARY KEY, option_value LONGTEXT)');
    $connection->exec('CREATE TABLE wp_postmeta (post_id BIGINT UNSIGNED, meta_key VARCHAR(191), meta_value LONGTEXT, PRIMARY KEY (post_id, meta_key))');
    $connection->exec('CREATE TABLE wp_terms (term_id BIGINT UNSIGNED PRIMARY KEY, name VARCHAR(255), slug VARCHAR(255), term_group BIGINT)');
    $connection->exec('CREATE TABLE wp_term_taxonomy (term_taxonomy_id BIGINT UNSIGNED PRIMARY KEY, term_id BIGINT UNSIGNED, taxonomy VARCHAR(64), description LONGTEXT, parent BIGINT UNSIGNED, count BIGINT)');
    $connection->beginTransaction();
    $tables = [
        'posts' => ['wp_posts', ['ID', 'post_name', 'post_title', 'post_parent', 'post_type', 'post_date', 'post_modified', 'post_content']],
        'attachments' => ['wp_attachments', ['ID', 'post_title', 'post_excerpt', 'post_mime_type', 'post_parent']],
        'terms' => ['wp_terms', ['term_id', 'name', 'slug', 'term_group']],
        'taxonomies' => ['wp_term_taxonomy', ['term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count']],
    ];
    foreach ($tables as $key => [$table, $columns]) {
        // Identifiers are fixed constants above. All dump-derived values use bindings.
        $columnSql = implode(', ', array_map(static fn ($column) => '`'.$column.'`', $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $insert = $connection->prepare("INSERT INTO `{$table}` ({$columnSql}) VALUES ({$placeholders})");
        foreach ($payload[$key] as $record) {
            $insert->execute(array_map(static fn ($column) => $record[$column], $columns));
        }
    }
    $insert = $connection->prepare('INSERT INTO wp_options (option_name, option_value) VALUES (?, ?)');
    foreach ($payload['options'] as $key => $value) {
        $insert->execute([$key, $value]);
    }
    $insert = $connection->prepare('INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)');
    foreach ($payload['metadata'] as $id => $metadata) {
        foreach ($metadata as $key => $value) {
            $insert->execute([$id, $key, $value]);
        }
    }
    $connection->prepare('INSERT INTO audit_import (source) VALUES (?)')->execute(['u802165322_KeH3A.sql: allowlisted public audit subset']);
    $connection->commit();
    echo "Imported the public audit subset into isolated wp_legacy. No user or submission tables imported.\n";
} catch (Throwable $error) {
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
    // Avoid printing SQL, credentials, or source row contents from exception messages.
    fwrite(STDERR, 'Audit import failed ('.get_class($error).'). '.($created ? 'New wp_legacy schema retained for inspection.' : 'No schema created.')."\n");
    exit(1);
}
