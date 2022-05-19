[DDEV](https://github.com/drud/ddev) es una herramienta de código abierto que simplifica enormemente la puesta en funcionamiento de entornos de desarrollo PHP locales en cuestión de minutos. Es potente y flexible como resultado de sus configuraciones de entorno por proyecto, que se pueden ampliar, controlar la versión y compartir. En resumen, DDEV tiene como objetivo permitir que los equipos de desarrollo utilicen Docker en su flujo de trabajo sin las complejidades de la configuración a medida.

[Documentación de DDEV](https://ddev.readthedocs.io/en/stable/)

Requisitos
-----------

1.  Docker 18.06 o superior
2.  docker-compose 1.21.0 o superior
3.  Tener disponible el puerto 80 y 443

Instalación
------------

Se instala según el sistema operativo, para ver las opciones disponibles [aquí](https://ddev.readthedocs.io/en/stable/#docker-installation).

Iniciar proyecto Drupal 9
-------------------------

### Versión rápida

    mkdir my-drupal9-site
    cd my-drupal9-site
    ddev config --project-type=drupal9 --docroot=web --create-docroot
    ddev start
    ddev composer create "drupal/recommended-project"
    # En caso de querer instalar drupal 8 usar
    # ddev composer create "drupal/recommended-project:^8"
    ddev composer require drush/drush
    ddev drush site:install -y
    ddev drush uli
    ddev launch

### Versión paso a paso

Ejecutar el siguiente comando y responder las preguntas que se hacen:

    ddev config

    # Especificamos el nombre del proyecto, dejar vacío para que tome el nombre d ela carpeta
    Project name (intendencia):

    # Especificamos la carpeta raíz del proyecto, dejar vacío para el directorio actual
    Docroot Location (current directory):

    # Especificar el tipo de proyecto
    Project Type [backdrop, drupal6, drupal7, drupal8, drupal9, laravel, magento, magento2, php, shopware6, typo3, wordpress] (php): drupal9


Este comando crea una carpeta **.ddev,** se pueden cambiar las configuraciones en el fichero **config.yaml.**

Para iniciar el proyecto se debe ejecutar el comando

    ddev start

Este comando descarga las imágenes necesario he inicia los contenedores de Docker que se necesitan para ejecutar el proyecto.

Una ves con los contenedores están ejecutándose, procedemos a crear el proyecto de Drupal.

Primero entramos al contenedor.

    ddev ssh
    # muestra la siguiente salida
    root@intendencia-web:/var/www/html$

Los comandos que ejecutemos a continuación tendrán efecto dentro del contenedor web, el que contiene PHP y un servidor web.

Ahora vamos a crear el proyecto de Drupal 9, para ello ejecutar el comando:

    composer create-project drupal/recommended-project drupal

Ahora movemos los ficheros para la carpeta raíz del servidor que esta en **/var/www/html**

    mv drupal/* ./ && mv drupal/.* ./

Instalar sitio

    drush site:install -y

Para entrar al sitio web, obtener la url con:

    drush uli

Configurar autenticación con gitlab
-----------------------------------

Cuando se usa un paquete de composer en un repositorio privado es necesario configurar los parámetros de autenticación, en nuestro caso vamos a usar gitalb, información oficial [aquí](https://docs.gitlab.com/ee/user/packages/composer_repository/index.html) .

1\. [Crear token de gitlab](https://gitlab.com/-/profile/personal_access_tokens) y dale permiso de **read\_repository** y **read\_api**.

2\. Configurar _composer_ con nuestro token

    ddev composer config gitlab-token.gitlab.com <personal_access_token>


3\. Configurar el repositorio de gitlab que se desea usar, para ello ir al _**Repositorio > Registro de paquetes  > dar click en la versión deseada.**_
y copiar la line debajo de **Add composer registry** y luego ejecutar la linea copiada con ddev

    # Ejemplo
    ddev composer config repositories.gitlab.com/<ID-GROUP> '{"type": "composer", "url": "https://gitlab.com/api/v4/group/<ID-GROUP>/-/packages/composer/packages.json"}'

5\. Instalar paquete, obteniendo la versión de la pagina anteriormente mencionada debajo de **Install package version**

    ddev composer req <REPO>:<VERSION>

Configurar postgres
-------------------

Si se desea usar el sistema de base de datos **posgres** en lugar de **mysql** que es el que se usa por defecto es necesario realizar algunas configuraciones extras.

1\. En el fichero _.ddev/config.yaml_ agregar las siguientes configuraciones.

    # Instalar el paquete para usar postgres en el contenedor web
    webimage_extra_packages:
      - postgresql-client
    # No inicial los contenedores de bases de datos mysql
    omit_containers: ["db","dba"]

    # No generar el fichero settings.ddev.php
    disable_settings_management: true

    # Configurar las variables de conexion.
    web_environment:
      - DB_NAME=dp_page
      - DB_USER=db
      - DB_PASSWORD=db
      - DB_HOSTNAME=postgres
      - DB_DRIVER=pgsql
      - DB_PORT=5432

2\. Copiar el fichero [docker-compose.postgres.yaml](https://gitlab.com/-/snippets/2174151/raw/main/docker-compose.postgres.yaml) para la carpeta **_.ddev_**

3\. Copiar los ficheros en disponibles [aqui](https://github.com/drud/ddev-contrib/tree/master/docker-compose-services/postgres/commands/postgres) a la carpeta _.ddev/commands._

_4\._ Configurar la base de datos en el fichero _settings.php_ de nuestro Drupal.

    $databases['default']['default'] = [
       'database' => getenv('DB_NAME'),
       'driver' => getenv('DB_DRIVER'),
       'host' => getenv('DB_HOSTNAME'),
       'password' => getenv('DB_PASSWORD'),
       'port' => getenv('DB_PORT'),
       'prefix' => '',
       'username' => getenv('DB_USER'),
       'namespace' => 'Drupal\\Core\\Database\\Driver\\pgsql'
    ];

Nota: Se pueden seguir los pasos [DDEV con PostgresSQL](https://github.com/drud/ddev-contrib/tree/master/docker-compose-services/postgres) pero se recomienda cambiar la imagen de Docker usada.
