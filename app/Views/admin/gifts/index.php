<?php
/** @var array $gifts */
/** @var string $search */
/** @var string $status */
/** @var \Closure $e */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fs-3 fw-semibold mb-0">Presentes</h1>
    <a href="/admin/gifts/create" class="btn btn-primary">+ Novo presente</a>
</div>

<!-- Filter bar -->
<form method="GET" action="/admin/gifts" class="row g-3 mb-4">
    <div class="col-md-6">
        <input
            type="text"
            id="admin-search"
            name="search"
            class="form-control"
            placeholder="Buscar por nome..."
            value="<?= $e($search) ?>"
        >
    </div>
    <div class="col-md-3">
        <select id="admin-status-filter" name="status" class="form-select">
            <option value="all"      <?= $status === 'all'      ? 'selected' : '' ?>>Todos</option>
            <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Ativos</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inativos</option>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-outline-secondary w-100">Filtrar</button>
    </div>
</form>

<?php if (empty($gifts)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-gift fs-1 d-block mb-3"></i>
        <p class="fs-5 fw-semibold mb-1">Nenhum presente cadastrado.</p>
        <p class="small mb-3">Clique em "+ Novo presente" para começar.</p>
        <a href="/admin/gifts/create" class="btn btn-primary">+ Novo presente</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:64px"></th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Disponível</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gifts as $gift): ?>
                <tr>
                    <!-- Thumbnail -->
                    <td>
                        <?php if (!empty($gift['image_path'])): ?>
                            <img
                                src="<?= $e($gift['image_path']) ?>"
                                alt="<?= $e($gift['name']) ?>"
                                width="48"
                                height="48"
                                class="rounded"
                                style="object-fit:cover"
                            >
                        <?php else: ?>
                            <div
                                class="rounded bg-light d-flex align-items-center justify-content-center text-muted"
                                style="width:48px;height:48px"
                            >
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Name -->
                    <td><span class="fw-semibold"><?= $e($gift['name']) ?></span></td>

                    <!-- Category -->
                    <td><span class="text-muted small"><?= $gift['category'] ? $e($gift['category']) : '—' ?></span></td>

                    <!-- Price -->
                    <td>
                        <?php if ($gift['price'] > 0): ?>
                            <span>R$ <?= number_format((float)$gift['price'], 2, ',', '.') ?></span>
                        <?php else: ?>
                            <span>—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Available qty -->
                    <td>
                        <span><?= (int)$gift['available_qty'] ?> / <?= (int)$gift['quantity'] ?></span>
                    </td>

                    <!-- Status badge -->
                    <td>
                        <?php if ($gift['is_active']): ?>
                            <span class="badge bg-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inativo</span>
                        <?php endif; ?>
                    </td>

                    <!-- Actions -->
                    <td class="text-nowrap">
                        <!-- Edit -->
                        <a
                            href="/admin/gifts/<?= $e($gift['id']) ?>/edit"
                            class="btn btn-sm btn-outline-primary"
                        >Editar</a>

                        <!-- Toggle active/inactive -->
                        <form
                            method="POST"
                            action="/admin/gifts/<?= $e($gift['id']) ?>/toggle-active"
                            class="d-inline ms-1"
                        >
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <?= $gift['is_active'] ? 'Inativar' : 'Ativar' ?>
                            </button>
                        </form>

                        <!-- Delete -->
                        <form
                            method="POST"
                            action="/admin/gifts/<?= $e($gift['id']) ?>/delete"
                            class="d-inline ms-1"
                        >
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="if(confirm('Excluir este presente permanentemente? Esta ação não pode ser desfeita.')){this.closest('form').submit()}"
                            >Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
