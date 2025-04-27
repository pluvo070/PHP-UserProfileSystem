function validateForm() {
    const user = document.getElementById('username').value;
    const pwd = document.getElementById('password').value;

    if (user.length < 3 || pwd.length < 3) {
        alert("用户名和密码长度必须 >= 3");
        return false;
    }
    return true;
}
