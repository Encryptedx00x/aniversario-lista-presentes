<?php
declare(strict_types=1);

namespace App\Models;

class SettingModel extends BaseModel {
    public function get(string $key, string $default = ''): string {
        $row = $this->query(
            'SELECT value FROM settings WHERE key_name = :key LIMIT 1',
            [':key' => $key]
        )->fetch();

        if (!$row) {
            return $default;
        }

        return (string) ($row['value'] ?? $default);
    }

    public function getMany(array $keys): array {
        if (empty($keys)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($keys) as $idx => $key) {
            $ph = ':k' . $idx;
            $placeholders[] = $ph;
            $params[$ph] = $key;
        }

        $sql = 'SELECT key_name, value
                FROM settings
                WHERE key_name IN (' . implode(',', $placeholders) . ')';

        $rows = $this->query($sql, $params)->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['key_name']] = (string) ($row['value'] ?? '');
        }
        return $out;
    }

    public function set(string $key, string $value): void {
        $this->query(
            'INSERT INTO settings (key_name, value)
             VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE value = VALUES(value)',
            [
                ':key' => $key,
                ':value' => $value,
            ]
        );
    }
}
