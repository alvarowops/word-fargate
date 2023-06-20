# Wordpress en AWS ECS Cluster
## _Realizado por Alvaro Navarro_

![N|Solid](https://upload.wikimedia.org/wikipedia/commons/thumb/1/1d/AmazonWebservices_Logo.svg/1200px-AmazonWebservices_Logo.svg.png)


En este proyecto habilitaremos un contenedor fargate en cluster de wordpress conectado a un Application Load Balancer donde utilizaremos tambien ECR para tener el repositorio de contenedores y generar las definiciones de tarea.

Servicios y herramientas utilizados
- Docker
- Application Load Balancer
- ECS
- ECR
- VPC
- Security Group
- RDS Aurora MySQL
- Git

## Crear una instancia EC2 para utilizarla como terminal 

## Crear base de datos aurora es igual a mysql solo que de amazon

1. Ir a Amazon RDS

2. Hacer click en crear base de datos

3. Seleccionar Creación estándar

4. en tipo de motor seleccionar Aurora(MySQL Compatible)

5. En plantillas elegir desarrollo y pruebas

6. Identificador del clúster de base de datos debemos escribir un nombre a eleccion 

7. En credenciales elegir el nombre de usuario maestro (como es test dejaremos admin)

8. Contraseña maestra elegir una  debe tener al menos 8 caracteres(esto se utilizara para conectarnos a la bd luego)

9. En Cluster storage configuration elegir Aurora Standard (esto es para los costos bajos)

10. En Configuración de la instancia elegir Clases con ráfagas e elegir la que consideremos apropiada la small es una bd con 2GB de memoria algo para un sitio estandar con no tanto requisito o flujo de usuario

11. En Disponibilidad y durabilidad si deseo crear algo resilente a fallos elegir crear nodo si no es el caso y es solo test elegir No crear una réplica de Aurora

12. En Conectividad elegir Conectarse a un recurso informático de EC2 esto es para establecer una conexion interna con nuestra instancia ec2 con el contenedor y crear los Security Group de conexion de RDS (todas las demas opciones dejar por defecto)

13. En Autenticación de bases de datos elegir Autenticación con contraseña

14. dejar todo lo demas por defecto y hacer click en crear base de datos

## Instalar Git en EC2
```sh
sudo yum install git
```
## Instalar mysql en server EC2 para usar comandos mysql
```sh
sudo yum install mariadb105-server-utils.x86_64
```
## Conectarse a mysql aurora para dar privilegios al usuario en esa bd que creamos
```sh
mysql -h puntodeconexioninstancia -P 3306 -u admin -p
```
# Mostrar bd
```sh
show databases;
```
# Crear base de datos
```sh
create database 'nombre'
```
# Dar privilegios al usuario admin en la bd
```sh
GRANT ALL PRIVILEGES ON nombreebd.* TO admin
```
# confirmar los cambios de privilegios
```sh
FLUSH PRIVILEGES;
```
## Configurar Security Groups

Al conectar la instancia EC2 con RDS se crean 2 SG
 | SG | Descripción |  Reglas |
| ------ | ------ | ------ |
| rds-ec2-1	 | Grupo de seguridad adjunto a la base de datos wordpress para permitir que las instancias EC2 con grupos de seguridad específicos adjuntos se conecten a la base de datos. La modificación podría conducir a la pérdida de conexión. | Entrada Tipo MYSQL Aurora Protocolo TCP Intervalo de puertos 3306 Origen SG tareas |
| ec2-rds-1	| Grupo de seguridad adjunto a las instancias para conectarse de forma segura a la base de datos wordpress. La modificación podría conducir a la pérdida de conexión. | Salida Tipo MYSQL Aurora Protocolo TCP Intervalo de puertos 3306 Origen SG tareas |

Crear 2 SG uno para el balanceador de carga y otro para las tareas "Contenedores"
| SG | Descripción | Reglas |
| ------ | ------ | ------ |
| ALB-SG| Grupo de seguridad para permitir trafico al puerto 80 . | entrada Tipo HTTP TCP Intervalo de puertos 80 Origen Anywhere IPV4 |

 este grupo debe tener una regla de salida para conectarse a el grupo de seguridad de las tareas 
- Tipo personalizado
- TCP 
- Intervalo de puertos 8001 
- Destino Security Group Tareas

| SG | Descripción |  Reglas |
| ------ | ------ | ------ |
| TASK-SG| Grupo de seguridad para permitir trafico al puerto 80 . | entrada Tipo HTTP TCP Intervalo de puertos 80 Origen Anywhere IPV4 |

Este grupo tiene 3 entradas más 
# 1
- Tipo personalizado
- TCP 
- Intervalo de puertos 8001 
- Origen Red interna vpc
# 2
 - Tipo HTTPS
- TCP 
- Intervalo de puertos 443
- Origen Anywhere IPV4
# 3
 - Tipo HTTP
- TCP 
- Intervalo de puertos 80
- Origen Red interna VPC
## Una de salida que apunta al RDS 
- Tipo MYSQLAURORA
- TCP 
- Intervalo de puertos 3306
- Destino Security Group ec2-rds-1	

## Paso a paso de contenedor

Clonar el repositorio en nuestra máquina EC2 e ir a la carpeta del repo

```sh
git clone https://github.com/alvarowops/word-fargate.githttps://github.com/alvarowops/word-fargate.git
cd word-fargate
```
## Editar archivo Dockerfile y wp_config.php con nuestras strings de conexiones con nano
Instrucciones a editar Dockerfile.

| ENV | STRING |
| ------ | ------ |
| WORDPRESS_DB_NAME | Nombre base de datos que creamos |
| WORDPRESS_DB_USER | admin(o usuario que le otorgamos permisos en la base de datos) |
| WORDPRESS_DB_PASSWORD | contraseña maestra mysql o del usuario mysql |
| WORDPRESS_DB_HOST | Punto de conexion instancia RDS |

Instrucciones a editar wp-config.php.

| ENV | STRING |
| ------ | ------ |
| define('DB_NAME', | 'Nombre base de datos que creamos' |
| define('DB_USER', | 'admin(o usuario que le otorgamos permisos en la base de datos)' |
| define('DB_PASSWORD', | 'contraseña maestra mysql o del usuario mysql' |
| define('DB_HOST', | 'Punto de conexion instancia RDS' |

En este sector cambiar por una clave unica
/** Claves únicas de autenticación y sal */
define('AUTH_KEY',         'contraseña');
define('SECURE_AUTH_KEY',  'contraseña');
define('LOGGED_IN_KEY',    'contraseña');
define('NONCE_KEY',        'contraseña');
define('AUTH_SALT',        'contraseña');
define('SECURE_AUTH_SALT', 'contraseña');
define('LOGGED_IN_SALT',   'contraseña');
define('NONCE_SALT',       'contraseña');

# Crear Repositorio ECR
1. Privado
2. Nombre del repositorio
# Instalar aws cli en instancia EC2
## instalar aws tools
```sh
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
sudo ./aws/install
```
## configurar acceso 
nano ~/.aws/credentials
Formato de como debe quedar
[default]
aws_access_key_id=
aws_secret_access_key=
aws_session_token=
# Seleccionamos en el repo ECR y en click en ver comandos de envios seguimos los pasos 
Dejo un ejemplo 
Recupere un token de autenticación y autentique su cliente de Docker en el registro.
Utilice AWS CLI:
```sh
aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin 773425074112.dkr.ecr.us-east-1.amazonaws.com
```
## Cree una imagen de Docker con el siguiente comando
```sh
docker build -t wordpress .
```
Cuando se complete la creación, etiquete la imagen para poder enviarla a este repositorio:
```sh
docker tag wordpress:latest 773425074112.dkr.ecr.us-east-1.amazonaws.com/wordpress:latest
```
Ejecute el siguiente comando para enviar esta imagen al repositorio de AWS recién creado:
```sh
docker push 773425074112.dkr.ecr.us-east-1.amazonaws.com/wordpress:latest
```

## Nos dirigimos a ECS para crear una definición de tarea para luego crear el cluster con esa imagen de nuestro repo

1. Crear una nueva definición de tarea
2. Familia de definición de tareas Especifique un nombre de familia de definición de tarea único.
3. Nombre y la uri que se copia del repo que creamos
4. Mapeos de puertos es el puerto 80 HTTP
5. click en Siguiente
6. Entorno de la aplicación Elegimos AWS FARGATE 
7. Sistema operativo/arquitectura Linux
8. Tamaño de la tarea 2 vCPU y 4 GB de memoria
9. Rol de tarea elegimos un rol con permisos en el caso mio como es labrole
10. Rol de ejecución de tareas labrole
11. Almacenamiento efímero 30 GB
12. Creamos
# Crear Cluster
1. Nombre del clúster
2. Redes elegimos todas
3. Creamos
# Crear Servicio
1. Opciones informáticas Estrategia de proveedor de capacidad
2. Configuración de implementación Servicio
3. Familia elegimos nuestra tarea y la version
4. Nombre del servicio
5. Tipo de servicio Réplica Tareas deseadas 1
6. Redes Subredes todas 
7.  Grupo de seguridad launch-wizard-1, task-sg, rds-ec2-1, ec2-rds-1
8.  Balanceo de carga
9.  Balanceador de carga de aplicaciones 
10.  Crear un nuevo balanceador de carga
11.  Nombre del balanceador de carga
12.  Crear nuevo agente de escucha puerto 80 http
13.  Grupo de destino Crear nuevo grupo de destino elegir el nombre 
14.  Crear servicio

Cuando se inicie el balanceador de carga debemos cambiar su security group por el de ALB-SG que tiene la regla de trafico

# Verificar el correcto funcionamiento debemos esperar a que se termine el cloudformation y copiar el dns de nuestro load balancer para probar el sitio wordpress

ingresamos el dns copiado con el siguiente formato

```sh
alb-word-1233871793.us-east-1.elb.amazonaws.com:80
```
### terminamos viendo la pagina de instalacion y probamos 


## License

Alvaro Navarro Cloud Virtualization


[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)

   [dill]: <https://github.com/joemccann/dillinger>
   [git-repo-url]: <https://github.com/joemccann/dillinger.git>
   [john gruber]: <http://daringfireball.net>
   [df1]: <http://daringfireball.net/projects/markdown/>
   [markdown-it]: <https://github.com/markdown-it/markdown-it>
   [Ace Editor]: <http://ace.ajax.org>
   [node.js]: <http://nodejs.org>
   [Twitter Bootstrap]: <http://twitter.github.com/bootstrap/>
   [jQuery]: <http://jquery.com>
   [@tjholowaychuk]: <http://twitter.com/tjholowaychuk>
   [express]: <http://expressjs.com>
   [AngularJS]: <http://angularjs.org>
   [Gulp]: <http://gulpjs.com>

   [PlDb]: <https://github.com/joemccann/dillinger/tree/master/plugins/dropbox/README.md>
   [PlGh]: <https://github.com/joemccann/dillinger/tree/master/plugins/github/README.md>
   [PlGd]: <https://github.com/joemccann/dillinger/tree/master/plugins/googledrive/README.md>
   [PlOd]: <https://github.com/joemccann/dillinger/tree/master/plugins/onedrive/README.md>
   [PlMe]: <https://github.com/joemccann/dillinger/tree/master/plugins/medium/README.md>
   [PlGa]: <https://github.com/RahulHP/dillinger/blob/master/plugins/googleanalytics/README.md>
