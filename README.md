# Introduction:

This repository has **API Gateway** only.  It acts as a front microservice and consist of three main modules:

 1. Authentication & Authorization
 2. Users
 3. Request Router

The API Gateway receives the request and forward to the corresponding service.

The API Gateway issues the token as the user logged-in and validate it  with the subsequent requests. It also check the authorization. If a user is requesting to access some resources, then API Gateway first ensure that the user has privileges to access the requested resources.

The user module is also a part of this microservices. The reason for this is to avoid more HTTP API calls and decrease network latency. 
 
The first two modules names make sense and understandable.  But the Request Router might be a new term for some viewers especially for junior or mid-level developers. 

Request Router module forward the received request to the corresponding service. When it receives a request, the API gateway consults a routing map that specifies which service to route the request to. All the configuration of this module is placed in `request_map.php` or `request_router.php` 

The another two benefits of  Request Router are **simplicity** and **security**. 

 - **Simplicity**:  It hides all the backend complexity of the project from the frontend developers and make their lives very easier. Examples are:
	 - How many microservices we have?
	 - What are their IPs etc.
	 - How they ensure security?
	 - etc. 
	 
They don't need to configure anything extra in the frontend codebase. They should only know the IP or Domain of the API Gateway and send their request to it.

 - **Security**: API Gateway help us to secure our internal infrastructure. Only the API Gateway can access the internal microservices servers. They are not accessible publicly. 

# Setup Guide

**Prerequisite:** These software must be installed on your machine to setup this microservice
 1. LAMP
 2. Git
 3. Composer

> **Pro Tip!** Making PHP globally available will make your life much easier.

**Installation Steps:**

 - **Download Code:** Clone the code
 -  **Setup Code:** Run these commands in the same order inside the project directory
	 - `composer install`
	 - `php artisan optimize`
	 - `php artisan scribe:generate`
	 - `php -r "file_exists('.env') || copy('.env.example', '.env');"`
	 - `php artisan key:generate`
	 - `chmod -R 777 storage bootstrap/cache`
	 
 - **Basic Configuration**
	 - Edit the .env file which is located in the root of the project
	 - Update database credentials
	 - Update mailing server credentials
 
 > **Pro Tip!** Also make sure you run, `php artisan optimize` command again
 > 
 - **Setup Database**
	 - Run `php artisan migrate:refresh --seed` to create database tables and seed them
	 
 - **Run Development Server**
	 - Run `php artisan serve --port=3531` to start the development server.

> **Pro Tip!** If you want to use different port, then make sure you also use the same port for scribe([API documentation package](https://github.com/knuckleswtf/scribe)).
>

 - **Access APIs Documentation**
	 - Hit [http://127.0.0.1:3541/docs](http://127.0.0.1:3541/docs) in your browser to access database documentation.


# Author
*Name: Sajid Javed
Email: work@sajidjaved.com
Last Update: 03 July 2021*

This document is generated through https://stackedit.io/
