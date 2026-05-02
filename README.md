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