<?php $fieldLabels = ['password' => 'Contraseña']; $summaryHelp = 'El correo se conservó. Compruebe sus credenciales y vuelva a introducir su contraseña.'; ?>
<section class="form-panel"><p class="eyebrow">Administración</p><h1>Iniciar sesión</h1><p>Utilice el correo y la contraseña de su cuenta administrativa.</p>
<?php require ROOT . '/app/Views/errors.php'; ?>
<form method="post" action="<?= e(url('login')) ?>" aria-label="Iniciar sesión"><input type="hidden" name="_token" value="<?= e(csrf()) ?>">
<div class="field"><label for="email">Correo electrónico (obligatorio)</label><input id="email" name="email" type="email" required maxlength="190" autocomplete="username" value="<?= e($email) ?>"></div>
<div class="field"><label for="password">Contraseña (obligatorio)</label><input id="password" name="password" type="password" required autocomplete="current-password" aria-describedby="password-error" <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>><p class="error" id="password-error"><?php if (isset($errors['password'])): ?>Error: <?= e($errors['password']) ?><?php endif ?></p></div>
<div class="form-actions"><button>Iniciar sesión</button><a class="button secondary" href="<?= e(url()) ?>">Volver al inicio</a></div></form></section>
