<?php
/** Esta classe é responsável por registrar o cpt dos Documentos do site */

if(!class_exists('Database_CPT')){
    class Database_CPT{

        # **************************************************************************************
        # ATRIBUTOS     
        # **************************************************************************************
        private $table_name;
        
        function __construct(){
            global $wpdb;
            $this->table_name = $wpdb->prefix . 'cpt_documents'; // define o nome da tabela

        }
        # **************************************************************************************
        # MÉTODOS
        # **************************************************************************************

        /* Cria a tabela customizada no banco de dados */
        public function create_custom_table() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $this->table_name;

            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                post_id bigint(20) NOT NULL UNIQUE,
                palavra_chave varchar(255) NOT NULL,
                esfera varchar(255) NOT NULL,
                agenda varchar(255) NOT NULL,
                tipo_doc varchar(255) NOT NULL,
                ano int(4) NOT NULL,
                file_id varchar(255) NOT NULL,
                file_url text NOT NULL,
                PRIMARY KEY (id),
                KEY post_id_idx (post_id)
            ) $charset_collate;";

            dbDelta($sql);
        }

        # **************************************************************************************
        # MÉTODOS CRUD
        # **************************************************************************************

        /* INSERT/UPDATE ------------------------------------------------------------------ */ 
        public function update_data($post_id, $data_to_save){
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $table_name = $this->table_name;

            $exists = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $this->table_name WHERE post_id = %d", $post_id));
            if ($exists) {
                // Se existe (edição de post), atualiza a linha
                $wpdb->update(
                    $table_name,
                    $data_to_save,
                    array('post_id' => $post_id),
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'),
                    array('%d')
                );
            } else {
                // Se não existe (novo post), insere a linha
                $wpdb->insert(
                    $table_name,
                    $data_to_save,
                    array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
                );
            }
        }

        /* SELECT ALL --------------------------------------------------------------------------- */
        public function select_data($post_id){
            global $wpdb;
            $table_name = $this->table_name;
            
            // Prepara e executa a query SELECT
            // Usamos $wpdb->prepare para segurança e ARRAY_A para obter um array associativo
            $data = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE post_id = %d",
                    $post_id
                ),
                ARRAY_A
            );
            // 2. Retorna os dados (se encontrados) ou um array vazio
            return $data ? $data : '';
        }
        
        /* SELECT apenas 1 campo ---------------------------------------------------------------- */
        public function select_field_data($post_id, $field_name){
            global $wpdb;
            $table_name = $this->table_name;
            
            // fields permitidos
            $allowed_fields = ['palavra_chave', 'esfera', 'agenda', 'tipo_doc', 'ano', 'file_id', 'file_url']; 
            if (!in_array($field_name, $allowed_fields)) return null; // abandona, caso seja recebido um campo não permitido

            // Prepara e executa a query SELECT
            $sql = $wpdb->prepare(
                "SELECT `{$field_name}` FROM {$table_name} WHERE post_id = %d",
                $post_id
            );
            $value = $wpdb->get_var($sql);

            // 3. Retorna o valor ou null
            return $value !== null ? (string) $value : null;
        }
    }
}