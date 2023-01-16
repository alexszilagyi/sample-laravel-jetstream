FROM php:8.1-fpm-bullseye

# Copy composer.lock and composer.json
COPY src/composer.lock src/composer.json /data/

WORKDIR /data

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    locales \
    libzip-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# install and enable xdebug
RUN pecl install xdebug-3.2.0RC1 \
	&& docker-php-ext-enable xdebug

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --enable-gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


COPY ./infra/docker/php/php.development.ini /usr/local/etc/php/php.ini
COPY ./infra/docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY ./infra/docker/php/php.deploy.ini /usr/local/etc/php/php.ini

# Copy existing application directory contents
COPY src/ /data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
