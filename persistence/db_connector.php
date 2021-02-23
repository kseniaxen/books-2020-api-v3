<?php
// Функция соединения с БД
function getDbContext(){
  /* Переменная, в которую должен быть записан объект - контекст для работы с БД */
  $pdo = false;
  /* Параметры соединения */
  // адрес сервера mysql
  $host = "localhost:3306"; // localhost
  // $host = "localhost";
  // имя пользователя БД
  $user = "root"; // id12898591_root
  // $user = "id12898591_root";
  // пароль пользователя БД
  $pass = "root"; // Password2
  // $pass = "Password2";
  // имя БД
  // $dbname = "tyaamariupol";
  $dbname = "books_as_a_gift"; // id12898591_books_as_a_gift
  // $dbname = "id12898591_books_as_a_gift";
  // склеиваем строку соединения
  $cs = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8;';
  /* Параметры получения результата из БД */
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC/*,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'*/
  );
  /* Попытка соединиться с БД и записать в переменную контекст для дальнейшей работы */
  try {
    $pdo = new PDO($cs, $user, $pass, $options);
    return $pdo;
  } catch (PDOException $e) {
    // echo mb_convert_encoding($e->getMessage(), 'UTF-8', 'Windows-1251');
    return false;
  }
}