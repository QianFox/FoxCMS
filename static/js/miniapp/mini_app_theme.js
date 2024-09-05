/*
 * @Descripttion : FOXCMS是一款高效的PHP多端跨平台内容管理系统
 * @Author       : FoxCMS Team
 * @Date         : 2022-12-20 15:53:25
 * @version      : V1.08
 * @copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2022-12-20 21:39:11
 */
$(document).on('click', '.theme-color-list .color-item', function () {
    let $this = $(this),
        isActive = $this.is('.active');

    if (!isActive) {
        // 切换激活项
        _switchColorActive($this);
        // 保存颜色
        let color1 = $this.find('span:nth-child(1)').css('backgroundColor');
        let color2 = $this.find('span:nth-child(2)').css('backgroundColor');
        _saveThemeColor(color1, color2);
    }
});

// 修改主题色1
$(document).on('click', '.diy-color-1 .confirm', function () {
    let $colorShow = $(this).closest('.foxui-color').find('.foxui-color-show').css('backgroundColor');
    // 修改自定义展示的颜色
    _modifyThemeColor($colorShow);
    // 切换激活项
    _switchColorActive($('.color-item.diy-color'));
    // 保存自定义颜色
    _saveThemeColor($colorShow);
});

// 修改主题色2
$(document).on('click', '.diy-color-2 .confirm', function () {
    let $colorShow = $(this).closest('.foxui-color').find('.foxui-color-show').css('backgroundColor');
    // 修改自定义展示的颜色
    _modifyThemeColor('', $colorShow);
    // 切换激活项
    _switchColorActive($('.color-item.diy-color'));
    // 保存自定义颜色
    _saveThemeColor('', $colorShow);
});

// 切换激活项
function _switchColorActive($item) {
    $('.theme-color-list .color-item').filter('.active').removeClass('active');
    $item.addClass('active');
}

// 修改自定义展示的颜色
function _modifyThemeColor(color1, color2) {
    if (color1) {
        $('#showDiyColor1').css('backgroundColor', color1);
        // 设置颜色值
        $('.diy-color-1 .text').text(color1);
    }
    if (color2) {
        $('#showDiyColor2').css('backgroundColor', color2);
        // 设置颜色值
        $('.diy-color-2 .text').text(color2);
    }
}

// 保存自定义颜色
function _saveThemeColor(color1, color2) {
    if (color1) {
        $('input[name=themeColor1]').val(color1);
    }
    if (color2) {
        $('input[name=themeColor2]').val(color2);
    }
}
