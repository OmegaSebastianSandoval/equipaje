<h1 class="titulo-principal"><i class="fas fa-cogs"></i> <?php echo $this->titlesection; ?></h1>
<div class="container-fluid">
	<form class="text-left" enctype="multipart/form-data" method="post" action="<?php echo $this->routeform; ?>"
		data-bs-toggle="validator">
		<div class="content-dashboard">
			<input type="hidden" name="csrf" id="csrf" value="<?php echo $this->csrf ?>">
			<input type="hidden" name="csrf_section" id="csrf_section" value="<?php echo $this->csrf_section ?>">
			<?php if ($this->content->aerolinea_id) { ?>
				<input type="hidden" name="id" id="id" value="<?= $this->content->aerolinea_id; ?>" />
			<?php } ?>
			<div class="row">
				<div class="col-12 form-group">
					<label class="control-label">Estado</label>
					<input type="checkbox" name="aerolinea_estado" value="1" class="form-control switch-form " <?php if ($this->getObjectVariable($this->content, 'aerolinea_estado') == 1) {
						echo "checked";
					} ?>></input>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-6 form-group">
					<label for="aerolinea_nombre" class="control-label">Nombre</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-verde-claro "><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_nombre; ?>" name="aerolinea_nombre"
							id="aerolinea_nombre" class="form-control" required>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-6 form-group">
					<label for="aerolinea_correo" class="control-label">Correo</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-verde "><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_correo; ?>" name="aerolinea_correo"
							id="aerolinea_correo" class="form-control" required>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<!-- <div class="col-6 form-group">
					<label for="aerolinea_contrasena"  class="control-label">Contrase&ntilde;a</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-azul-claro " ><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_contrasena; ?>" name="aerolinea_contrasena" id="aerolinea_contrasena" class="form-control"   >
					</label>
					<div class="help-block with-errors"></div>
				</div> -->
				<div class="col-3 form-group">
					<label for="aerolinea_codigo" class="control-label">C&oacute;digo</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rosado "><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_codigo; ?>" name="aerolinea_codigo"
							id="aerolinea_codigo" class="form-control" required>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-3 form-group">
					<label for="aerolinea_iata" class="control-label">C&oacute;digo IATA (vuelos)</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rosado "><i class="fas fa-plane"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_iata; ?>" name="aerolinea_iata"
							id="aerolinea_iata" class="form-control" maxlength="3" placeholder="Ej: AV" style="text-transform: uppercase;">
					</label>
					<div class="help-block with-errors">Debe coincidir con el linecode del XML de vuelos. Sin IATA no se muestran vuelos.</div>
				</div>
				<div class="col-6 form-group">
					<label for="aerolinea_telefono" class="control-label">Teléfono</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rosado "><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_telefono; ?>" name="aerolinea_telefono"
							id="aerolinea_telefono" class="form-control" required>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-12 form-group">
					<label for="aerolinea_imagen">Imagen</label>
					<input type="file" name="aerolinea_imagen" id="aerolinea_imagen" class="form-control  file-image"
						data-buttonName="btn-primary" accept="image/gif, image/jpg, image/jpeg, image/png">
					<div class="help-block with-errors"></div>
					<?php if ($this->content->aerolinea_imagen) { ?>
						<div id="imagen_aerolinea_imagen">
							<img src="/images/<?= $this->content->aerolinea_imagen; ?>" class="img-thumbnail thumbnail-administrator" />
							<div><button class="btn btn-danger btn-sm" type="button"
									onclick="eliminarImagen('aerolinea_imagen','<?php echo $this->route . "/deleteimage"; ?>')"><i
										class="glyphicon glyphicon-remove"></i> Eliminar Imagen</button></div>
						</div>
					<?php } ?>
				</div>
				<hr class="mt-3">
				<div class="col-6 form-group">
					<label for="aerolinea_telefono_over" class="control-label">Teléfono Over</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rosado "><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_telefono_over; ?>" name="aerolinea_telefono_over"
							id="aerolinea_telefono_over" class="form-control" required>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-6 form-group">
					<label for="aerolinea_correo_over" class="control-label">Correo Over</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-verde "><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->aerolinea_correo_over; ?>" name="aerolinea_correo_over"
							id="aerolinea_correo_over" class="form-control" required>
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