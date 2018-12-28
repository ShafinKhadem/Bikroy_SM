# Bikroy_SM
Our level 2 term 2 project of database sessional

# How to run
Make sure that u can run php. Then from xampp, in the row starting with 'apache' click on config->browse php->php.ini->find (ctrl+f) extension_dir without ; in front (if all of them semicolon in front, delete semicolon from one of them and make: extension_dir="C:\xampp\php\ext" assuming that xampp is installed in C:\xampp. Also find pdo_pgsql and delete the semicolon from front. <br>
Create a blank postgresql database and from command prompt run psql -U postgres dbname < backup.sql to restore database. <br>
Later to backup database run: pg_dump -U postgres dbname > backup.sql. <br>
Modify helper/database.ini of this folder according to ur database connection. <br>
Then run the index.php file.