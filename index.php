<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mysql";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = $_POST["message"];
    $conn = new mysqli($host, $user, $password, $dbname);
    $stmt = $conn->prepare("INSERT INTO messages (message) VALUES (?)");
    $stmt->bind_param("s", $message);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo htmlspecialchars($message);
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
$conn = new mysqli($host, $user, $password, $dbname);
$sql = "SELECT message FROM messages ORDER BY id DESC"; // LIMIT 50";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row["message"]) . "</li>";
    }
}
$conn->close();
?>

    </ul>
</body>

<script>
    const con = document.getElementById('con')
    const sub = document.getElementById('sub')
    const msgs = document.getElementById('msgs')

    sub.addEventListener("click", () => {
        const message = con.value.trim()
        if (!message) return
        sub.disabled = true
        con.value = ""
        fetch('', {
            method: "POST",
            body: new URLSearchParams({ message })
        })
            .then(response => response.text())
            .then(msg => {
                msgs.insertAdjacentHTML("afterbegin", `<li>${msg}</li>`)
                sub.disabled = false
            })
    })
</script>
