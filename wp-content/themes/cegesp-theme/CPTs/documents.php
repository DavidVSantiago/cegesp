<?php
/** Esta classe é responsável por registrar o cpt dos Documentos do site */

if(!class_exists('Documents_CPT')){
    class Documents_CPT{

        # **************************************************************************************
        # ATRIBUTOS     
        # **************************************************************************************
        private $palavra_chave_value;
        private $esfera_value;
        private $agenda_value;
        private $tipo_doc_value;
        private $ano_value;
        private $file_url_value;

        function get_palavra_chave_value() {
            return 'saude';
        }

        function get_esfera_value() {
            // Exemplo: return $wpdb->get_results("SELECT id, name FROM custom_spheres_table", ARRAY_A);
            return 'Federal';
        }
        
        function get_agenda_value() {
            return 'poder_judiciario';
        }

        function get_tipo_doc_value() {
            return 'ppa';
        }

        function get_ano_value() {
            return '2006';
        }

        function get_file_url_value() {
            return 'https://cegesp.b-cdn.net/assets/dumb.pdf';
        }
        
        function __construct(){
            add_action('init',[$this,'create_documents_cpt']); # agenda a criação do CPT
            add_action('add_meta_boxes',[$this,'create_meta_boxes']); # agenda a criação dos Metaboxes para o CPT
            add_action('save_post',[$this,'save_meta_boxes_data']); # para salvar os dados do metabox na tabela 'wp_postmeta'

            // Carregando as opções dinâmicas
            $this->palavra_chave_value = strtolower(trim($this->get_palavra_chave_value()));
            $this->esfera_value = strtolower(trim($this->get_esfera_value()));
            $this->agenda_value = strtolower(trim($this->get_agenda_value()));
            $this->tipo_doc_value  = strtolower(trim($this->get_tipo_doc_value()));
            $this->ano_value  = strtolower(trim($this->get_ano_value()));
            $this->file_url_value  = strtolower(trim($this->get_file_url_value()));
        }

        # **************************************************************************************
        # MÉTODOS
        # **************************************************************************************

        public function create_documents_cpt(){
            $labels = [ # rótulos do CPT
                'name' => _x( 'Documentos', 'nome genérico do post type' ),
                'singular_name' => _x( 'Documento', 'nome singular do post type' ),
                'add_new' => _x('Novo Documento', 'nome add_new'),
                'add_new_item'=>_x('Adicionar Novo Documento','nome add_new_item'),
                'edit_item'=>_x('Editar Documento','nome edit_item'),
            ];
            $supports = [ # recursos que a área de criação do CPT terá
                    'title',
                    'custom-fields',
            ];
            $args = [ # lista de argumentos de configuração do CPT
                'labels' => $labels,
                'description' => 'Posts dos documentos',
                'public'=>true, // necessário para aparecer no painel
                'show_in_menu' => true,
                'supports' => $supports,
                'capability_type' => 'post',
                'hierarchical' => 'false', // permite habilitar posts pais e filhos
                'menu_position'=>5, //
                'show_in_admin_bar'=>true, //
                'show_in_nav_menus'=>true,
                'can_export'=>true,
                'exclude_from_search'=>false,
                'publicly_queryable'=>true,
                'show_in_rest'=>false,
                'menu_icon'=>'dashicons-media-code',
                'supports'=>array('title') //'excerpt','editor'
            ];
            register_post_type('documents',$args); # registra o cpt
            update_option('rewrite_rules',''); # para limpar o cache das regras de reescrita
        }

        public function create_meta_boxes(){
            add_meta_box(
                'documents-meta-box', // identificador unico
                'Dados do documento', // titulo do metabox
                function($post){// callback de construção do layout da metabox
                    require_once('views-metaboxes/view.documents-meta-box.php'); // o layout é definido em view (uma forma inteligente de fazer)
                },
                'documents', // tela onde aparece (neste caso a chave do cpt)
                'normal', // posição
                'high', // prioridade
            );
        }

        public function save_meta_boxes_data($post_id){
            /** CONDIÇÕES DE GUARDA! */
            if(!isset($_POST['documentsMeta-nonce'])) return; # se a submissão do form não trouxe o nonce, aborta!
            if(!wp_verify_nonce($_POST['documentsMeta-nonce'],'documentsMeta-nonce')) return; # se o nonce não coincidir, aborta!
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return; # se for salvamento automático, aborta!
            if(!isset($_POST['post_type']) || $_POST['post_type'] !== 'documents') return; # se não for salvamento do CPT 'documents', aborta!
            if(!current_user_can('edit_page',$post_id)) return; # se o usuário não possuir permissão para editar a página, aborta!
            if(!current_user_can('edit_post',$post_id)) return; # se o usuário não possuir permissão para editar o post, aborta!
            if(!isset($_POST['action'])) return; # verifica se houve de fato submissão do formulário
            if($_POST['action']!='editpost') return; # verifica se a submissão foi um save de post

            # obtém os valores dos metadados já existentes
            // TODO fazer o select no banco para buscar os dados
            
            # obtém os valores dos metadados novos, vindos do formulário
            $new_palavra_chave_value = $_POST['document_palavra_chave'];
            $new_esfera_value = $_POST['document_esfera'];
            $new_agenda_value = $_POST['document_agenda'];
            $new_tipo_doc_value = $_POST['document_tipo_doc'];
            $new_ano_value = $_POST['document_ano'];
            $file_name = $_POST['document_file'];
            
            // atualiza os dados no banco
            //prepara a query linha a linha
            
            if($file_name!=''){ // se houve anexo de um novo arquivo
                // acessa o recurso pela url e remove-o
                // $this->file_url_value 
                // insere o novo arquivo
            }
        }
    }
}