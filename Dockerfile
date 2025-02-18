FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && \
    apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    netcat-openbsd \
    libgd-dev \
    libicu-dev && \
    rm -rf /var/lib/apt/lists/* 

# Generate locales (if needed)
RUN locale-gen en_US.UTF-8

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo_mysql mbstring zip exif pcntl mysqli gd intl

# Install additional PHP extensions, if needed
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . .

# Optionally, copy custom php.ini
# COPY ./path/to/your/php.ini /usr/local/etc/php/

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
