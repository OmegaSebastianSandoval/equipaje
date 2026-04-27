<?php if ($this->error != '') {?>
    <div class="text-center error_login">
        <?= $this->error;?>
    </div>
    <br>
    <div class="text-center"><a href="/administracion" class="olvido">Volver al Login</a></div>
<?php } else { ?>
    <?php if ($this->message != '') { ?>
        <div class="text-center mensaje_login">
            <?php echo $this->message; ?>
        </div>
        <br>
        <div class="text-center"><a href="/administracion" class="olvido">Volver al Login</a></div>
    <?php } else { ?>
        <div class="box_password">
            <form  data-bs-toggle="validator" role="form" method="post" action="/administracion/index/changepassword">
                <input type="hidden" name="code" value="<?php echo $this->code; ?>" />
                <div class="form-group">
                    <div class="info-olvido"> <strong>USUARIO:</strong> <?php echo $this->usuario; ?></div>
                </div>
                <div class="form-group">
                    <label class="control-label sr-only">Contraseña:</label>
                    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Contraseña" required value="" />
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group">
                    <label class="control-label sr-only">Repita Contraseña:</label>
                    <input type="password" name="re_password" class="form-control" data-match="#inputPassword" data-match-error="Las dos Contraseñas no son iguales"  value="" placeholder="Repita Contraseña" required/>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="text-center">
                    <button class="btn-azul-login" type="submit">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    <?php } ?>
<?php } ?>