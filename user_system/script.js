// function validateForm() {
//     const user = document.getElementById('username').value;
//     const pwd = document.getElementById('password').value;

//     if (user.length < 3 || pwd.length < 3) {
//         alert("用户名和密码长度必须 >= 3");
//         return false;
//     }
//     return true;
// }


document.addEventListener('DOMContentLoaded', function() {
    /*  DOMContentLoaded 事件：代码会在 HTML 文档的初始内容加载完成后执行
        这意味着所有的 DOM 元素(如表单、输入框等)已经可以被访问 */

    const form = document.querySelector('form'); // 选择表单元素
    form.addEventListener('submit', function(e) { // 监听提交事件
        
        const user = document.getElementById('username').value;
        const pwd = document.getElementById('password').value;

        if (user.length < 3 || pwd.length < 3) {
            alert("用户名和密码长度必须 >= 3");
            e.preventDefault(); // 阻止表单提交
        }
    });
});

