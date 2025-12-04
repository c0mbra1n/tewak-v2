<?php
require_once 'includes/config.php';

// Mock Session
$_SESSION['user_id'] = 1; // Assume admin/teacher exists
$_SESSION['role'] = 'guru';

function testScan($qr_code, $lat, $lng, $description)
{
    global $pdo;
    echo "<h3>Test: $description</h3>";
    echo "QR: $qr_code, Loc: ($lat, $lng)<br>";

    // Prepare Mock Request
    $url = 'http://localhost/mogu/api/scan.php';
    $data = [
        'qr_code' => $qr_code,
        'latitude' => $lat,
        'longitude' => $lng,
        'subject' => 'Test Subject'
    ];

    // We can't easily curl localhost from within the script if it's running on CLI without a server.
    // Instead, we will include the logic or just assume the user runs this in browser.
    // Let's make this a browser-runnable script that uses JS fetch to hit the real API.
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Location Test</title>
</head>

<body>
    <h1>Location Validation Test</h1>
    <div id="results"></div>

    <script>
        async function runTest(name, qr, lat, lng) {
            const div = document.getElementById('results');
            div.innerHTML += `<h3>${name}</h3>`;

            try {
                const response = await fetch('api/scan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        qr_code: qr,
                        latitude: lat,
                        longitude: lng,
                        subject: 'Test Subject'
                    })
                });
                const data = await response.json();
                const color = data.status === 'success' ? 'green' : 'red';
                div.innerHTML += `<p style="color:${color}">Result: ${data.message}</p>`;
            } catch (e) {
                div.innerHTML += `<p style="color:red">Error: ${e}</p>`;
            }
            div.innerHTML += '<hr>';
        }

        // You need to know a valid QR code and its location to test properly.
        // This script is just a template for the user.
        document.write("<p>Please use a real QR code from your database to test.</p>");
    </script>
</body>

</html>