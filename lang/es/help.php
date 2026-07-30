<?php

// FAQs del centro de ayuda de Nexo Short (renderizadas por HelpController).
return [
    'faqs' => [
        [
            'q' => '¿Qué es Nexo Short?',
            'a' => 'Nexo Short es un acortador de enlaces de código abierto. Conviertes enlaces largos en cortos servidos desde un dominio que controlas, y obtienes métricas de clics respetuosas con la privacidad —clics, referentes, dispositivos y países— sin cookies ni terceros.',
        ],
        [
            'q' => '¿Es gratis y de código abierto?',
            'a' => 'Sí. Nexo Short es gratuito y con licencia MIT: el código está publicado en GitHub y puedes usarlo sin costo ni comisiones, como el resto del ecosistema Nexo.',
        ],
        [
            'q' => '¿Puedo autohospedarlo?',
            'a' => 'Sí. Puedes desplegar Nexo Short en tu propio servidor, apuntarlo a tu base de datos y servir los enlaces cortos desde tu propio dominio. Funciona en modo standalone, sin depender de la instancia hospedada ni del resto del ecosistema.',
        ],
        [
            'q' => '¿Usan cookies o me rastrean?',
            'a' => 'No. El redirect no coloca cookies ni carga rastreadores de terceros, y nunca se almacenan direcciones IP en crudo. Los visitantes únicos se cuentan con una huella anónima que rota a diario y no puede vincularse entre días.',
        ],
        [
            'q' => '¿Necesito una cuenta?',
            'a' => 'Necesitas una cuenta para crear y gestionar enlaces desde el panel. El registro puede estar cerrado en la instancia hospedada, pero al autohospedar tienes tu propio panel con tus propias cuentas.',
        ],
    ],
];
