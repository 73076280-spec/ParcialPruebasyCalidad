<?php if ($errors): ?>
<section class="error-summary" role="alert" aria-labelledby="error-heading" tabindex="-1" data-error-summary>
<h2 id="error-heading">Revise <?= count($errors) === 1 ? 'el campo indicado' : 'los campos indicados' ?></h2>
<p><?= e($summaryHelp ?? 'Sus valores se conservaron. Corrija los errores para continuar.') ?><?php if (!empty($showImageHint)): ?> Si eligió una imagen, vuelva a seleccionarla.<?php endif ?></p>
<ul><?php foreach ($errors as $field => $error): ?><li><a href="#<?= e($field) ?>"><?= e($fieldLabels[$field] ?? $field) ?>: <?= e($error) ?></a></li><?php endforeach ?></ul>
</section>
<?php endif ?>
