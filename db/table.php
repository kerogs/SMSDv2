<?php

// check if '__DIR__./database.db' exists
if (!file_exists(__DIR__ . '/database.db')) {
    // create the database file
    die("Database file not found at " . __DIR__ . '/database.db | Copy pas the database file in /db/template/ and rename it to database.db');
}

// Connect to the database
$db = new SQLite3(__DIR__ . '/database.db');

// Check the most recent entry in coupons_log
$query = "SELECT date, success FROM coupons_log ORDER BY date DESC LIMIT 1";
$result = $db->querySingle($query, true);

$should_call_code_get = false;

if (!$result) {
    // Table is empty
    $should_call_code_get = true;
} else {
    $last_date = strtotime($result['date']);
    $current_time = time();
    $time_difference = ($current_time - $last_date) / 3600; // Convert seconds to hours

    // Every 2 hours or if the last request failed
    if ($time_difference > 2 || $result['success'] == 0) {
        $should_call_code_get = true;
    }
}

if ($should_call_code_get) {
    $rHeader = [
        "Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148",
    ];

    $headerSelected = $rHeader[array_rand($rHeader)]; 
    echo "<script>console.log('Selected User-Agent: $headerSelected');</script>";
    $r = scrap($db, $headerSelected, "https://ucngame.com/codes/sword-master-story-coupon-codes/");
}

/**
 * Saves a new entry in the coupons_log table
 *
 * @param SQLite3 $db
 * @param string $url the URL that was scraped
 * @param string $scrap_result the result of the scrap (ok or error)
 * @param string $reason the reason of the scrap (optional)
 */
function saveDateScrap($db, $url, $scrap_result, $reason = "none")
{
    $date = date("Y-m-d H:i:s");
    $stmt = $db->prepare("INSERT INTO coupons_log (date, url, success) VALUES (:date, :url, :success)");
    $stmt->bindValue(":date", $date, SQLITE3_TEXT);
    $stmt->bindValue(":url", $url, SQLITE3_TEXT);
    $stmt->bindValue(":success", $scrap_result === "ok" ? 1 : 0, SQLITE3_INTEGER);
    $stmt->execute();
}


/**
 * Scrapes a given URL to fetch coupons
 *
 * @param SQLite3 $db
 * @param string $user_agent
 * @param string $url
 * @return int 0 if the URL is not supported, 1 if the coupons are fetched successfully, 2 if there is an error
 */
function scrap($db, $user_agent, $url) {
    if ($url === "https://ucngame.com/codes/sword-master-story-coupon-codes/") {
        $options = [
            "http" => [
                "header" => "User-Agent: $user_agent"
            ]
        ];
        $context = stream_context_create($options);
        $site_content = @file_get_contents($url, false, $context);

        if ($site_content === false) {
            echo '<script>
                console.log("Coupons Error: Failed to fetch URL");
                document.addEventListener("DOMContentLoaded", function() {
                    const notyf = new Notyf();
                    notyf.error({
                        message: "Coupons Error: Failed to fetch URL",
                        duration: 8000,
                        position: {
                            y: "bottom",
                            x: "right"
                        },
                        dismissible: true,
                        icon: "😭"
                    });
                });
            </script>';
            saveDateScrap($db, $url, "ko - Failed to fetch", "Error: Failed to fetch URL");
            return 2;
        }

        preg_match_all('/<strong>([A-Z0-9]+)<\/strong><\/td><td>Redeem this coupon code for ([^(]+) \(Valid until ([^)]+)\)/', $site_content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $coupon_code = $match[1];

            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM coupons WHERE code = :code");
            $checkStmt->bindValue(":code", $coupon_code, SQLITE3_TEXT);
            $result = $checkStmt->execute()->fetchArray(SQLITE3_ASSOC);
            if ($result['count'] > 0) {
                continue;
            }

            $reward_description = trim($match[2]);
            $expiration_raw = preg_replace('/(\d+)(st|nd|rd|th)/', '$1', $match[3]);
            $expiration = date("Y-m-d", strtotime($expiration_raw));

            $reward_value = "";
            $reward_type = "";
            if (preg_match('/x([0-9,]+)/', $reward_description, $value_match)) {
                $reward_value = str_replace(",", "", $value_match[1]);
            }

            if (strpos($reward_description, "Ruby") !== false) {
                $reward_type = "Ruby";
            } elseif (strpos($reward_description, "Stamina") !== false) {
                $reward_type = "Stamina";
            } elseif (strpos($reward_description, "Gold Bar") !== false) {
                $reward_type = "Gold Bar";
            }

            $stmt = $db->prepare("INSERT INTO coupons (code, type, reward, value, date, description) VALUES (:code, :type, :reward, :value, :date, :description)");
            $stmt->bindValue(":code", $coupon_code, SQLITE3_TEXT);
            $stmt->bindValue(":type", $reward_type, SQLITE3_TEXT);
            $stmt->bindValue(":reward", $reward_description, SQLITE3_TEXT);
            $stmt->bindValue(":value", $reward_value, SQLITE3_TEXT);
            $stmt->bindValue(":date", $expiration, SQLITE3_TEXT);
            $stmt->bindValue(":description", $reward_description, SQLITE3_TEXT);
            $stmt->execute();
        }

        saveDateScrap($db, $url, "ok");
        echo '<script>
            console.log("Coupons fetched successfully");
            document.addEventListener("DOMContentLoaded", function() {
                const notyf = new Notyf();
                notyf.success({
                    message: "Coupons fetched successfully",
                    duration: 8000,
                    position: {
                        y: "bottom",
                        x: "right"
                    },
                    dismissible: true,
                    icon: "😎"
                });
            });
        </script>';
        return 1;
    } else {
        echo '<script>
            console.log("Coupons Error: URL not supported");
            document.addEventListener("DOMContentLoaded", function() {
                const notyf = new Notyf();
                notyf.error({
                    message: "Coupons Error: URL not supported",
                    duration: 8000,
                    position: {
                        y: "bottom",
                        x: "right"
                    },
                    dismissible: true,
                    icon: "😭"
                });
            });
        </script>';
        saveDateScrap($db, $url, "ko - URL not supported", "Error: URL not supported");
        return 0;
    }
}

// close database connection
$db->close();