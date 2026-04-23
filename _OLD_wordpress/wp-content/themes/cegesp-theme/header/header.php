<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<?php wp_head(); ?> <!-- necessário para incorporar estilos e scripts dinamicamente aqui -->
			<?php get_template_part('_dynamic-styles');?>
			
		</head>
		<body>
            <header id="header">
                <a id="container-logo" href="<?php echo esc_url( home_url() ); ?>">
                    <img src="<?php echo g_get_file_bucket()?>/assets/images/logo-cegesp-desktop.png" />
                    <span>CEGESP</span>
                </a>

                <div id="menu-toggle" title="Menu mobile">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <nav id="nav-group">
                    <ul id="nav-header">
                        <li id="about" class="hover-btn has-submenu">
                            <a>
                                <span>SOBRE</span>
                                <img src="<?php echo g_get_file_bucket()?>/assets/icons/icon-chevron-down.png" alt="v" />
                            </a>
                            <ul id="about-menu" class="submenu">
                                <li><a href="<?php echo esc_url(get_permalink(get_page_by_path('objetivo')));?>">OBJETIVO</a></li>
                                <li><a href="<?php echo esc_url(get_permalink(get_page_by_path('equipe')));?>">EQUIPE</a></li>
                            </ul>
                        </li>
                        <li class="hover-btn"><a href="<?php echo esc_url(get_permalink(get_page_by_path('infodados')));?>">INFO DADOS</a></li>
                        <li class="hover-btn"><a href="<?php echo esc_url(get_permalink(get_page_by_path('contato')));?>">CONTATO</a></li>
                        <li id="login"><a id="login-a" href="<?php echo esc_url( wp_login_url() ); ?>">LOGIN</a></li>
                    </ul>
                </nav>
            </header>