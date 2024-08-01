# Invoices-Management-System 
<h3>Getting Started :-</h3>
1) Create <b>Attachments</b> folder in public/ directory.<br>
2) Setup you <b>.env</b> file, create database, link your db username/password and add mail system information (iam using mailjet in this project after adding a new user to system). <br>
3) Run <b>php artisan migration</b> command.<br>
4) Run <b> php artisan db:seed --class=PermissionTableSeeder </b> then <b> php artisan db:seed --class=CreateAdminUserSeeder </b> in the same order. <br>
5) Your username and password are ducky@gmail.com , test1234.
6) now in terminal run the following -------> <b> npm run dev </b> and <b> php artisan serve </b> then, you are ready to go.

