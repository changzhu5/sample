sample
======

PHP MVC framework with tinyCMS application

Install
=======

1. Place framework into you web server.
2. Override database information at {doc_root}/sample/app/tinycms/config.php, make sure MySQL service installed and launched.
3. Visit the site {yourdomain}/sample/

Documentation
=============

1. URL patten:
{site_url}/app_name/controller_name/method_name/param1/param2/....

2. Routing patten:
The request will be dispatched into the file: app/app_name/controller_name_controller.php, and the method : method_name will be executed with parameters : param1 and param2

3. Demo: http://ccui.me





