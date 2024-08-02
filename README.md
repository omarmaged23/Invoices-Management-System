# Invoices-Management-System 
<h3>Getting Started</h3>
1) Create <b>Attachments</b> folder in public/ directory.<br>
2) Setup you <b>.env</b> file, create database, link your db username/password and add mail system information (iam using mailjet in this project after adding a new user to system). <br>
3) Run <b>php artisan migration</b> command.<br>
4) Run <b> php artisan db:seed --class=PermissionTableSeeder </b> then <b> php artisan db:seed --class=CreateAdminUserSeeder </b> in the same order. <br>
5) Your username and password are <b>ducky@gmail.com , test1234</b>.<br>
6) now in terminal run the following -------> <b> npm run dev </b> and <b> php artisan serve </b> then, you are ready to go.

<h3>Features</h3>
1) Ability to add,edit,view,filter and delete invoices.<br>
2) Add invoices attachment files view,download and delete them.<br>
3) Added roles and permission where only the admin can create users at first but feel free to give any user permission to do certain or all tasks on the software.<br>
4) Added validations and 404 default screen.<br>
5) Add products/sections with full ability to add,edit and delete.<br> 
6) Print invoice and export as excel. <br>
7) Added mini notification system for added invoices.<br>
8) Added dashboard at main screen to summarize the invoices details.<br>

