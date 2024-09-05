/*
 * @Descripttion : FOXCMS是一款高效的PHP多端跨平台内容管理系统
 * @Author       : FoxCMS Team
 * @Date         : 2023-04-08 15:39:02
 * @version      : V1.08
 * @copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023-04-18 09:25:50
 */
/**
 * @description: 定位滚动
 * @param {*}
 * @return {*}
 * @Date: 2023-02-20 09:26:07
 */
$(document).on('click', '.aside-slide dd a, #helpFastNav .foxui-dropdown-item', function () {
    let id = $(this).attr('data-targetid'),
        $curDom = $(id),
        top = $curDom.offset().top - 92;
    $(window).scrollTop(top);
});

/**
 * @description: 右侧滚动条
 * @param {*} function
 * @return {*}
 * @Date: 2023-02-20 13:59:22
 */
$(document).scroll(function () {
    let scrollTop = $(document).scrollTop(),
        slideHeight = $(document.body).height(),
        windowHeight = $(window).height();
    let runwayHeight = $('.slider .runway').height(),
        barHeight = $('.slider .bar').height();
    let scrollPre = scrollTop / (slideHeight - windowHeight);
    $('.slider .runway .bar').css({ top: `${scrollPre * (runwayHeight - barHeight)}px` });

    if (scrollTop >= 386) {
        $('.list-help-main .aside-slide-inner').css({
            position: 'fixed',
            top: '92px',
            width: '14%',
        });
    } else {
        $('.list-help-main .aside-slide-inner').css({
            position: 'relative',
            top: 0,
            width: '100%',
        });
    }
});
