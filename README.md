ToDoList
========

Project basis #8: Improve an existing project

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1

## Installation

1. Open a Terminal on the server root or localhost (git bash on Windows).
2. Run the following command, replacing "FolderName" with the name you want to give to the Project :
    ```sh
    git clone https://github.com/Nerym492/TodoList.git FolderName
    ```
3. Install Symfony CLI (https://symfony.com/download), composer (https://getcomposer.org/download/) and
    nodeJs (https://nodejs.org/en)
4. Install the project's back-end dependencies with Composer and optimize autoloader :
    ```sh
    composer install -a --classmap-authoritative
    ```
    ```sh
    composer dump-autoload -a --classmap-authoritative
    ```
5. Install the project's front-end dependencies with npm :
    ```sh
    npm install
    ```
6. Create assets build with Webpack Encore :
    ```sh
    npx encore prod
    ```
7. Launch wamp, mamp or lamp depending on your operating system.
8. Create the database :
    ```sh
    php bin/console doctrine:database:create
    ```
9. Create database tables by applying the migrations :
   ```sh
   php bin/console doctrine:migrations:migrate
   ```
10. Add a base dataset by loading the fixtures :
     ```sh
     php bin/console doctrine:fixtures:load
     ```
11. To create the test database, run the 3 previous commands and add "--env=test" at the end.
12. Start the Symfony Local Web Server :
    ```sh
    symfony server:start
    ```
13. The application is now ready to use !

