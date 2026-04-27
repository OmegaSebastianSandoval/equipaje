<h1 class="titulo-principal"><i class="fas fa-cogs"></i> <?php echo $this->titlesection; ?></h1>
<div class="container-fluid">
	<form class="text-left" enctype="multipart/form-data" method="post" action="<?php echo $this->routeform;?>"  data-bs-toggle="validator">
		<div class="content-dashboard">
			<input type="hidden" name="csrf" id="csrf" value="<?php echo $this->csrf ?>">
			<input type="hidden" name="csrf_section" id="csrf_section" value="<?php echo $this->csrf_section ?>">
			<?php if ($this->content->publicidad_id) { ?>
				<input type="hidden" name="id" id="id" value="<?= $this->content->publicidad_id; ?>" />
			<?php }?>
			<div class="row">
				<div class="col-9 form-group">
					<label for="publicidad_nombre"  class="control-label">Nombre</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rosado " ><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->publicidad_nombre; ?>" name="publicidad_nombre" id="publicidad_nombre" class="form-control"  required >
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-3 form-group">
					<label for="publicidad_fecha"  class="control-label">Fecha</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-cafe " ><i class="fas fa-calendar-alt"></i></span>
						</div>
					<input type="text" value="<?php if($this->content->publicidad_fecha){ echo $this->content->publicidad_fecha; } else { echo date('Y-m-d'); } ?>" name="publicidad_fecha" id="publicidad_fecha" class="form-control"  required data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-language="es"  >
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-6 form-group">
					<label for="publicidad_imagen" >Imagen</label>
					<input type="file" name="publicidad_imagen" id="publicidad_imagen" class="form-control  file-image" data-buttonName="btn-primary" onchange="validarimagen('publicidad_imagen');" accept="image/gif, image/jpg, image/jpeg, image/png" >
					<div class="help-block with-errors"></div>
					<?php if($this->content->publicidad_imagen) { ?>
						<div id="imagen_publicidad_imagen">
							<img src="/images/<?= $this->content->publicidad_imagen; ?>"  class="img-thumbnail thumbnail-administrator" />
							<div><button class="btn btn-danger btn-sm" type="button" onclick="eliminarImagen('publicidad_imagen','<?php echo $this->route."/deleteimage"; ?>')"><i class="glyphicon glyphicon-remove" ></i> Eliminar Imagen</button></div>
						</div>
					<?php } ?>
				</div>
				<div class="col-6 form-group">
					<label for="publicidad_video"  class="control-label">Video</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rojo " ><i class="fab fa-youtube"></i></span>
						</div>
						<input type="text" value="<?= $this->content->publicidad_video; ?>" name="publicidad_video" id="publicidad_video" class="form-control" >
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-12 form-group">
					<label for="publicidad_descripcion" class="form-label" >
						Descripcion
					</label>
					<textarea name="publicidad_descripcion" id="publicidad_descripcion"   class="form-control tinyeditor" rows="10"   ><?= $this->content->publicidad_descripcion; ?></textarea>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-4 form-group">
					<label class="control-label">Estado</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-azul-claro " ><i class="far fa-check-circle"></i></span>
						</div>
						<select class="form-control" name="publicidad_estado"   >
							<?php foreach ($this->list_publicidad_estado AS $key => $value ){?>
								<option <?php if($this->getObjectVariable($this->content,"publicidad_estado") == $key ){ echo "selected"; }?> value="<?php echo $key; ?>" /> <?= $value; ?></option>
							<?php } ?>
						</select>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-2 form-group">
					<label for="publicidad_click"  class="control-label">clicks</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-azul " ><i class="far fa-hand-point-up"></i></span>
						</div>
						<input type="text" value="<?= $this->content->publicidad_click; ?>" name="publicidad_click" id="publicidad_click" class="form-control" disabled   >
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-6 form-group">
					<label for="publicidad_enlace"  class="control-label">Enlace</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rojo-claro " ><i class="fas fa-link"></i></span>
						</div>
						<input type="text" value="<?= $this->content->publicidad_enlace; ?>" name="publicidad_enlace" id="publicidad_enlace" class="form-control"   >
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