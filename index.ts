import { Elysia } from 'elysia';

export const app = new Elysia()
    .get('/*', async ({ path, set }) => {
        // 1. Mapeamento de Rotas do Servidor para Arquivos HTML
        const routes: Record<string, string> = {
            '/': 'public/index.html',
            '/contatos': 'public/contatos.html',
            '/sobre': 'public/sobre.html'
        };

        // Se a URL for uma das suas rotas, retorna o HTML correspondente
        if (routes[path]) {
            return Bun.file(routes[path]);
        }

        // 2. Busca automática por arquivos estáticos (JS, CSS, Imagens, etc)
        // O path já começa com '/', então concatenamos com 'public'
        const file = Bun.file(`public${path}`);

        if (await file.exists()) {
            return file;
        }

        // 3. Caso não seja rota nem arquivo físico, retorna 404
        set.status = 404;
        return '404 - Not Found';
    })
    .listen(8080);

console.log(`🦊 Servidor rodando em http://${app.server?.hostname}:${app.server?.port}`);