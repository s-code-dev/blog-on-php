<?php
use Blog\Twig\AssetExtension;
use Blog\Slim\TwigMiddleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Blog\PostMapper;

require __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$view = new Environment($loader);


$config = include 'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];

$password = $config['password'];
try{
    $connection = new PDO($dsn, $username, $password);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);



}catch(PDOExeption $exception){
    echo 'Database error: ' . $exception->getMessage();
    die();
}





$app = AppFactory::create();

$app->add(new TwigMiddleware($view));


$app->get('/', function (Request $request, Response $response, $args) use ($view, $connection)  {
    $latestPost = new \Blog\LatestPosts($connection);
    $posts = $latestPost->get(3);
    $body = $view->render('index.html', ['posts' => $posts]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/about', function (Request $request, Response $response, $args) use ($view) {
    $body = $view->render('about.html', ['name'=> 'Max']);

    $response->getBody()->write( $body);
    return $response;
});

$app->get('/blog[/{page}]', function (Request $request, Response $response, $args) use ($view, $connection) {
    $latestPost = new PostMapper($connection);

    $page = isset($args['page']) ? (int) $args['page'] : 1;
    $limit = 2;
    $posts = $latestPost->getList($page, $limit, 'DESC');
    $body = $view->render('blog.html', ['posts' => $posts]);
    $response->getBody()->write($body);
    return $response;
});


$app->get('/{url_key}', function (Request $request, Response $response, $args) use ($view, $connection) {
    $postMapper = new PostMapper($connection);

    $post =  $postMapper->getByUrlKey((string) $args['url_key']);

    if(empty($post)){

        $body = $view->render('not-found.html');

    }else{

        $body = $view->render('post.html', ['post' => $post]);

    }
    

    $response->getBody()->write($body);
    return $response;
});



$app->run();

?>
