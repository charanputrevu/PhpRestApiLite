# PhpRestApiLite
A lite weight PHP framework to build REST APIs.

You can directly download the repo and use it in your project to create REST APIs. It is also free to modify this as per your requirement.

## Getting Started
Project is based on composer. Please run 
`composer install`
and
`composer dump-autoload -o` before starting

The code initialized code based on the route received and configured. The path should point to the folder PhpRestApiLite. Path after that is treated controller, method in the controller and params respectively. For example if 
your rest endpoint is `/PhpRestApiLite/test/helloWorld`. test is treated as the controller name and `helloWorld` as method name. This will execute helloWorld method in TestController class.

Paths after that will be treated as route params and will be sent to respective methods as arguments. For example, if your path is `/PhpRestApiLite/test/helloWorld/newWorld/happWorld` - `newWorld` and `happyWorld` are sent as argumnents to method helloWorld as `helloWorld($params1, $params2)`. Please note that controller methods are user defined and you should receive the route params in the method.

You can send both query params and route params. query params will be sanitized for html entities and stored in $get class variable and route params will be sent as arguments. Routes should be configured in App/Helpers/Routes.json

Controllers should be inside the App/Controllers folder and should extend the Controller class.

SQL connection parameters and other parameters can be configured in settings.ini file. You wish to put the code in a different folder, configure the folder path upto index.php in settings.ini
JWT is available in the code and you can use JWT tokens for authentication. Turn on JWT in settings.ini file.
