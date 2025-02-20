# Use an official PHP image as the base image
FROM php:8.2-cli

# Install dependencies for Composer and other utilities
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the PHP project files into the container
COPY . /var/www/html

# Install the PHP dependencies using Composer
RUN composer install

# Expose port 80 for the web server (optional, for serving the app)
EXPOSE 80

# Start the PHP built-in server (optional, for testing purposes)
CMD ["php", "-S", "0.0.0.0:80", "-t", "src"]