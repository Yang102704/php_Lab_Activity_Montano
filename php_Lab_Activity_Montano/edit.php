<?php
require_once 'dbcon.php';

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$message = "";

// UPDATE USER
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name !== "" && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $name, $email, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?status=updated");
            exit;
        } else {
            $message = "Error updating user.";
        }

        $stmt->close();
    } else {
        $message = "Please enter a valid name and email.";
    }
}

// GET USER DETAILS
$stmt = $conn->prepare(
    "SELECT name, email FROM users WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    exit("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit User</title>
<style>
body {
    font-family: Arial, sans-serif;
    max-width: 500px;
    margin: 50px auto;
    padding: 20px;
    color: #333;
}
input {
    width: 100%;
    padding: 12px;
    margin: 8px 0 18px;
    box-sizing: border-box;
}
button, a {
    display: inline-block;
    padding: 11px 18px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
}
button {
    background: #0866e8;
    color: white;
}
a {
    background: #6c757d;
    color: white;
}
.error {
    padding: 12px;
    background: #f8d7da;
    color: #842029;
}
</style>
</head>
<body>

<h2>Edit User</h2>

<?php if ($message !== ""): ?>
    <p class="error"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" action="edit.php">
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <label>Full Name</label>
    <input type="text" name="name"
           value="<?= htmlspecialchars($user['name']) ?>" required>

    <label>Email Address</label>
    <input type="email" name="email"
           value="<?= htmlspecialchars($user['email']) ?>" required>

    <button type="submit">Update User</button>
    <a href="index.php">Cancel</a>
</form>

</body>
</html>