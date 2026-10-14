<?php $fieldLabels = ['name' => 'Nombre de la categoría']; ?>
<section class="form-panel"><p class="eyebrow">Administración de categorías</p><h1><?= $id ? 'Editar categoría' : 'Crear categoría' ?></h1>
<?php require ROOT . '/app/Views/errors.php'; ?>
<form method="post" aria-label="<?= $id ? 'Editar categoría' : 'Crear categoría' ?>"><input type="hidden" name="_token" value="<?= e(csrf()) ?>">
<div class="field"><label for="name">Nombre (obligatorio)</label><p id="name-help" class="field-help">Use de 2 a 60 caracteres, con un nombre único. No se acepta un nombre formado solo por espacios.</p>
<input id="name" name="name" required minlength="2" maxlength="60" value="<?= e($data['name']) ?>" aria-describedby="name-help name-error" <?= isset($errors['name']) ? 'aria-invalid="true"' : '' ?>>
<p id="name-error" class="error"><?php if (isset($errors['name'])): ?>Error: <?= e($errors['name']) ?><?php endif ?></p></div>
<div class="form-actions"><button><?= $id ? 'Guardar cambios' : 'Crear categoría' ?></button><a class="button secondary" href="<?= e(url('categories')) ?>">Cancelar y volver a categorías</a></div></form></section>
