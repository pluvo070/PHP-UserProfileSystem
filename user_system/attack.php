<!-- 模拟 CSRF 攻击, 验证 token 是否设置正确 (进行不包含token的post提交) -->

<!-- 用户登录界面 -->
<form method="post" action="http://project1:8001/user_system/login.php">
    <label for="username">用户名:</label>
    <input type="text" name="username">
    <label for="password">密码:</label>
    <input type="password" id="password" name="password" required>
    <input type="submit" value="攻击提交">
</form>
