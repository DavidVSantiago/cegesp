const IMAGE_BUCKET = 'http://localhost:8080'; // fazer com que este valor seja enviado pleo servidor no request da rota '/'

/** Função utilitária para carregar e injetar fragmento html em seletor específico
 * url - caminho do arquivo html a ser carregado
 * selector - seletor do elemento onde o html será injetado */
async function injectHTML(url, selector) {
  try {
    const response = await fetch(url); // busca a página contendo o html
    if (!response.ok) {throw new Error(`Erro ao carregar ${url}: ${response.status}`);}
    const html = await response.text(); // extrai o conteudo da página
    
    const target = document.querySelector(selector);
    if (target) target.innerHTML = html;
    else console.warn(`Elemento "${selector}" não encontrado.`);
  } catch (err) {console.error('Erro ao carregar o HTML:', err);}
}

/** Função utilitária. Recebe o link do arquivo .css e o carrega no head*/
function addStyle(href) {
  document.head.appendChild(Object.assign(document.createElement('link'), {
    rel: 'stylesheet', href
  }));
}
function addScript(src) {
    const script = document.createElement('script');
    script.src = src;
    script.type = 'text/javascript';
    script.defer = true; // evita que a página trave antes do carregamento do script
    document.head.appendChild(script);
}

/** Carrega dinamicamente o conteúdo e os estilos do header */
async function loadHeader(){
    await injectHTML('templates/header/header.html', '#header');
    addStyle('templates/header/header.css');
    addStyle('templates/header/header_tablet.css');
    addStyle('templates/header/header_phone.css');
    addScript('templates/header/header.js');
}

/** Carrega dinamicamente o conteúdo e os estilos do footer */
async function loadFooter(){
    await injectHTML('/templates/footer/footer.html', '#footer');
    addStyle('/templates/footer/footer.css');
    addStyle('/templates/footer/footer_tablet.css');
    addStyle('/templates/footer/footer_phone.css');

}

async function inicializarPagina() {
  await loadHeader(); // carrega o header e seus estilos
  await loadFooter(); // carrega o footer e seus estilos
  document.documentElement.style.display = ''; // volta a exibir o conteúdo da página
}

document.documentElement.style.display = 'none'; // a principio a página é invisível
inicializarPagina();

	