# PHP2EXE
A real working HTML JS CSS PHP etc.. and MYSQL app packer!

![image](https://github.com/hitsfm/PHP2EXE/blob/main/screenshot.png)

*Advanced users take a look at Syntax options below to add/activate PHP extensions and a simple MYSQL database to your project if needed. Default setting will not auto import SQL.


Steps:
-Simply add your website/app content into the "site" directory. Run compile.bat

-Wait for complete prompt.

-Extract APP.zip file inside /output/output directory within this directory. No subdirectory. It must be extracted here. (Electron was too big for github so I had to split up and zip.)

-Find finshed project in "output" folder.

-Open PHP_MyAPP.exe (Feel free to rename to something more suitable to your needs)

-Copy contents of "output" folder. Your standalone PHP2EXE Windows app is ready. Thats all!


More info:


This program is based on some old scripts that where no longer working properly. For one it depended on very old "called" Internet Explorer. Or optionaly a default system browser. One or the other was not good for my needs!
I wanted a full "Standalone" app. Built from my simple PHP site/app. If I wanted a browser I could just run "AppServ" on a local system and kill the server when i'm done.

There are many html2exe solutions out there. but I have not found anything that works well with PHP! There are a few options like "PHPBOX" But these are more or less portable versions of Apache environment isolated and wrapped up in a nice app for development. Not really that practical for app distribution.(Click and Play) And the support of a basic MYSQL database. Wow thats a whole other issue!But have no fear as this compiler supports basic MYSQL! You just need to provide sql file.

I have wrapped it up around an Electron app under the hood. The Electron project makes it possible to run the compiled results in a nice clean APP window and takes care of the ugly internet explorer issue I had with some of the original files.

**I had issues with this method with many PHP versions. Not even sure if this was going to work at some point! Specificaly. PHP Sockets would simply not load via this method. I tried but could not figure it out, So because of this issue. I am using PHP 4.3. As This was able to work. I'm sorry. I just needed basic functions like PHP readfile. On the bright side. This opens up tons of scripts that may assist in building your "App" that may no longer work properly on newer PHP versions or would simply be none secure in a public web. No worries as this compiled EXE app can only run(take requests) on the localhost ignoring everything else as configured in the apache file for this project as an extra layer of caution.

The compiled EXE is "Smart" it detects closes and shutsdown all it's background tasks and servers.

adanved users edit compile.bat
Syntax : job [-options] siteDirectory destDirectory

- Options -

-my:mysqlData[,mysqlData2...] Mysql is added with specified datas,

-ext:extension.dll[,extension2.dll] Specified extensions is added

(extentions must be exists in PHP2EXE extension directory),


Not running compile.bat beforehand and running the file /output/PHP_MyAPP.exe will result in the display of PHPinfo inside that window instead of your site. Make sure to overwrite the index.php file within the /site/ dir. With your own before compiling!. Along with your complete web project files and subdirs.
