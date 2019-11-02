FROM node:11

# Install dependencies
RUN apt-get update && apt-get install -y \
    nodejs \
    bash
# Copy composer.lock and composer.json
# COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

RUN npm install
RUN npm install -g laravel-echo-server

# Expose port 6001 and start php-fpm server
EXPOSE 6001 

ENTRYPOINT ["laravel-echo-server", "start"]