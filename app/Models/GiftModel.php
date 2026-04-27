<?php
declare(strict_types=1);

namespace App\Models;

class GiftModel extends BaseModel {

    /**
     * Returns all active gifts ordered by availability then sort_order.
     */
    public function getAllActive(): array {
        return $this->query(
            'SELECT * FROM gifts WHERE is_active = 1
             ORDER BY available_qty DESC, sort_order ASC'
        )->fetchAll();
    }

    /**
     * Returns active gifts filtered by optional search and category,
     * ordered by the given sort key.
     */
    public function filter(string $search, string $category, string $sort): array {
        $orderBy = match ($sort) {
            'price_asc'  => 'price ASC',
            'price_desc' => 'price DESC',
            'featured'   => 'featured DESC, sort_order ASC',
            default      => 'available_qty DESC, sort_order ASC', // 'available'
        };

        $conditions = ['is_active = 1'];
        $params     = [];

        if ($search !== '') {
            $conditions[] = 'name LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        if ($category !== '') {
            $conditions[] = 'category = :category';
            $params[':category'] = $category;
        }

        $where = implode(' AND ', $conditions);
        $sql   = "SELECT * FROM gifts WHERE {$where} ORDER BY {$orderBy}";

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Returns gifts for admin filtered by optional search text and status.
     */
    public function adminFilter(string $search, string $status): array {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = 'name LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        if ($status === 'active') {
            $conditions[] = 'is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'is_active = 0';
        }
        // 'all' — no status condition

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $sql   = "SELECT * FROM gifts {$where} ORDER BY id DESC";

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Returns a single gift by ID or false if not found.
     */
    public function findById(int $id): array|false {
        return $this->query(
            'SELECT * FROM gifts WHERE id = :id LIMIT 1',
            [':id' => $id]
        )->fetch();
    }

    /**
     * Inserts a new gift and returns the new row ID.
     *
     * Expected $data keys:
     *   name, slug, description, category, type, price,
     *   quantity (total), available_qty, featured, is_active, sort_order
     */
    public function insert(array $data): int {
        $this->query(
            'INSERT INTO gifts
                (name, slug, description, purchase_url, category, type, price, image_path,
                 quantity, available_qty, featured, is_active, sort_order)
             VALUES
                (:name, :slug, :description, :purchase_url, :category, :type, :price, :image_path,
                 :quantity, :available_qty, :featured, :is_active, :sort_order)',
            [
                ':name'          => $data['name']          ?? '',
                ':slug'          => $data['slug']          ?? '',
                ':description'   => $data['description']  ?? null,
                ':purchase_url'  => $data['purchase_url'] ?? null,
                ':category'      => $data['category']      ?? null,
                ':type'          => $data['type']          ?? 'single',
                ':price'         => $data['price']         ?? 0,
                ':image_path'    => $data['image_path']    ?? null,
                ':quantity'      => $data['quantity']      ?? 0,
                ':available_qty' => $data['available_qty'] ?? 0,
                ':featured'      => $data['featured']      ?? 0,
                ':is_active'     => $data['is_active']     ?? 1,
                ':sort_order'    => $data['sort_order']    ?? 0,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Updates an existing gift by ID.
     * Only keys present in $data are updated; updated_at is always refreshed.
     */
    public function update(int $id, array $data): void {
        if (empty($data)) {
            return;
        }

        $allowed = [
            'name', 'slug', 'description', 'purchase_url', 'category', 'type', 'price',
            'image_path', 'quantity', 'available_qty', 'featured',
            'is_active', 'sort_order',
        ];

        $setClauses = [];
        $params     = [':id' => $id];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue; // silently skip unknown columns
            }
            $setClauses[]      = "`{$key}` = :{$key}";
            $params[":{$key}"] = $value;
        }

        if (empty($setClauses)) {
            return;
        }

        $setClauses[] = 'updated_at = NOW()';
        $setStr       = implode(', ', $setClauses);

        $this->query("UPDATE gifts SET {$setStr} WHERE id = :id", $params);
    }

    /**
     * Deletes a gift by ID.
     * Caller must check reservationCount() before calling this.
     */
    public function delete(int $id): bool {
        $this->query('DELETE FROM gifts WHERE id = :id', [':id' => $id]);
        return true;
    }

    /**
     * Flips the is_active flag for a gift.
     */
    public function toggleActive(int $id): void {
        $gift = $this->findById($id);
        if (!$gift) {
            return;
        }
        $newValue = $gift['is_active'] ? 0 : 1;
        $this->query(
            'UPDATE gifts SET is_active = :is_active, updated_at = NOW() WHERE id = :id',
            [':is_active' => $newValue, ':id' => $id]
        );
    }

    /**
     * Returns the number of active reservations for a gift.
     * Used by AdminGiftController::delete() to block deletion when reservations exist.
     */
    public function reservationCount(int $id): int {
        return (int) $this->query(
            'SELECT COUNT(*) FROM reservations WHERE gift_id = :id',
            [':id' => $id]
        )->fetchColumn();
    }
}
