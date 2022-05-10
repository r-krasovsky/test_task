<div class="container ">
    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9 col-md-9">
            <?php if ($this->bConfirmSuccess): ?>
                <div class="alert alert-success">
                    Регистрация подтверждена<br>
                    Теперь вы можете перейти на страницу <a href="<?=CONFIG["site_url"]?>login">авторизации</a>
                </div>
            <?php elseif ($this->sConfirmCode !== ""): ?>
                <div class="alert alert-danger">
                    Код подтверждения регистрации не верный!
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Для подтверждения регистрации перейдите по ссылке в отправленном письме
                </div>
            <?php endif; ?>
            <a href="<?=CONFIG["site_url"]?>">
                <button class="btn btn-primary">Вернуться на главную</button>
            </a>
        </div>
    </div>
</div>