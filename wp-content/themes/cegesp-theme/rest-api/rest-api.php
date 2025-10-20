<?php
/** Esta classe é responsável por registrar as opções do site no banco de dados */

if(!defined('ABSPATH')){ # segurança contra acesso direto
    die('Forbiden access!');
    exit();
}

if(!class_exists('Rest_API')){
    class Rest_API{
        function __construct(){
            add_action('rest_api_init', [$this,'register_document_search_endpoint']);
        }
        # **************************************************************************************
        # MÉTODOS
        # **************************************************************************************
        function register_document_search_endpoint() {
            register_rest_route('v1/documentos', '/search', [ // Exemplo de namespace: v1/documentos. Rota: /search
                'methods'  => 'GET', // ou POST, dependendo de como você prefere receber os parâmetros
                'callback' => [$this, 'handle_document_search_request'],
                'permission_callback' => '__return_true', // Permite acesso público. Se precisar de autenticação, altere esta linha.
            ]);
        }
        function handle_document_search_request(WP_REST_Request $request) {
            // Captura os parâmetros da query string (para método GET)
            // O WordPress já sanitiza as variáveis de requisição, mas é bom sanitizar
            // novamente na função de pesquisa para garantir.
            $search_args = $request->get_params();

            // Chama a função de pesquisa de documentos
            $documents_data = $this->search_documents_data($search_args);

            // Retorna a resposta, o WordPress automaticamente formata o array em JSON
            if (empty($documents_data)) {
                return new WP_REST_Response(['message' => 'Nenhum documento encontrado.'], 200);
            }

            return new WP_REST_Response($documents_data, 200);
        }
  
        function search_documents_data($args) {
            // Acessa a classe global do WordPress para interagir com o banco de dados
            global $wpdb;

            // Nome da tabela de posts
            $tabela = $wpdb->prefix . 'posts'; // Usa $wpdb->prefix para garantir o prefixo correto (ex: wp_posts)
            
            // definição dos campos da tabela
            $post_type = 'acf-field';

            // 1. Preparar e executar a consulta SQL
            $query = $wpdb->prepare( // A função $wpdb->prepare() é crucial para a segurança, prevenindo SQL Injection
                "SELECT ID, post_content, post_excerpt, post_parent
                FROM {$tabela}
                WHERE post_type = %s", // %s é um placeholder para strings
                $post_type
            );

            // 2. Executar a consulta e obter os resultados como um array de objetos
            // $wpdb->get_results() é o método ideal para SELECTs que retornam várias linhas
            $resultados = $wpdb->get_results( $query, ARRAY_A ); // ARRAY_A retorna um array associativo
        }
    }
}