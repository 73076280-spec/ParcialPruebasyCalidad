<?php $isNews = $route === 'news-delete'; $kind = $isNews ? 'noticia' : 'categoría'; ?>
<section class="form-panel"><p class="eyebrow">Administración</p><h1>Confirmar eliminación</h1>
<p>Registro seleccionado: <strong><?= e($item['title'] ?? $item['name']) ?></strong>.</p>
<?php if ($related > 0): ?><p class="notice" role="status">No puede eliminar esta categoría: tiene <?= e($related) ?> noticias asociadas. Reasigne sus noticias primero desde «Editar noticia».</p>
<?php else: ?><p>¿Desea eliminar esta <?= $kind ?>? Esta acción no se puede deshacer. Puede cancelar para conservarla.</p><?php endif ?>
<form method="post" action="<?= e(url($route, ['id' => $id])) ?>"><input type="hidden" name="_token" value="<?= e(csrf()) ?>">
<div class="form-actions"><?php if ($related === 0): ?><button class="danger">Sí, eliminar <?= $kind ?></button><?php endif ?><a class="button secondary" href="<?= e(url($isNews ? 'news' : 'categories')) ?>">Cancelar y volver al listado</a></div></form></section>
