<?php
require_once 'config.php';
require_once 'db.php';

foreach (glob("models/*.php") as $filename) {
    require_once $filename;
}

$db = getDatabaseConnection();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$table = $_GET['table'] ?? '';
$requestMethod = $_SERVER["REQUEST_METHOD"];

if (class_exists(ucfirst($table))) {
    $className = ucfirst($table);
    $apiHandler = new $className($db);
} else {
    echo json_encode(['message' => 'Invalid table specified']);
    exit();
}

$id = $_GET['id'] ?? null;

switch ($requestMethod) {
    case 'GET':
        if ($id) {
            $result = $apiHandler->getOne($id);
            echo json_encode($result);
        } else {
            $result = $apiHandler->getAll();
            echo json_encode($result);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if ($apiHandler->create($data)) {
            echo json_encode(['message' => ucfirst($table) . ' created successfully']);
        } else {
            echo json_encode(['message' => ucfirst($table) . ' creation failed']);
        }
        break;

    case 'PUT':
        if ($id) {
            $data = json_decode(file_get_contents("php://input"), true);
            if ($apiHandler->update($id, $data)) {
                echo json_encode(['message' => ucfirst($table) . ' updated successfully']);
            } else {
                echo json_encode(['message' => ucfirst($table) . ' update failed']);
            }
        }
        break;

    case 'DELETE':
        if ($id) {
            if ($apiHandler->delete($id)) {
                echo json_encode(['message' => ucfirst($table) . ' deleted successfully']);
            } else {
                echo json_encode(['message' => ucfirst($table) . ' deletion failed']);
            }
        }
        break;

    default:
        echo json_encode(['message' => 'Invalid request']);
        break;
}
?>
