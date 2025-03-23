<?php
$_SESSION['user_id'] = 1; // ⚠️ temporaire, remplace avec session réelle

// Récupérer les notifications depuis le contrôleur
$notifController = new NotificationController();
$lastNotif = $notifController->getLastUnreadNotification($_SESSION['user_id']);

?>

<style>
    .notif-screen {
        text-align: center;
        font-family: Arial, sans-serif;
        padding: 40px;
    }

    .notif-icon {
        width: 100px;
        animation: pulse 1.2s infinite;
    }

    .notif-message {
        font-size: 28px;
        font-weight: bold;
        margin: 30px 0;
    }

    .notif-validate {
        width: 100px;
        cursor: pointer;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
</style>

<div class="notif-screen">
    <?php if ($lastNotif): ?>
        <img src="images/IconeRappel.png" class="notif-icon" alt="Notification">

        <div class="notif-message">
            <?= htmlspecialchars($lastNotif['content']) ?>
        </div>

        <form method="POST" action="index.php?controller=notification&action=markNotificationAsRead">
            <input type="hidden" name="notif_id" value="<?= $lastNotif['id'] ?>">
            <button type="submit" style="border:none;background:none;">
                <img src="images/check-button.png" class="notif-validate" alt="Valider">
            </button>
        </form>
    <?php else: ?>
        <div class="notif-message">
            ✅ Aucune nouvelle notification.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function loadNotifications() {
        fetch("index.php?controller=notification&action=getUserNotifications")
        .then(response => response.json())
        .then(data => {
            const notifList = document.getElementById("notif-list");
            const notifCount = document.getElementById("notif-count");

            notifList.innerHTML = "";

            if (data.error) {
                notifList.innerHTML = `<li>${data.error}</li>`;
                notifCount.innerText = "0";
                return;
            }

            notifCount.innerText = data.length;

            data.forEach(notif => {
                const li = document.createElement("li");
                li.innerHTML = notif.content +
                    `<button onclick="markAsRead(${notif.id})" style="float:right;">✔</button>`;
                notifList.appendChild(li);
            });
        });
    }

    function markAsRead(notifId) {
        fetch("index.php?controller=notification&action=markNotificationAsRead", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "notif_id=" + notifId
        })
        .then(response => response.json())
        .then(() => loadNotifications());
    }

    // Charger au démarrage
    loadNotifications();

    // 🔄 Recharger toutes les 10 secondes
    setInterval(loadNotifications, 10000);
});
</script>
