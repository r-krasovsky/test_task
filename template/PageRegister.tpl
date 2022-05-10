<script src='https://www.hCaptcha.com/1/api.js?onload=onCaptchaLoad&render=explicit' async defer></script>
<script>

</script>

<div class="container ">
    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-5 col-lg-9 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-2">
                <div class="card-body p-0">
                    <div class="pl-5 pr-5 pb-3 pt-3">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Регистрация</h1>
                        </div>
                        <form class="login user" id="register_form" action="<?=CONFIG["site_url"]?>register/save">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" class="form-control form-control-user" name="email"  placeholder="Введите email" required>
                            </div>
                            <div class="form-group">
                                <label>Имя</label>
                                <input type="text" class="form-control form-control-user" name="name" placeholder="Введите имя" required>
                            </div>
                            <div class="form-group">
                                <label>Пароль</label>
                                <input type="password" class="form-control form-control-user " name="passwd" placeholder="Пароль" required>
                            </div>
                            <div class="form-group">
                                <label>Повторите пароль</label>
                                <input type="password" class="form-control form-control-user " name="passwd_confirm" placeholder="Повторите пароль" required>
                            </div>
                            <div class="form-group">
                                <label>Фото</label>
                                <input type="file" name="photo" class="form-control-file">
                            </div>
                            <div class="form-group">
                                <div class="h-captcha" data-sitekey="979f2067-afbb-489f-9b26-22ab45cf0f3e" id="hCaptcha"></div>
                                
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Зарегестрироваться
                            </button>
                        </form>
                        <div class="text-center">
                            <a class="small" href="/login">Войти</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>