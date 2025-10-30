<?php
    if(!defined('ABSPATH')){ # segurança contra acesso direto
        die('Forbiden access!');
        exit();
    }

    // dados para a construção do formulário dos documentos
    $json_data_string = '[
        {
            "id":"document_palavra_chave",
            "options":[
                {"value":"vacina","text":"Vacina"},
                {"value":"esporte","text":"Esporte"},
                {"value":"educacao","text":"Educação"}
            ]
        },
        {
            "id":"document_esfera",
            "options":[
                {"value":"federal","text":"Federal"},
                {"value":"estadual","text":"Estadual"},
                {"value":"municipal","text":"Municipal"}
            ]
        },
        {
            "id":"document_poder",
            "options":[
                {"value":"poder_executivo","text":"Poder Executivo"},
                {"value":"poder_legislativo","text":"Poder Legislativo"},
                {"value":"poder_judiciario","text":"Poder Judiciário"}
            ]
        },
        {
            "id":"document_etapa",
            "options":[
                {"value":"planejamento","text":"Planejamento"},
                {"value":"implementacao","text":"Implementação"},
                {"value":"avaliacao","text":"Avaliação"}
            ]
        },
        {
            "id":"document_indicador",
            "options":[
                {"value":"loa","text":"LOA"},
                {"value":"ldo","text":"LDO"},
                {"value":"ppa","text":"PPA"},
                {"value":"termo_posse","text":"Termo de posse (Agenda retórica)"},
                {"value":"decretos","text":"Decretos"},
                {"value":"plano_governo","text":"Plano de Governo"},
                {"value":"mensagem_anual","text":"Mensagem Anual"}
            ]
        }
    ]';
    
    // Decodifica o JSON para um array PHP
    $meta_fields = json_decode($json_data_string, true);

    // Mapeamento dos 'id's para os rótulos e variáveis de valor (para o título e o 'selected')
    $label_map = [
        'document_palavra_chave' => ['label' => 'Palavra Chave', 'value_var' => 'palavra_chave_value'],
        'document_esfera'        => ['label' => 'Esfera', 'value_var' => 'esfera_value'],
        'document_poder'         => ['label' => 'Poder', 'value_var' => 'poder_value'],
        'document_etapa'         => ['label' => 'Etapa', 'value_var' => 'etapa_value'],
        'document_indicador'     => ['label' => 'Indicador', 'value_var' => 'indicador_value'],
    ];

    // obtêm as opções dinâmicas carregadas do banco
    $palavra_chave_value = ($data!=='') ? strtolower(trim($data['palavra_chave'])):'';
    $esfera_value = ($data!=='') ? strtolower(trim($data['esfera'])):'';
    $poder_value = ($data!=='') ? strtolower(trim($data['poder'])):'';
    $etapa_value = ($data!=='') ? strtolower(trim($data['etapa'])):'';
    $indicador_value  = ($data!=='') ? strtolower(trim($data['indicador'])):'';
    $ano_value  = ($data!=='') ? strtolower(trim($data['ano'])):'';
    $file_url_value  = ($data!=='') ? strtolower(trim($data['file_url'])):'';
?>



<table class="form-table">
    <input type="hidden" name="documentsMeta-nonce" value="<?php echo wp_create_nonce('documentsMeta-nonce') ?>">
    
    <?php
    // Loop para construir dinamicamente cada linha (<tr>)
    foreach ($meta_fields as $field) {
        $id = $field['id'];
        $options = $field['options'];
        $metadata = $label_map[$id] ?? ['label' => ucfirst(str_replace('_', ' ', str_replace('document_', '', $id))), 'value_var' => 'default_value_var'];
        $label = $metadata['label'];
        $value_variable = $metadata['value_var'];
        
        // Obtém o valor salvo do post (variável '$$value_variable' deve existir no escopo)
        $current_value = $$value_variable ?? ''; 
        
        // Define o texto padrão
        $placeholder = '-- Selecione a ' . $label . ' --';
        
        ?>
        <tr>
            <th><label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?>:</label></th>
            <td>
                <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>" required>
                    <option value=""><?php echo esc_html($placeholder); ?></option>
                    <?php
                    // Loop para construir dinamicamente as opções (<option>)
                    foreach ($options as $option) {
                        $value = $option['value'];
                        $text = $option['text'];
                        
                        // Usa a função selected() do WordPress para marcar a opção salva
                        ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_value, $value); ?>>
                            <?php echo esc_html($text); ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }
    ?>

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
