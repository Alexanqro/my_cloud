<?php

class User {

    static public function showUsers()
    {
        header('Content-Type: application/json');
        try {
            global $connection;
            $statement = $connection->query("SELECT id, email FROM users");
            $statement->execute();
            $data = [];
            $data = $statement->fetchAll();
            $connection = null;
            if (empty($data)) {
                throw new Exception("Users not found!");
            } else {
                echo json_encode(['users' => $data]);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    static public function addUser()
    {
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if ((isset($data['email'], $data['password'], $data['role'])) && (!empty(trim($data['email'])) && !empty(trim($data['password'])) && !empty(trim($data['role'])))) {
                $email = htmlspecialchars(trim($data['email']));
                $role = htmlspecialchars(trim($data['role']));
                $password = htmlspecialchars(trim($data['password']));
                $password = password_hash($password, PASSWORD_DEFAULT);

                global $connection;
                $connection->beginTransaction();
                $statement = $connection->prepare("INSERT INTO users (id, email, password, role) values(null, :email, :password, :role)");
                $statement->execute(['email' => $email, 'password' => $password, 'role' => $role]);

                $user_id = $connection->lastInsertId();


                $statement = $connection->prepare('INSERT INTO folders (id, folder_name, user_id, parent_folder_id, created_at, updated_at) values(NULL, :folder_name, :user_id, NULL, DEFAULT, DEFAULT)');
                $statement->execute(['folder_name' => $user_id, 'user_id' => $user_id]);
                $pathFolder = $_SERVER['DOCUMENT_ROOT'] . '/files/' . $user_id . '/';
                try {
                    if (!file_exists($pathFolder)) {
                        mkdir($pathFolder, 0777, true);
                        $connection->commit();
                    }
                } catch (PDOException $e) {

                    $connection->rollBack();
                    echo json_encode(['Error' => 'Folder is not created']);
                }


                $data = [
                    'id' => $user_id,
                    'email' => $email,
                    'role' => $role
                ];
                header('HTTP/1.1 201 Created');
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                throw new Exception('Не все данные были переданы!');
            }
        } catch (PDOException $e) {
            echo "Error data base: " . $e->getMessage();
        } catch (Exception $e) {

            echo "Error: " . $e->getMessage();
        }
    }


    static public function getUser()
    {
        header('Content-Type: application/json');
        try {
            $url = $_SERVER['REQUEST_URI'];
            $parts = explode('/', $url);
            $user_id = $parts[sizeof($parts) - 1];
            if (!empty(htmlspecialchars(trim($user_id)))) {
                global $connection;
                $statement = $connection->prepare("SELECT id, email FROM users WHERE id = :id");
                $statement->execute(['id' => $user_id]);
                $data = [];
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                $connection = null;
                if (empty($data) || !is_numeric($user_id)) {
                    throw new Exception("User with id {$user_id} not found!");
                } else {
                    echo json_encode($data);
                }
            } else {
                echo "Error: user not found";
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    static public function updateUser()
    {
        header('Content-Type: application/json');
        try {
            $url = $_SERVER['REQUEST_URI'];
            $parts = explode('/', $url);
            $user_id = $parts[sizeof($parts) - 1];
            $data = json_decode(file_get_contents("php://input"), true);

            if (!empty($data['email']) && !empty($data['role'])) {
                $email = htmlspecialchars($data['email']);
                $role = htmlspecialchars($data['role']);
                global $connection;
                $statement = $connection->prepare('UPDATE users SET email = :email, role = :role WHERE id = :id');
                $statement->execute(['email' => $email, 'role' => $role, 'id' => $user_id]);


                $statement = $connection->prepare("SELECT id, email, role FROM users WHERE id = :id");
                $statement->execute(['id' => $user_id]);
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {

                throw new Exception('Не хватает данных');
            }
        } catch (PDOException $e) {

            echo "Error data base: " . $e->getMessage();
        } catch (Exception $e) {

            echo "Error: " . $e->getMessage();
        }
    }


    static public function deleteUser()
    {
        header('Content-Type: application/json');
        try {
            $url = $_SERVER['REQUEST_URI'];
            $parts = explode('/', $url);
            $user_id = $parts[sizeof($parts) - 1];

            global $connection;
            $statement = $connection->prepare('SELECT COUNT(*) FROM users WHERE id = :id');
            $statement->execute(['id' => $user_id]);

            $user_exists = $statement->fetchColumn();

            if ($user_exists) {
                $statement = $connection->prepare('DELETE FROM users WHERE id = :id');
                $statement->execute(['id' => $user_id]);
                http_response_code(204);
            } else {
                http_response_code(404);
                echo "User not found";
            }
        } catch (PDOException $e) {
            echo "Error database: " . $e->getMessage();
            http_response_code(500);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            http_response_code(500);
        }
    }


    static public function loginUser()
    {
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if ((isset($data['email'], $data['password']))  && (!empty(trim($data['email'])) && !empty(htmlspecialchars(trim($data['password']))))) {
                $email = htmlspecialchars($data['email']);
                $password = htmlspecialchars(trim($data['password']));

                global $connection;
                $statement = $connection->prepare("SELECT id, password FROM users WHERE email = :email");
                $statement->execute(['email' => $email]);
                $user = $statement->fetch(PDO::FETCH_ASSOC);

                if (!password_verify($password, $user['password'])) {
                    http_response_code(401);
                    echo 'Error: Login information not provided';
                    return;
                    $connection = null;
                }

                $bytes = random_bytes(32);

                $token = hash('sha256', $bytes);
                $expires_in = time() + (24 * 60 * 60);
                $expiration_time = date('Y-m-d H:i:s', $expires_in);

                $statement = $connection->prepare("INSERT INTO user_tokens (id, user_id, token, expiration_time) values(null, :user_id, :token, :expiration_time)");
                $statement->execute(['user_id' => $user['id'], 'token' => $token, 'expiration_time' => $expiration_time]);
                $connection = null;

                $response = ['access_token' => $token, 'token_type' => 'Bearer', 'expires_in' => 3600];
                echo json_encode($response);
            } else {
                http_response_code(400);
                echo 'Error: Login information not provided';
            }
        } catch (Exception $e) {

            echo "Error: " . $e->getMessage();
        }
    }


    static public function logoutUser()
    {
        header('Content-Type: application/json');
        try {
            $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset($_SERVER['HTTP_X_AUTHORIZATION']) ? $_SERVER['HTTP_X_AUTHORIZATION'] : null);
            if (empty($authHeader)) {
                return http_response_code(400);
            }
            $bearerToken = explode(" ", $authHeader);
            if (count($bearerToken) < 2 || !isset($bearerToken[1])) {
                return http_response_code(400);
            }
            $accessToken = $bearerToken[1];

            global $connection;
            $statement = $connection->prepare('DELETE FROM user_tokens WHERE token = :token');
            $statement->execute(['token' => $accessToken]);

            return "[OK]";
        } catch (PDOException $e) {
            return http_response_code(500);
        }
    }


    static public function resetPassword()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $email = htmlspecialchars(trim($data['email']));
        if (empty($email)) {
            http_response_code(400);
            echo 'Error: Login information not provided';
            return;
        }

        try {
            global $connection;
            $statement = $connection->prepare("SELECT email, id FROM users WHERE email = :email");
            $statement->execute(['email' => $email]);
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            if (!empty($user)) {
                $bytes = random_bytes(32);
                $token = hash('sha256', $bytes);
                $expires_in = time() + (1 * 60 * 60);
                $expiration_time = date('Y-m-d H:i:s', $expires_in);
                $statement = $connection->prepare("INSERT INTO user_tokens (id, user_id, token, expiration_time) values(null, :user_id, :token, :expiration_time)");
                $statement->execute(['user_id' => $user['id'], 'token' => $token, 'expiration_time' => $expiration_time]);
                $connection = null;


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://my_cloud/mail/mail.php");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('token' => $token, 'email' => $email)));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                echo json_encode($response);

                curl_close($ch);
            } else {
                http_response_code(404);
                echo "Error: User with this email was not found";
            }
        } catch (PDOException $e) {
            echo "Connection failed:" . $e->getMessage();
        }
        $connection = null;
    }


    static public function newPassword()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty(htmlspecialchars(trim($data['token']))) || empty(htmlspecialchars(trim($data['password'])))) {
            http_response_code(400);
            echo 'Error: Access to change password denied';
            return;
        } else {
            $token = htmlspecialchars(trim($data['token']));
            $password = password_hash(htmlspecialchars(trim($data['password'])), PASSWORD_DEFAULT);
            global $connection;
            $statement = $connection->prepare("SELECT user_id, expiration_time FROM user_tokens WHERE token = :token");
            $statement->execute(['token' => $token]);
            $data = $statement->fetch(PDO::FETCH_ASSOC);

            if (!empty($data)) {
                $created_at_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $data['expiration_time']);
                $current_time = new DateTime();
                if (($current_time->getTimestamp() > $created_at_datetime->getTimestamp())) {

                    $statement = $connection->prepare('DELETE FROM user_tokens WHERE token = :token');
                    $statement->execute(['token' => $token]);
                    http_response_code(400);
                    echo 'Error: Access to change password denied';
                } else {

                    $statement = $connection->prepare('UPDATE users SET password = :password WHERE id = :id');
                    $statement->execute(['password' => $password, 'id' => $data['user_id']]);

                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json');
                    echo 'Password changed successfully';
                }
            } else {
                http_response_code(400);
                echo 'Error: Access to change password denied';
                return;
            }
        }
    }
}