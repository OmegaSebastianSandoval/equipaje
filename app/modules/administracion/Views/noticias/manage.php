<h1 class="titulo-principal"><i class="fas fa-cogs"></i> <?php echo $this->titlesection; ?></h1>
<div class="container-fluid">
	<form class="text-left" enctype="multipart/form-data" method="post" action="<?php echo $this->routeform;?>"  data-bs-toggle="validator">
		<div class="content-dashboard">
			<input type="hidden" name="csrf" id="csrf" value="<?php echo $this->csrf ?>">
			<input type="hidden" name="csrf_section" id="csrf_section" value="<?php echo $this->csrf_section ?>">
			<?php if ($this->content->noticia_id) { ?>
				<input type="hidden" name="id" id="id" value="<?= $this->content->noticia_id; ?>" />
			<?php }?>
			<div class="row">
				<div class="col-6 form-group">
					<label for="noticia_fecha"  class="control-label">Fecha</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rosado " ><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->noticia_fecha; ?>" name="noticia_fecha" id="noticia_fecha" class="form-control"  required >
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-6 form-group">
					<label for="noticia_titulo"  class="control-label">Titulo</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-verde-claro " ><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->noticia_titulo; ?>" name="noticia_titulo" id="noticia_titulo" class="form-control"  required >
					</label>
					<div class="help-block with-errors"></div>
				</div>
			</div>
		</div>
		<div class="botones-acciones">
			<button class="btn btn-guardar" type="submit">Guardar</button>
			<a href="<?php echo $this->route; ?>" class="btn btn-cancelar">Cancelar</a>
		</div>
	</form>
</div>