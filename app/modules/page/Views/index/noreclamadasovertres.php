<!-- Vista de tabla para maletas no reclamadas -->
<div class="w-100">
  <div class="row m-0">
    <!-- <div class="col-12 mt-5">
      <h2>Maletas no reclamadas</h2>
    </div> -->
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead>
          <tr>
            <th colspan="7" style="border-bottom: 0; padding: 0 !important;">
              <div class="d-flex justify-content-center" style="height: 70px;">
                <img src="/skins/administracion/images/dorado-logo.png" alt="" style="">
              </div>
            </th>
          </tr>
          <tr>
            <th colspan="7"
              style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; padding: 15px 20px !important; border: none;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <span
                  style="background: rgba(255,255,255,0.2); padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.9rem;">TIPO
                  3</span>
                <span style="font-size: 1rem; font-weight: 500; color: #fff;">Equipaje over - categoría 3</span>
              </div>
            </th>
          </tr>
          <tr>
            <th scope="col">Código</th>
            <th scope="col">Aerolínea</th>
            <th scope="col">Estado</th>
            <th scope="col">Tiempo de espera</th>
            <th scope="col">Fecha de ingreso</th>
            <th scope="col">Hora de ingreso</th>
            <th scope="col">Over</th>
          </tr>
        </thead>
        <tbody id="baggageTbodyOverTres">
          <?php foreach ($this->maletas as $maleta): ?>
            <tr>
              <td><?= $maleta->maleta_codigo ?></td>
              <?php if ($maleta->aerolinea_imagen != '') { ?>
                <td><img src="/images/<?php echo $maleta->aerolinea_imagen ?>" alt=""></td>
              <?php } else { ?>
                <td><?= $maleta->aerolinea_nombre ?></td>
              <?php } ?>
              <td><?= $maleta->maleta_fingreso ?></td>
              <td><?= $maleta->maleta_hingreso ?></td>
              <td><?= $maleta->maleta_over_tipo ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<style>
  * {
    box-sizing: border-box;
  }

  body {
    font-family: Arial, sans-serif;
    margin: 0;
    background-color: #f4f4f4;
    overflow: hidden;
  }

  table {
    width: 100%;
    /* margin-top: 20px; */
    border-collapse: collapse;
    /* border-radius: 15px; */
    overflow: hidden;
    margin-bottom: 0px !important;
  }

  th,
  td {
    padding: 10px 20px !important;
    text-align: left;
    border-bottom: 1px solid #ddd;
    height: 60px;
    vertical-align: middle;
  }

  table thead th {
    background-color: #000 !important;
    color: #ffd04f;
    font-size: 1.3rem;
    font-weight: 500;
  }

  tr:hover {
    background-color: #f5f5f5;
  }

  img {
    height: 100%;
  }

  header {
    display: none;
  }

  footer {
    display: none;
  }

  .fecha {
    font-size: 14px;
  }
</style>
<script>
  // $(document).ready(function(){
  //   setInterval(function(){
  //     $.ajax({
  //       url: '/page/index/getNoReclamadas',
  //       mehtod: 'post',
  //       dataType: 'json',
  //       success: function(data){
  //         $('table tbody').html(data.html);
  //       }
  //     })
  //   }, 10000)
  // })
</script>