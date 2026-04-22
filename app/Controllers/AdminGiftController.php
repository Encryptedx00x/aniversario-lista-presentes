<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\GiftModel;
use App\Helpers\View;
use App\Helpers\Flash;
use App\Helpers\Upload;

class AdminGiftController {

    public function index(array $params = []): void {
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? 'all'; // 'all' | 'active' | 'inactive'
        $gifts  = (new GiftModel)->adminFilter($search, $status);
        View::render('admin/gifts/index', [
            'title'  => 'Presentes',
            'layout' => 'admin',
            'gifts'  => $gifts,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(array $params = []): void {
        View::render('admin/gifts/form', [
            'title'  => 'Novo Presente',
            'layout' => 'admin',
            'gift'   => null,
            'errors' => [],
        ]);
    }

    public function store(array $params = []): void {
        $data   = $this->extractFormData();
        $errors = $this->validate($data);

        $imagePath = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            try {
                $imagePath = $this->handleImageUpload();
            } catch (\RuntimeException $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if ($errors) {
            View::render('admin/gifts/form', [
                'title'  => 'Novo Presente',
                'layout' => 'admin',
                'gift'   => null,
                'errors' => $errors,
            ]);
            return;
        }

        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        }

        try {
            (new GiftModel)->insert($data);
        } catch (\PDOException $ex) {
            if (str_starts_with((string)$ex->getCode(), '23')) {
                $errors['slug'] = 'Este slug já está em uso. Escolha outro.';
                View::render('admin/gifts/form', [
                    'title'  => 'Novo Presente',
                    'layout' => 'admin',
                    'gift'   => null,
                    'errors' => $errors,
                ]);
                return;
            }
            throw $ex;
        }

        Flash::set('success', 'Presente criado com sucesso.');
        header('Location: /admin/gifts');
        exit;
    }

    public function edit(array $params = []): void {
        $id   = (int)($params['id'] ?? 0);
        $gift = (new GiftModel)->findById($id);
        if (!$gift) {
            Flash::set('error', 'Presente não encontrado.');
            header('Location: /admin/gifts');
            exit;
        }
        View::render('admin/gifts/form', [
            'title'  => 'Editar Presente',
            'layout' => 'admin',
            'gift'   => $gift,
            'errors' => [],
        ]);
    }

    public function update(array $params = []): void {
        $id        = (int)($params['id'] ?? 0);
        $giftModel = new GiftModel;
        $gift      = $giftModel->findById($id);
        if (!$gift) {
            Flash::set('error', 'Presente não encontrado.');
            header('Location: /admin/gifts');
            exit;
        }

        $data   = $this->extractFormData();
        $errors = $this->validate($data);

        if (!empty($_FILES['image']['tmp_name'])) {
            try {
                $data['image_path'] = $this->handleImageUpload();
                // Delete old image if it exists and differs from new
                if (!empty($gift['image_path'])) {
                    $oldFile = ROOT_PATH . '/public' . $gift['image_path'];
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            } catch (\RuntimeException $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if ($errors) {
            View::render('admin/gifts/form', [
                'title'  => 'Editar Presente',
                'layout' => 'admin',
                'gift'   => array_merge($gift, $data),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $giftModel->update($id, $data);
        } catch (\PDOException $ex) {
            if (str_starts_with((string)$ex->getCode(), '23')) {
                $errors['slug'] = 'Este slug já está em uso. Escolha outro.';
                View::render('admin/gifts/form', [
                    'title'  => 'Editar Presente',
                    'layout' => 'admin',
                    'gift'   => array_merge($gift, $data),
                    'errors' => $errors,
                ]);
                return;
            }
            throw $ex;
        }

        Flash::set('success', 'Presente atualizado com sucesso.');
        header('Location: /admin/gifts');
        exit;
    }

    public function delete(array $params = []): void {
        $id        = (int)($params['id'] ?? 0);
        $giftModel = new GiftModel;

        $gift = $giftModel->findById($id);
        if (!$gift) {
            Flash::set('error', 'Presente não encontrado.');
            header('Location: /admin/gifts');
            exit;
        }

        // D-19: block deletion if reservations exist — check and delete inside a
        // transaction so no concurrent reservation can slip between the count and delete.
        $db = $giftModel->getDb();
        $db->beginTransaction();
        try {
            $count = $giftModel->reservationCount($id);
            if ($count > 0) {
                $db->rollBack();
                Flash::set('error', 'Este presente possui reservas associadas e não pode ser excluído.');
                header('Location: /admin/gifts');
                exit;
            }
            $giftModel->delete($id);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        // Clean up image file on successful delete
        if (!empty($gift['image_path'])) {
            $filePath = ROOT_PATH . '/public' . $gift['image_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        Flash::set('success', 'Presente excluído.');
        header('Location: /admin/gifts');
        exit;
    }

    public function toggleActive(array $params = []): void {
        $id        = (int)($params['id'] ?? 0);
        $giftModel = new GiftModel;
        $gift      = $giftModel->findById($id);
        if (!$gift) {
            Flash::set('error', 'Presente não encontrado.');
            header('Location: /admin/gifts');
            exit;
        }

        $giftModel->toggleActive($id);
        $msg = $gift['is_active'] ? 'Presente inativado.' : 'Presente ativado.';
        Flash::set('success', $msg);
        header('Location: /admin/gifts');
        exit;
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function extractFormData(): array {
        return [
            'name'          => trim($_POST['name']          ?? ''),
            'slug'          => trim($_POST['slug']          ?? ''),
            'description'   => trim($_POST['description']  ?? ''),
            'category'      => trim($_POST['category']     ?? ''),
            'type'          => (($_POST['type'] ?? '') === 'multi') ? 'multi' : 'single',
            'price'         => (float)($_POST['price']         ?? 0),
            'quantity'      => max(0, (int)($_POST['total_qty']     ?? 0)),
            'available_qty' => max(0, (int)($_POST['available_qty'] ?? 0)),
            'featured'      => isset($_POST['featured']) ? 1 : 0,
            'is_active'     => isset($_POST['active'])   ? 1 : 0,
            'sort_order'    => 0,
        ];
    }

    private function validate(array $data): array {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Nome é obrigatório.';
        }
        if ($data['slug'] === '') {
            $errors['slug'] = 'Slug é obrigatório.';
        } elseif (!preg_match('/^[a-z0-9\-]+$/', $data['slug'])) {
            $errors['slug'] = 'Slug deve conter apenas letras minúsculas, números e hífens.';
        }
        return $errors;
    }

    private function handleImageUpload(): string {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize      = 2 * 1024 * 1024; // 2MB

        if ($_FILES['image']['size'] > $maxSize) {
            throw new \RuntimeException('A imagem deve ter no máximo 2MB.');
        }
        if (!Upload::validateMime($_FILES['image']['tmp_name'], $allowedMimes)) {
            throw new \RuntimeException('Formato inválido. Use JPG, PNG ou WebP.');
        }
        if (!Upload::validateExtension($_FILES['image']['name'], $allowedExts)) {
            throw new \RuntimeException('Formato inválido. Use JPG, PNG ou WebP.');
        }

        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = uniqid('gift_', true) . '.' . $ext;
        $dest     = ROOT_PATH . '/public/uploads/gifts/' . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            throw new \RuntimeException('Falha ao salvar imagem.');
        }

        return '/uploads/gifts/' . $filename;
    }
}
