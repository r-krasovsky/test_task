<div class="container-fluid bg-gradient-primary h-100">
    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-5 col-lg-9 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Авторизация</h1>
                        </div>
                        <form  class="login user" id="login_form" action="<?=CONFIG["site_url"]?>login/authorise">
                            <div class="form-group">
                                <input type="text" class="form-control form-control-user" name="login" id="login" placeholder="Введите логин (ваш email)" required>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-user " name="passwd" placeholder="Пароль" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Войти
                            </button>
                        </form>
                        <div class="text-center">
                            <a class="small" href="/register">Зарегестрироваться</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
