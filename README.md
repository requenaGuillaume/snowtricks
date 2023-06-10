# snowtricks

## Requirements

Database & database interface (like phpmyadmin, workbench, adminer ...)  
php 8^  
symfony 6.2^

## Download the projet

Terminal command : "git clone https://github.com/requenaGuillaume/snowtricks.git"  
or  
Go to https://github.com/requenaGuillaume/snowtricks and choose another way to get the project (download zip folder etc..)

## Run server

Use the "symfony serve" command in terminal (pointing to the project)

## Install dependecies

Run the terminal command : "composer install"

## Create database

Create database using terminal command : "symfony console d:d:c"  
Run the migrations using terminal command : "symfony console d:m:m"

## Fixtures

Run the fixtures using terminal command : "symfony console d:f:l"

## Create an account

Register as a classic user using the form in 'https://127.0.0.1:8000/register'  
You go to this page using the url directly or with the "Sign up" button in the navbar.

### You're done

Project must be ready now.
