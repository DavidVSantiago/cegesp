<?php
/** Esta classe é responsável por fazer a conexão coom o seviço cloud que armazena os documentos .PDF*/

if(!class_exists('Storage_Bucket')){
    class Storage_Bucket{

        # **************************************************************************************
        # ATRIBUTOS     
        # **************************************************************************************
        private $BUNNY_ACCESS_KEY;
        private $BUNNY_STORAGE_ZONE;
        private $BUNNY_STORAGE_HOST;
        private $BUNNY_PULL_ZONE_HOST;
        
        function __construct(){
            $this->BUNNY_ACCESS_KEY = '9557d7f9-d2d9-4b99-bdcc83a0d366-a2e9-4fbb';
            $this->BUNNY_STORAGE_ZONE = 'cegesp';
            $this->BUNNY_STORAGE_HOST = 'storage.bunnycdn.com';
            $this->BUNNY_PULL_ZONE_HOST = 'cegesp.b-cdn.net';

        }
        # **************************************************************************************
        # MÉTODOS
        # **************************************************************************************

        /* Faz o upload (PUT) de um arquivo para o Bunny.net Storage. */
        public function bunny_upload_file($file_id, string $file_tmp_path, string $file_name): string {
            
            // Cada arquivo é único dentro de um diretório com o id do arquivo
            $unique_file_name = $file_id . '/' . $file_name;

            $this->bunny_delete_old($file_id);
            

            // Define o caminho no bucket (ex: /documents/filename.pdf)
            $remote_file_path = 'documents/' . $unique_file_name;
            // define o caminho completo para o envio
            $upload_url = 'https://' . $this->BUNNY_STORAGE_HOST . '/' . $this->BUNNY_STORAGE_ZONE . '/' . $remote_file_path;

            // Conteúdo do arquivo a ser enviado
            $file_content = file_get_contents($file_tmp_path);
            if ($file_content === false) {
                return ''; // Falha ao ler o arquivo temporário
            }

            $response = wp_remote_request($upload_url, [
                'method'    => 'PUT',
                'headers'   => [
                    // A chave de API do Storage Zone é usada para autenticação
                    'AccessKey'     => $this->BUNNY_ACCESS_KEY,
                    'Content-Type'  => mime_content_type($file_tmp_path) ?: 'application/octet-stream',
                    'Content-Length'=> filesize($file_tmp_path),
                ],
                'body'      => $file_content,
                'timeout'   => 45, // Aumenta o timeout para uploads grandes
            ]);

            $status_code = wp_remote_retrieve_response_code($response);
            if (is_wp_error($response) || $status_code !== 201) return '';

            return 'https://' . $this->BUNNY_PULL_ZONE_HOST . '/' . $remote_file_path;
        }

        /* remove o diretório do post, juntamente com todos os seus arquivos */
        public function delete_file($file_id){
            $normalized_id = trim((string) $file_id, '/');
            $remote_directory_path = $this->BUNNY_STORAGE_ZONE . '/documents/' . $normalized_id . '/';
            $this->_bunny_delete_file_by_path($remote_directory_path);
        }

        # **************************************************************************************
        # MÉTODOS DE SERVIÇO (Listagem, Exclusão)
        # **************************************************************************************

        /** * Remove do bucket o possível arquivo existente associado ao post.
         * Busca e deleta qualquer arquivo em 'documents/' que comece com "$post_id\_".
         */
        private function bunny_delete_old(int $file_id) {

            // Lista todos os arquivos do diretório único $file_id do arquivo
            $files_to_delete = $this->_bunny_list_files($file_id);

            if (empty($files_to_delete)) return; // Nenhum arquivo encontrado.

            // Itera sobre a lista e deleta cada arquivo
            foreach ($files_to_delete as $file) {
                // O campo 'Path' retornado pela API já inclui o StorageZone e o caminho completo.
                // Ex: "cegesp/documents/56_dumb.pdf"
                $remote_file_path = $file['Path'] . $file['ObjectName']; 
                $this->_bunny_delete_file_by_path($remote_file_path);
            }
        }

        /**
         * Lista todos os arquivo de um diretório
         */
        private function _bunny_list_files(string $file_id): array {
            // A URL base é o host + zona de storage
            $base_url = 'https://' . $this->BUNNY_STORAGE_HOST . '/' . $this->BUNNY_STORAGE_ZONE . '/';
            $list_url = $base_url . 'documents/' . $file_id . '/';
            

            $response = wp_remote_request($list_url, [
                'method'    => 'GET',
                'headers'   => [
                    'AccessKey' => $this->BUNNY_ACCESS_KEY,
                    'Content-Type' => 'application/json',
                ],
                'timeout'   => 10,
            ]);

            if (is_wp_error($response)) {
                error_log('Bunny.net List Files Error: ' . $response->get_error_message());
                return [];
            }

            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code !== 200) {
                error_log('Bunny.net List Files HTTP Error: ' . $status_code . ' - ' . wp_remote_retrieve_body($response));
                return [];
            }

            $body = wp_remote_retrieve_body($response);
            $files = json_decode($body, true);

            return is_array($files) ? $files : [];
        }

        /**
         * Executa a requisição DELETE para um caminho de arquivo específico no Storage Zone.
         * @param string $remote_file_path O caminho completo do arquivo no bucket (ex: cegesp/documents/file.pdf).
         * @return bool True se excluído ou não encontrado (sucesso), False em erro.
         */
        private function _bunny_delete_file_by_path(string $remote_file_path): bool {
            // remote_file_path já inclui o StorageZone. Ex: cegesp/documents/56_dumb.pdf
            $delete_url = 'https://' . $this->BUNNY_STORAGE_HOST . '/' . $remote_file_path;

            $response = wp_remote_request($delete_url, [
                'method'    => 'DELETE',
                'headers'   => [
                    'AccessKey'     => $this->BUNNY_ACCESS_KEY,
                ],
                'timeout'   => 10,
            ]);

            $status = wp_remote_retrieve_response_code($response);
            
            // Status 200 (OK) ou 404 (Not Found, já removido) são considerados sucesso.
            if (!is_wp_error($response) && ($status === 200 || $status === 404)) {
                return true;
            }

            error_log('Bunny.net Delete Error (Path: ' . $remote_file_path . '): Status ' . $status . ' - ' . wp_remote_retrieve_body($response));
            return false;
        }

    }
}
