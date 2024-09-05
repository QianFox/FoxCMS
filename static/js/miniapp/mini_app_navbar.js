/*
 * @Descripttion :
 * @Author       : liuzhifang
 * @Date         : 2022-12-24 19:37:00
 * @LastEditors  : liuzhifang
 * @LastEditTime : 2022-12-24 23:00:23
 */

// 切换 navbar-tap 图
$(document).on('click', '.navbar-tip .navbar-tip-item', function () {
    let $this = $(this),
        isActive = $this.is('is-active');

    if (!isActive) {
        $('.navbar-tip .navbar-tip-item.is-active').removeClass('is-active');
        $this.addClass('is-active');
        $('.view-navbar-tip').attr('src', $this.find('img').attr('src'));
    }
});

// 开启/关闭底部导航
$(document).on('click', '#tabbarRadio .foxui-radio', function () {
    let index = $(this).index();
    if (index === 0) {
        $('.page-container .tabbar').show();
    } else if (index === 1) {
        $('.page-container .tabbar').hide();
    }
});

// 开启/关闭悬浮按钮
$(document).on('click', '#fixedRadio .foxui-radio', function () {
    let index = $(this).index();
    if (index === 0) {
        $('.page-container .fixed-button').show();
    } else if (index === 1) {
        $('.page-container .fixed-button').hide();
    }
});

// 修改颜色
$(document).on('click', '#tabbarLineColor .confirm', function () {
    let color = $(this).closest('.foxui-color-bottom__btns').find('input').val();
    $('#tabbarLineColor .color-show-text').val(color);
});
