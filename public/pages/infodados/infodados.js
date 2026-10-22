document.addEventListener("DOMContentLoaded", async function () {
    const form = document.getElementById("infodadosForm");
    const esferaSelect = document.getElementById("esfera");
    const ufGroup = document.getElementById("ufGroup");
    const ufSelect = document.getElementById("uf");
    
    const agrupamentoGroup = document.getElementById("agrupamentoGroup");
    const municipioGroup = document.getElementById("municipioGroup");
    const municipioSelect = document.getElementById("municipio");
    const territorioGroup = document.getElementById("territorioGroup");
    const territorioSelect = document.getElementById("territorio");
    
    const agrupamentoRadios = document.querySelectorAll('algol-switch-radio[name="agrupamento"]');
    const etapaSelect = document.getElementById("etapa");
    const themeSelect = document.getElementById("theme");
    
    const judiciarioContainer = document.getElementById("judiciarioContainer");
    const agendaGrid = document.getElementById("agendaGrid");
    const resultadoContainer = document.getElementById("resultadoInfodados");

    const SPREADSHEET_CSV_URL = "https://docs.google.com/spreadsheets/d/1nXfiJcw04JkZOz6rsFhn45lRAt-GliBMWmF9S1AySIo/gviz/tq?tqx=out:csv";

    let acervoPlanilha = [];
    let mapaTemas = {};

    //Carrega JSON de temas pra cada politica publica

    async function carregarMapaTemas() {
        try {
            const response = await fetch("temas.json");
            if (!response.ok) throw new Error("Erro ao carregar temas.json");
            mapaTemas = await response.json();
        } catch (error) {
            console.error("Erro ao carregar arquivo de temas:", error);
        }
    }

    function atualizarSelectTemas(politicaSelecionada) {
        if (!themeSelect) return;

        let optionsHTML = '<option value="" disabled selected>Selecione um Tema</option>';
        const listaTemas = mapaTemas[politicaSelecionada] || [];

        if (listaTemas.length === 0) {
            optionsHTML += '<option value="" disabled>Nenhum tema encontrado para esta política</option>';
        } else {
            listaTemas.forEach(tema => {
                optionsHTML += `<option value="${tema}">${tema}</option>`;
            });
        }

        themeSelect.innerHTML = optionsHTML;
    }

    //===============================================

    // NORMALIZAÇÃO DE TEXTo
//remove acentos, converte para minúsculas, remove hífens/underscores e espaços duplicados
   function normalizarTexto(texto) {
    if (!texto) return "";
    let str = String(texto)
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/[-_]/g, " ")
        .replace(/\s+/g, " ")
        .trim();

    if (str === "f") return "federal";
    if (str === "e") return "estadual";
    if (str === "m") return "municipal";

    return str;
}

    function parseCSVLine(line) {
        const result = [];
        let cur = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            if (char === '"') {
                inQuotes = !inQuotes;
            } else if (char === ',' && !inQuotes) {
                result.push(cur.trim().replace(/^"|"$/g, ''));
                cur = '';
            } else {
                cur += char;
            }
        }
        result.push(cur.trim().replace(/^"|"$/g, ''));
        return result;
    }

    async function carregarDadosPlanilha() {
        try {
            const response = await fetch(SPREADSHEET_CSV_URL);
            if (!response.ok) throw new Error("Erro ao carregar dados da planilha");
            
            const csvText = await response.text();
            const linhas = csvText.split("\n").map(l => l.trim()).filter(Boolean);
            if (linhas.length < 2) return [];

            const cabecalhos = parseCSVLine(linhas[0]).map(h => normalizarTexto(h));
            
            return linhas.slice(1).map(linha => {
                const valores = parseCSVLine(linha);
                const objeto = {};
                cabecalhos.forEach((coluna, idx) => {
                    objeto[coluna] = valores[idx] || "";
                });
                return objeto;
            });
        } catch (error) {
            console.error("Erro no carregamento do Google Sheets:", error);
            return null;
        }
    }

    function getValPlanilha(item, ...chaves) {
        for (const chave of chaves) {
            const normChave = normalizarTexto(chave);
            if (item[normChave] !== undefined) {
                return item[normChave];
            }
        }
        return "";
    }

    //=============================================

    //FILTRAGEM

    function extrairFiltrosFormulario() {
        const politicaEl = document.querySelector('input[name="politicas"]:checked');
        const poderEl = document.querySelector('input[name="poder"]:checked');
        
        const getVal = (el) => el ? (el.value || el.getAttribute("value") || "") : "";

        return {
            politica: politicaEl ? politicaEl.value : "",
            esfera: getVal(esferaSelect),
            uf: getVal(ufSelect),
            municipio: getVal(municipioSelect),
            territorio: getVal(territorioSelect),
            poder: poderEl ? poderEl.value : "",
            etapa: getVal(etapaSelect),
            theme: getVal(themeSelect)
        };
    }

    function filtrarRegistros(dados, filtros) {
        return dados.filter(item => {
            const itemPolitica = getValPlanilha(item, "politica_publica", "politica");
            const itemTema = getValPlanilha(item, "tema", "theme");
            const itemEsfera = getValPlanilha(item, "esfera");
            const itemUf = getValPlanilha(item, "uf");
            const itemMunicipio = getValPlanilha(item, "municipio");
            const itemTerritorio = getValPlanilha(item, "territorio");
            const itemPoder = getValPlanilha(item, "poder");
            const itemEtapa = getValPlanilha(item, "etapa");

            if (filtros.politica && normalizarTexto(itemPolitica) !== normalizarTexto(filtros.politica)) return false;
            if (filtros.esfera && normalizarTexto(itemEsfera) !== normalizarTexto(filtros.esfera)) return false;
            if (filtros.uf && normalizarTexto(itemUf) !== normalizarTexto(filtros.uf)) return false;
            if (filtros.municipio && normalizarTexto(itemMunicipio) !== normalizarTexto(filtros.municipio)) return false;
            if (filtros.territorio && normalizarTexto(itemTerritorio) !== normalizarTexto(filtros.territorio)) return false;
            if (filtros.poder && normalizarTexto(itemPoder) !== normalizarTexto(filtros.poder)) return false;
            if (filtros.etapa && normalizarTexto(itemEtapa) !== normalizarTexto(filtros.etapa)) return false;
            if (filtros.theme && normalizarTexto(itemTema) !== normalizarTexto(filtros.theme)) return false;

            return true;
        });
    }

    //RENDERIZAÇÃO 

    function renderizarResultado(resultados) {
    if (!resultadoContainer) return;

    resultadoContainer.hidden = false;

    if (resultados === null) {
        resultadoContainer.innerHTML = `
            <div style="text-align: center; padding: 2vw; color: #e74c3c;">
                <p>Erro ao conectar com a planilha. Verifique as permissões de acesso.</p>
            </div>
        `;
        return;
    }

    if (resultados.length === 0) {
        resultadoContainer.innerHTML = `
            <div style="text-align: center; padding: 2vw; color: #666;">
                <p>Nenhum infográfico encontrado para os filtros selecionados.</p>
            </div>
        `;
        resultadoContainer.scrollIntoView({ behavior: 'smooth' });
        return;
    }

    const htmlCards = resultados.map(item => {
        const rawLinks = getValPlanilha(item, "url_doc_oficial", "url_documento", "url_documento_oficial") || "#";
        const tituloDoc = getValPlanilha(item, "titulo_documento") || "Documento Oficial";
        const imgInfografico = getValPlanilha(item, "url_infografico") || "";

        // Divide múltiplos links separados por vírgula na mesma célula
        const linksArray = rawLinks.split(',').map(link => link.trim()).filter(Boolean);

        const linksMarkup = linksArray.map((link, index) => {
            const rotuloLink = linksArray.length > 1 ? `Link ${index + 1}: ${link}` : link;
            return `
                <a href="${link}" target="_blank" rel="noopener noreferrer" class="doc-sublink">
                    🔗 ${rotuloLink}
                </a>
            `;
        }).join('');

        return `
            <article class="infodados-card algol-bubble-01">
                <img class="infodados-media" src="${imgInfografico}" alt="Infográfico" />
                <footer class="infodados-footer">
                    <p class="doc-title-label"> Acessar Documento Oficial: <strong>${tituloDoc}</strong></p>
                    <div class="doc-links-container">
                        ${linksMarkup}
                    </div>
                </footer>
            </article>
        `;
    }).join("<hr style='margin: 2vw 0; border: none; border-top: 0.1vw solid #eee;' />");

    resultadoContainer.innerHTML = htmlCards;
    resultadoContainer.scrollIntoView({ behavior: 'smooth' });
}

    // ==========================================

    // POPULAÇÃO DINÂMICA DE UF E MUNICÍPIO (em tempo real)

    /**
     * Obtém os filtros parciais já selecionados pelo usuário
     * (política e esfera), para restringir as opções disponíveis.
     */
    function obterFiltrosParciais() {
        const politicaEl = document.querySelector('input[name="politicas"]:checked');
        const getVal = (el) => el ? (el.value || el.getAttribute("value") || "") : "";

        return {
            politica: politicaEl ? politicaEl.value : "",
            esfera: getVal(esferaSelect)
        };
    }

    /**
     * Popula o dropdown de UF com apenas os estados que possuem
     * documentos cadastrados na planilha para a política/esfera atuais.
     */
    function atualizarSelectUF() {
        if (!ufSelect) return;

        const filtros = obterFiltrosParciais();

        // Filtra os registros compatíveis com política e esfera atuais
        const registrosCompativeis = acervoPlanilha.filter(item => {
            const itemPolitica = getValPlanilha(item, "politica_publica", "politica");
            const itemEsfera = getValPlanilha(item, "esfera");

            if (filtros.politica && normalizarTexto(itemPolitica) !== normalizarTexto(filtros.politica)) return false;
            if (filtros.esfera && normalizarTexto(itemEsfera) !== normalizarTexto(filtros.esfera)) return false;
            return true;
        });

        // Extrai UFs únicas (não vazias) e ordena
        const ufsUnicas = [...new Set(
            registrosCompativeis
                .map(item => getValPlanilha(item, "uf").trim().toUpperCase())
                .filter(uf => uf !== "")
        )].sort();

        let optionsHTML = '<option value="" disabled selected>Selecione o Estado</option>';

        if (ufsUnicas.length === 0) {
            optionsHTML += '<option value="" disabled>Nenhum estado com documentos</option>';
        } else {
            ufsUnicas.forEach(uf => {
                optionsHTML += `<option value="${uf}">${uf}</option>`;
            });
        }

        ufSelect.innerHTML = optionsHTML;

        // Reseta o município/território ao trocar as UFs disponíveis
        atualizarSelectMunicipio();
        atualizarSelectTerritorio();
    }

    /**
     * Popula o dropdown de Município com apenas as cidades que possuem
     * documentos cadastrados para a política/esfera/UF atuais.
     */
    function atualizarSelectMunicipio() {
        if (!municipioSelect) return;

        const filtros = obterFiltrosParciais();
        const getVal = (el) => el ? (el.value || el.getAttribute("value") || "") : "";
        const ufSelecionada = getVal(ufSelect);

        // Se não há UF selecionada, mostra placeholder
        if (!ufSelecionada) {
            municipioSelect.innerHTML = '<option value="" disabled selected>Selecione um estado primeiro</option>';
            return;
        }

        // Filtra registros compatíveis com política + esfera + UF
        const registrosCompativeis = acervoPlanilha.filter(item => {
            const itemPolitica = getValPlanilha(item, "politica_publica", "politica");
            const itemEsfera = getValPlanilha(item, "esfera");
            const itemUf = getValPlanilha(item, "uf");

            if (filtros.politica && normalizarTexto(itemPolitica) !== normalizarTexto(filtros.politica)) return false;
            if (filtros.esfera && normalizarTexto(itemEsfera) !== normalizarTexto(filtros.esfera)) return false;
            if (ufSelecionada && normalizarTexto(itemUf) !== normalizarTexto(ufSelecionada)) return false;
            return true;
        });

        // Extrai municípios únicos (não vazios), capitaliza e ordena
        const municipiosUnicos = [...new Set(
            registrosCompativeis
                .map(item => getValPlanilha(item, "municipio").trim())
                .filter(m => m !== "")
        )].sort((a, b) => a.localeCompare(b, "pt-BR"));

        let optionsHTML = '<option value="" disabled selected>Selecione o Município</option>';

        if (municipiosUnicos.length === 0) {
            optionsHTML += '<option value="" disabled>Nenhum município com documentos</option>';
        } else {
            municipiosUnicos.forEach(municipio => {
                optionsHTML += `<option value="${municipio}">${municipio}</option>`;
            });
        }

        municipioSelect.innerHTML = optionsHTML;
    }

    /**
     * Popula o dropdown de Território com apenas os territórios que possuem
     * documentos cadastrados para a política/esfera/UF atuais.
     */
    function atualizarSelectTerritorio() {
        if (!territorioSelect) return;

        const filtros = obterFiltrosParciais();
        const getVal = (el) => el ? (el.value || el.getAttribute("value") || "") : "";
        const ufSelecionada = getVal(ufSelect);

        if (!ufSelecionada) {
            territorioSelect.innerHTML = '<option value="" disabled selected>Selecione um estado primeiro</option>';
            return;
        }

        // Filtra registros compatíveis com política + esfera + UF
        const registrosCompativeis = acervoPlanilha.filter(item => {
            const itemPolitica = getValPlanilha(item, "politica_publica", "politica");
            const itemEsfera = getValPlanilha(item, "esfera");
            const itemUf = getValPlanilha(item, "uf");

            if (filtros.politica && normalizarTexto(itemPolitica) !== normalizarTexto(filtros.politica)) return false;
            if (filtros.esfera && normalizarTexto(itemEsfera) !== normalizarTexto(filtros.esfera)) return false;
            if (ufSelecionada && normalizarTexto(itemUf) !== normalizarTexto(ufSelecionada)) return false;
            return true;
        });

        // Extrai territórios únicos (não vazios) e ordena
        const territoriosUnicos = [...new Set(
            registrosCompativeis
                .map(item => getValPlanilha(item, "territorio").trim())
                .filter(t => t !== "")
        )].sort((a, b) => a.localeCompare(b, "pt-BR"));

        let optionsHTML = '<option value="" disabled selected>Selecione o Território</option>';

        if (territoriosUnicos.length === 0) {
            optionsHTML += '<option value="" disabled>Nenhum território com documentos</option>';
        } else {
            territoriosUnicos.forEach(territorio => {
                optionsHTML += `<option value="${territorio}">${territorio}</option>`;
            });
        }

        territorioSelect.innerHTML = optionsHTML;
    }

    // ==========================================

    // 4. EVENTOS DA INTERFACE

    function atualizarCamposFormulario(esferaValue) {
        if (!judiciarioContainer || !agendaGrid) return;
        
        ufGroup.classList.add("hidden");
        agrupamentoGroup.classList.add("hidden");
        municipioGroup.classList.add("hidden");
        territorioGroup.classList.add("hidden");

        const esferaNorm = normalizarTexto(esferaValue);

        if (esferaNorm === "federal") {
            judiciarioContainer.style.display = "block";
            agendaGrid.setAttribute("cols", "repeat(3, 1fr)");
        } else if (esferaNorm === "estadual") {
            ufGroup.classList.remove("hidden");
            judiciarioContainer.style.display = "none";
            agendaGrid.setAttribute("cols", "repeat(2, 1fr)");
            // Atualiza opções de UF em tempo real
            atualizarSelectUF();
        } else if (esferaNorm === "municipal") {
            ufGroup.classList.remove("hidden");
            agrupamentoGroup.classList.remove("hidden"); 
            judiciarioContainer.style.display = "none";
            agendaGrid.setAttribute("cols", "repeat(2, 1fr)");
            // Atualiza opções de UF em tempo real
            atualizarSelectUF();
        }
    }

    esferaSelect.addEventListener("algol-change", function (e) {
        atualizarCamposFormulario(e.detail.value);
    });

    // Quando o usuário seleciona uma UF, atualiza município e território
    ufSelect.addEventListener("algol-change", function () {
        atualizarSelectMunicipio();
        atualizarSelectTerritorio();
    });

    agrupamentoRadios.forEach(radio => {
        radio.addEventListener("algol-input", function (e) {
            const escolha = e.detail.value;
            municipioGroup.classList.add("hidden");
            territorioGroup.classList.add("hidden");

            if (escolha === "municipio") {
                municipioGroup.classList.remove("hidden");
            } else if (escolha === "territorio") {
                territorioGroup.classList.remove("hidden");
            }
        });
    });

    //Filtra temas para cada politica publica
    const politicasInputs = document.querySelectorAll('input[name="politicas"]');
    politicasInputs.forEach(input => {
        input.addEventListener("change", function () {
            if (this.checked) {
                atualizarSelectTemas(this.value);
                // Ao trocar a política, atualiza também as UFs disponíveis
                const getVal = (el) => el ? (el.value || el.getAttribute("value") || "") : "";
                const esferaAtual = getVal(esferaSelect);
                if (normalizarTexto(esferaAtual) === "estadual" || normalizarTexto(esferaAtual) === "municipal") {
                    atualizarSelectUF();
                }
            }
        });
    });

    function validarFormulario() {
        const politicasSelecionada = document.querySelector('input[name="politicas"]:checked');
        const temaSelecionado = themeSelect ? (themeSelect.value || themeSelect.getAttribute("value")) : "";
        const etapaSelecionada = etapaSelect ? (etapaSelect.value || etapaSelect.getAttribute("value")) : "";

        if (!politicasSelecionada) {
            alert("Selecione uma Política Pública.");
            return false;
        }
        if (!temaSelecionado) {
            alert("Selecione um Tema.");
            return false;
        }
        if (!etapaSelecionada) {
            alert("Selecione uma etapa da Política Pública.");
            return false;
        }
        return true;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        if (!validarFormulario()) return;

        const filtros = extrairFiltrosFormulario();
        const resultados = filtrarRegistros(acervoPlanilha, filtros);
        renderizarResultado(resultados);
    });

    // =============================================

    // INICIALIZAÇÃO

    await carregarMapaTemas();

    const dados = await carregarDadosPlanilha();
    if (dados) {
        // Filtra linhas vazias (sem política pública preenchida)
        acervoPlanilha = dados.filter(item => {
            const politica = getValPlanilha(item, "politica_publica", "politica");
            return politica && politica.trim() !== "";
        });
    }

    //Se ja tiver alguma política marcada no carregamento, atualiza os temas
    const politicaInicial = document.querySelector('input[name="politicas"]:checked');
    if (politicaInicial) {
        atualizarSelectTemas(politicaInicial.value);
    }

    setTimeout(() => {
        const valorInicial = esferaSelect.value || esferaSelect.getAttribute("value") || "f";
        atualizarCamposFormulario(valorInicial);
    }, 150);
});
