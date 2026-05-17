document.addEventListener("DOMContentLoaded", function () {
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
    
    const judiciarioContainer = document.getElementById("judiciarioContainer");
    const agendaGrid = document.getElementById("agendaGrid");

    const dadosPesquisa = {
        ufs: [
            { id: "ba", nome: "Bahia" },
            { id: "sp", nome: "São Paulo" }
        ],
        municipios: {
            "ba": [
                { id: "jequie", nome: "Jequié" },
                { id: "vitoria_conquista", nome: "Vitória da Conquista" },
                { id: "ipiau", nome: "Ipiaú" },
                { id: "itagi", nome: "Itagi" }
            ],
            "sp": [
                { id: "campinas", nome: "Campinas" }
            ]
        },
        territorios: {
            "ba": [
                { id: "medio_rio_contas", nome: "Médio Rio de Contas" },
                { id: "sudoeste", nome: "Sudoeste Baiano" }
            ],
            "sp": [
                { id: "rmc", nome: "Região Metropolitana de Campinas" }
            ]
        }
    };

    function carregarUFs() {
        ufSelect.innerHTML = '<option value="" disabled selected>Selecione um estado</option>';
        dadosPesquisa.ufs.forEach(uf => {
            const option = document.createElement("option");
            option.value = uf.id;
            option.textContent = `${uf.id.toUpperCase()} - ${uf.nome}`;
            ufSelect.appendChild(option);
        });
    }

    function carregarMunicipios(ufId) {
        municipioSelect.innerHTML = '<option value="" disabled selected>Selecione um município</option>';
        if (dadosPesquisa.municipios[ufId]) {
            dadosPesquisa.municipios[ufId].forEach(mun => {
                const option = document.createElement("option");
                option.value = mun.id;
                option.textContent = mun.nome;
                municipioSelect.appendChild(option);
            });
        }
    }

    function carregarTerritorios(ufId) {
        territorioSelect.innerHTML = '<option value="" disabled selected>Selecione um território</option>';
        if (dadosPesquisa.territorios[ufId]) {
            dadosPesquisa.territorios[ufId].forEach(ter => {
                const option = document.createElement("option");
                option.value = ter.id;
                option.textContent = ter.nome;
                territorioSelect.appendChild(option);
            });
        }
    }

    function atualizarCamposFormulario(esferaValue) {
        if (!judiciarioContainer || !agendaGrid) return;
        
        ufGroup.classList.add("hidden");
        agrupamentoGroup.classList.add("hidden");
        municipioGroup.classList.add("hidden");
        territorioGroup.classList.add("hidden");

        if (esferaValue === "f") {
            judiciarioContainer.style.display = "block";
            agendaGrid.setAttribute("cols", "repeat(3, 1fr)");
        } else if (esferaValue === "e") {
            ufGroup.classList.remove("hidden");
            judiciarioContainer.style.display = "none";
            agendaGrid.setAttribute("cols", "repeat(2, 1fr)");
            carregarUFs();
        } else if (esferaValue === "m") {
            ufGroup.classList.remove("hidden");
            agrupamentoGroup.classList.remove("hidden"); 
            judiciarioContainer.style.display = "none";
            agendaGrid.setAttribute("cols", "repeat(2, 1fr)");
            carregarUFs();
        }
    }

    esferaSelect.addEventListener("algol-change", function (e) {
        atualizarCamposFormulario(e.detail.value);
    });

    ufSelect.addEventListener("algol-change", function (e) {
        const ufEscolhida = e.detail.value;
        if (ufEscolhida) {
            carregarMunicipios(ufEscolhida);
            carregarTerritorios(ufEscolhida);
        } else {
            municipioSelect.innerHTML = '';
            territorioSelect.innerHTML = '';
        }
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

    form.addEventListener("submit", function (e) {
        if (!validarAnos()) {
            e.preventDefault();
            return;
        }
    });

    function validarAnos() {
        const startYear = document.querySelector('algol-input-number[name="start-year"]').value;
        const endYear = document.querySelector('algol-input-number[name="end-year"]').value;

        if (startYear !== "" && endYear !== "") {
            const anoInicio = parseInt(startYear);
            const anoFim = parseInt(endYear);

            if (anoInicio > anoFim) {
                alert("O ano inicial não pode ser maior que o ano final!");
                return false;
            }
        }
        return true;
    }

    setTimeout(() => {
        const valorInicial = esferaSelect.value || "f";
        atualizarCamposFormulario(valorInicial);
    }, 150);
});