<?php
// Сущность "Язык"
class Language {
  protected static $TABLE_NAME = '`Languages`'; 
  /* // уникальный id - будет генерироваться БД при вставке строки
  protected $id;
  // название языка для отображения пользователям
  protected $name;
  // ключ локали
  protected $localeKey;
  // ключ флага
  protected $flagKey;
  // приоритет отображения в списке (1 - самый высокий)
  protected $priority;
  // Конструктор
  function __construct(
    $name
    , $priority
    , $localeKey
    , $flagKey
    , $id = 0
    ) {
    $this->id = $id;
    $this->name = $name;
    $this->localeKey = $localeKey;
    $this->flagKey = $flagKey;
    $this->priority = $priority;
  } */
  // TODO применить, когда окружение для php8 будет готово
  function __construct(
    protected string $name
    , protected int $priority
    , protected string $localeKey
    , protected string $flagKey
    , protected int $id = 0
    ) {}
  // вставка строки о языке в БД
  function create () {
    try {
      // Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос добавления строки в таблицу "Языки"
      $ps = $pdo->prepare("INSERT INTO " .  self::$TABLE_NAME . " (`name`, `localeKey`, `flagKey`, `priority`) VALUES (:name, :localeKey, :flagKey, :priotity)");
      // Превращаем объект в массив
      $ar = get_object_vars($this);
      // Удаляем из него первый элемент - id потому что его создаст СУБД
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
  // Редактирование строки по идентификатору
  function edit() {
    try {
      // Удаляем старую версию строки из БД
      Type::delete($this->id);
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
  // Удаление строки из БД по идентификатору
  function delete ($id) {
    try {
      //Получаем контекст для работы с БД
      $pdo = getDbContext();
      // Готовим sql-запрос удаления строки из таблицы "Тип"
      $pdo->exec("DELETE FROM" . self::$TABLE_NAME . " WHERE `id` = $id");
    } catch (PDOException $e) {
      $err = $e->getMessage();
      if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {
        return 1062;
      } else {
        return $e->getMessage();
      }
    }
  }
  // Получение списка всех языков из БД
  static function getAll () {
    // Переменная для подготовленного запроса
    $ps = null;
    // Переменная для результата запроса
    $types = null;
    try {
        // Получаем контекст для работы с БД
        $pdo = getDbContext();
        // получаем все строки таблицы языков
        $ps = $pdo->prepare("SELECT * FROM" . self::$TABLE_NAME);
        // Выполняем
        $ps->execute();
        //Сохраняем полученные данные в ассоциативный массив
        $types = $ps->fetchAll();
        return $types;
    } catch (PDOException $e) {
        echo $e->getMessage();
        return false;
    }
  }
}