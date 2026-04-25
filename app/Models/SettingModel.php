<?php
declare(strict_types=1);

namespace App\Models;

class SettingModel extends BaseModel {
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
}
