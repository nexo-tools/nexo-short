<?php

// Legal pages (privacy + terms), rendered by legal/show.
//
// NOT reviewed by a lawyer. Written to describe accurately what this codebase
// actually does — which is the part an agent can get right — so that a review,
// if the owner wants one, starts from something true rather than from a
// template full of clauses about data the app never touches.
//
// Translation of lang/es/legal.php, which is the source of this content (the
// ecosystem is Spanish-first). Kept by hand: these are paragraphs, so they do
// not go through scripts/generate-translations.mjs.
return [
    'updated' => 'Última atualização: 28 de julho de 2026',

    'privacy' => [
        'title' => 'Privacidade',
        'intro' => 'Esta instância do Nexo Short é open source e auto-hospedável. É um encurtador de links: recolhe o mínimo para que um link curto funcione e para que quem o criou veja quantos cliques teve. O domínio curto não usa cookies, não há rastreadores de terceiros e endereços IP nunca são guardados em bruto.',
        'sections' => [
            [
                'h' => 'O que guardamos da sua conta',
                'p' => 'O seu nome, o seu email e uma versão cifrada (hash) da palavra-passe. É preciso uma conta para criar links; o registo pode estar aberto ou fechado conforme a instância. Se entrar com o Nexo ID, guardamos também o identificador que esse serviço nos devolve para o reconhecer, e a sua palavra-passe nunca passa por aqui.',
            ],
            [
                'h' => 'O que guardamos dos seus links',
                'p' => 'De cada link curto: o seu código, o URL de destino, a conta que o criou, se está ativo e quando foi criado. O URL de destino é visível para quem opera esta instância, porque é quem responde pelo que é servido a partir do seu domínio.',
            ],
            [
                'h' => 'O que guardamos de cada clique',
                'p' => 'Quando alguém abre um link curto guardamos cinco coisas: que link foi e quando, o domínio do site de origem (apenas o domínio, e só se o navegador o enviar), um tipo de dispositivo aproximado (telemóvel, computador ou bot), o país indicado pelo cabeçalho da Cloudflare quando disponível, e uma impressão anónima do visitante. Nada mais: nem o endereço completo da página anterior, nem o IP, nem o navegador.',
            ],
            [
                'h' => 'A impressão do visitante, e porque não o identifica',
                'p' => 'A impressão é calculada com a chave da aplicação, a data do dia, o IP e o navegador, e de tudo isso guarda-se apenas o resultado (um sha256): o IP e o navegador são usados em memória e nunca escritos em disco. Como a data entra no cálculo, a impressão de hoje não pode ser comparada com a de amanhã: serve para contar visitantes únicos dentro de um dia, não para o seguir.',
            ],
            [
                'h' => 'O domínio curto não usa cookies',
                'p' => 'O domínio que serve os links curtos não coloca nenhum cookie, não executa JavaScript e não carrega recursos de terceiros. O redirecionamento é temporário (302) e marcado como não cacheável, para que desativar um link tenha efeito imediato.',
            ],
            [
                'h' => 'Cookies do painel',
                'p' => 'Apenas os necessários para o painel funcionar: o de sessão enquanto está autenticado, e os que memorizam o idioma e o tema claro/escuro que escolheu (partilhados com o resto do ecossistema Nexo). Nenhum é de publicidade ou de rastreio. A sessão é guardada na base de dados e inclui o IP e o navegador com que iniciou sessão: é o mecanismo padrão do Laravel e diz respeito apenas às contas, nunca a quem clica num link.',
            ],
            [
                'h' => 'Verificação de segurança ao criar um link',
                'p' => 'Se quem opera esta instância configurou uma chave do Google Safe Browsing, o URL de destino é enviado a esse serviço no momento da criação para verificar se não é malicioso. Sem chave configurada a verificação está desligada e nenhum pedido a terceiros sai do servidor.',
            ],
            [
                'h' => 'Denúncias de abuso',
                'p' => 'Qualquer pessoa pode denunciar um link sem se identificar. Da denúncia guardamos o código do link, o motivo escolhido e o comentário opcional que escrever. Não guardamos quem a enviou: sem conta, sem IP, sem qualquer outro identificador.',
            ],
            [
                'h' => 'Métricas do ecossistema (opcionais e desligadas por omissão)',
                'p' => 'Quem opera a instância pode ativar um sinal anónimo de visita ao painel para o hub do ecossistema Nexo. Vem desligado de origem, não usa cookies, não envia nada que o identifique e nunca é emitido a partir do domínio curto.',
            ],
            [
                'h' => 'Durante quanto tempo',
                'p' => 'Os cliques são conservados enquanto o link existir: apagar um link apaga os seus cliques, e apagar uma conta apaga os seus links com eles. As denúncias são conservadas como histórico de moderação.',
            ],
            [
                'h' => 'Os seus direitos',
                'p' => 'Pode pedir acesso aos seus dados, a sua correção ou a sua eliminação escrevendo a quem opera esta instância (o contacto está na página de ajuda).',
            ],
            [
                'h' => 'Outras instâncias',
                'p' => 'O Nexo Short pode ser instalado em qualquer servidor. Cada instalação é independente e responsável pelos seus próprios dados: esta política fala apenas desta instância.',
            ],
        ],
    ],

    'terms' => [
        'title' => 'Termos de utilização',
        'intro' => 'Ao usar esta instância do Nexo Short aceita o que se segue. É um serviço gratuito, oferecido tal como está.',
        'sections' => [
            [
                'h' => 'O que é o serviço',
                'p' => 'Um encurtador de links: transforma um URL longo num link curto servido a partir do domínio desta instância, e mostra a quem o criou quantos cliques teve, a partir de que sites, com que tipo de dispositivo e de que países. Só são aceites destinos http e https.',
            ],
            [
                'h' => 'A sua conta',
                'p' => 'É preciso uma conta para criar links. É responsável pelos links criados a partir dela e por manter a sua palavra-passe segura. O registo pode estar fechado nesta instância; isso não impede que os links já criados continuem a funcionar.',
            ],
            [
                'h' => 'Os links curtos são públicos por natureza',
                'p' => 'Qualquer pessoa com o endereço curto pode segui-lo: não tem palavra-passe, e o código é curto, por isso pode ser adivinhado à tentativa. Não use links curtos para material privado ou confidencial. O que tem de ser protegido é o destino, não o link.',
            ],
            [
                'h' => 'Utilização indevida',
                'p' => 'Não é permitido encurtar links para malware, phishing, burlas, spam, usurpação de identidade ou conteúdo ilegal, nem usar o serviço para contornar bloqueios ou disfarçar um destino desse tipo. Há limites de criação por conta e por IP, e se quem opera a instância configurou o Google Safe Browsing, os destinos assinalados como perigosos são rejeitados na criação.',
            ],
            [
                'h' => 'Denúncias e moderação',
                'p' => 'Qualquer pessoa pode denunciar um link a partir do próprio domínio curto, sem conta. Quem opera a instância pode desativar qualquer link: deixa de redirecionar de imediato — os redirecionamentos nunca são cacheados — e passa a mostrar a página de "link não encontrado". O link não é apagado, para conservar o histórico de moderação.',
            ],
            [
                'h' => 'Disponibilidade',
                'p' => 'O serviço é oferecido sem garantias de disponibilidade. Um link curto pode deixar de funcionar, e o serviço pode mudar ou ser descontinuado. Se um link é importante para si, guarde também o seu destino original.',
            ],
            [
                'h' => 'Limite de responsabilidade',
                'p' => 'Quem opera esta instância não se responsabiliza por danos decorrentes da utilização do serviço, incluindo links que deixem de funcionar ou perda de métricas. O conteúdo do site de destino é da responsabilidade de quem criou o link e de quem publica esse site.',
            ],
            [
                'h' => 'Software livre',
                'p' => 'O Nexo Short é distribuído com licença MIT: pode ler o código, modificá-lo e alojar a sua própria instância. O software é entregue sem garantias, conforme indica essa licença.',
            ],
            [
                'h' => 'Alterações',
                'p' => 'Estes termos podem mudar. A data acima indica a última atualização.',
            ],
        ],
    ],
];
