# News Aggregator API with Docker 

This is a Laravel 11 project running inside a Docker environment with MySQL, Nginx, and Redis.

---

## Prerequisites

Ensure you have the following installed:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Git](https://git-scm.com/)

---

## 📂 Project Setup

### Clone the Repository

    git clone git@github.com:anumohanonline97/NewsAggregatorAPI.git
    cd NewsAggregatorAPI

### Build and Start Docker Containers

    #Run the following command:

    docker-compose up --build 

#This will:

#Build the application container
#Start the database, PHP, and Nginx services
#Run the Laravel application in a container

###  Configure Environment Variables

#Update .env with correct database values.

### Install composer so that we can include all packages in our project using composer.

    composer install
    composer dump-autoload --optimize

### Run Migrations and Seeders

### Once the containers are running, run:
    #Run laravel
    docker exec -it news_app php artisan serve

    #Rollback and migrate all migrations and seeders
    docker exec -it news_app php artisan migrate:fresh --seed

### Access the Application

    News Aggregator API App → http://localhost:8082
    phpMyAdmin → http://localhost:8081



###  I have implemented Laravel scheduled commands to regularly fetch and update articles from the chosen news APIs

### After running the artisan command please run the queue starting commands.

       php artisan queue:restart # Run if needed to restart the queue.
       php artisan queue:work

### After running this please hit the API below to  fetch and store articles in the local database.

     http://localhost:8082/api/scheduler


### Stopping and Restarting Docker

    #To stop the containers:

    docker-compose down

    #To restart the containers:

    docker-compose up 

###

### I have done the project at first using the xampp server because while running docker, my system takes more execution time to run the APIs.
### But after running the APIs I installed docker and all the configurations are done and all functionalities are working fine.

### If any issues are facing like 403 forbidden or 504 gateway time out while running docker in your system, make sure your ngnix.conf file is pointing to the public folder of the project folder.

### I have chosen 3 Data Sources among the sources you have provided.It is listed below:
    # The Guardian
    # New York Times
    # NewsAPI.org

📌 API Documentation

### Swagger UI
        #The API is documented using Swagger (OpenAPI). You can view the API documentation in your browser.

### 📖 Access Swagger Docs:
                http://localhost:8082/api/documentation (for local development)

### Generating Swagger Documentation
        # If you have integrated Laravel OpenAPI (Swagger), you may need to generate or update the documentation.

### Run the following command to enter into the news_app container:

        docker exec -it news_app php artisan l5-swagger:generate
        

### After running this command, the documentation should be available at /api/documentation.

### Using Postman
        #If you prefer Postman, you can import the API collection using the following steps:

### Open Postman.
                Click Import > Select Link.
### Paste:
                http://localhost:8082/api/documentation (for local development)

### Then 
                Click Continue > Import.













