<?php

// Legal pages (privacy + terms), rendered by legal/show.
//
// NOT reviewed by a lawyer. Written to describe accurately what this codebase
// actually does — which is the part an agent can get right — so that a review,
// if the owner wants one, starts from something true rather than from a
// template full of clauses about data the app never touches.
//
// Spanish is the source of this content (the ecosystem is Spanish-first); en/pt
// are translations of it, kept in the sibling files by hand (these are
// paragraphs, so they do not go through scripts/generate-translations.mjs).
return [
    'updated' => 'Última actualización: 28 de julio de 2026',

    // Rendered only when NEXO_LEGAL_OPERATOR / NEXO_LEGAL_CONTACT are set.
    'operator' => [
        'h' => 'Quién opera esta instancia',
        'p' => 'Esta instancia la opera :operator.',
        'contact' => 'Para cualquier consulta sobre tus datos podés escribir a :contact.',
    ],

    'privacy' => [
        'title' => 'Privacidad',
        'intro' => 'Esta instancia de Nexo Short es open source y autoalojable. Es un acortador de enlaces: recoge lo mínimo para que un enlace corto funcione y para que quien lo creó vea cuántos clics tuvo. El dominio corto no pone cookies, no hay rastreadores de terceros y nunca se guardan direcciones IP en crudo.',
        'sections' => [
            [
                'h' => 'Qué guardamos de tu cuenta',
                'p' => 'Tu nombre, tu email y una versión cifrada (hash) de la contraseña. Hace falta una cuenta para crear enlaces; el registro puede estar abierto o cerrado según la instancia. Si entrás con Nexo ID, guardamos además el identificador que ese servicio nos devuelve para reconocerte, y tu contraseña no pasa nunca por aquí.',
            ],
            [
                'h' => 'Qué guardamos de tus enlaces',
                'p' => 'De cada enlace corto: su código, la URL de destino, la cuenta que lo creó, si está activo y cuándo se creó. La URL de destino es visible para quien opera esta instancia, porque es quien responde por lo que se sirve desde su dominio.',
            ],
            [
                'h' => 'Qué guardamos de cada clic',
                'p' => 'Cuando alguien abre un enlace corto guardamos cinco cosas: qué enlace fue y cuándo, el dominio del sitio desde el que venía (solo el dominio, y solo si el navegador lo envía), un tipo de dispositivo aproximado (móvil, escritorio o bot), el país que informa la cabecera de Cloudflare cuando está disponible, y una huella anónima del visitante. Nada más: ni la dirección de la página anterior completa, ni la IP, ni el navegador.',
            ],
            [
                'h' => 'La huella del visitante, y por qué no te identifica',
                'p' => 'La huella se calcula con la clave de la aplicación, la fecha del día, la IP y el navegador, y de todo eso solo se guarda el resultado (un sha256): la IP y el navegador se usan en memoria y no se escriben nunca en disco. Como la fecha entra en el cálculo, la huella de hoy no se puede comparar con la de mañana: sirve para contar visitantes únicos dentro de un día, no para seguirte.',
            ],
            [
                'h' => 'El dominio corto no usa cookies',
                'p' => 'El dominio que sirve los enlaces cortos no coloca ninguna cookie, no ejecuta JavaScript y no carga recursos de terceros. El redirect es temporal (302) y se marca como no cacheable, para que desactivar un enlace surta efecto de inmediato.',
            ],
            [
                'h' => 'Cookies del panel',
                'p' => 'Solo las necesarias para que el panel funcione: la de sesión mientras estás identificado, y las que recuerdan el idioma y el tema claro/oscuro que elegiste (compartidas con el resto del ecosistema Nexo). Ninguna es publicitaria ni de seguimiento. La sesión se guarda en la base de datos e incluye la IP y el navegador con los que iniciaste sesión: es el mecanismo estándar de Laravel y afecta solo a las cuentas, nunca a quien hace clic en un enlace.',
            ],
            [
                'h' => 'Comprobación de seguridad al crear un enlace',
                'p' => 'Si quien opera esta instancia configuró una clave de Google Safe Browsing, la URL de destino se envía a ese servicio en el momento de crear el enlace para comprobar que no es maliciosa. Sin clave configurada la comprobación está apagada y no sale ninguna petición a terceros.',
            ],
            [
                'h' => 'Reportes de abuso',
                'p' => 'Cualquiera puede reportar un enlace sin identificarse. Del reporte guardamos el código del enlace, el motivo que elegiste y el comentario opcional que escribas. No guardamos quién lo envió: no hay cuenta, ni IP, ni ningún otro identificador.',
            ],
            [
                'h' => 'Métricas del ecosistema (opcional y apagadas por defecto)',
                'p' => 'Quien opera la instancia puede activar una señal anónima de visita al panel hacia el hub del ecosistema Nexo. Viene apagada de fábrica, no usa cookies, no envía nada que te identifique y nunca se emite desde el dominio corto.',
            ],
            [
                'h' => 'Cuánto tiempo',
                'p' => 'Los clics se conservan mientras exista el enlace: al borrar un enlace se borran sus clics, y al borrar una cuenta se borran sus enlaces con ellos. Los reportes se conservan como historial de moderación.',
            ],
            [
                'h' => 'Tus derechos',
                'p' => 'Podés pedir acceso a tus datos, su corrección o su borrado escribiendo a quien opera esta instancia (el contacto está en la página de ayuda).',
            ],
            [
                'h' => 'Otras instancias',
                'p' => 'Nexo Short se puede instalar en cualquier servidor. Cada instalación es independiente y responsable de sus propios datos: esta política habla solo de esta instancia.',
            ],
        ],
    ],

    'terms' => [
        'title' => 'Términos de uso',
        'intro' => 'Al usar esta instancia de Nexo Short aceptás lo que sigue. Es un servicio gratuito, ofrecido tal cual está.',
        'sections' => [
            [
                'h' => 'Qué es el servicio',
                'p' => 'Un acortador de enlaces: convierte una URL larga en un enlace corto servido desde el dominio de esta instancia, y muestra a quien lo creó cuántos clics tuvo, desde qué sitios, con qué tipo de dispositivo y desde qué países. Solo se admiten destinos http y https.',
            ],
            [
                'h' => 'Tu cuenta',
                'p' => 'Hace falta una cuenta para crear enlaces. Sos responsable de los enlaces creados desde ella y de mantener tu contraseña a salvo. El registro puede estar cerrado en esta instancia; eso no impide que los enlaces ya creados sigan funcionando.',
            ],
            [
                'h' => 'Los enlaces cortos son públicos por naturaleza',
                'p' => 'Cualquiera que tenga la dirección corta puede seguirla: no lleva contraseña, y el código es corto, así que se puede llegar a adivinar probando. No uses enlaces cortos para material privado o confidencial. Lo que hay que proteger es el destino, no el enlace.',
            ],
            [
                'h' => 'Uso indebido',
                'p' => 'No se permite acortar enlaces a malware, phishing, estafas, spam, suplantación de terceros ni contenido ilegal, ni usar el servicio para esquivar bloqueos o disimular un destino de esa clase. Hay límites de creación por cuenta y por IP, y si quien opera la instancia configuró Google Safe Browsing, los destinos marcados como peligrosos se rechazan al crearlos.',
            ],
            [
                'h' => 'Reportes y moderación',
                'p' => 'Cualquiera puede reportar un enlace desde el propio dominio corto, sin cuenta. Quien opera la instancia puede desactivar cualquier enlace: deja de redirigir de inmediato —los redirects no se cachean— y pasa a mostrar la página de "enlace no encontrado". El enlace no se borra, para conservar el historial de moderación.',
            ],
            [
                'h' => 'Disponibilidad',
                'p' => 'El servicio se ofrece sin garantías de disponibilidad. Un enlace corto puede dejar de funcionar, y el servicio puede cambiar o discontinuarse. Si un enlace es importante para vos, guardá también su destino original.',
            ],
            [
                'h' => 'Límite de responsabilidad',
                'p' => 'Quien opera esta instancia no se hace responsable de daños derivados del uso del servicio, incluidos enlaces que dejen de funcionar o pérdida de métricas. El contenido del sitio de destino es responsabilidad de quien creó el enlace y de quien publica ese sitio.',
            ],
            [
                'h' => 'Software libre',
                'p' => 'Nexo Short se distribuye con licencia MIT: podés leer el código, modificarlo y alojar tu propia instancia. El software se entrega sin garantías, según indica esa licencia.',
            ],
            [
                'h' => 'Cambios',
                'p' => 'Estos términos pueden cambiar. La fecha de arriba indica la última actualización.',
            ],
        ],
    ],
];
