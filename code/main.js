document.getElementById('check').addEventListener('change', function() {
    document.querySelector('#btn').style.display = this.checked ? 'none' : 'block';
    document.querySelector('#cancel').style.display = this.checked ? 'block' : 'none';
    const sidebar = document.querySelector('.sidebar');
    sidebar.style.transition = 'left 0.5s ease-in-out';
});

function openAvatarModal() {
    document.getElementById("avatarModal").style.display = "block";
}

window.onclick = function(event) {
    if (event.target === document.getElementById("avatarModal")) {
        document.getElementById("avatarModal").style.display = "none";
    }
}

function selectAvatar(avatar) {
    document.getElementById("selectedAvatar").value = avatar;
    const currentAvatar = document.querySelector(".current-avatar");
    currentAvatar.src = "avatars/" + avatar;
}

function changeAvatar() {
    const selectedAvatar = document.getElementById("selectedAvatar").value;
    if (selectedAvatar) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "change_avatar.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert(xhr.responseText);
                location.reload();
            }
        };
        xhr.send("avatar=" + encodeURIComponent(selectedAvatar));
    } else {
        alert("Пожалуйста, выберите аватар.");
    }
}
