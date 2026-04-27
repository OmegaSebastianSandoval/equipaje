
<div style="font-family: Arial, sans-serif; color: #1c3455; background: url('https://nivel5.omegasolucionesweb.com/skins/administracion/images/dorado-01.jpg'); background-position: center; background-size: cover; padding: 50px 15px;">
  <div style="width: 80%; margin: auto; padding: 10px; background-color: #000; border-radius: 13px; color: #f4f5f5; max-width: 500px;">
    <div style="border: 1px solid yellow; border-radius: 10px; padding: 20px; ">
      <div>
        <img src="https://nivel5.omegasolucionesweb.com/skins/administracion/images/dorado-logo.png" alt="" style="width: 250px; margin: auto; display: block;">
      </div>
      <div style="text-align: center; ">
        <h1>Hola! <?php echo $this->user->aerolinea_nombre; ?></h1>
      </div>
      <p style="text-align: center;">
        Se ha reclamado la maleta con el código:
      </p>
      <p>
        <span style="color: #6e6e6e; background-color: #f4f5f5; display: block; padding: 10px 15px; border-radius: 5px; text-decoration: none; text-align: center;"><?php echo $this->code; ?></span>
      </p>
    </div>
  </div>
</div>
