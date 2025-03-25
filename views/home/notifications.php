<h2>📋 Notifications</h2>
<ul id="notif-list"></ul>

<script>
function loadNotifications() {
    fetch("index.php?controller=notification&action=getUserNotifications")
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById("notif-list");
        list.innerHTML = "";

        if (data.length === 0) {
            list.innerHTML = "<li>Aucune notification non lue 📭</li>";
            return;
        }

        data.forEach(notif => {
            const li = document.createElement("li");
            li.innerHTML = `
                <strong>${notif.type} :</strong> ${notif.content}
                <button onclick="markAsRead(${notif.id})">✔️ Marquer comme lue</button>
            `;
            list.appendChild(li);
        });
    });
}

function markAsRead(id) {
    fetch("index.php?controller=notification&action=markNotificationAsRead", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "notif_id=" + id
    })
    .then(res => res.json())
    .then(() => loadNotifications());
}

document.addEventListener("DOMContentLoaded", loadNotifications);
</script>

<style>
ul#notif-list {
    list-style: none;
    padding: 0;
}
ul#notif-list li {
    background: #f0f0f0;
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 6px;
}
button {
    margin-left: 10px;
    padding: 5px 10px;
    cursor: pointer;
}
</style>
