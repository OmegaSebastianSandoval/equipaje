<div class="container-fluid">
	<form enctype="multipart/form-data" method="post" action="<?php echo $this->routeform; ?>"  data-bs-toggle="validator">
		<input type="hidden" name="csrf" id="csrf" value="<?php echo $this->csrf ?>">
		<input type="hidden" name="csrf_section" id="csrf_section" value="<?php echo $this->csrf_section ?>">
		<?php if ($this->content->info_pagina_id) { ?>
			<input type="hidden" name="id" id="id" value="<?= $this->content->info_pagina_id; ?>" />
		<?php } ?>

		<a id="redes" name="redes"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/redessociales.png"> Redes Sociales
			</h2>
			<div align="center" class="pading-dashboard">
				<br>
				<div class="row">
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-facebook"><i class="fab fa-facebook-f"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_facebook; ?>" name="info_pagina_facebook" id="info_pagina_facebook" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-instagram"><i class="fab fa-instagram"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_instagram; ?>" name="info_pagina_instagram" id="info_pagina_instagram" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-twitter"><i class="fab fa-twitter"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_twitter; ?>" name="info_pagina_twitter" id="info_pagina_twitter" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-pinterest"><i class="fab fa-pinterest"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_pinterest; ?>" name="info_pagina_pinterest" id="info_pagina_pinterest" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-youtube"><i class="fab fa-youtube"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_youtube; ?>" name="info_pagina_youtube" id="info_pagina_youtube" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-flickr"><i class="fab fa-flickr"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_flickr; ?>" name="info_pagina_flickr" id="info_pagina_flickr" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-linkedin"><i class="fab fa-linkedin"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_linkedin; ?>" name="info_pagina_linkedin" id="info_pagina_linkedin" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-google"><i class="fab fa-google-plus-g"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_google; ?>" name="info_pagina_google" id="info_pagina_google" class="form-control">
						</div>
					</div>
					<div class="col-6 form-group">
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-whatsapp"><i class="fab fa-whatsapp"></i></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_whatsapp; ?>" name="info_pagina_whatsapp" id="info_pagina_whatsapp" class="form-control">
						</div>
					</div>
				</div>
			</div>
		</div>
		<a id="favicon" name="favicon"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/redessociales.png"> Favicon
			</h2>
			<div align="center" class="pading-dashboard">
				<br>
				<div class="row">
					<div class="col-12 form-group">
						<input type="file" name="info_pagina_favicon" id="info_pagina_favicon" class="form-control  file-image" data-buttonName="btn-primary" accept="image/ico" <?php if (!$this->content->info_pagina_favicon) {
																																														echo 'required';
																																													} ?>>
						<div class="help-block with-errors"></div>
						<?php if ($this->content->info_pagina_favicon) { ?>
							<div id="imagen_info_pagina_favicon">
								<img src="/images/<?= $this->content->info_pagina_favicon; ?>" class="img-thumbnail thumbnail-administrator" />
								<div><button class="btn btn-danger btn-sm" type="button" onclick="eliminarImagen('info_pagina_favicon','<?php echo $this->route . "/deleteimage"; ?>')"><i class="glyphicon glyphicon-remove"></i> Eliminar Imagen</button></div>
							</div>
						<?php } ?>
					</div>
					<div class="col-12 text-center">

						<div class="alert alert-warning" role="alert">
							Tamaño de la imagen 32x32 px
						</div>


					</div>

				</div>
			</div>
		</div>

		<a id="contactenos" name="contactenos"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/informaciondecotactenos.png"> Información de Contáctenos
			</h2>
			<div class="pading-dashboard">
				<br>
				<div class="row">
					<div class="col-4 form-group">
						<label for="info_pagina_telefono" class="control-label">Teléfonos:</label>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-telefono"><i class="fas fa-phone"></i></span>
							</div>
							<textarea name="info_pagina_telefono" id="info_pagina_telefono" class="form-control" rows="2"><?= $this->content->info_pagina_telefono; ?></textarea>
						</div>
					</div>
					<div class="col-4 form-group">
						<label for="info_pagina_correos_contacto" class="form-label">Correo Contacto:</label>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-correo"><i class="fas fa-envelope"></i></span>
							</div>
							<textarea name="info_pagina_correos_contacto" id="info_pagina_correos_contacto" class="form-control" rows="2"><?= $this->content->info_pagina_correos_contacto; ?></textarea>
						</div>
					</div>
					<div class="col-4 form-group">
						<label for="info_pagina_direccion_contacto" class="form-label">Dirección:</label>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono fondo-direccion"><i class="fas fa-map-marked-alt"></i></span>
							</div>
							<textarea name="info_pagina_direccion_contacto" id="info_pagina_direccion_contacto" class="form-control" rows="2"><?= $this->content->info_pagina_direccion_contacto; ?></textarea>
						</div>
					</div>
					<div class="col-6 form-group">
						<label for="info_pagina_informacion_contacto" class="form-label">Información Contacto:</label>
						<textarea name="info_pagina_informacion_contacto" id="info_pagina_informacion_contacto" class="form-control tinyeditor" rows="10"><?= $this->content->info_pagina_informacion_contacto; ?></textarea>
					</div>
					<div class="col-6 form-group">
						<label for="info_pagina_informacion_contacto_footer" class="control-label">Información Contacto Footer:</label>
						<textarea name="info_pagina_informacion_contacto_footer" id="info_pagina_informacion_contacto_footer" class="form-control tinyeditor" rows="10"><?= $this->content->info_pagina_informacion_contacto_footer; ?></textarea>
					</div>
				</div>
			</div>
		</div>
		<a id="configcorreo" name="configcorreo"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/informaciondecotactenos.png"> Configuración Envio Correo
			</h2>
			<div class="pading-dashboard">
				<br>


				<div class="row">
					<div class="col-2">&nbsp</div>
					<div class="col-8">
						<table class="table table-bordered table-striped">
							<tbody>
								<tr>
									<th scope="row">Host</th>
									<td>
										<input class="input_correo" type="text" name="info_pagina_host" id="info_pagina_host" value="<?= $this->content->info_pagina_host; ?>" required>
									</td>

								</tr>
								<tr>
									<th scope="row">Port</th>
									<td>
										<input class="input_correo" type="number" name="info_pagina_port" id="info_pagina_port" value="<?= $this->content->info_pagina_port; ?>" required>

									</td>

								</tr>
								<tr>
									<th scope="row">Username</th>
									<td>
										<input class="input_correo" type="text" name="info_pagina_username" id="info_pagina_username" value="<?= $this->content->info_pagina_username; ?>" required>

									</td>

								</tr>
								<tr>
									<th scope="row">Password</th>
									<td>
										<input class="input_correo" type="password" name="info_pagina_password" id="info_pagina_password"  >

									</td>

								</tr>

								<tr>
									<th scope="row">Correo remitente</th>
									<td>
										<input class="input_correo" type="text" name="info_pagina_correo_remitente" id="info_pagina_correo_remitente" value="<?= $this->content->info_pagina_correo_remitente; ?>" required>

									</td>

								</tr>
								<tr>
									<th scope="row">Nombre remitente</th>
									<td>
										<input class="input_correo" type="text" name="info_pagina_nombre_remitente" id="info_pagina_nombre_remitente" value="<?= $this->content->info_pagina_nombre_remitente; ?>" required>

									</td>

								</tr>
								<tr>
									<th scope="row">Correo oculto</th>
									<td>
										<input class="input_correo" type="text" name="info_pagina_correo_oculto" id="info_pagina_correo_oculto" value="<?= $this->content->info_pagina_correo_oculto; ?>">

									</td>

								</tr>
								<tr>
									<th scope="row">Alerta de salida (minutos)</th>
									<td>
										<input class="input_correo" type="number" min="1" max="600" name="info_pagina_alerta_minutos" id="info_pagina_alerta_minutos" value="<?= $this->content->info_pagina_alerta_minutos ?: 60; ?>" required>
										<small class="d-block text-muted">Se avisa a la aerolínea cuando faltan estos minutos para la salida de un vuelo con maletas sin reclamar.</small>
									</td>
								</tr>
							</tbody>
						</table>

					</div>
					<div class="col-2">&nbsp</div>


				</div>
			</div>
		</div>
		<a id="maps" name="maps"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/googlemaps.png"> Google Maps
			</h2>
			<div class="pading-dashboard">
				<div class="row">
					<div class="col-4 form-group">
						<label for="info_pagina_latitud" class="control-label">Latitud:</label>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono-grande fondo-mapa-latitud"><img src="/skins/administracion/images/mapa-latitud.png"></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_latitud; ?>" name="info_pagina_latitud" id="info_pagina_latitud" class="form-control">
						</div>
					</div>
					<div class="col-4 form-group">
						<label for="info_pagina_longitud" class="control-label">Longitud:</label>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono-grande fondo-mapa-longitud"><img src="/skins/administracion/images/mapa-longitud.png"></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_longitud; ?>" name="info_pagina_longitud" id="info_pagina_longitud" class="form-control">
						</div>
					</div>
					<div class="col-4 form-group">
						<label for="info_pagina_zoom" class="control-label">Zoom:</label>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono-grande fondo-mapa-zoom"><img src="/skins/administracion/images/mapa-zoom.png"></span>
							</div>
							<input type="text" value="<?= $this->content->info_pagina_zoom; ?>" name="info_pagina_zoom" id="info_pagina_zoom" class="form-control">
						</div>
					</div>
				</div>
			</div>
		</div>

		<a id="seo" name="seo"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/seo.png"> Archivo SEO
			</h2>
			<div class="pading-dashboard">
				<div class="row">
					<div class="col-4 form-group">
						<h5> Descripción: </h5>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono-grande fondo-descripcion"><img src="/skins/administracion/images/descripcion.png" width="50px;"></span>
							</div>
							<textarea name="info_pagina_descripcion" id="info_pagina_descripcion" class="form-control" rows="6"><?= $this->content->info_pagina_descripcion; ?></textarea>
						</div>
					</div>
					<div class="col-4 form-group">
						<h5> Tags: </h5>
						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono-grande fondo-tags"><img src="/skins/administracion/images/tags.png" width="50px;"></span>
							</div>
							<textarea name="info_pagina_tags" id="info_pagina_tags" class="form-control" rows="6"><?= $this->content->info_pagina_tags; ?></textarea>
						</div>
					</div>
					<div class="col-4">
						<h5> Archivos SEO: </h5>
						<div class="contenedor-informacion">
							<div class="contenido">
								<input type="file" name="info_pagina_robot" id="info_pagina_robot" class="form-control  file-robot" data-buttonName="btn-primary" onchange="validardocumento('info_pagina_robot');" accept="text/plain">
								<br>
								<input type="file" name="info_pagina_sitemap" id="info_pagina_sitemap" class="form-control  file-sitemap" data-buttonName="btn-primary" onchange="validardocumento('info_pagina_sitemap');" accept="text/xml">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<a id="politicadatos" name="politicadatos"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/seo.png"> Política de Manejo de Datos
			</h2>
			<div class="pading-dashboard">
				<div class="row">
					<div class="col-12">
						<div><label>Título</label>

							<div class="input-group-prepend ">
							</div>
							<input type="text" name="info_pagina_titulo_legal" id="info_pagina_titulo_legal" class="form-control" value="<?= $this->content->info_pagina_titulo_legal; ?>">
						</div>
						<div style="margin-top:2%;">
							<label>Descripción</label>
							<textarea name="info_pagina_descripcion_legal" id="info_pagina_descripcion_legal" class="form-control tinyeditor" rows="10"><?= $this->content->info_pagina_descripcion_legal; ?></textarea>

						</div>

					</div>
					<div></div>
				</div>
			</div>
		</div>
		<a id="scripts" name="scripts"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/logo-scripts.png"> Scripts Head
			</h2>
			<br>
			<div class="pading-dashboard">
				<div class="row">
					<div class="col-12 form-group">

						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono-grande fondo-scripts"><img src="/skins/administracion/images/scripts.png" width="50px;"></span>
							</div>
							<textarea name="info_pagina_scripts" id="info_pagina_scripts" class="form-control" rows="10"><?= $this->content->info_pagina_scripts; ?></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>

		<a id="metricas" name="metricas"></a>
		<div class="div-dashboard">
			<h2>
				<img src="/skins/administracion/images/logo-scripts.png"> Script métricas
			</h2>
			<br>
			<div class="pading-dashboard">
				<div class="row">
					<div class="col-12 form-group">

						<div class="input-group">
							<div class="input-group-prepend ">
								<span class="input-group-text input-icono-grande fondo-scripts"><i class="fas fa-chart-pie icono-metricas"></i></span>
							</div>
							<textarea name="info_pagina_metricas" id="info_pagina_metricas" class="form-control" rows="10"><?= $this->content->info_pagina_metricas; ?></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="botones-acciones">
			<button class="btn btn-guardar" type="submit">Guardar</button>
			<a href="/administracion/panel" class="btn btn-cancelar">Cancelar</a>
		</div>
	</form>
</div>
