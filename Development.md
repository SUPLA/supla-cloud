# SUPLA Cloud - Development

This description shows how to install launch the SUPLA Cloud in
developer environment. It does not apply to production instances.

Please note, that this only installs SUPLA Cloud, without [supla-server](https://github.com/SUPLA/supla-core/tree/master/supla-server).
Managing the devices from SUPLA Cloud only is impossible. Development instance
uses mock of supla-server by default.

## The first launch

1. Install a web server with PHP Support. We recommend [Apache](http://httpd.apache.org/docs/2.4/).
   On Winows machines, you can install [XAMPP](https://www.apachefriends.org/pl/index.html).
2. Install [MySQL database server](https://dev.mysql.com/downloads/installer/) (XAMPP already contains it).
3. Install [Node JS LTS](https://nodejs.org/en/). Version 14.x with NPM 7.x is proved to be working.
4. Install [Git](https://git-scm.com/), then fork and clone the repository with `git clone` command.
   It is good idea to clone in into a directory from which Apache can server the application
   (for example, `/var/www/supla-cloud` or `C:\xampp\htdocs\supla-cloud` or wherever
   you have your `DocumentRoot` configured).
5. Go to the cloned directory.
6. Install [Composer](https://getcomposer.org/download/).
7. Install project's PHP dependencies with `composer install`. During the installation,
   copmoser will ask you for a bunch of things. For the development environment, the most important are:
    1. `database_*` - configuration of your database connection
    1. `supla_server` - set it to `supla.local` 
    1. `recaptcha_enabled` - set it to `false`
    
   You can leave the defaults for all of the other settings. If you made a mistake,
   you can fix it by editing the `app/config/parameters.yml` file.
   
8. Install the frontend dependencies and build it for the first time with
    ```
    composer run-script webpack
    ```
9. Initialize the database and load sample data with
   ```
   php bin/console cache:clear --no-warmup -e dev
   php bin/console supla:dev:dropAndLoadFixtures -e dev
   ```
10. Configure virtual host so you can easily access the application.
    * on Unix, add add a `/etc/apache2/sites-available/supla-dev` with the following contents
      (pay attention to the SUPLA Cloud path)
      ```
      <VirtualHost *:80>
          SetEnv APPLICATION_ENV dev
          DocumentRoot /var/www/supla-cloud/web
          ServerName supla.local
      </VirtualHost>
      ```
      then, enable the configuration with `a2ensite supla-dev` and restart the Apache `service apache2 restart`
    * on Windows, edit the `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and add there a vhost configuration
      (pay attention to the SUPLA Cloud path)
      ```
      <VirtualHost *:80>
          SetEnv APPLICATION_ENV dev
          DocumentRoot "C:/xampp/htdocs/supla-cloud/web"
          ServerName supla.local
      </VirtualHost>
      ```
      Restart Apache with XAMPP Control Panel.
11. Add a `127.0.0.1 supla.local` line in `/etc/hosts` on Unix or `C:\Windows\System32\drivers\etc\hosts` on Windows
    so the `http://supla.local` address points at your localhost.


## Development

1. Run webpack dev server with
    ```
    cd src/frontend && npm run serve
    ```
2. Enter http://localhost:8080 to see the application.
3. Login with `user@supla.org` and `pass` (this is a sample account).

## Contributing

1. See [what needs to be done](https://github.com/SUPLA/supla-cloud/issues) or create an issue you would like to solve.
2. Tell us you are willing to help with the issue in comments.
3. Fix the issue on a separate branch on your fork.
4. Make a Pull Request with your work.
5. Become a [contributor](https://github.com/SUPLA/supla-cloud/graphs/contributors) of SUPLA project!
