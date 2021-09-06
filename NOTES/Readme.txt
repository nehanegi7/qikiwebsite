1) Create Database and put details inside config/database.php 
  in line no. 51,52,53
  
  Database sql file inside NOTES/DB folder


2) Change Email setting
   inside config/mail.php


3) Change SMS gateway details
  from admin menu "settings" 
  enter full SMS api url with [message] and [mobile] in place of these
 ex: 
  http://domain/api/sendhttp.php?authkey=12345&sender=ABCDEF&message=[message]&mobiles=[mobile]


4) Some other settings (like website name, url etc)
config/app.php


5) HTML files inside
resources/views/

Header , footer file except vendor pages (It's common file in Laravel)
resources/views/layout/master.blade.php


Header , footer of vendor pages (It's common file in Laravel)
resources/views/layout/master2.blade.php

6) Major CSS files  for theme layout or color combination change 
public/css/custom.css
public/css/responsive.css
public/css/partner.css

Admin Login
----------------
http://domain_name/admin

username : admin
password : admin

Admin Setting
-------------
from admin menu -> Settings
