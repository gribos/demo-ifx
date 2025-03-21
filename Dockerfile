FROM php:8.2-cli

RUN apt-get update && apt-get install -y unzip git curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
EXPOSE 8000

# Uruchamianie serwera PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "./"]