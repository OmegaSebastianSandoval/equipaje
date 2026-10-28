<h1 class="titulo-principal"><i class="fas fa-cogs"></i> <?php echo $this->titlesection; ?></h1>
<div class="container-fluid">
  <style>
    #toggle-filtros-maletas .fa-chevron-down { transition: transform .2s ease; }
    #toggle-filtros-maletas[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
  </style>
  <button id="toggle-filtros-maletas" class="btn btn-azul w-100 d-lg-none mt-4 mb-3 d-flex align-items-center justify-content-between py-2 px-3" type="button"
    data-bs-toggle="collapse" data-bs-target="#filtros-maletas" aria-expanded="false" aria-controls="filtros-maletas">
    <span><i class="fas fa-filter"></i> Filtros</span>
    <span><small class="mr-2 me-2">Mostrar / ocultar</small><i class="fas fa-chevron-down"></i></span>
  </button>
  <form action="<?php echo $this->route; ?>" method="post">
    <div id="filtros-maletas" class="content-dashboard collapse d-lg-block">
      <div class="row">
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>Codigo</label>
          <label class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text input-icono fondo-azul "><i class="fas fa-pencil-alt"></i></span>
            </div>
            <input type="text" class="form-control" name="maleta_codigo"
              value="<?php echo $this->getObjectVariable($this->filters, 'maleta_codigo') ?>"></input>
          </label>
        </div>
        <?php if (Session::getInstance()->get('kt_login_level') != '4') { ?>
          <div class="col-12 col-sm-6 col-lg-3 mb-2">
            <label>Aerolinea</label>
            <label class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text input-icono fondo-verde "><i class="far fa-list-alt"></i></span>
              </div>
              <select class="form-control" name="maleta_aerolinea">
                <option value="">Todas</option>
                <?php foreach ($this->list_maleta_aerolinea as $key => $value): ?>
                  <option value="<?= $key; ?>" <?php if ($this->getObjectVariable($this->filters, 'maleta_aerolinea') == $key) {
                      echo "selected";
                    } ?>><?= $value; ?></option>
                <?php endforeach ?>
              </select>
            </label>
          </div>
        <?php } ?>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>Estado</label>
          <label class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text input-icono fondo-azul-claro "><i class="far fa-list-alt"></i></span>
            </div>
            <select class="form-control" name="maleta_estado">
              <option value="" selected disabled>Todas</option>
              <option value="2" <?php if ($this->getObjectVariable($this->filters, 'maleta_estado') == '2') {
                echo 'selected';
              } ?>>Sin Reclamar</option>
              <option value="1" <?php if ($this->getObjectVariable($this->filters, 'maleta_estado') == '1') {
                echo 'selected';
              } ?>>Reclamadas</option>
            </select>
          </label>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>Fecha de ingreso</label>
          <label class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text input-icono fondo-morado "><i class="fas fa-pencil-alt"></i></span>
            </div>
            <input type="text" class="form-control" name="maleta_fingreso"
              value="<?php echo $this->getObjectVariable($this->filters, 'maleta_fingreso') ?>"
              data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-language="es"></input>
          </label>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>Fecha salida</label>
          <label class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text input-icono fondo-verde-claro "><i class="fas fa-pencil-alt"></i></span>
            </div>
            <input type="text" class="form-control" name="maleta_fsalida"
              value="<?php echo $this->getObjectVariable($this->filters, 'maleta_fsalida') ?>" data-provide="datepicker"
              data-date-format="yyyy-mm-dd" data-date-language="es"></input>
          </label>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>Hora ingreso</label>
          <label class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text input-icono fondo-rosado "><i class="fas fa-pencil-alt"></i></span>
            </div>
            <select name="maleta_hingreso" id="maleta_hingreso" class="form-control">
              <option value="" selected disabled>Seleccione...</option>
              <?php for ($i = 0; $i <= 23; $i++) { ?>
                <?php
                $zero = '';
                if ($i < 10) {
                  $zero = '0';
                }
                ?>
                <option value="<?= $zero . $i; ?>:" <?php if ($this->getObjectVariable($this->filters, 'maleta_hingreso') == $zero . $i . ':') {
                      echo 'selected';
                    } ?>><?= $zero . $i; ?></option>
              <?php } ?>
            </select>
          </label>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>Hora salida</label>
          <label class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text input-icono fondo-cafe "><i class="fas fa-pencil-alt"></i></span>
            </div>
            <select name="maleta_hsalida" id="maleta_hsalida" class="form-control">
              <option value="" selected disabled>Seleccione...</option>
              <?php for ($i = 0; $i <= 23; $i++) { ?>
                <?php
                $zero = '';
                if ($i < 10) {
                  $zero = '0';
                }
                ?>
                <option value="<?= $zero . $i; ?>:" <?php if ($this->getObjectVariable($this->filters, 'maleta_hsalida') == $zero . $i) {
                      echo 'selected';
                    } ?>><?= $zero . $i; ?></option>
              <?php } ?>
            </select>
          </label>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>Vuelo</label>
          <label class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text input-icono fondo-azul "><i class="fas fa-plane"></i></span>
            </div>
            <input type="text" class="form-control" name="maleta_vuelo_numero" placeholder="Ej: AV 8484"
              value="<?php echo $this->getObjectVariable($this->filters, 'maleta_vuelo_numero') ?>"></input>
          </label>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>&nbsp;</label>
          <button type="submit" class="btn btn-block btn-azul"><i class="fas fa-filter"></i> Filtrar</button>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
          <label>&nbsp;</label>
          <a class="btn btn-block btn-azul-claro " href="<?php echo $this->route; ?>?cleanfilter=1"> <i
              class="fas fa-eraser"></i> Limpiar Filtro</a>
        </div>
      </div>
    </div>
  </form>
  <?php if ($this->totalpages > 1): ?>
    <div class="mt-4 mb-5 ">
      <nav aria-label="Paginación">
        <ul class="pagination justify-content-center">
          <?php
          $url = $this->route;
          $maxSide = 5; // mostrar hasta 5 páginas a cada lado
          $start = max(1, $this->page - $maxSide);
          $end = min($this->totalpages, $this->page + $maxSide);

          if ($this->page != 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($this->page - 1) . '">&laquo; Anterior</a></li>';
          }

          // Mostrar primera página y puntos si es necesario
          if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=1">1</a></li>';
            if ($start > 2) {
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
          }

          for ($i = $start; $i <= $end; $i++) {
            if ($this->page == $i) {
              echo '<li class="active page-item"><a class="page-link">' . $this->page . '</a></li>';
            } else {
              echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
            }
          }

          // Mostrar puntos y última página si es necesario
          if ($end < $this->totalpages) {
            if ($end < $this->totalpages - 1) {
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $this->totalpages . '">' . $this->totalpages . '</a></li>';
          }

          if ($this->page != $this->totalpages) {
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($this->page + 1) . '">Siguiente &raquo;</a></li>';
          }
          ?>
        </ul>
      </nav>
    </div>
  <?php endif; ?>
  <div class="content-dashboard">
    <div class="franja-paginas">
      <div class="row">
        <div class="col-12 col-lg-4 order-2 order-lg-1 mb-2 mb-lg-0">
          <div class="titulo-registro">Se encontraron <?php echo $this->register_number; ?> Registros</div>
        </div>
        <div class="col-8 col-lg-3 order-3 order-lg-2 text-right">
          <div class="texto-paginas">Registros por pagina:</div>
        </div>
        <div class="col-4 col-lg-1 order-4 order-lg-3">
          <select class="form-control form-control-sm selectpagination">
            <option value="20" <?php if ($this->pages == 20) {
              echo 'selected';
            } ?>>20
            </option>
            <option value="30" <?php if ($this->pages == 30) {
              echo 'selected';
            } ?>>30
            </option>
            <option value="50" <?php if ($this->pages == 50) {
              echo 'selected';
            } ?>>50
            </option>
            <option value="100" <?php if ($this->pages == 100) {
              echo 'selected';
            } ?>>100
            </option>
          </select>
        </div>
        <div class="col-12 col-lg-4 order-1 order-lg-4 d-flex justify-content-lg-end mb-3 mb-lg-0">
          <?php if (
            Session::getInstance()->get('kt_login_level') == '1' || Session::getInstance()->get('kt_login_level') == '2' || Session::getInstance()->get('kt_login_level') == '3'
          ) { ?>
            <div class="text-right flex-fill flex-lg-grow-0 mr-2 me-2"><a class="btn btn-sm btn-success btn-block" href="<?php echo $this->route . "/registrar"; ?>">
                <i class="fas fa-plus-square"></i> Registrar</a></div>
            <div class="text-right flex-fill flex-lg-grow-0"><a class="btn btn-sm btn-success btn-block" href="<?php echo $this->route . "/reclamar"; ?>">
                <i class="fas fa-plus-square"></i> Reclamar</a></div>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="content-table table-responsive">
      <table class=" table table-striped  table-hover table-administrator text-left">
        <thead>
          <tr>
            <td>Código</td>
            <td>Aerolínea</td>
            <td>Aerolínea de conexión</td>
            <td>Estado</td>
            <td>Fecha de ingreso</td>
            <td>Fecha salida</td>
            <td>Hora ingreso</td>
            <td>Hora salida</td>
            <td>Over</td>
            <td>Vuelo</td>
            <td>Salida</td>
            <td>Tiempo restante</td>
            <td>Margen al registrar</td>
            <td width="100"></td>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->lists as $content) { ?>
           
            <?php $id = $content->maleta_id; ?>
            <?php
            $salida = $this->salidas[$id];
            $pendiente = $content->maleta_estado == '';
            $claseFila = '';
            if ($pendiente && in_array($salida['urgencia'], ['alta', 'salio'])) {
              $claseFila = 'table-danger';
            } elseif ($pendiente && $salida['urgencia'] == 'media') {
              $claseFila = 'table-warning';
            }
            ?>
            <tr class="<?= $claseFila; ?>">
              <td><?= $content->maleta_codigo; ?></td>
              <td><?= $this->list_maleta_aerolinea[$content->maleta_aerolinea]; ?>
              <td><?= $this->list_maleta_aerolinea[$content->maleta_aerolinea_conexion]; ?>
              <td><?= $this->list_maleta_estado[$content->maleta_estado]; ?>
              <td><?= $content->maleta_fingreso; ?></td>
              <td><?= $content->maleta_fsalida; ?></td>
              <td><?= $content->maleta_hingreso; ?></td>
              <td><?= $content->maleta_hsalida; ?></td>
              <td>
                <?php echo $content->maleta_over_tipo;
                $dataOver = [];
                $dataOver['N'] = 'Nacional';
                $dataOver['I'] = 'Internacional';
                if ($content->maleta_over_lugar != '' && $content->maleta_over_tipo == 1) {
                  echo " / " .  $dataOver[$content->maleta_over_lugar];
                }

                ?>
              </td>
              <td><?= $salida['vuelo']; ?></td>
              <td>
                <?php if ($salida['salida_programada']) { ?>
                  <span data-bs-toggle="tooltip" title="Programada: <?= $salida['salida_programada']; ?>"><?= $salida['salida']; ?> <i class="fas fa-clock text-danger"></i></span>
                <?php } else { ?>
                  <?= $salida['salida']; ?>
                <?php } ?>
              </td>
              <td><?= $pendiente ? $salida['tiempo_restante'] : ''; ?></td>
              <td><?= $content->maleta_minutos_disponibles !== null ? Core_Model_Vuelos::formatoMinutos($content->maleta_minutos_disponibles) : ''; ?></td>
              <td class="text-right">
                <div>
                  <?php if (Session::getInstance()->get('kt_login_level') == '1') { ?>
                    <a class="btn btn-azul btn-sm" href="<?php echo $this->route; ?>/manage?id=<?= $id ?>"
                      data-bs-toggle="tooltip" data-placement="top" title="Editar"><i class="fas fa-pen-alt"></i></a>
                    <span data-bs-toggle="tooltip" data-placement="top" title="Eliminar"><a class="btn btn-rojo btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modal<?= $id ?>"><i class="fas fa-trash-alt"></i></a></span>
                  </div>
                <?php } ?>
                <!-- Modal -->
                <div class="modal fade text-left" id="modal<?= $id ?>" tabindex="-1" role="dialog"
                  aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Eliminar Registro</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                      </div>
                      <div class="modal-body">
                        <div class="">¿Esta seguro de eliminar este registro?</div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <a class="btn btn-danger"
                          href="<?php echo $this->route; ?>/delete?id=<?= $id ?>&csrf=<?= $this->csrf; ?><?php echo ''; ?>">Eliminar</a>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <input type="hidden" id="csrf" value="<?php echo $this->csrf ?>"><input type="hidden" id="page-route"
      value="<?php echo $this->route; ?>/changepage">
  </div>
  <?php if ($this->totalpages > 1): ?>
    <div class="mt-4 mb-5 ">
      <nav aria-label="Paginación">
        <ul class="pagination justify-content-center">
          <?php
          $url = $this->route;
          $maxSide = 5; // mostrar hasta 5 páginas a cada lado
          $start = max(1, $this->page - $maxSide);
          $end = min($this->totalpages, $this->page + $maxSide);

          if ($this->page != 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($this->page - 1) . '">&laquo; Anterior</a></li>';
          }

          // Mostrar primera página y puntos si es necesario
          if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=1">1</a></li>';
            if ($start > 2) {
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
          }

          for ($i = $start; $i <= $end; $i++) {
            if ($this->page == $i) {
              echo '<li class="active page-item"><a class="page-link">' . $this->page . '</a></li>';
            } else {
              echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
            }
          }

          // Mostrar puntos y última página si es necesario
          if ($end < $this->totalpages) {
            if ($end < $this->totalpages - 1) {
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $this->totalpages . '">' . $this->totalpages . '</a></li>';
          }

          if ($this->page != $this->totalpages) {
            echo '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($this->page + 1) . '">Siguiente &raquo;</a></li>';
          }
          ?>
        </ul>
      </nav>
    </div>
  <?php endif; ?>
</div>