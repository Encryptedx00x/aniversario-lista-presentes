<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\GiftModel;
use App\Helpers\View;

class GiftController {

    public function index(array $params = []): void {
        $giftModel = new GiftModel;
        $gifts     = $giftModel->getAllActive();

        // Build distinct category list for the filter dropdown
        $categories = array_values(array_unique(
            array_filter(array_column($gifts, 'category'))
        ));
        sort($categories);

        View::render('public/gifts/index', [
            'title'      => APP_NAME . ' — Lista de Presentes',
            'layout'     => 'main',
            'gifts'      => $gifts,
            'categories' => $categories,
        ]);
    }

    public function filter(array $params = []): void {
        $search   = trim($_GET['search']   ?? '');
        $category = trim($_GET['category'] ?? '');
        $sort     = $_GET['sort'] ?? 'available';

        // Whitelist sort values — never pass raw user input to ORDER BY
        $allowedSorts = ['available', 'price_asc', 'price_desc', 'featured'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'available';
        }

        $gifts = (new GiftModel)->filter($search, $category, $sort);

        // Render PARTIAL ONLY — not a full page. Do NOT use View::render() here.
        $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        require ROOT_PATH . '/app/Views/public/gifts/_cards.php';
        exit;
    }
}
