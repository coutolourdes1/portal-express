# Portal Express
Perfil de instalación de Drupal 9 para un sitio general de una intendencia.

## Empezar
El perfil de instalación se agrega como un paquete de **composer** y se agrega a un repositorio privado de **gitlab** esto posibilita que cuando se instale **composer** detecte sus dependencias y las instale automáticamente, pero esta opción no debe ser usada para el desarrollo.

1. Configurar autenticación con gitlab
	Cuando se usa un paquete de **composer** en un repositorio privado es necesario configurar los parámetros de autenticación, en nuestro caso vamos a usar **GitLab**, mas información [aquí](https://docs.gitlab.com/ee/user/packages/composer_repository/index.html) .

	a [Crear token de gitlab](https://gitlab.com/-/profile/personal_access_tokens) y darle los siguientes permiso de [**read\_repository**, **read\_api**].

	b\. Configurar **composer** con token creado

	    composer config gitlab-token.gitlab.com <personal_access_token>


3\. Configurar el repositorio de gitlab que se desea usar, para ello ir a la pagina de gitlab  _**Repositorio > Registro de paquetes  > dar click en la versión deseada.**_
y copiar la line debajo de **Add composer registry** y luego ejecutar la linea copiada con ddev

    # Ejemplo
    composer config repositories.gitlab.com/<ID-GROUP> '{"type": "composer", "url": "https://gitlab.com/api/v4/group/<ID-GROUP>/-/packages/composer/packages.json"}'

5\. Instalar paquete, obteniendo la versión de la pagina anteriormente mencionada debajo de **Install package version**

    composer req <REPO>:<VERSION>

**Nota:** Si lanza un error que con un mensaje similar a 
"but it does not match your minimum-stability"
Cambiar en el fichero composer.json
	
	"minimum-stability": "stable",
por

	"minimum-stability": "dev",

6\. Instalar el siguiente paquete que permite agregarle parches de git al sitio 

	composer req cweagans/composer-patches

7\. Agregar al fichero composer.json en la sección "extra" la linea
	
    "patches-file": "web/profiles/contrib/isa/composer.patches.json"

Ejemplo:
```json
	...
	"config": {	"sort-packages": true	},
	"extra": {
		"patches-file": "web/profiles/contrib/isa/composer.patches.json",
	...
```
7\. Ejecutar el comando

	composer update

8.\. [Instalar sitio usando drush](#Instalar sitio usando drush)

## Desarrollar
Para desarrollar usando el perfil de instalación es necesario tener una instancia de Drupal 9 disponible y realizar los siguientes pasos:
1. Clone el proyecto dentro de la carpeta **web/profiles/contrib/isa/**.
	```bash
	 git clone git@gitlab.com:digitalprojex/drupal/isa/isa.git web/profiles/contrib/isa
	```
	**Nota:** La dirección de git puede cambiar.
	#### Bajar Submódulos de git
	Los estilos CSS están en un repositorio git independiente y se usan como Submódulos de git, para que este repositorio baje sus ficheros es necesario ejecutar el siguiente comando.
	```bash
	git submodule update --init --remote
	```
	Para mas información leer documentación oficial de [Git Submodules](https://git-scm.com/book/en/v2/Git-Tools-Submodules)

2. Agregar todo el contenido de la sección **require** que se encuentra en el fichero **composer.json** del proyecto recién clonado al fichero **composer.json** que se encuentra en la raíz del proyecto.

3. Instale el paquete de
	```bash
	composer require "cweagans/composer-patches"
	```

4. Agregar al **composer.json** de la raíz en la sección extra **"patches-file": "web/profiles/isa/composer.patches.json"**
	```json
	...
	 "extra": {
	  ...
	   "patches-file": "web/profiles/contrib/isa/composer.patches.json"
	   }
	```

5. Instalar dependencias
	```bash
	composer install
	```

## Traducciones
Para traducir los módulos hay que seguir los siguientes pasos:
Por ejemplo vamos a agregar las traducciones del módulo **isa_activity**

1. Agregar en el fichero isa_activity.info.yml
	```yml
	'interface translation project': isa_activity
	'interface translation server pattern': profiles/contrib/isa/modules/custom/isa_activity/translations/%language.po
	```

2. Crear una carpeta **translations** dentro del módulo y un fichero **es.po**, donde se agregan las traducciones.

### Para el desarrollo
Es importante trabajar en ingles cuando se vallan a exportar las configuraciones. Siempre verificar que el idioma de la
configuración este en ingles ( **langcode: en ** ) al igual que los textos.

### Actualizar las traducciones
Cuando se instala el sitio con el comando
```bash
drush si --yes
```
No importa las configuraciones de los módulos y es necesario ejecutar el siguiente comando
```bash
drush locale-check && drush locale-update && drush cr
```

# Instalar sitio usando drush

```bash
drush si --locale=es \
      --account-name=moly \
      --account-pass="U#Rl#1k6B8a" \
      --site-name="Portal Express" \
      --site-mail=portal-express@gmail.com \
      --yes \
&& drush en isa_default_content isa_demo_content --yes \
&& drush locale-check && drush locale-update \
&& drush sapi-r \
&& drush pag all all \
&& drush cr

```
Para el desarrollo instalar
```bash
drush en devel devel_generate features_ui views_ui --yes
```

# CI/CD
Para que se ejecuten las tareas de Integración continua correctamente es necesario configurar en gitlab las siguientes variables.

	#Token de gitlab con el permiso de leer repositorios
	PERSONAL_ACCESS_TOKEN=token-user-1
	GITLAB_USER=user1
	# Dominio dond esta desplegado el gitla
	GITLAB_DOMAIN=gitlab.com
	# Nombre del pqquete de php
	APP_PACKAGIST=digitalprojex/isa
	# grupo donde esta el proyecto
	GROUP_ID=8740961
	# Usuario y Token del gtlab de isa para descargar el proyecto del diseño
	GITLAB_ISA_USER=user
	GITLAB_ISA_TOKEN=token-user-1

## Entregables
Los entregables se guardan como un paquete de composer en el [Packagist de GitLab](https://gitlab.com/digitalprojex/drupal/isa/isa/-/packages)
Para crear un nuevo paquete es necesario seguir los siguientes pasos:
1. Aumentar la versión de perfil de instalación en el fichero "isa.info.yml"
2. Crear un tag de git con la version correspondiente y subirlo. Esto ejecuta en tarea de gitlab que crea el paquete en el Packagist. El progreso de la tarea se puede vern en [pipelines](https://gitlab.com/digitalprojex/drupal/isa/isa/-/pipelines)

# Trabajo con Drupal
1. Se establece Drupal 9 como la versión a usar.
2. Mantener las funcionalidades en módulos, para que puedan ser activadas y desactivadas.
3. Usar el módulo **features** para que facilite el trabajo de exportar las configuraciones.
4. Siempre usar **composer** para instalar las dependencias, instalar parches y ejecutar **scripts**.
5. Solo agregar al **git** el código propio, nunca el de terceros.
6. Siempre tener todo en código, no ejecutar tareas manuales.
7. Los módulos personalizados **custom** siempre empiezan con el nombre del **profile** como prefijo.
8. Para el desarrollo se usara [Git flow](https://git-scm.com/docs/gitworkflows) lo que significa que siempre se suben los **commit** a la rama **develop**, la rama **qa** y **master** están bloqueadas y solo aceptan **commit** vía **merger request**.
