<?php
/** Rest_API registra endpoints REST relacionados ao CPT de Documentos. */

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
        public function register_document_search_endpoint() { // namespace v1/documentos, rota /search
            register_rest_route('v1/documentos', '/search', [
                'methods'  => ['GET', 'POST'],
                'callback' => [$this, 'handle_document_search_request'],
                'permission_callback' => '__return_true',
                'args' => [
                    'palavra_chave' => ['sanitize_callback' => 'sanitize_text_field'],
                    'esfera'        => ['sanitize_callback' => 'sanitize_text_field'],
                    'poder'         => ['sanitize_callback' => 'sanitize_text_field'],
                    'etapa'         => ['sanitize_callback' => 'sanitize_text_field'],
                    'indicador'     => ['sanitize_callback' => 'sanitize_text_field'],
                    'ano_inicio'    => ['sanitize_callback' => 'absint'],
                    'ano_fim'       => ['sanitize_callback' => 'absint'],
                ]
            ]);
        }
        public function handle_document_search_request(WP_REST_Request $request) {
            $search_args = $request->get_params(); // args já sanitizados via register_rest_route

            $ano_inicio = isset($search_args['ano_inicio']) ? absint($search_args['ano_inicio']) : 0;
            $ano_fim = isset($search_args['ano_fim']) ? absint($search_args['ano_fim']) : 0;

            if (!$ano_inicio || !$ano_fim) {
                return new WP_Error(
                    'missing_year_range',
                    __('Os parâmetros "ano_inicio" e "ano_fim" são obrigatórios.', 'cegesp-theme'),
                    ['status' => 400]
                );
            }

            $documents_data = $this->search_documents_data($search_args);

            return rest_ensure_response([
                'results' => $documents_data,
                'count'   => count($documents_data),
            ]);
        }
  
        public function search_documents_data($args) {
            global $wpdb;

            $table = $wpdb->prefix . 'cpt_documents';
            $where_clauses = [];
            $params = [];

            if (!empty($args['palavra_chave'])) {
                $where_clauses[] = 'palavra_chave LIKE %s';
                $params[] = '%' . $wpdb->esc_like($args['palavra_chave']) . '%';
            }
            if (!empty($args['esfera'])) {
                $where_clauses[] = 'esfera = %s';
                $params[] = $args['esfera'];
            }
            if (!empty($args['poder'])) {
                $where_clauses[] = 'poder = %s';
                $params[] = $args['poder'];
            }
            if (!empty($args['etapa'])) {
                $where_clauses[] = 'etapa = %s';
                $params[] = $args['etapa'];
            }
            if (!empty($args['indicador'])) {
                $where_clauses[] = 'indicador = %s';
                $params[] = $args['indicador'];
            }

            $ano_inicio = isset($args['ano_inicio']) ? absint($args['ano_inicio']) : 0;
            $ano_fim = isset($args['ano_fim']) ? absint($args['ano_fim']) : 0;

            if ($ano_inicio && $ano_fim && $ano_inicio > $ano_fim) {
                $tmp = $ano_inicio;
                $ano_inicio = $ano_fim;
                $ano_fim = $tmp;
            }

            if ($ano_inicio) {
                $where_clauses[] = 'ano >= %d';
                $params[] = $ano_inicio;
            }
            if ($ano_fim) {
                $where_clauses[] = 'ano <= %d';
                $params[] = $ano_fim;
            }

            $where_sql = '';
            if (!empty($where_clauses)) {
                $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
            }

            $query = "SELECT *
                      FROM {$table}
                      {$where_sql}
                      ORDER BY ano DESC, id DESC";

            if (!empty($params)) {
                $query = $wpdb->prepare($query, $params);
            }

            $results = $wpdb->get_results($query, ARRAY_A);

            return $results ? $results : [];
        }
    }
}
