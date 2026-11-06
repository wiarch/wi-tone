# WI Tone

**Afinador musical digital para guitarra, bajo y más instrumentos.**

WI Tone es una plataforma de herramientas musicales centrada en la afinación de instrumentos. Su objetivo es ofrecer una experiencia sencilla y fiable para afinar guitarra, bajo y otros instrumentos, directamente desde el navegador y con una arquitectura preparada para futuras experiencias de escritorio y móviles.

> Estado: proyecto en evolución. Este repositorio ya contiene un afinador basado en Web Audio API, un metrónomo y herramientas de teoría musical. Las capacidades descritas como futuras en la documentación no deben interpretarse como funcionalidades ya implementadas.

## Enfoque del producto

La experiencia principal será un afinador cromático con detección de frecuencia mediante micrófono, indicación visual de desviación en cents y soporte para distintas afinaciones e instrumentos.

### Funcionalidades existentes

- Afinador cromático en navegador mediante Web Audio API.
- Detección de frecuencia mediante autocorrelación normalizada y estimación de calidad de señal.
- Conversión de frecuencia a nota, octava y desviación en cents.
- Selector de guitarra y bajo con afinaciones estándar y Drop D.
- Tonos de referencia por cuerda para los presets disponibles.
- Indicador visual de desviación respecto a la nota cromática más cercana.
- Reproducción de tonos de referencia para afinar por oído.
- Metrónomo con tempo ajustable y compases 4/4, 3/4 y 6/8.
- Círculo de quintas y utilidades de teoría musical.
- Aplicación web construida con Laravel, Blade, JavaScript y Vite.

### Dirección del producto

- Afinaciones estándar y alternativas para guitarra y bajo.
- Selector de instrumento, afinación y referencia de frecuencia (A4).
- Indicador de aguja y estado visual: bemol, afinado o sostenido.
- Calibración y controles de sensibilidad para distintos entornos.
- Soporte progresivo para ukelele, violín y otros instrumentos.
- Experiencia responsive y PWA; escritorio y móvil como objetivos de evolución.
- Herramientas complementarias: metrónomo, tonos de referencia, acordes y escalas.

Consulta [PRODUCT.md](docs/PRODUCT.md) y [ROADMAP.md](docs/ROADMAP.md) para distinguir el alcance actual del plan futuro.

## Stack tecnológico

- PHP 8.3+
- Laravel 13
- Blade
- JavaScript
- Web Audio API y MediaDevices API
- Vite
- Tailwind CSS

## Requisitos

- PHP 8.3 o superior.
- Composer.
- Node.js y npm.
- Una base de datos compatible con Laravel (SQLite es adecuada para desarrollo local).
- Navegador moderno con soporte para Web Audio API y acceso al micrófono.

El acceso al micrófono requiere un contexto seguro: HTTPS en despliegues y normalmente `localhost` en desarrollo.

## Instalación

```bash
git clone https://github.com/wiarch/wi-tone.git
cd wi-tone
composer install
cp .env.example .env
php artisan key:generate
```

Configura la conexión de base de datos en `.env`. Para SQLite:

```bash
touch database/database.sqlite
```

Asegúrate de que `.env` tenga `DB_CONNECTION=sqlite` y que la ruta de la base de datos sea la correspondiente a tu entorno. Después:

```bash
php artisan migrate
npm install
npm run build
```

Pruebas del motor musical:

\`\`\`bash
npm run test:music
\`\`\`

Inicia el entorno de desarrollo:

```bash
composer run dev
```

Abre la URL local que indique Laravel. Para probar el afinador, concede permiso de micrófono al navegador.

## Privacidad y permisos

- El afinador necesita permiso explícito del navegador para acceder al micrófono.
- El procesamiento de audio del afinador actual se realiza en el cliente mediante Web Audio API.
- La aplicación debe detener las pistas del micrófono cuando el usuario detenga el afinador o abandone la experiencia.
- No se debe grabar, almacenar ni transmitir audio sin una función explícita, consentimiento informado y documentación correspondiente.
- En producción, sirve la aplicación mediante HTTPS.

Consulta [SECURITY.md](docs/SECURITY.md).

## Estructura de documentación

- [PRODUCT.md](docs/PRODUCT.md): visión, usuarios, alcance y principios.
- [ARCHITECTURE.md](docs/ARCHITECTURE.md): arquitectura actual y evolución propuesta.
- [ROADMAP.md](docs/ROADMAP.md): fases de desarrollo.
- [SECURITY.md](docs/SECURITY.md): privacidad, permisos y controles técnicos.
- [CONTRIBUTING.md](CONTRIBUTING.md): pautas de contribución.
- [CLAUDE.md](CLAUDE.md): contexto e instrucciones para asistentes de programación.
- [PROMPT.md](PROMPT.md): prompt maestro para continuar el desarrollo con Claude.

## Módulos heredados

El código actual también incluye gestión de canciones, acordes, contactos y planes de servicio. Estos módulos pertenecen al enfoque anterior del proyecto. Se mantienen en el repositorio por compatibilidad y no forman parte del núcleo del nuevo WI Tone. Su eliminación o extracción debe realizarse en una tarea separada, después de revisar dependencias, rutas, datos y migraciones.

## Licencia

Este proyecto se distribuye bajo la licencia indicada en el archivo [LICENSE](LICENSE), si está presente en el repositorio.
