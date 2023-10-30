# Import WordPress to local Mamp
Every database is created with

username:[root] password:[root]

and WordPress databases login are

username:[admin] password:[root]

## Before you start
1. Install [Mamp](https://www.mamp.info/en/downloads/) on your computer
2. Move the folder you clone from this GitHub into the Mamp root folder. If you're on Mac, it should be in Applications ▹ MAMP ▹ htdocs by default. Or you can locate the Mamp root folder under the Mamp setting.

> [!WARNING]
>You can't change any folder name or this won't work

## 1. Import Database

There two ways you can do this.

### Import Database using phpMyAdmin

1. Locate this project database which is the .sql file in this GitHub.

2. Open __Mamp__

3. Click start
<img width="648" alt="Screen Shot 2566-01-26 at 17 43 15" src="https://user-images.githubusercontent.com/122365726/214816748-104b50d9-ce02-4d15-852b-e604d2321022.png">

4. Go to http://localhost:8888/MAMP/?language=English or click __WebStart button__

5. Click tools then go to __phpMyAdmin__

6. Go to __Databases__ tab

7. Create a new database by clicking on create

   * The database name must be the same as the .sql file in this project
<img width="1142" alt="Screen Shot 2566-01-26 at 17 52 32" src="https://user-images.githubusercontent.com/122365726/214818411-e2ade1fc-d775-436c-8083-2631b53c61b0.png">

8. Go to the databases you created by clicking its name on the left side menu.

9. Click __Import__ button on the top bar.

10. Click upload a file then locate the [DatabaseName].sql file.
If you have a problem with file size follow the instructions [here](http://localhost:8888/phpMyAdmin5/doc/html/faq.html#faq1-16)

11. If the upload is successful it'll show something like this.
<img width="1159" alt="Screen Shot 2566-01-26 at 18 02 54" src="https://user-images.githubusercontent.com/122365726/214824153-79e83a5c-2efe-446e-9522-9464219f3704.png">

### Import Database using Terminal
1. Open a new terminal window
CAREFUL: This will replace all tables in the database you specify!

2. /applications/MAMP/library/bin/mysql -u [USERNAME] -p [DATABASE_NAME] < [PATH_TO_SQL_FILE]
⋅⋅* Hit the Enter Key
⋅⋅* Example:
/applications/MAMP/library/bin/mysql -u root -p wordpress_db < /Applications/MAMP/htdocs/backupDB.sql

> [!NOTE]
> Don’t forget that you can simply drag the file into the terminal window and it will enter the location of the file for you.

3. You should be prompted with the following line:
- Enter password:
- Type your password, keep in mind that the letters will not appear, but they are there
- Hit the enter key

4. Check if your database was successfully imported
- Navigate to phpMyAdmin in a browser
- http://localhost:8888/MAMP/

## 2.Accessing the WordPress dashboard

1. Go to __Webstart__ page.

2. Click on __My Website__

3. Click on [Project folder name].

4. Login using Username: admin Password: root

> [!WARNING]
> Some images might have some error loading after being imported please compare them to the [Live Site](https://wiseeater.netlify.app) and make changes to the ones that aren't loading correctly
