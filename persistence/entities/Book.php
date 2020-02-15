<?php
// Сущность "Книга"
class Book {
  // уникальный id - будет генерироваться БД при вставке строки
  protected $id;
  // временная метка последнего обновления
  protected $updatedAt;
  // заголовок
  protected $title;
  // автор
  protected $author;
  // жанр (опционально)
  protected $genre;
  // описание
  protected $description;
  // идентификатор страны предложения
  protected $countryId;
  // идентификатор города предложения
  protected $cityId;
  // идентификатор типа предложения
  protected $typeId;
  // изображение Base64 (опционально)
  protected $image;
  // активность (предложение доступно для запроса или по какой-либо причине скрыто от поиска)
  protected $active;
  // Конструктор
  function __construct(
    $title
    , $author
    , $genre
    , $description
    , $countryId
    , $cityId
    , $typeId
    , $image
    , $active
    , $id = 0
    , $updatedAt = ''
    ) {
    $this->id = $id;
    $this->updatedAt = $updatedAt;
    $this->title = $title;
    $this->author = $author;
    $this->genre = $genre;
    $this->description = $description;
    $this->countryId = $countryId;
    $this->cityId = $cityId;
    $this->typeId = $typeId;
    $this->image = $image;
    $this->active = $active;
  }
  // вставка строки о книге в БД
  function create () {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос добавления строки в таблицу "Книги"
      $ps = $pdo->prepare("INSERT INTO `Books` (`title`, `author`, `genre`, `description`, `countryId`, `cityId`, `typeId`, `image`, `active`) VALUES (:title, :author, :genre, :description, :countryId, :cityId, :typeId, :image, :active)");
      // Превращаем объект в массив
      $ar = get_object_vars($this);
      // Удаляем из него первые два элемента - id и created_at, потому что их создаст СУБД
      array_shift($ar);
      array_shift($ar);
      // Выполняем запрос к БД для добавления записи
      $ps->execute($ar);
    } catch (PDOException $e) {
      // Если произошла ошибка - возвращаем ее текст
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }
    }
  }
  // Редактирование строки о книге по ее идентификатору
  function edit() {
    try {
      // Удаляем старую версию строки из БД
      Book::delete($this->id);
      // Вставляем новую версию строки в БД
      $this->create();
    } catch (PDOException $e) {
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }
    }
  }
  // Удаление строки книге из БД по идентификатору
  function delete ($id) {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос удаления строки из таблицы "Книги"
      $pdo->exec("DELETE FROM `Books` WHERE `id` = $id");
    } catch (PDOException $e) {
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }
    }
  }
  // Получение списка всех книг из БД
  static function filter($args) {
    // Переменная для подготовленного запроса
    $ps = null;
    // Переменная для результата запроса
    $books = null;
    try {
        // Получаем контекст для работы с БД
        $pdo = getDbContext();
        // Массив для условий запроса
        $whereClouse = [];
        // Сбор условий запроса в массив
        if (isset($args['lastId'])) {
          $whereClouse[] = "`b`.`id` < '{$args['lastId']}'";
        }
        if (isset($args['active'])) {
          $whereClouse[] = "`b`.`active` = '{$args['active']}'";
        }
        if (isset($args['title'])) {
          $whereClouse[] = "locate('{$args['title']}', `b`.`title`) > 0";
        }
        if (isset($args['author'])) {
          $whereClouse[] = "locate('{$args['author']}', `b`.`author`) > 0";
        }
        if (isset($args['countryId'])) {
          $whereClouse[] = "`b`.`countryId` = '{$args['countryId']}'";
        }
        if (isset($args['cityId'])) {
          $whereClouse[] = "`b`.`cityId` = '{$args['cityId']}'";
        }
        if (isset($args['typeId'])) {
          $whereClouse[] = "`b`.`typeId` = '{$args['typeId']}'";
        }
        $whereClouseString = 'WHERE ';
        $expressionCount = 0;
        foreach ($whereClouse as $expression) {
          if ($expressionCount++ == 0) {
            $whereClouseString .=  $expression;
          } else {
            $whereClouseString .= ' AND ' . $expression;
          }
        }
        // Готовим sql-запрос чтения всех строк данных из таблицы "Книги"
        // с подключением связанных таблиц "Страна", "Город", "Тип",
        // сортируем по идентификаторам,
        // пытаемся получить только три значения из строк, идентификаторы которых меньше заданного
        $ps = $pdo->prepare("SELECT `b`.`id`, `b`.`updatedAt`, `b`.`title`, `b`.`author`, `b`.`genre`, `b`.`description`, `co`.`name`, `ci`.`name`, `ty`.`name`, `b`.`image`, `b`.`active` FROM `Books` AS `b` INNER JOIN `Country` AS `co` ON (`b`.`countryId` = `co`.`id`) INNER JOIN `City` AS `ci` ON (`b`.`cityId` = `ci`.`id`) INNER JOIN `Type` AS `ty` ON (`b`.`typeId` = `ty`.`id`) {$whereClouseString} ORDER BY `b`.`id` DESC LIMIT 3");
        // Выполняем
        $ps->execute();
        //Сохраняем полученные данные в ассоциативный массив
        $books = $ps->fetchAll();
        return $books;
    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
  }
}