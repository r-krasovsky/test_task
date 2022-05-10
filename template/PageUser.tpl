<div class="col-xs-12 col-sm-12 col-md-12 col-lg-8 col-xs-offset-0 col-sm-offset-0 col-md-offset-3 col-lg-offset-3 toppad">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Профиль пользователя: <?=$this->oUser->name?></h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3 col-lg-3 " align="center">
                    <img alt="User Pic" src="<?=CONFIG["site_url"]."user/photo/".$this->oUser->id?>" class="img-fluid img-thumbnail">
                </div>
                <div class=" col-md-9 col-lg-9 ">
                    <table class="table table-user-information">
                        <tbody>
                        <tr>
                            <td>Имя</td>
                            <td><?=$this->oUser->name?></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><a href="mailto:<?=$this->oUser->email?>"><?=$this->oUser->email?></a></td>
                        </tr>
                        <tr>
                            <td>API key</td>
                            <td><?=$this->oUser->key?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>