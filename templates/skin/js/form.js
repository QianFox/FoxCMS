/*
 * @Descripttion : FOXCMS是一款高效的PHP多端跨平台内容管理系统
 * @Author       : FoxCMS Team
 * @Date         : 2023-04-19 11:06:25
 * @version      : V1.08
 * @copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2024-07-06 11:11:32
 */

$(document).on('click', '#requiredForm .submit-btn', function () {
    event.preventDefault();
    if (checkform($(this).closest('form'))) {
        $('input[type="submit"]').click();
    }
});

$(document).on('input', '#input[name="text0"]', function () {
    let $this = $(this);
    if (isEmpty($this.val())) {
        $this.removeClass('err');
    } else {
        $this.addClass('err');
    }
});
$(document).on('input', 'input[name="text1"]', function () {
    let $this = $(this);
    if (isPhone($this.val())) {
        $this.removeClass('err');
    } else {
        $this.addClass('err');
    }
});
$(document).on('input', 'input[name="text2"]', function () {
    let $this = $(this);
    if (isEmpty($this.val())) {
        $this.removeClass('err');
    } else {
        $this.addClass('err');
    }
});

function checkform($form) {
    let $text0 = $form.find('input[name="text0"]'),
        $text1 = $form.find('input[name="text1"]'),
        $text2 = $form.find('input[name="text2"]'),
        text0 = $text0.val(),
        text1 = $text1.val(),
        text2 = $text2.val();

    if (!isEmpty(text0)) {
        foxui.message({
            text: '请填写阁下姓名',
            type: 'danger',
        });
        $text0.addClass('err');
        return false;
    } else if (!isPhone(text1)) {
        foxui.message({
            text: '请填写正确的手机号码',
            type: 'danger',
        });
        $text1.addClass('err');
        return false;
    } else if (!isEmpty(text2)) {
        foxui.message({
            text: '请填写您的需求',
            type: 'danger',
        });
        $text2.addClass('err');
        return false;
    } else {
        return true;
    }
}

/**
 * @description: 校验电话号码
 * @param {*} phone
 * @return {*}
 * @Date: 2023-03-04 16:21:21
 */
function isPhone(phone) {
    let mobileReg = /^1[3|4|5|7|8|9]\d{9}$/,
        teleReg = /^((0\d{2,3})-)?(\d{7,8})$/;
    if (mobileReg.test(phone) || teleReg.test(phone)) {
        return true;
    } else {
        return false;
    }
}

/**
 * @description: 校验值是否有校验
 * @param {*} str
 * @return {*}
 * @Date: 2023-03-04 16:22:52
 */
function isEmpty(str) {
    if (str) {
        return true;
    } else {
        return false;
    }
}
