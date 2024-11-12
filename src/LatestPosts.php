<?php
declare(strict_types=1);

namespace Blog;


class LatestPosts 
{
  private $connection;

  public function __construct($connection){

    $this->connection = $connection;

  }

  public function get(string $limit): ?array
  {

    $statement = $this->connection->prepare('SELECT * FROM post ORDER BY published_date DESC LIMIT ' . $limit );

    $statement->execute();
    return $statement->fetchAll();

  }

}



?>