<?php
$fieldLabels = ['title' => 'Título', 'summary' => 'Resumen', 'content' => 'Contenido', 'category_id' => 'Categoría', 'image' => 'Imagen', 'image_alt' => 'Texto alternativo', 'status' => 'Estado'];
$categoryIds = array_map('strval', array_column($categories, 'id'));
$showImageHint = true;
?>
<section class="form-panel wide">
<p class="eyebrow">Administración de noticias</p><h1><?= $id ? 'Editar noticia' : 'Crear noticia' ?></h1>
<p>Los campos marcados como «obligatorio» deben completarse. Los espacios al inicio y al final no cuentan para las longitudes.</p>
<?php require ROOT . '/app/Views/errors.php'; ?>
<form method="post" enctype="multipart/form-data" aria-label="<?= $id ? 'Editar noticia' : 'Crear noticia' ?>">
<input type="hidden" name="_token" value="<?= e(csrf()) ?>">
<?php foreach (['title', 'summary', 'content', 'image_alt'] as $field): ?>
<?php [$min, $max] = NEWS_TEXT_RULES[$field] ?? [0, 180]; ?>
<div class="field">
<label for="<?= $field ?>"><?= e($fieldLabels[$field]) ?> <?= $field === 'image_alt' ? '(obligatorio si hay imagen)' : '(obligatorio)' ?></label>
<p class="field-help" id="<?= $field ?>-help"><?= $field === 'image_alt' ? 'Describa la información de la imagen en 1 a 180 caracteres.' : "Use de $min a $max caracteres." ?><?= $field === 'content' ? ' El contenido se mostrará como texto; las etiquetas HTML no se ejecutan.' : '' ?></p>
<?php if (in_array($field, ['summary', 'content'], true)): ?>
<textarea id="<?= $field ?>" name="<?= $field ?>" rows="<?= $field === 'content' ? 9 : 3 ?>" required minlength="<?= $min ?>" maxlength="<?= $max ?>" aria-describedby="<?= $field ?>-help <?= $field ?>-error" <?= isset($errors[$field]) ? 'aria-invalid="true"' : '' ?>><?= e($data[$field]) ?></textarea>
<?php else: ?>
<input id="<?= $field ?>" name="<?= $field ?>" <?php if ($field === 'title'): ?>required minlength="<?= $min ?>"<?php endif ?> maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" aria-describedby="<?= $field ?>-help <?= $field ?>-error" <?= isset($errors[$field]) ? 'aria-invalid="true"' : '' ?>>
<?php endif ?>
<p id="<?= $field ?>-error" class="error"><?php if (isset($errors[$field])): ?>Error: <?= e($errors[$field]) ?><?php endif ?></p>
</div>
<?php endforeach ?>
<div class="field"><label for="category_id">Categoría (obligatorio)</label><select id="category_id" name="category_id" required aria-describedby="category_id-help category_id-error" <?= isset($errors['category_id']) ? 'aria-invalid="true"' : '' ?>>
<option value="">Seleccione una categoría</option>
<?php if ($data['category_id'] !== '' && !in_array((string)$data['category_id'], $categoryIds, true)): ?><option value="<?= e($data['category_id']) ?>" selected>Selección anterior no válida: <?= e($data['category_id']) ?></option><?php endif ?>
<?php foreach ($categories as $c): ?><option value="<?= e($c['id']) ?>" <?= (string)$data['category_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach ?></select>
<p id="category_id-help" class="field-help">Seleccione una categoría existente. Puede gestionarlas desde el menú Categorías.</p>
<p id="category_id-error" class="error"><?php if (isset($errors['category_id'])): ?>Error: <?= e($errors['category_id']) ?><?php endif ?></p></div>
<div class="field"><label for="image">Imagen (opcional)</label><p id="image-help" class="field-help">JPEG, PNG o WebP auténticos; máximo 2 MiB (2.097.152 bytes). <?= $data['image'] ? 'Elegir otra imagen reemplaza la actual; dejar este campo vacío la conserva.' : '' ?></p>
<input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="image-help image-error" <?= isset($errors['image']) ? 'aria-invalid="true"' : '' ?>>
<p id="image-error" class="error"><?php if (isset($errors['image'])): ?>Error: <?= e($errors['image']) ?><?php endif ?></p>
<?php if ($data['image']): ?><figure><img class="preview" src="<?= e(($GLOBALS['config']['base_url'] ?? '') . '/uploads/' . $data['image']) ?>" alt="<?= e($data['image_alt']) ?>"><figcaption>Imagen actual</figcaption></figure><?php endif ?></div>
<div class="field"><label for="status">Estado (obligatorio)</label><select id="status" name="status" required aria-describedby="status-help status-error" <?= isset($errors['status']) ? 'aria-invalid="true"' : '' ?>>
<?php if (!in_array($data['status'], ['draft', 'published'], true)): ?><option value="<?= e($data['status']) ?>" selected>Estado no válido: <?= e($data['status']) ?></option><?php endif ?>
<option value="draft" <?= $data['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option><option value="published" <?= $data['status'] === 'published' ? 'selected' : '' ?>>Publicado</option></select>
<p id="status-help" class="field-help">Borrador: visible solo en el panel. Publicado: disponible para los visitantes.</p>
<p id="status-error" class="error"><?php if (isset($errors['status'])): ?>Error: <?= e($errors['status']) ?><?php endif ?></p></div>
<?php if ($id): ?><p class="metadata">Creación: <?= e($data['created_at']) ?> · Actualización: <?= e($data['updated_at']) ?></p><?php endif ?>
<div class="form-actions"><button><?= $id ? 'Guardar cambios' : 'Crear noticia' ?></button><a class="button secondary" href="<?= e(url('news')) ?>">Cancelar y volver a noticias</a></div>
</form></section>
