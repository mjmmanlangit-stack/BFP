<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $username = $data["username"];
    $password = $data["password"];

    $stmt = $conn->prepare("SELECT id, username,role,password,email FROM user WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password === $row["password"]) {
            session_start();
            $_SESSION['user'] = $row['id'];
            echo json_encode(["success" => "hi", "user" => $row["email"], "id"=>$row['id'], "role"=>$row['role']]);
        } else {
            echo json_encode(["error" => "invalid password"]);
        }
    } else {
        echo json_encode(["error" => "no user"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "no data"]);
}

$conn->close();
?>
