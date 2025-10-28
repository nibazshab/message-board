<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mysql";

ini_set("display_errors", 0);

$conn = new mysqli($host, $user, $password, $dbname);

$pages_k = "pages";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = $_POST["message"];
    $stmt = $conn->prepare("INSERT INTO messages (message) VALUES (?)");
    $stmt->bind_param("s", $message);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    apcu_delete($pages_k);
    exit();
}
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    li {
        margin-bottom: 20px;
        white-space: pre-wrap;
    }
</style>

<body style="margin: 0;">
    <textarea id="con" style="width: 100%; resize: none;"></textarea>
    <input id="sub" type="submit">
    <ul id="msgs">

<?php
$page = max((int)($_GET["page"] ?? 1), 1);
$record = 100;
$offset = ($page - 1) * $record;

$result = $conn->query("SELECT message FROM messages ORDER BY id DESC LIMIT $offset, $record");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row["message"]) . "</li>";
    }
}
?>

    </ul>

<?php
$pages = apcu_fetch($pages_k);

if ($pages === false) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM messages");
    $pages = ceil($result->fetch_assoc()["total"] / $record);
    apcu_store($pages_k, $pages, 120);
}

if ($pages > 1) {
    echo "页码 ";
    for ($i = 1; $i <= $pages; $i++) {
        if ($i == $page) {
            echo "$i ";
        } else {
            echo "<a href='?page=$i'>$i</a> ";
        }
    }
}

$conn->close();
?>

</body>

<script>
    const con = document.getElementById("con")
    const sub = document.getElementById("sub")
    const msgs = document.getElementById("msgs")

    sub.addEventListener("click", () => {
        const message = con.value.trim()
        if (!message) return
        sub.disabled = true
        con.value = ""
        fetch("", {
            method: "POST",
            body: new URLSearchParams({ message })
        })
            .then(response => {
                if (response.ok) {
                    const li = document.createElement("li");
                    li.textContent = message;
                    msgs.insertBefore(li, msgs.firstChild);
                } else {
                    con.value = message;
                    msgs.insertAdjacentHTML("afterbegin", `<li><p style="color: red;">error</p></li>`)
                }
                sub.disabled = false;
            })
    })
</script>
