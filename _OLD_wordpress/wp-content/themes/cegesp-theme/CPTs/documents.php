<?php
/** Esta classe é responsável por registrar o cpt dos Documentos do site */

if(!class_exists('Documents_CPT')){
    class Documents_CPT{

        # **************************************************************************************
        # ATRIBUTOS     
        # **************************************************************************************
        private $database;
        private $storage_bucket;

        # **************************************************************************************
        # CONSTRUTOR     
        # **************************************************************************************

        function __construct(){
            // instancia o Banco de Dados do CPT
            require_once('database.php'); 
            $this->database = new Database_CPT();
            // instancia o Bucket de servidor de arquivos
            require_once('storage-bucket.php'); 
            $this->storage_bucket = new Storage_Bucket();
            // instancia rest api
            require_once('rest-api.php');
            new Rest_API();
            
            add_action('init',[$this,'create_documents_cpt']); # agenda a criação do CPT
            add_action('init', [$this->database , 'create_custom_table']);
            add_action('add_meta_boxes',[$this,'create_meta_boxes']); # agenda a criação dos Metaboxes para o CPT
            add_action('save_post',[$this,'save_meta_boxes_data']); # para salvar os dados do metabox na tabela 'wp_postmeta'
            // HOOK para a exclusão manual (ON DELETE CASCADE via PHP)
            add_action('before_delete_post', [$this, 'delete_meta_boxes_data']); 
            // HOOK para adicionar o enctype="multipart/form-data" para upload (permite upload manual de arquivo)
            add_action('post_edit_form_tag', function(){
                if (get_current_screen()->id === 'documents') {
                    echo ' enctype="multipart/form-data"';
                }
            });
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
                    $post_id = $post->ID;
                    $data = $this->database->select_data($post_id); // carrega os dados do banco
                    require_once('views-metaboxes/view.documents-meta-box.php'); // o layout é definido em view (uma forma inteligente de fazer)
                },
                'documents', // tela onde aparece (neste caso a chave do cpt)
                'normal', // posição
                'high', // prioridade
            );
        }

        public function save_meta_boxes_data($post_id){
            /** CONDIÇÕES DE GUARDA! */
            if(!isset($_POST['documentsMeta-nonce'])) return;
            if(!wp_verify_nonce($_POST['documentsMeta-nonce'],'documentsMeta-nonce')) return;
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if(!isset($_POST['post_type']) || $_POST['post_type'] !== 'documents') return;
            if(!current_user_can('edit_page',$post_id)) return;
            if(!current_user_can('edit_post',$post_id)) return;
            if(!isset($_POST['action'])) return;
            if($_POST['action']!='editpost') return;
            
            // ----------------------------------------------------------------------
            // 1. OBTENÇÃO E DISTINÇÃO DO STATUS (CRIAÇÃO vs. ATUALIZAÇÃO)
            // ----------------------------------------------------------------------
            
            // Tenta obter o registro existente para determinar o cenário e o file_id.
            $existing_record = $this->database->select_data($post_id);
            
            // Se não há registro, é o primeiro salvamento (Criação).
            $is_creation = empty($existing_record);

            // Define o file_id e file_url iniciais.
            if ($is_creation) {
                // Na criação, o file_id será o post_id
                $file_id = $post_id;
                $file_url = ''; // Começa sem URL
            } else {
                // Na atualização, usa os valores existentes
                $file_id = $existing_record['file_id'];
                $file_url = $existing_record['file_url'];
            }

            // ----------------------------------------------------------------------
            // 2. PROCESSAMENTO DO UPLOAD DO ARQUIVO
            // ----------------------------------------------------------------------

            // Verifica se houve submissão de um novo arquivo
            $new_file_attached = isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK;
            
            if ($new_file_attached) {
                $file = $_FILES['document_file'];
                $file_name = $file['name'];
                $file_tmp_path = $file['tmp_name'];

                // A função bunny_upload_file lida com a substituição (ou novo upload).
                // Se ela sempre usa o $file_id para o caminho, não precisamos deletar o antigo.
                $new_file_url = $this->storage_bucket->bunny_upload_file($file_id, $file_tmp_path, $file_name); 
                
                // Atualiza a URL do arquivo com a nova URL
                $file_url = $new_file_url;
            } 
            // Se for atualização e NÃO houver novo arquivo anexado, $file_url mantém a URL antiga.
            // Se for criação e NÃO houver arquivo anexado, $file_url permanece vazio.

            // ----------------------------------------------------------------------
            // 3. OBTENÇÃO E SANITIZAÇÃO DOS METADADOS (Sem Duplicação)
            // ----------------------------------------------------------------------
            
            # obtém os valores dos metadados novos, vindos do formulário
            $new_palavra_chave_value = isset($_POST['document_palavra_chave']) ? sanitize_text_field($_POST['document_palavra_chave']) : '';
            $new_esfera_value = isset($_POST['document_esfera']) ? sanitize_text_field($_POST['document_esfera']) : '';
            $new_poder_value = isset($_POST['document_poder']) ? sanitize_text_field($_POST['document_poder']) : '';
            $new_etapa_value = isset($_POST['document_etapa']) ? sanitize_text_field($_POST['document_etapa']) : '';
            $new_indicador_value  = isset($_POST['document_indicador']) ? sanitize_text_field($_POST['document_indicador']) : '';
            $new_ano_value  = isset($_POST['document_ano']) ? sanitize_text_field($_POST['document_ano']) : '';

            // ----------------------------------------------------------------------
            // 4. PREPARAÇÃO FINAL DOS DADOS E SALVAMENTO (Única Chamada)
            // ----------------------------------------------------------------------

            $data_to_save = array(
                'post_id'       => $post_id,
                'palavra_chave' => $new_palavra_chave_value,
                'esfera'        => $new_esfera_value,
                'poder'         => $new_poder_value,
                'etapa'         => $new_etapa_value,
                'indicador'     => $new_indicador_value,
                'ano'           => $new_ano_value,
                'file_id'       => $file_id, // Sempre o $file_id determinado na Seção 1
                'file_url'      => $file_url, // Sempre o $file_url atualizado ou existente
            );

            // A função update_data deve ser capaz de INSERIR (na criação) ou 
            // ATUALIZAR (na atualização) o registro com base no 'post_id'.
            $this->database->update_data($post_id, $data_to_save);
        }

        public function delete_meta_boxes_data($post_id){
            if (wp_is_post_revision($post_id)) return;
            if (get_post_type($post_id) !== 'documents') return;
            
            // obtem o identificador do arquivo do post
            $file_id = $this->database->select_field_data($post_id,'file_id');
            // remove o arquivo associado no bucket
            $this->storage_bucket->delete_file($file_id);
            // remove o registro associados ao post na tabela wp_cpt_documents
            $this->database->delete_data($post_id);
        }
    }
}