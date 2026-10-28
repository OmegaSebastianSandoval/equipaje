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
      <input type="text" maxlength="6" minlength="6" class="form-control" id="code" name="code" placeholder="Código"
        inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" autofocus required>
      <div class="help-block with-errors"></div>
    </div>
    <div class="form-check mt-2" style="color: #000; font-size: 14px;">
      <input class="form-check-input" type="checkbox" id="recordar" name="recordar" value="1" checked>
      <label class="form-check-label" for="recordar">Recordar este dispositivo por 30 días</label>
    </div>
  </div>
  <?php if ($this->error_login) { ?>
    <div class="error_login"><?php echo $this->error_login; ?></div>
  <?php } ?>
  <?php if ($this->mensaje_otp) { ?>
    <div class="text-center mb-2" style="color: #1a7f37; font-size: 14px;"><?php echo $this->mensaje_otp; ?></div>
  <?php } ?>
  <input type="hidden" id="csrf" name="csrf" value="<?php echo $this->csrf; ?>" />
  <div class="text-center"><button class="btn-azul-login" type="submit">Entrar</button></div>
  <div class="text-center"><a href="/administracion/loginuser/reenviarotp" class="olvido my-3">¿No te llegó? Reenviar código</a></div>
  <div class="text-center"><a href="/administracion/" class="olvido my-3">Regresar al inicio</a></div>
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
  // Deja solo dígitos (al pegar "Código: 123456" del correo) y envía al completar los 6
  const codeInput = document.getElementById("code");
  const otpForm = codeInput.form;
  const otpButton = otpForm.querySelector('button[type="submit"]');
  let enviado = false;
  codeInput.addEventListener("input", () => {
    codeInput.value = codeInput.value.replace(/\D/g, "").slice(0, 6);
    if (codeInput.value.length === 6 && !enviado) {
      otpForm.requestSubmit();
    }
  });

  // Evita doble envío (doble clic, o clic justo cuando se auto-envía)
  otpForm.addEventListener("submit", (event) => {
    if (enviado) {
      event.preventDefault();
      return;
    }
    enviado = true;
    otpButton.disabled = true;
    otpButton.textContent = "Verificando...";
  });
  window.addEventListener("pageshow", (event) => {
    if (event.persisted) {
      enviado = false;
      otpButton.disabled = false;
      otpButton.textContent = "Entrar";
    }
  });

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