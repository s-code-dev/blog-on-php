<?php
declare(strict_types=1);

namespace Blog;


class PostMapper 
{

  private $connection;

  public function __construct($connection){

    $this->connection = $connection;

  }

public function getBaseUrl(): string
{
    $params = $this->request->getServerParams();
    $scheme = $params['REQUEST_SCHEME'] ?? 'http';
    return $scheme . '://' . $params['HTTP_HOST'] . '/';
}

  public function getList(int $page = 1, int $limit = 2, string $direction = 'ASC'): ?array
  {


    if(!in_array($direction, ['DESC', 'ASC'])){
      THROW NEW Exception('The direction is not supported');
    }

    $start = ($page-1) * $limit;
    $statement = $this->connection->prepare("SELECT * FROM post ORDER BY published_date " . $direction . " LIMIT " . $start . "," . $limit);
    $statement->execute();
    return $statement->fetchAll();



  }
public function getTotalCount(): int
{
    $statement = $this->connection->prepare(
        'SELECT count(post_id) as total FROM post'
    );

    $statement->execute();

    return (int) ($statement->fetchColumn() ?? 0);
}


   /**
     * @param string $urlKey
     * @return array|null
     */
    public function getByUrlKey(string $urlKey): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM post WHERE url_key = :url_key');
        $statement->execute([
            'url_key' => $urlKey
        ]);

        $result = $statement->fetchAll();

        return array_shift($result);
    }

}



?>