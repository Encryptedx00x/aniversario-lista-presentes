<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h4 mb-4 text-center">Acesso Admin</h1>
        <form method="POST" action="/admin/login" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= $e($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>
