<?php
// Отправляем в ответ на запрос клиента заголовки,
// разрешающие ответы кросс-доменные запросы
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
// Если в цепочке параметров запроса присутствует параметр 'controller'
if (isset($_REQUEST['controller'])) {
  // Открываем блок перехвата исключений
	try {
		// Создаем переменную с безымянным объектом ответа клиенту
		$response = new class {
      public $status = "ok"; 
      public $message = "completed";
      public $data = null;
    };
		// Подключаем файл работы с БД
		require_once('persistence/db_connector.php');
    // Читаем значение параметра запроса с именем controller,
    // приводим все символы к нижнему регистру, а первый - к верхнему
    $controller = ucfirst(strtolower($_REQUEST['controller']));
    // Подключаем файл сущности из файла с именем, как у параметра controller
		require_once("persistence/entities/$controller.php");
    // Формируем имя действия (метода контроллера) на основании парметра 'action'
    $action = strtolower($_REQUEST['action']) ?: 'getAll';
    // Получаем из входного потока данных тело запроса,
    // раскодируем json-строку в объект и приводим его к массиву
    $args = (array)json_decode(file_get_contents('php://input'));
    // Если тело запроса удалось получить
    if ($args) {
      // Если в цепочке параметров запроса присутствует параметр 'filter' 
      if ($action == 'filter') {
        // Вызываем из класса контроллера с именем, хранящимся в переменной $controller,
        // статическое действие с именем, хранящимся в переменной $action,
        // передавая ему ассоциативный массив из переменной $args
        $response->data = $controller::$action($args);
      } else {
        // Иначе превращаем массив в нумерованный,
        // расщепляем на отдельные аргументы,
        // передаем их в конструктор контроллера $controller
        // и вызываем на экземпляре контроллера действие по имени, хранящемся в переменной $action
        $response->data = (new $controller(...array_values($args)))->$action();
      }
    } else {
      if (isset($_REQUEST['id'])) {
        $response->data = $controller::$action($_REQUEST['id']);
      } else if (isset($_REQUEST['userId'])) {
        $response->data = $controller::$action($_REQUEST['userId']);
      } else {
        $response->data = $controller::$action();
      }
    }
	} catch (Exception $e) {
    $response->status = "error";
    $response->message = $e->getMessage();
  } finally {
    // 
  }
  // Отправляем в браузер то, что получилось в переменной ответа
  // (данные / сообщение об успешном выполнении / об ошибке).
  // Объект модели ответа предварительно кодируется в json-строку 
	echo json_encode($response);
}