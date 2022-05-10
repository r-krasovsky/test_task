<?php include_once "common_header.tpl";?>
    <nav class="navbar navbar-expand bg-white navbar-light fixed-top  shadow container">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item <?=($_SERVER["REQUEST_URI"] === "/main")? "active" : ""?>">
                    <a class="nav-link" href="/main">Главная <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item <?=($_SERVER["REQUEST_URI"] === "/user/list" || $_SERVER["REQUEST_URI"] === "/")? "active" : ""?>"">
                    <a class="nav-link" href="/user/list">Список пользователей</a>
                </li>
            </ul>
        </div>
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <div class="topbar-divider d-none d-sm-block"></div>
                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 "><?=$oCurrentUser->name?></span>
                        <i class="fas fa-user d-lg-none"></i>
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="/user/<?=$oCurrentUser->id?>">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Профиль
                        </a>
                
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/logout" _data-toggle="modal" _data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Выход
                        </a>
                    </div>
                </li>
    
            </ul>
    </nav>
<div class="container ">