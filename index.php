<?php
use PhpDevCommunity\DotEnv;
use Blog\Twig\AssetExtension;
use Blog\Slim\TwigMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Blog\PostMapper;
use DI\ContainerBuilder;
use Blog\Database;

require __DIR__ . '/vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->addDefinitions('config/di.php');

$absolutePathToEnvFile = __DIR__ . '/.env';

(new DotEnv($absolutePathToEnvFile))->load();

$container = $builder->build();

AppFactory::setContainer($container);

$app = AppFactory::create();
$view = $container->get(Environment::class);
$app->add(new TwigMiddleware($view));

$connection = $container->get(Database::class)->getConnection();

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

$app->get('/blog[/{page}]',
    function (Request $request, Response $response, $args)
     use ($view, $connection) {
        $postMapper = new PostMapper($connection);

        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $limit = 2;

        $posts = $postMapper->getList($page, $limit, 'DESC');

        $totalCount = $postMapper->getTotalCount();
        $body = $view->render('blog.html', [
            'posts' => $posts,
            'pagination' => [
                'current' => $page,
                'paging' => ceil($totalCount / $limit),
            ],
        ]);
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
