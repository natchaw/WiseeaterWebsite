# How to import Database to local mamp
*every database are create using user with
username:[root] password:[root]
and wordpress databases login are
username:[admin] password:[root]
## Before you start
1. Install [Mamp](https://www.mamp.info/en/downloads/) on your computer
2. Move folder you download from this github into mamp root folder. It shoud be in Applications ▹ MAMP ▹ htdocs by defult.
*you can't change any folder name or this won't work
## Import Database using phpMyAdmin
1. Open __Mamp__

2. Click start
<img width="648" alt="Screen Shot 2566-01-26 at 17 43 15" src="https://user-images.githubusercontent.com/122365726/214816748-104b50d9-ce02-4d15-852b-e604d2321022.png">

3. Go to __Webstart__ that'll open automaticlly or click __WebStart button__ or go to http://localhost:8888/MAMP/

4. Click tools then go to __phpMyAdmin__

5. Go to __Databases__ Section

6. Create new database
*the database name must be the same name as the .sql file in this project

7. Click __Create__
<img width="1142" alt="Screen Shot 2566-01-26 at 17 52 32" src="https://user-images.githubusercontent.com/122365726/214818411-e2ade1fc-d775-436c-8083-2631b53c61b0.png">

8. Go to databases you created by clicking its name on the left tab.

9. Click __Import__ button on the top bar.

10. Click upload a file then choose [DatabaseName].sql file in the backup folder.
If you have problem with file size follow the instrustion [here](http://localhost:8888/phpMyAdmin5/doc/html/faq.html#faq1-16)

11. If the upload is successful it'll show something like this.
<img width="1159" alt="Screen Shot 2566-01-26 at 18 02 54" src="https://user-images.githubusercontent.com/122365726/214824153-79e83a5c-2efe-446e-9522-9464219f3704.png">

12.Go to __Webstart__ page again. Click on __My Website__ then click on [Project folder name].
13. Login using Username:admin Password:root
14. You should be able to access Website and Wordpress backend now.

## Import Database using Terminal
1. Open a new terminal window
CAREFUL: This will replace all tables in the database you specify!

2. /applications/MAMP/library/bin/mysql -u [USERNAME] -p [DATABASE_NAME] < [PATH_TO_SQL_FILE]
⋅⋅* Hit the Enter Key
⋅⋅* Example:
/applications/MAMP/library/bin/mysql -u root -p wordpress_db < /Applications/MAMP/htdocs/backupDB.sql
Quick Tip: Don’t forget that you can simply drag the file into the terminal window and it will enter the location of the file for you.

3. You should be prompted with the following line:
⋅⋅* Enter password:
⋅⋅*  Type your password, keep in mind that the letters will not appear, but they are there
⋅⋅*  Hit the enter key
4. Check if you database was successfully imported
⋅⋅*  Navigate to phpMyAdmin in a browser
⋅⋅*  http://localhost:8888/MAMP/
