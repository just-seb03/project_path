# project_path

## Descripción del Proyecto
Plataforma web integral diseñada para centralizar y gestionar los procesos de prácticas. El sistema actúa como eje articulador entre estudiantes, universidades y empresas, facilitando el seguimiento y el desarrollo de las actividades formativas.

## Arquitectura
El proyecto utiliza una **arquitectura por capas**. Este enfoque garantiza:
* **Mantenibilidad:** Facilidad para localizar y corregir errores.
* **Escalabilidad:** Permite mejorar el sistema en el futuro sin afectar la estructura base.
* **Facilidad de Implementación:** Optimiza la produccion del sistema en distintas instituciones educativas.

## Motivadores del Proyecto
* **Estandarización de procesos:** Homologar las etapas del ciclo de prácticas para asegurar un rigor metodológico uniforme.
* **Fortalecimiento del conocimiento institucional:** Difundir de manera efectiva los protocolos y normativas vigentes entre estudiantes y entidades empresariales.
* **Optimización del seguimiento activo:** Proveer herramientas analíticas que permitan a las universidades supervisar el desempeño y progreso del estudiante en tiempo real.

## Estructura del Directorio
* **`config/`**: Contiene los archivos de configuración del servidor y los scripts de conexión a las bases de datos.
* **`public/`**: Directorio de acceso público. Incluye el punto de entrada (`index`), pantallas de autenticación y recursos estáticos (imágenes, audios y documentos).
* **`src/`**: Contiene la lógica de negocio y el núcleo del backend del sistema.
* **`templates/`**: Almacena los layouts base para estandarizar el diseño y optimizar el tiempo de desarrollo de nuevas interfaces al estandarizar los diseños.
* **`views/`**: Contiene las interfaces de usuario finales, organizadas modularmente para facilitar su navegación.

## Diagrama de Entidad-Relacion

![Diagrama](/docs/db_diagram.svg "Diagrama de Entidad-Relacion respecto a la base de datos.")

## Digrama de Actividades

![Diagrama](/docs/activity_diagram_uml.svg "Diagrama de Actividades.")

## Instrucciones de Montaje

### Ubuntu/Debian

#### Dependencias:
`php`: Lenguaje de programación del lado del servidor encargado de procesar la lógica de la web.

`php-mysql`: Módulo de extensión que permite la comunicación entre PHP y el gestor de bases de datos MySQL.

`mysql-server`: Sistema de gestión de bases de datos donde se almacena toda la información del sistema.

`apache2`: Servidor web HTTP encargado de servir los archivos al navegador y procesar directivas de configuración.

#### Configuración:

##### 1. Instalación de paquetes:
instala todas las dependencias con `sudo apt install php php-mysql mysql-server apache2`
##### 2. Inicio de servicios 
Es importante inicar los servicios de la siguiente forma:
`sudo service apache2 start`
`sudo service mysql start`

##### 3. Creacion de directorio 
Descarga el código fuente en tu máquina local usando `git clone` o `git remote add origin`

##### 4. Despliegue del servidor 
Dentro del repositorio dirigete al directorio `dir/` en el cual se encuentra el codigo fuente y utiliza el servidor integrado de PHP para pruebas locales en el puerto 8000: `php -S localhost:8000`

##### 5. Entrar en la WEB
Una vez inicializada la web, dentro de tu navegador ve a `http://localhost:8000/public/inicio.php`

##### 6. Creacion de Base de datos
Ejecuta `sudo mysql` y dentro de la line de comandos crear la base de datos con `CREATE DATABASE path_db;`

##### 7. Importacion de archivos SQL
Fuera de la terminal mysql dirigete al directorio `sql/` y dentro de el `sudo mysql path_db < nombre del archivo sql`

### Windows

#### Dependencias:

En Windows puedes usar la implementacion XAMPP para inicializar la web de forma rapida

#### Configuracion:

##### 1. Instalacion:
Dirijete a la web oficial de XAMPP e intala su software, una vez instalado aprueba su solicitud de acceso a tu Firewall.

##### 2. Creacion del directorio: 
Descarga el código fuente en tu máquina local usando `git clone` o `git remote add origin`

##### 3. Localizar el directorio:
Una vez con el directorio contruido, copia todo dentro de la carpeta htdocs del directorio de XAMPP

##### 4. Despliege del servidor
Inicia XAMPP Control Panel y inicializa tanto Apache como MySql, aprueba el acceso al Firewall a MySql

##### 5. Entrar en la WEB
Una vez inicado los servicios de XAMPP, en tu navegador ve a `http://localhost/project_path`

##### 6. Creacion de Base de datos
Dentro de `dir/` veras el directorio `sql/`, su contenido has de importarlo en el servicio web `http://localhost/phpmyadmin`

* Crea una base de datos con un nombre reconocible como `path_db`
* Dentro de la base de datos ve a importar y selecciona el contenido dentro del directorio `sql/` 