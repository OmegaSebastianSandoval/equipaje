<?php
$vuelo = $this->vuelo;
$programada = date('H:i', strtotime($vuelo->vuelo_schedule));
$actualizada = $vuelo->vuelo_actual ? date('H:i', strtotime($vuelo->vuelo_actual)) : $programada;
$overLugares = ['N' => 'Nacional', 'I' => 'Internacional'];
?>
<div
  style="font-family: Arial, sans-serif; color: #1c3455; background: url('https://equipajerezagado.opain.co/skins/administracion/images/dorado-01.jpg'); background-position: center; background-size: cover; padding: 50px 15px;">
  <div
    style="width: 80%; margin: auto; padding: 10px; background-color: #000; border-radius: 13px; color: #f4f5f5; max-width: 560px;">
    <div style="border: 1px solid yellow; border-radius: 10px; padding: 20px; ">
      <div>
        <img src="https://equipajerezagado.opain.co/skins/administracion/images/dorado-logo.png" alt=""
          style="width: 250px; margin: auto; display: block;">
      </div>
      <div style="text-align: center; ">
        <h1>Hola! <?php echo $this->user->aerolinea_nombre; ?></h1>
      </div>
      <p style="text-align: center;">
        El vuelo <strong style="color: #ffd04f;"><?php echo $this->numeroVuelo; ?></strong>
        <?php if ($vuelo->vuelo_destino) { ?>con destino a <strong><?php echo $vuelo->vuelo_destino; ?></strong><?php } ?>
        sale en <strong style="color: #ffd04f;"><?php echo $this->minutosRestantes; ?></strong>
        y tiene <?php echo count($this->maletas); ?> maleta(s)<?php echo $this->esOver ? ' over' : ''; ?> sin reclamar.
      </p>
      <p style="text-align: center;">
        Salida programada: <strong><?php echo $programada; ?></strong>
        <?php if ($actualizada != $programada) { ?>
          &middot; Salida actualizada: <strong style="color: #ffd04f;"><?php echo $actualizada; ?></strong>
        <?php } ?>
        <?php if ($vuelo->vuelo_puerta) { ?>
          &middot; Puerta: <strong><?php echo $vuelo->vuelo_puerta; ?></strong>
        <?php } ?>
      </p>
      <table style="width: 100%; border-collapse: collapse; background-color: #f4f5f5; color: #1c3455; border-radius: 5px;">
        <thead>
          <tr>
            <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #ccc;">Código</th>
            <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #ccc;">Registro</th>
            <?php if ($this->esOver) { ?>
              <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #ccc;">Over</th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->maletas as $maleta) { ?>
            <tr>
              <td style="padding: 8px 10px; border-bottom: 1px solid #ddd;"><?php echo Core_Model_Sendingemail::codigoSinEnlace($maleta->maleta_codigo); ?></td>
              <td style="padding: 8px 10px; border-bottom: 1px solid #ddd;"><?php echo $maleta->maleta_fingreso . ' ' . substr($maleta->maleta_hingreso, 0, 5); ?></td>
              <?php if ($this->esOver) { ?>
                <td style="padding: 8px 10px; border-bottom: 1px solid #ddd;">
                  <?php echo $maleta->maleta_over_tipo;
                  if ($maleta->maleta_over_tipo == '1' && isset($overLugares[$maleta->maleta_over_lugar])) {
                    echo ' - ' . $overLugares[$maleta->maleta_over_lugar];
                  } ?>
                </td>
              <?php } ?>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
