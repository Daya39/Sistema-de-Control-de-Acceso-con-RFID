# Dockerfile para IOT-door-lock (PHP + Apache + MySQL)
# Basado en la plantilla de ESP32, adaptado para PHP web backend

# Imagen base con Apache y PHP
FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copiar el código fuente PHP al contenedor
COPY ./src/php/ /var/www/html/

# Dar permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80

# Configuración de Apache (opcional, para .htaccess)
RUN a2enmod rewrite

# Comando por defecto
CMD ["apache2-foreground"]
