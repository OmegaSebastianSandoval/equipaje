<h1 class="titulo-principal"><i class="fas fa-cogs"></i> <?php echo $this->titlesection; ?></h1>
<div class="container-fluid">
	<form class="text-left" enctype="multipart/form-data" method="post" action="<?php echo $this->routeform;?>"  data-bs-toggle="validator">
		<div class="content-dashboard">
			<input type="hidden" name="csrf" id="csrf" value="<?php echo $this->csrf ?>">
			<input type="hidden" name="csrf_section" id="csrf_section" value="<?php echo $this->csrf_section ?>">
			<?php if ($this->content->contenido_id) { ?>
				<input type="hidden" name="id" id="id" value="<?= $this->content->contenido_id; ?>" />
			<?php }?>
			<?php 
				if($this->content->contenido_padre) { $padre = $this->content->contenido_padre; } else { $padre = $this->padre; }
				if($this->content->contenido_tipo) { $tipo = $this->content->contenido_tipo; } else { $tipo = $this->tipo; }
				if($this->content->contenido_seccion) { $seccion = $this->content->contenido_seccion; } else { $seccion = $this->seccion; }
			 ?>
			<div class="row">
				<input type="hidden" name="contenido_padre"  value="<?php if($this->content->contenido_padre) { echo $this->content->contenido_padre; } else { echo $this->padre; }  ?>">
				<div class="col-2 form-group">
					<label   class="control-label">Activar Contenido</label>
					<br>
					<input type="checkbox" name="contenido_estado" value="1" class="form-control switch-form" <?php if ($this->getObjectVariable($this->content, 'contenido_estado') == 1) { echo "checked";} ?>   ></input>
					<div class="help-block with-errors"></div>
				</div>
				<?php if($padre == 0 || $padre == ''){ ?>
					<div class="col-3 form-group">
						<label class="control-label">Sección</label>
						<label class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text input-icono  fondo-cafe " ><i class="far fa-list-alt"></i></span>
							</div>
							<select class="form-control" name="contenido_seccion" id="contenido_seccion" required  >
								<option value="">Seleccione...</option>
								<?php foreach ($this->list_contenido_seccion AS $key => $value ){?>
									<option <?php if($this->getObjectVariable($this->content,"contenido_seccion") == $key ){ echo "selected"; }?> value="<?php echo $key; ?>" /> <?= $value; ?></option>
								<?php } ?>
							</select>
						</label>
						<div class="help-block with-errors"></div>
					</div>
				<?php } else { ?>
					<input type="hidden" name="contenido_seccion" id="contenido_seccion" value="<?php echo $seccion; ?>">
				<?php } ?>
				<?php if($this->mostrartipos == 1 || $padre == 0 || $padre == '' ){ ?>
					<div class="col-3 form-group">
						<label class="control-label">Tipo</label>
						<label class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text input-icono  fondo-verde " ><i class="far fa-list-alt"></i></span>
							</div>
							
							
							<select class="form-control" name="contenido_tipo" id="contenido_tipo" required  onchange="aparecercolumna();">
								<option value="">Seleccione...</option>
								<?php foreach ($this->list_contenido_tipo AS $key => $value ){?>
									<option <?php if($this->getObjectVariable($this->content,"contenido_tipo") == $key ){ echo "selected"; }?> value="<?php echo $key; ?>" /> <?= $value; ?></option>
								<?php } ?>
							</select>
						</label>
						<div class="help-block with-errors"></div>
					</div>
				<?php } else { ?>
					<input type="hidden" name="contenido_tipo" id="contenido_tipo" value="<?php echo $tipo; ?>">
				<?php } ?>
				<div class="col-3 <?php if($this->content->contenido_padre==0){ ?>d-none <?php } ?> form-group si-banner si-seccion no-contenido  si-carrousel no-acordion no-contenido2" <?php if( $tipo != 1 && $tipo != 2  && $tipo != 6 ){ ?> style="display: none;" <?php } ?>>
					<label class="control-label">Diseño Columnas</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-verde " ><i class="fas fa-arrows-alt-h"></i></span>
						</div>
						<select class="form-control" name="contenido_columna_espacios"   >
							<option value="">Seleccione...</option>
							<?php foreach ($this->list_contenido_columna_espacios AS $key => $value ){?>
								<option <?php if($this->getObjectVariable($this->content,"contenido_columna_espacios") == $key ){ echo "selected"; }?> value="<?php echo $key; ?>" /> <?= $value; ?></option>
							<?php } ?>
						</select>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-3 <?php if($this->content->contenido_padre==0){ ?>d-none <?php } ?> form-group no-banner no-carrousel no-acordion si-seccion" <?php if( $tipo != 2 && $tipo != 4){ ?> style="display: none;" <?php } ?>>
					<label class="control-label " >Alineación</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-verde " ><i class="fas fa-align-center"></i></span>
						</div>
						<select class="form-control" name="contenido_columna_alineacion"   >
							<option value="">Seleccione...</option>
							<?php foreach ($this->list_contenido_columna_alineacion AS $key => $value ){?>
								<option <?php if($this->getObjectVariable($this->content,"contenido_columna_alineacion") == $key ){ echo "selected"; }?> value="<?php echo $key; ?>" /> <?= $value; ?></option>
							<?php } ?>
						</select>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<?php if( ($tipo != 4) || $this->contentpadre->contenido_tipo == 2 ){ ?>
					<div class="col-12 <?php if($this->content->contenido_padre==0){ ?>d-none <?php } ?> form-group no-colum" >
						<label for="contenido_columna"  class="control-label">Columna</label>
						<div class="row">
							<div class="col-2">
								<label class="radio-col">
									<input type="radio" value="col-sm-12" <?php if($this->content->contenido_columna == 'col-sm-12'){ ?> checked  <?php } ?> name="contenido_columna" id="contenido_columna" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/columna12.png">
									</span>
								</label>
							</div>
							<div class="col-2">
								<label class="radio-col">
									<input type="radio" value="col-sm-6" <?php if($this->content->contenido_columna == 'col-sm-6'){ ?> checked  <?php } ?> name="contenido_columna" id="contenido_columna" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/columna6.png">
									</span>
								</label>
							</div>
							<div class="col-2">
								<label class="radio-col">
									<input type="radio" value="col-sm-4" <?php if($this->content->contenido_columna == 'col-sm-4'){ ?> checked  <?php } ?> name="contenido_columna" id="contenido_columna" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/columna4.png">
									</span>
								</label>
							</div>
							<div class="col-2">
								<label class="radio-col">
									<input type="radio" value="col-sm-3" <?php if($this->content->contenido_columna == 'col-sm-3'){ ?> checked  <?php } ?> name="contenido_columna" id="contenido_columna" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/columna3.png">
									</span>
								</label>
							</div>
							<div class="col-2 no-carrousel2 ">
								<label class="radio-col">
									<input type="radio" value="col-sm-8" <?php if($this->content->contenido_columna == 'col-sm-8'){ ?> checked  <?php } ?> name="contenido_columna" id="contenido_columna" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/columna8.png">
									</span>
								</label>
							</div>
							<div class="col-2 no-carrousel2 ">
								<label class="radio-col">
									<input type="radio" value="col-sm-9" <?php if($this->content->contenido_columna == 'col-sm-9'){ ?> checked  <?php } ?> name="contenido_columna" id="contenido_columna" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/columna9.png">
									</span>
								</label>
							</div>
						</div>
						<div class="help-block with-errors"></div>
					</div>
				<?php } ?>
				<?php if( $tipo == 5 || $tipo == 6 || $this->contentpadre->contenido_tipo == 2  ){ ?>
					<div class="col-10 form-group no-banner no-seccion si-carrousel no-acordion si-contenido2 " <?php if( ($tipo != 2 && $tipo != 4 && $tipo != 5 && $tipo != 6 ) || $tipo == 0 ){ ?> style="display: none;" <?php } ?>>
						<label for="contenido_disenio"  class="control-label">Diseño del Contenido</label>
						<div class="row">
							<div class="col-3">
								<label class="radio-disenio">
									<input type="radio" value="1" <?php if($this->content->contenido_disenio == '1'){ ?> checked  <?php } ?> name="contenido_disenio" id="contenido_disenio" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/forma1.png">
									</span>
								</label>
							</div>
							<div class="col-3">
								<label class="radio-disenio">
									<input type="radio" value="2" <?php if($this->content->contenido_disenio == '2'){ ?> checked  <?php } ?> name="contenido_disenio" id="contenido_disenio" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/forma2.png">
									</span>
								</label>
							</div>
							<div class="col-3">
								<label class="radio-disenio">
									<input type="radio" value="3" <?php if($this->content->contenido_disenio == '3'){ ?> checked  <?php } ?> name="contenido_disenio" id="contenido_disenio" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/forma3.png">
									</span>
								</label>
							</div>
							<div class="col-3">
								<label class="radio-disenio">
									<input type="radio" value="4" <?php if($this->content->contenido_disenio == '4'){ ?> checked  <?php } ?> name="contenido_disenio" id="contenido_disenio" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/forma4.png">
									</span>
								</label>
							</div>
							<div class="col-3">
								<label class="radio-disenio">
									<input type="radio" value="5" <?php if($this->content->contenido_disenio == '5'){ ?> checked  <?php } ?> name="contenido_disenio" id="contenido_disenio" class="form-control"   >
									<span>
										<img src="/skins/administracion/images/forma4.png">
									</span>
								</label>
							</div>
						</div>
						<div class="help-block with-errors"></div>
					</div>
					<div class="col-2 form-group no-banner si-carrousel no-acordion si-contenido2" <?php if(isset($tipo) == false || ($tipo != 2 && $tipo != 4  && $tipo != 6 ) ){ ?> style="display: none;" <?php } ?>>
						<label   class="control-label">Diseño con Borde</label><br>
						<input type="checkbox" class="switch-form" name="contenido_borde" value="1" <?php if ($this->getObjectVariable($this->content, 'contenido_borde') == 1) { echo "checked";} ?>   ></input>
						<div class="help-block with-errors"></div>
					</div>
				<?php } ?>
				<div class="col-3 form-group">
					<label for="contenido_fecha"  class="control-label">Fecha</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-morado " ><i class="fas fa-calendar-alt"></i></span>
						</div>
						<input type="text" value="<?php if($this->content->contenido_fecha){ echo $this->content->contenido_fecha; } else { echo date('Y-m-d'); } ?>" name="contenido_fecha" id="contenido_fecha" class="form-control"   data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-language="es"  >
					</label>
					<div class="help-block with-errors"></div>
				</div>
			
				<div class="col-10 form-group">
					<label for="contenido_titulo"  class="control-label">Título</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rojo-claro " ><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->contenido_titulo; ?>" name="contenido_titulo" id="contenido_titulo" class="form-control"   >
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-2 form-group no-banner  si-seccion no-carrousel no-acordion si-contenido2" <?php if($tipo == 1 || $tipo == 6 || $tipo == 7  || $tipo == 0 ){ ?> style="display: none;" <?php } ?>>
					<label   class="control-label">Mostrar Título</label><br>
					<input type="checkbox" name="contenido_titulo_ver" value="1" class="form-control switch-form " <?php if ($this->getObjectVariable($this->content, 'contenido_titulo_ver') == 1) { echo "checked";} ?>   ></input>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-12 form-group no-banner si-seccion si-contenido no-carrousel  no-acordion  si-contenido2" <?php if($tipo == 1 || $tipo == 4 || $tipo == 6 || $tipo == 7  || $tipo == 0){ ?> style="display: none;" <?php } ?>>
					<label for="contenido_imagen" >Imagen</label>
					<input type="file" name="contenido_imagen" id="contenido_imagen" class="form-control  file-image" data-buttonName="btn-primary" accept="image/gif, image/jpg, image/jpeg, image/png"  >
					<div class="help-block with-errors"></div>
					<?php if($this->content->contenido_imagen) { ?>
						<div id="imagen_contenido_imagen">
							<img src="/images/<?= $this->content->contenido_imagen; ?>"  class="img-thumbnail thumbnail-administrator" />
							<div><button class="btn btn-danger btn-sm" type="button" onclick="eliminarImagen('contenido_imagen','<?php echo $this->route."/deleteimage"; ?>')"><i class="glyphicon glyphicon-remove" ></i> Eliminar Imagen</button></div>
						</div>
					<?php } ?>
				</div>
				<div class="col-12 form-group">
					<label for="contenido_archivo" >Archivo</label>
					<input type="file" name="contenido_archivo" id="contenido_archivo" class="form-control  file-document" data-buttonName="btn-primary" onchange="validardocumento('contenido_archivo');" accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf" >
					<div class="help-block with-errors"></div>
					<?php if($this->content->contenido_archivo) { ?>
						<div id="archivo_contenido_archivo">
							<div><?php echo $this->content->contenido_archivo; ?></div>
							<div><button class="btn btn-danger btn-sm" type="button" onclick="eliminararchivo('contenido_archivo','<?php echo $this->route."/deletearchivo"; ?>')"><i class="glyphicon glyphicon-remove" ></i> Eliminar Archivo</button></div>
						</div>
					<?php } ?> 
				</div>
			<div class="col-4 form-group no-banner no-acordion no-carrousel si-seccion" <?php if($tipo != 2 && $tipo != 4  ){ ?> style="display: none;" <?php } ?>>
				<label for="contenido_fondo_imagen" ><?php if($tipo == 4){ ?>Imagen Banner <?php } else{ ?> Imagen Fondo  <?php } ?></label>
				<input type="file" name="contenido_fondo_imagen" id="contenido_fondo_imagen" class="form-control  file-image" data-buttonName="btn-primary" accept="image/gif, image/jpg, image/jpeg, image/png"  >
				<div class="help-block with-errors"></div>
				<?php if($this->content->contenido_fondo_imagen) { ?>
					<div id="imagen_contenido_fondo_imagen">
						<img src="/images/<?= $this->content->contenido_fondo_imagen; ?>"  class="img-thumbnail thumbnail-administrator" />
						<div><button class="btn btn-danger btn-sm" type="button" onclick="eliminarImagen('contenido_fondo_imagen','<?php echo $this->route."/deleteimage"; ?>')"><i class="glyphicon glyphicon-remove" ></i> Eliminar Imagen</button></div>
					</div>
				<?php } ?>
			</div>
			<div class="col-4 form-group no-carrousel no-acordion no-carrousel si-seccion" <?php if($tipo != 2){ ?> style="display: none;" <?php } ?>>
				<label class="control-label">Tipo Fondo</label>
				<label class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text input-icono  fondo-rosado " ><i class="far fa-list-alt"></i></span>
					</div>
					<select class="form-control" name="contenido_fondo_imagen_tipo"   >
						<option value="">Seleccione...</option>
						<?php foreach ($this->list_contenido_fondo_imagen_tipo AS $key => $value ){?>
							<option <?php if($this->getObjectVariable($this->content,"contenido_fondo_imagen_tipo") == $key ){ echo "selected"; }?> value="<?php echo $key; ?>" /> <?= $value; ?></option>
						<?php } ?>
					</select>
				</label>
				<div class="help-block with-errors"></div>
			</div>
			<div class="col-4 form-group no-contenido no-banner si-seccion no-acordion si-carrousel si-contenido2" <?php if($tipo != 2 && $tipo!= 4 && $tipo!= 5  && $tipo!= 6 ){ ?> style="display: none;" <?php } ?> >
				<label for="contenido_fondo_color"  class="control-label"><?php if($tipo == 4){ ?> Color Caption <?php } else{ ?> Color Fondo <?php } ?></label>
				<label class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text input-icono  fondo-azul-claro " ><i class="fas fa-pencil-alt"></i></span>
					</div>
					<input type="text" value="<?= $this->content->contenido_fondo_color; ?>" name="contenido_fondo_color" id="contenido_fondo_color" class="form-control"   >
				</label>
				<div class="help-block with-errors"></div>
			</div>
				<div class="col-12 form-group no-banner no-carrousel no-seccion no-acordion si-contenido"  <?php if($tipo != 3){ ?> style="display: none;" <?php } ?>>
					<label for="contenido_introduccion" class="form-label" >Introducci&oacute;n</label>
					<textarea name="contenido_introduccion" id="contenido_introduccion"   class="form-control tinyeditor" rows="10"   ><?= $this->content->contenido_introduccion; ?></textarea>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-12 form-group no-banner si-seccion si-contenido si-carrousel si-acordion si-contenido2 " <?php if( ($tipo == 1 || $tipo == 0) && $this->contentpadre->contenido_tipo != 2  ){ ?> style="display: none;" <?php } ?>>
					<label for="contenido_descripcion" class="form-label" >Descripción</label>
					<textarea name="contenido_descripcion" id="contenido_descripcion"   class="form-control tinyeditor" rows="10"   ><?= $this->content->contenido_descripcion; ?></textarea>
					<div class="help-block with-errors"></div>
				</div>
			</div>
			<div class="row no-banner si-seccion si-contenido no-acordion no-carrousel si-contenido2"  <?php if($tipo == 1 || $tipo == 6 || $tipo == 7 || ($tipo == 0 && $this->contentpadre->contenido_tipo == 2  )){ ?> style="display: none;" <?php } ?>>
				<div class="col-6 form-group">
					<label for="contenido_enlace"  class="control-label">Enlace</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-verde-claro " ><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->contenido_enlace; ?>" name="contenido_enlace" id="contenido_enlace" class="form-control"   >
						<div class="input-group-prepend">
							<span class="input-group-text" >Abrir en </span>
							<select class="form-control" name="contenido_enlace_abrir"   >
								<?php foreach ($this->list_contenido_enlace_abrir AS $key => $value ){?>
									<option <?php if($this->getObjectVariable($this->content,"contenido_enlace_abrir") == $key ){ echo "selected"; }?> value="<?php echo $key; ?>" /> <?= $value; ?></option>
								<?php } ?>
							</select>
						</div>
					</label>
					<div class="help-block with-errors"></div>
				</div>
				<div class="col-6 form-group">
					<label for="contenido_vermas"  class="control-label">Texto Ver m&aacute;s</label>
					<label class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text input-icono  fondo-rosado " ><i class="fas fa-pencil-alt"></i></span>
						</div>
						<input type="text" value="<?= $this->content->contenido_vermas; ?>" name="contenido_vermas" id="contenido_vermas" class="form-control"   >
					</label>
					<div class="help-block with-errors"></div>
				</div>
			</div>
		</div>
		<div class="botones-acciones">
			<button class="btn btn-guardar" type="submit">Guardar</button>
			<a href="<?php echo $this->route; ?><?php if($padre){ echo "?padre=".$padre; } ?>" class="btn btn-cancelar">Cancelar</a>
		</div>
	</form>
</div>