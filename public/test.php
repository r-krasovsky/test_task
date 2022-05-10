<?php
chdir('../');
include_once 'vendor/autoload.php';

include_once 'init.php';
include_once 'init_session.php';

ModelUser::sendRegConfirmMail(1);




