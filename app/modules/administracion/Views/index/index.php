<form action="/administracion/loginuser" method="post" class="recaptcha-form">
    <div class="form-group">
        <label class="control-label sr-only">Usuario</label>
        <div class="input-group">
            <i class="fas fa-user-tie icon-input-left"></i>
            <input type="text" class="form-control" id="user" name="user" placeholder="Usuario" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" required>
            <div class="help-block with-errors"></div>
        </div>
    </div>
    <div class="form-group my-4">
        <label class="control-label sr-only">Contraseña</label>
        <div class="input-group">
            <i class="fas fa-shield-alt icon-input-left"></i>
            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" autocomplete="current-password" required>
            <div class="help-block with-errors"></div>
        </div>
        <input type="hidden" name="email">

    </div>
    <?php if ($this->error_login) { ?>
        <div class="error_login"><?php echo $this->error_login; ?></div>
    <?php } ?>
    <div class="form-group my-2 d-none">
        <div class="g-recaptcha" data-sitekey="6Lf6QnAcAAAAAHHlpbZTNWZ_Rep9bdKwdl82huj-"></div>
    </div>
    <input type="hidden" id="csrf" name="csrf" value="<?php echo $this->csrf; ?>" />
    <div class="text-center"><a href="/administracion/index/olvido" class="olvido">¿Has olvidado tu contraseña?</a></div>
    <div class="text-center">
        <button class="btn-azul-login" id="submitButton" type="submit">Entrar</button>
    </div>
</form>

<script>
    function disableSubmitButton(form) {


        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Enviando...'; // Opcional: Cambia el texto del botón mientras se envía el formulario
    }
</script>
<script>
    document.querySelector('.recaptcha-form').addEventListener('submit', function(event) {
       // Evita doble envío (doble clic o Enter repetido)
       if (this.dataset.enviado === '1') {
        event.preventDefault();
        return;
       }
       this.dataset.enviado = '1';
       disableSubmitButton(this);
       if (!(document.cookie.indexOf('consentimiento=aceptado') !== -1)) {
        var fechaExpiracion = new Date();
        fechaExpiracion.setFullYear(fechaExpiracion.getFullYear() + 1);
        document.cookie = "consentimiento=aceptado; expires=" + fechaExpiracion.toUTCString() + "; path=/; SameSite=Lax";
      }
    });
    // Si el navegador restaura la página con "atrás", reactiva el botón
    window.addEventListener('pageshow', function(event) {
      if (event.persisted) {
        const form = document.querySelector('.recaptcha-form');
        const submitButton = form.querySelector('button[type="submit"]');
        form.dataset.enviado = '';
        submitButton.disabled = false;
        submitButton.textContent = 'Entrar';
      }
    });
</script>