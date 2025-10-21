<?php
    if(!defined('ABSPATH')){ # segurança contra acesso direto
        die('Forbiden access!');
        exit();
    }
    
    // obtêm as opções dinâmicas carregadas do banco
    $palavra_chave_value = strtolower(trim($data['palavra_chave']));
    $esfera_value = strtolower(trim($data['esfera']));
    $agenda_value = strtolower(trim($data['agenda']));
    $tipo_doc_value  = strtolower(trim($data['tipo_doc']));
    $ano_value  = strtolower(trim($data['ano']));
    $file_url_value  = strtolower(trim($data['file_url']));
?>



<table class="form-table">
    <input type="hidden" name="documentsMeta-nonce" value="<?php echo wp_create_nonce('documentsMeta-nonce') ?>">
    
    <tr>
        <th><label for="document_palavra_chave">Palavra Chave:</label></th>
        <td>
            <select id="document_palavra_chave" name="document_palavra_chave" required>
                <option value="">-- Selecione a palavra chave --</option>
                <option value="saude" <?php selected($palavra_chave_value, 'saude'); ?>>Saúde</option>
                <option value="esporte" <?php selected($palavra_chave_value, 'esporte'); ?>>Esporte</option>
                <option value="lazer" <?php selected($palavra_chave_value, 'lazer'); ?>>Lazer</option>
                <option value="educacao" <?php selected($palavra_chave_value, 'educacao'); ?>>Educação</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="document_esfera">Esfera:</label></th>
        <td>
            <select id="document_esfera" name="document_esfera" required>
                <option value="">-- Selecione a Esfera --</option>
                <option value="federal" <?php selected($esfera_value, 'federal'); ?>>Federal</option>
                <option value="estadual" <?php selected($esfera_value, 'estadual'); ?>>Estadual</option>
                <option value="municipal" <?php selected($esfera_value, 'municipal'); ?>>Municipal</option>
            </select>
        </td>
    </tr>
    
    <tr>
        <th><label for="document_agenda">Agenda:</label></th>
        <td>
            <select id="document_agenda" name="document_agenda" required>
                <option value="">-- Selecione a Agenda --</option>
                <option value="poder_executivo" <?php selected($agenda_value, 'poder_executivo'); ?>>Poder Executivo</option>
                <option value="poder_legislativo" <?php selected($agenda_value, 'poder_legislativo'); ?>>Poder Legislativo</option>
                <option value="poder_judiciario" <?php selected($agenda_value, 'poder_judiciario'); ?>>Poder Judiciário</option>
            </select>
        </td>
    </tr>
    
    <tr>
        <th><label for="document_tipo_doc">Tipo de Documento:</label></th>
        <td>
            <select id="document_tipo_doc" name="document_tipo_doc" required>
                <option value="">-- Selecione o Tipo de documento --</option>
                <option value="loa" <?php selected($tipo_doc_value, 'loa'); ?>>LOA</option>
                <option value="ldo" <?php selected($tipo_doc_value, 'ldo'); ?>>LDO</option>
                <option value="ppa" <?php selected($tipo_doc_value, 'ppa'); ?>>PPA</option>
                <option value="decretos" <?php selected($tipo_doc_value, 'decretos'); ?>>Decretos</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="document_ano">Ano:</label></th>
        <td>
            <input 
                type="number" 
                id="document_ano" 
                name="document_ano" 
                value="<?php echo esc_attr($ano_value); ?>" 
                class="small-text"
                min="1900"
                max="<?php echo date('Y'); ?>"
                placeholder="<?php echo date('Y'); ?>"
                required
            >
            <p class="description">Ano de publicação do documento.</p>
        </td>
    </tr>
    
    <tr>
        <th><label for="document_file">Arquivo:</label></th>
        <td>
            <input 
                type="file" 
                id="document_file" 
                name="document_file" 
                accept=".pdf" 
                <?php if (empty($file_url_value)) echo 'required'; ?>
            >
            
            <?php if (!empty($file_url_value)) : ?>
                <p class="description">
                    Arquivo atual: <a href="<?php echo esc_url($file_url_value); ?>" target="_blank">Visualizar Documento</a>
                    <br/>
                    URL: <?php echo esc_url($file_url_value);?>
                    <br/>
                    *O upload de um novo arquivo substituirá o atual.
                </p>
            <?php else : ?>
                <p class="description">Selecione o arquivo do documento .PDF</p>
            <?php endif; ?>
        </td>
    </tr>
    
</table>
