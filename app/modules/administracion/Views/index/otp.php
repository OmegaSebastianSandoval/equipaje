<form autocomplete="off" action="/administracion/loginuser/otplogin" method="post">
  <div class="form-group ">
    <div class="col-12">
      <p style="color: #000; font-size: 14px; text-align: center;">
        <span class="text-center">Ingresa el código que te enviamos a tu correo electrónico</span>
      </p>
    </div>
    <label class="control-label sr-only">Código</label>
    <div class="input-group mb-2">
      <i class="fa-solid fa-shield icon-input-left"></i>
      <input type="text" maxlength="6" minlength="6" class="form-control" id="code" name="code" placeholder="Código" required>
      <div class="help-block with-errors"></div>
    </div>
  </div>
  <?php if ($this->error_login) { ?>
    <div class="error_login"><?php echo $this->error_login; ?></div>
  <?php } ?>
  <input type="hidden" id="csrf" name="csrf" value="<?php echo $this->csrf; ?>" />
  <div class="text-center"><a href="/administracion/" class="olvido my-3">Regresar al inicio</a></div>
  <div class="text-center"><button class="btn-azul-login" type="submit">Entrar</button></div>
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
  // Expresión regular para eliminar caracteres peligrosos
  const sanitizeInput = (input) => {
    return input.replace(/[<>'`{}()[\]\\\/]/g, ""); // Quita los caracteres prohibidos
  };

  document.body.addEventListener("input", (event) => {
    const field = event.target;
    if (field.tagName !== "INPUT" && field.tagName !== "TEXTAREA") return;

    const cleanValue = sanitizeInput(field.value);
    if (field.value !== cleanValue) {
      field.value = cleanValue; // Reemplaza el texto con la versión limpia

    } else {

      field.title = "";
    }
  });

  document.body.addEventListener("submit", (event) => {
    if (event.target.classList.contains("secureForm")) {
      event.preventDefault();
      let isValid = true;

      const inputs = event.target.querySelectorAll("input, textarea");
      inputs.forEach((field) => {
        const cleanValue = sanitizeInput(field.value);
        if (field.value !== cleanValue) {
          field.value = cleanValue; // Limpia el input antes de enviar

          isValid = false;
        }
      });

      if (isValid) {
        console.log("Formulario enviado correctamente.");
        event.target.submit();
      }
    }
  });
});
</script>