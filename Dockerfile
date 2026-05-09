FROM php:8.2-apache

# Cài đặt extension mysqli cho PHP
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy toàn bộ code vào thư mục web root của Apache
COPY . /var/www/html/

# Phân quyền cho thư mục uploads để lưu ảnh
RUN chown -R www-data:www-data /var/www/html/uploads && chmod -R 777 /var/www/html/uploads

# Bật Apache Rewrite Module (nếu cần dùng .htaccess)
RUN a2enmod rewrite

# Mở cổng 80
EXPOSE 80
