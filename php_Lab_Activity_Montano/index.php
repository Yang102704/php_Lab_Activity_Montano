<?php
require_once 'dbcon.php';

$message = "";
$messageType = "";
$editUser = null;

// DELETE USER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $id = filter_var($_POST["delete_id"], FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "User deleted successfully!";
            $messageType = "error";
        } else {
            $message = "User not found.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

// UPDATE USER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_user"])) {
    $id = filter_var($_POST["update_id"], FILTER_VALIDATE_INT);
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($id && $id > 0 && $name !== "" &&
        filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $stmt = $conn->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $name, $email, $id);

        if ($stmt->execute()) {
            $message = "User updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating user.";
            $messageType = "error";
        }

        $stmt->close();
    } else {
        $message = "Please enter a valid name and email.";
        $messageType = "error";
    }
}

// ADD USER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_user"])) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name !== "" && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare(
            "INSERT INTO users (name, email) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $name, $email);

        if ($stmt->execute()) {
            $message = "User added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding user.";
            $messageType = "error";
        }

        $stmt->close();
    } else {
        $message = "Please enter a valid name and email.";
        $messageType = "error";
    }
}

// LOAD USER FOR EDITING
if (isset($_GET["edit"]) && !$message) {
    $id = filter_var($_GET["edit"], FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = $conn->prepare(
            "SELECT id, name, email FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $editUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// GET REGISTERED USERS
$result = $conn->query(
    "SELECT id, name, email, created_at FROM users ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management System</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px;
    font-family: Arial, sans-serif;
    background: #fff;
    color: #333;
}

h2 {
    text-align: center;
    margin: 10px 0 35px;
}

.container {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 35px;
    max-width: 1100px;
    margin: auto;
}

h3 {
    font-size: 17px;
    margin-bottom: 18px;
}

label {
    display: block;
    font-size: 13px;
    margin-bottom: 5px;
}

input {
    width: 100%;
    padding: 11px;
    margin-bottom: 17px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

button, .btn {
    display: inline-block;
    padding: 8px 12px;
    border: none;
    border-radius: 3px;
    color: white;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
}

.submit-btn {
    width: 100%;
    padding: 12px;
    background: #0866e8;
    font-size: 14px;
}

.edit-btn {
    background: #f0ad4e;
    color: #222;
}

.delete-btn {
    background: #dc3545;
}

.cancel-btn {
    background: #6c757d;
    margin-bottom: 15px;
}

.message {
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 14px;
}

.success {
    background: #d1e7dd;
    color: #0f5132;
}

.error {
    background: #f8d7da;
    color: #842029;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 8px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 13px;
    overflow-wrap: anywhere;
}

th {
    background: #f8f9fa;
}

.actions {
    white-space: nowrap;
}

.actions form {
    display: inline-block;
}

@media (max-width: 750px) {
    body {
        padding: 18px;
    }

    .container {
        grid-template-columns: 1fr;
    }

    table {
        min-width: 600px;
    }
}
</style>
</head>

<body>

<h2>User Management System</h2>

<?php if ($message !== ""): ?>
    <div class="message <?= htmlspecialchars($messageType) ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="container">

    <!-- ADD OR EDIT FORM -->
    <section>
        <h3><?= $editUser ? "Edit User" : "Add New User" ?></h3>

        <?php if ($editUser): ?>
            <a class="btn cancel-btn"
               href="index.php">Cancel Edit</a>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <?php if ($editUser): ?>
                <input type="hidden" name="update_id"
                       value="<?= (int)$editUser["id"] ?>">
            <?php endif; ?>

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name"
                   placeholder="Enter Full Name"
                   value="<?= htmlspecialchars($editUser["name"] ?? "") ?>"
                   required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   placeholder="Enter Email Address"
                   value="<?= htmlspecialchars($editUser["email"] ?? "") ?>"
                   required>

            <?php if ($editUser): ?>
                <button class="submit-btn" type="submit"
                        name="update_user">
                    Update
                </button>
            <?php else: ?>
                <button class="submit-btn" type="submit"
                        name="add_user">
                    Submit
                </button>
            <?php endif; ?>
        </form>
    </section>

    <!-- REGISTERED USERS TABLE -->
    <section>
        <h3>Registered Users</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$row["id"] ?></td>

                        <td><?= htmlspecialchars($row["name"]) ?></td>

                        <td><?= htmlspecialchars($row["email"]) ?></td>

                        <td><?= htmlspecialchars($row["created_at"]) ?></td>

                        <td class="actions">
                            <a class="btn edit-btn"
                               href="index.php?edit=<?= (int)$row["id"] ?>">
                                Edit
                            </a>

                            <form method="POST" action="index.php"
                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="delete_id"
                                       value="<?= (int)$row["id"] ?>"
                                       style="display:none">

                                <button class="delete-btn" type="submit">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center">
                            No data found
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

</body>
</html>


