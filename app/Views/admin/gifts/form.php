<?php
/** @var array|null $gift */
/** @var array $errors */
/** @var \Closure $e */
$isEdit  = $gift !== null;
$formAction = $isEdit
    ? '/admin/gifts/' . $e($gift['id']) . '/update'
    : '/admin/gifts/store';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fs-3 fw-semibold mb-0"><?= $isEdit ? 'Editar Presente' : 'Novo Presente' ?></h1>
</div>

<form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="row g-4">

        <!-- Left column — main fields -->
        <div class="col-md-8">

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                    value="<?= $e($gift['name'] ?? '') ?>"
                    required
                >
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= $e($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Slug -->
            <div class="mb-3">
                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="slug"
                    name="slug"
                    class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>"
                    value="<?= $e($gift['slug'] ?? '') ?>"
                    data-auto="true"
                    required
                >
                <?php if (isset($errors['slug'])): ?>
                    <div class="invalid-feedback"><?= $e($errors['slug']) ?></div>
                <?php endif; ?>
                <div class="form-text">Apenas letras minúsculas, números e hífens.</div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="4"
                ><?= $e($gift['description'] ?? '') ?></textarea>
            </div>

            <!-- Category -->
            <div class="mb-3">
                <label for="category" class="form-label">Categoria</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    class="form-control"
                    value="<?= $e($gift['category'] ?? '') ?>"
                >
            </div>

            <!-- Price -->
            <div class="mb-3">
                <label for="price" class="form-label">Preço sugerido (R$)</label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="<?= $e(number_format((float)($gift['price'] ?? 0), 2, '.', '')) ?>"
                >
            </div>

            <!-- Type -->
            <div class="mb-3">
                <label for="type" class="form-label">Tipo</label>
                <select id="type" name="type" class="form-select">
                    <option value="single" <?= ($gift['type'] ?? 'single') === 'single' ? 'selected' : '' ?>>
                        Individual (single)
                    </option>
                    <option value="multi" <?= ($gift['type'] ?? '') === 'multi' ? 'selected' : '' ?>>
                        Múltiplos (multi)
                    </option>
                </select>
            </div>

            <!-- Total qty -->
            <div class="mb-3">
                <label for="total_qty" class="form-label">Quantidade total</label>
                <input
                    type="number"
                    id="total_qty"
                    name="total_qty"
                    class="form-control"
                    min="0"
                    value="<?= $e((string)(int)($gift['quantity'] ?? 0)) ?>"
                >
            </div>

            <!-- Available qty -->
            <div class="mb-3">
                <label for="available_qty" class="form-label">Quantidade disponível</label>
                <input
                    type="number"
                    id="available_qty"
                    name="available_qty"
                    class="form-control"
                    min="0"
                    value="<?= $e((string)(int)($gift['available_qty'] ?? 0)) ?>"
                >
            </div>

        </div><!-- /.col-md-8 -->

        <!-- Right column — image + checkboxes -->
        <div class="col-md-4">

            <!-- Image upload -->
            <div class="mb-3">
                <label for="image" class="form-label">Imagem</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <?php if (isset($errors['image'])): ?>
                    <div class="invalid-feedback"><?= $e($errors['image']) ?></div>
                <?php endif; ?>
                <div class="form-text">Máximo 2MB. Formatos: JPG, PNG, WebP.</div>
            </div>

            <!-- Image preview -->
            <div class="mt-2">
                <img
                    id="image-preview"
                    src="<?= $isEdit && !empty($gift['image_path']) ? $e($gift['image_path']) : '' ?>"
                    alt="Pré-visualização da imagem"
                    class="img-fluid rounded"
                    style="max-height:200px;<?= ($isEdit && !empty($gift['image_path'])) ? '' : 'display:none' ?>"
                >
            </div>

            <!-- Featured checkbox -->
            <div class="form-check mt-3">
                <input
                    type="checkbox"
                    id="featured"
                    name="featured"
                    class="form-check-input"
                    value="1"
                    <?= !empty($gift['featured']) ? 'checked' : '' ?>
                >
                <label class="form-check-label" for="featured">Destacado</label>
            </div>

            <!-- Active checkbox -->
            <div class="form-check mt-2">
                <input
                    type="checkbox"
                    id="active"
                    name="active"
                    class="form-check-input"
                    value="1"
                    <?= (!$isEdit || !empty($gift['is_active'])) ? 'checked' : '' ?>
                >
                <label class="form-check-label" for="active">Ativo</label>
            </div>

        </div><!-- /.col-md-4 -->

    </div><!-- /.row -->

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Salvar presente</button>
        <a href="/admin/gifts" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </div>

</form>

<script>
(function () {
    // Image preview
    var fileInput = document.getElementById('image');
    var preview   = document.getElementById('image-preview');
    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                preview.src   = ev.target.result;
                preview.style.display = '';
            };
            reader.readAsDataURL(file);
        });
    }

    // Slug auto-generation from name
    var nameInput = document.getElementById('name');
    var slugInput = document.getElementById('slug');
    if (nameInput && slugInput) {
        // If slug was manually edited, stop auto-filling
        slugInput.addEventListener('input', function () {
            this.dataset.auto = 'false';
        });
        nameInput.addEventListener('input', function () {
            if (slugInput.dataset.auto === 'false') return;
            var slug = this.value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '')
                .replace(/[^a-z0-9\s\-]/g, '')
                .trim()
                .replace(/[\s]+/g, '-');
            slugInput.value = slug;
        });
    }
})();
</script>
