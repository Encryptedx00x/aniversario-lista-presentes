<?php
declare(strict_types=1);

namespace App\Models;

class SettingsModel extends BaseModel {

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

        $sql = 'SELECT key_name, value FROM settings WHERE key_name IN (' . implode(',', $placeholders) . ')';
        $rows = $this->query($sql, $params)->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['key_name']] = (string) ($row['value'] ?? '');
        }
        return $map;
    }

    public function upsertMany(array $data): void {
        foreach ($data as $key => $value) {
            $this->query(
                'INSERT INTO settings (key_name, value) VALUES (:key_name, :value)
                 ON DUPLICATE KEY UPDATE value = VALUES(value)',
                [
                    ':key_name' => (string) $key,
                    ':value' => (string) $value,
                ]
            );
        }
    }
}
