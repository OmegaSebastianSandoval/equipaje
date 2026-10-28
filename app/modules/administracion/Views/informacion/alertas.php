<h1 class="titulo-principal"><i class="fas fa-bell"></i> <?php echo $this->titlesection; ?></h1>
<div class="container-fluid">
	<?php if ($this->guardado) { ?>
		<div class="alert alert-success" role="alert">Los minutos de alerta se guardaron correctamente.</div>
	<?php } ?>
	<?php if ($this->error) { ?>
		<div class="alert alert-danger" role="alert">No se pudo guardar. Escribe un número entre 1 y 600 e intenta de nuevo.</div>
	<?php } ?>
	<form action="<?php echo $this->route; ?>/alertasguardar" method="post">
		<input type="hidden" name="csrf" value="<?php echo $this->csrf; ?>">
		<div class="content-dashboard">
			<div class="row">
				<div class="col-12 form-group">
					<label for="info_pagina_alerta_minutos" class="control-label">Minutos antes de la salida</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono fondo-azul"><i class="fas fa-clock"></i></span>
						</div>
						<input type="number" min="1" max="600" step="1" class="form-control" name="info_pagina_alerta_minutos"
							id="info_pagina_alerta_minutos" value="<?php echo (int) $this->minutos; ?>" required>
					</label>
					<div class="help-block">
						Cuando falten estos minutos para la salida de un vuelo que tiene maletas sin reclamar, se le envía una alerta a la
						aerolínea (una sola vez por vuelo). También define el resaltado en rojo de las pantallas y del listado de maletas;
						el amarillo aparece cuando falta el doble de este tiempo.
					</div>
				</div>
			</div>
		</div>
		<div class="botones-acciones">
			<button class="btn btn-guardar" type="submit">Guardar</button>
		</div>
	</form>

	<div class="content-dashboard mt-4">
		<h5>Sincronización de vuelos</h5>
		<?php if ($this->sync && $this->sync->sync_ultimo_ok) { ?>
			<p class="mb-1">Última sincronización correcta: <strong><?php echo $this->sync->sync_ultimo_ok; ?></strong>
				(<?php echo (int) $this->sync->sync_filas; ?> vuelos)</p>
		<?php } else { ?>
			<p class="mb-1 text-danger">Todavía no hay una sincronización correcta. Revisa que el cron esté configurado.</p>
		<?php } ?>
		<?php if ($this->sync && $this->sync->sync_error) { ?>
			<p class="mb-0 text-danger">Último error (<?php echo $this->sync->sync_ultimo_intento; ?>): <?php echo htmlspecialchars($this->sync->sync_error); ?></p>
		<?php } ?>
		<p class="mb-0 text-muted small">Las alertas solo se envían cuando corre el cron (<code>/page/cron/run</code>), cada minuto.</p>
	</div>
</div>
