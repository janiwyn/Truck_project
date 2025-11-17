# ARAKNERD LIB

This is a framework that can help you better organise your php project.

Installation Guide
 NOTE: This is a light framework that doesn't require dependancies.
 1. To install, copy the APPName folder into your server directory
 2. Configure the virtual hosts file by adding the following at the bottom of the .conf file
    <Directory /var/www/html/>
        AllowOverride All
    </Directory>
3. Create a mysql database
4. Configure the .env file with your database coonnection details
5. Test to see if your connection is successful by running the browser http://localhost/AppName/db.run If successful, two tables will be added into the database i.e. tbl_role and tbl_user.
6. Enable mod_write sudo a2enmod rewrite

For any enquiries, email me at andrizar2@gmail.com


