<?php
    session_start();
    require_once("./includes/db.php"); // Ensure correct path
    try {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $redirect_url = $_SESSION['current_page'];
            if (empty($email) || empty($password)) {
                echo "Please fill in all fields.";
                exit();
            }
                // Fetch user data from `credentials` & `users` table
                $query = "SELECT c.user_id, c.user_email, c.user_password, u.perm_level 
                        FROM credentials c 
                        JOIN users u ON c.user_id = u.user_id 
                        WHERE c.user_email = :email";

                $stmt = $conn->prepare($query);
                $stmt->bindParam(":email", $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if(empty($user)){
                    $_SESSION['login_error'] = "Account not found.";
                    header("Location: " . $redirect_url);
                    exit();
                }
                
                if (password_verify($password, $user['user_password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_email'] = $user['user_email'];
                    $_SESSION['permLevel'] = $user['perm_level'];
                    $_SESSION["isLogin"] = true;

                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    $_SESSION['login_error'] = "Invalid email or password.";
                    header("Location: " . $redirect_url);
                    exit();
                }
        } else {
            echo "Invalid request.";
        }
    } catch (PDOException $e) {
        echo "Oops! There was a SQL error.";
        error_log("[login.php] SQL Error: " . $error_message . PHP_EOL, 3, "../error_log.txt");
        header("Location: " . $redirect_url);
        exit();
    }
?>
