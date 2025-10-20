<?php
/* Valores globais */
function g_get_file_bucket(){return 'https://cegesp.b-cdn.net';}

/* Carregamento dos CSS e JS */

function registrar_estilos(){ // todos os estilos do tema
    
    wp_register_style('style',get_stylesheet_uri(),array(),'1.0','all'); // enfileira o carregamento do CSS
    wp_register_style('style_tablet', get_template_directory_uri().'/style_tablet.css',array(),'1.0','all');
    wp_register_style('style_phone', get_template_directory_uri().'/style_phone.css',array(),'1.0','all');
    wp_register_style('header', get_template_directory_uri().'/header/header.css',array(),'1.0','all');
    wp_register_style('header_tablet', get_template_directory_uri().'/header/header_tablet.css',array(),'1.0','all');
    wp_register_style('header_phone', get_template_directory_uri().'/header/header_phone.css',array(),'1.0','all');
    wp_register_style('footer', get_template_directory_uri().'/footer/footer.css',array(),'1.0','all');
    wp_register_style('footer_tablet', get_template_directory_uri().'/footer/footer_tablet.css',array(),'1.0','all');
    wp_register_style('footer_phone', get_template_directory_uri().'/footer/footer_phone.css',array(),'1.0','all');
    wp_register_style('objetivo', get_template_directory_uri().'/objetivo/objetivo.css',array(),'1.0','all');
    wp_register_style('objetivo_tablet', get_template_directory_uri().'/objetivo/objetivo_tablet.css',array(),'1.0','all');
    wp_register_style('objetivo_phone', get_template_directory_uri().'/objetivo/objetivo_phone.css',array(),'1.0','all');
    wp_register_style('equipe', get_template_directory_uri().'/equipe/equipe.css',array(),'1.0','all');
    wp_register_style('equipe_tablet', get_template_directory_uri().'/equipe/equipe_tablet.css',array(),'1.0','all');
    wp_register_style('equipe_phone', get_template_directory_uri().'/equipe/equipe_phone.css',array(),'1.0','all');
    wp_register_style('infodados', get_template_directory_uri().'/infodados/infodados.css',array(),'1.0','all');
    wp_register_style('infodados_tablet', get_template_directory_uri().'/infodados/infodados_tablet.css',array(),'1.0','all');
    wp_register_style('infodados_phone', get_template_directory_uri().'/infodados/infodados_phone.css',array(),'1.0','all');
    wp_register_style('contato', get_template_directory_uri().'/contato/contato.css',array(),'1.0','all');
    wp_register_style('contato_tablet', get_template_directory_uri().'/contato/contato_tablet.css',array(),'1.0','all');
    wp_register_style('contato_phone', get_template_directory_uri().'/contato/contato_phone.css',array(),'1.0','all');
    //wp_enqueue_script( 'id', $src:string, $deps:array, '1.0', $in_footer:boolean )
}
add_action('init', 'registrar_estilos'); // registra todos os estilos do tema

function carregar_estilos() { // carrega os estilos por página
    // desativa estilos indesejados
    wp_dequeue_style( 'global-styles' ); 
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-blocks-style' );
    wp_dequeue_style( 'classic-theme-styles' );
    
    // enfileira estilos css 
    wp_enqueue_style( 'style' );
    wp_enqueue_style( 'style_tablet' );
    wp_enqueue_style( 'style_phone' );
    wp_enqueue_style( 'header' );
    wp_enqueue_style( 'header_tablet' );
    wp_enqueue_style( 'header_phone' );
    wp_enqueue_script('header-script',get_template_directory_uri() . '/header/header.js',array(),'1.0',array( 'strategy' => 'defer' ));
    wp_enqueue_style( 'footer' );
    wp_enqueue_style( 'footer_tablet' );
    wp_enqueue_style( 'footer_phone' );
    if(is_page('home')){
       wp_enqueue_style( 'index' );
       wp_enqueue_style( 'index_tablet' );
       wp_enqueue_style( 'index_phone' );
    }else if(is_page('objetivo')){
       wp_enqueue_style( 'objetivo' );
       wp_enqueue_style( 'objetivo_tablet' );
       wp_enqueue_style( 'objetivo_phone' );
    }else if(is_page('equipe')){
       wp_enqueue_style( 'equipe' );
       wp_enqueue_style( 'equipe_tablet' );
       wp_enqueue_style( 'equipe_phone' );
    }else if(is_page('infodados')){
       wp_enqueue_style( 'infodados' );
       wp_enqueue_style( 'infodados_tablet' );
       wp_enqueue_style( 'infodados_phone' );
       wp_enqueue_script('infodados_js',get_template_directory_uri() . '/infodados/infodados.js',array(),'1.0',array( 'strategy' => 'defer' ));
       wp_enqueue_script('sendData_js',get_template_directory_uri() . '/infodados/sendData.js',array(),'1.0',array( 'strategy' => 'defer' ));
    }else if(is_page('contato')){
       wp_enqueue_style( 'contato' );
       wp_enqueue_style( 'contato_tablet' );
       wp_enqueue_style( 'contato_phone' ); 
    }
}
add_action( 'wp_enqueue_scripts', 'carregar_estilos' ); // engancha o carregamento

require_once 'CPTs/documents.php';
$doc = new Documents_CPT();

require_once 'rest-api/rest-api.php';
new Rest_API();

/**************************************************************************************************************************************************** */
/**************************************************************************************************************************************************** */
/**************************************************************************************************************************************************** */

/* Registrar o menu */
register_nav_menus(
    array(
        'main_menu'=>'Menu principal',
        'footer_menu'=>'Menu rodapé'
    )
);

// adiciona suporte à thumb de imagens dos posts
add_theme_support( 'post-thumbnails' );
// adiciona suport aos "post formats"
add_theme_support( 'post-formats',array('video'));
// para colocar a tag <title> no cabeçalho do html
add_theme_support( 'title-tag');

//adiciona suporte à tradução do tema
// function suporte_traducao(){
//     load_theme_textdomain('algol-dev',get_template_directory( ).'/languages');
// }
// add_action("after_setup_theme", 'suporte_traducao');

/** Adiciona os shortcodes do tema */

/** remove a metatag '<meta name="generator"...' */
remove_action('wp_head', 'wp_generator');

/**************************************************************************************************************************************************** */
/**************************************************************************************************************************************************** */
/**************************************************************************************************************************************************** */
/** Disable the emoji's
 */
function disable_emojis() {
 remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
 remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
 remove_action( 'wp_print_styles', 'print_emoji_styles' );
 remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
 remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
 remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
 remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
 add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
 add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 * 
 * @param array $plugins 
 * @return array Difference betwen the two arrays
 */
function disable_emojis_tinymce( $plugins ) {
 if ( is_array( $plugins ) ) {
 return array_diff( $plugins, array( 'wpemoji' ) );
 } else {
 return array();
 }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
 if ( 'dns-prefetch' == $relation_type ) {
 /** This filter is documented in wp-includes/formatting.php */
 $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

$urls = array_diff( $urls, array( $emoji_svg_url ) );
 }

return $urls;
}
?>