/*
 * @Descripttion : 通用js
 * @Author       : liuzhifang
 * @Date         : 2022-06-02 13:42:45
 * @LastEditors  : Please set LastEditors
 * @LastEditTime : 2022-12-24 00:18:35
 */

// 导航服务和消息
foxui.tooltip({
    el: '#barService',
    content: barServiceHtml(),
    className: 'bar-service-container',
    width: '324px',
});
foxui.tooltip({
    el: '#barBell',
    content: barBellHtml(),
    className: 'bar-bell-container',
    effect: 'light',
    width: '330px',
});

// 导航折叠功能
$('.foxcms-menu-nav-fold').on('click', function () {
    let $this = $(this),
        $icon = $this.find('i'),
        $inner = $('.foxcms-menu-nav-inner'),
        isRotate = $icon.is('.rotate');

    if (isRotate) {
        $icon.removeClass('rotate');
        $inner.animate({ width: '148px' });
    } else {
        $icon.addClass('rotate');
        $inner.animate({ width: 0 });
    }
});

// 根据窗口宽度判断是否折叠
let foldTimer = null;
$(window).on('resize', function () {
    if (foldTimer) {
        clearTimeout(foldTimer);
        foldTimer = null;
    }
    foldTimer = setTimeout(function () {
        let width = $(window).width(),
            $fold = $('.foxcms-menu-nav-fold'),
            $icon = $fold.find('i'),
            $inner = $('.foxcms-menu-nav-inner'),
            isRotate = $icon.is('.rotate');

        if (width < 1440) {
            if (!isRotate) {
                $icon.addClass('rotate');
                $inner.animate({ width: 0 });
            }
        } else {
            if (isRotate) {
                $icon.removeClass('rotate');
                $inner.animate({ width: '148px' });
            }
        }

        clearTimeout(foldTimer);
        foldTimer = null;
    }, 300);
});

// 收起/展开所有导航
$('.foxcms-menu-expand-all i').on('click', function () {
    let $container = $('.foxui-menu.foxui-type-vertical'),
        $this = $(this),
        isActive = $this.is('.is-active');

    if (isActive) {
        let $openHandle = $container.find('.foxui-menu-handle.is-active');
        $openHandle.click();
        $this.removeClass('is-active');
    } else {
        let $foldHandle = $container.find('.foxui-menu-handle:not(.is-active)');
        $foldHandle.click();
        $this.addClass('is-active');
    }
});

// 根据折叠转换全部折叠按钮状态
$(document).on('click', '.foxui-menu.foxui-type-vertical .foxui-menu-handle', function () {
    let count = $('.foxui-menu.foxui-type-vertical .foxui-menu-handle').length,
        openLen = $('.foxui-menu.foxui-type-vertical .foxui-menu-handle.is-active').length;
    if (count === openLen) {
        $('.foxcms-menu-expand-all i').addClass('is-active');
    } else {
        $('.foxcms-menu-expand-all i').removeClass('is-active');
    }
});

// 表单字段调用
$(document).on('click', '.call-field .foxui-tag', function () {
    const text = $(this).text(),
        el = document.createElement('input');

    el.value = text;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.opacity = 0;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);

    foxui.message({ text: '已复制到剪贴板' });
});

// 右侧浮动预览模板
$('.web-image-view .content-box .main-part').on('mouseover', function () {
    const viewHeight = $(this).height();
    const imgHeight = $(this).find('img').height();
    const mValue = viewHeight - imgHeight;
    $(this)
        .find('img')
        .css('marginTop', mValue + 'px');
});

$('.web-image-view .content-box .main-part').on('mouseout', function () {
    $(this).find('img').css('marginTop', 0);
});

/* bar -> html
 *****************************************************************************************************/
function barServiceHtml() {
    return [
        '<div class="bar-service-content">',
        '<div class="top">',
        '<div class="foxui-row foxui-gutter-0">',
        '<div class="foxui-col-xs-8 foxui-col-sm-8">',
        '<a class="col-inter" href="#">',
        '<i class="foxui-icon-weixiao-f"></i>',
        '<span>建议留言</span>',
        '</a>',
        '</div>',
        '<div class="foxui-col-xs-8 foxui-col-sm-8">',
        '<a class="col-inter" href="#">',
        '<i class="foxui-icon-QQ-f"></i>',
        '<span>QQ客服</span>',
        '</a>',
        '</div>',
        '<div class="foxui-col-xs-8 foxui-col-sm-8">',
        '<a class="col-inter" href="#">',
        '<i class="foxui-icon-hezuo-f"></i>',
        '<span>代理加盟</span>',
        '</a>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="bottom">',
        '<div class="foxui-row foxui-gutter-6 foxui-align-items-center">',
        '<div class="foxui-col-xs-10 foxui-col-sm-10">',
        '<div class="col-inter img-box">',
        '<img src="'+STATIC_PATH+'images/service_qr_code.jpg"/>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-14 foxui-col-sm-14">',
        '<div class="col-inter">',
        '<p>扫码联系官方客服</p>',
        '<p>提供线上咨询</p>',
        '<h3><i class="foxui-icon-dianhua-o"></i><span>400-888-3116</span></h3>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

function barBellHtml() {
    return [
        '<div class="bar-bell-content">',
        '<div class="head">',
        '<h2>系统消息</h2>',
        '</div>',
        '<div class="content">',
        '<a class="img-box" href="#">',
        '<img src="'+STATIC_PATH+'images/join.png"/>',
        '</a>',
        '<ul class="message-list">',
        '<li class="message-item">',
        '<a class="foxui-link" href="#">',
        '<span>功能更新 (2022年8月第2期)</span>',
        '<i class="foxui-icon-arrow-right"></i>',
        '</a>',
        '</li>',
        '<li class="message-item">',
        '<a class="foxui-link" href="#">',
        '<span>功能更新 (2022年8月第2期)</span>',
        '<i class="foxui-icon-arrow-right"></i>',
        '</a>',
        '</li>',
        '<li class="message-item">',
        '<a class="foxui-link" href="#">',
        '<span>功能更新 (2022年8月第2期)</span>',
        '<i class="foxui-icon-arrow-right"></i>',
        '</a>',
        '</li>',
        '</ul>',
        '</div>',
        '</div>',
    ].join('');
}

// 标签调用 html
function _callHtml(dataObj) {
    return [
        '<div class="edit-model-call-dialog">',
        '<div class="foxui-input-group foxui-vertical">',
        '<label>首页调用：</label>',
        `<input class="foxui-size-small" readOnly placeholder="" value="${dataObj.index}" />`,
        '</div>',
        '<div class="foxui-input-group foxui-vertical margin-top-32">',
        '<label>列表页调用：</label>',
        `<input class="foxui-size-small" readOnly placeholder="" value="${dataObj.list}" />`,
        '</div>',
        '<div class="foxui-input-group foxui-vertical margin-top-32">',
        '<label>列表页调用：</label>',
        `<input class="foxui-size-small" readOnly placeholder="" value="${dataObj.article}" />`,
        '</div>',
        '<div class="section-top-info margin-top-32">',
        '<p>请将相应标签复制并粘贴到对应模板文件中！</p>',
        '</div>',
        '</div>',
    ].join('');
}

// 校验颜色值是否合法
function _CheckIsColor(bgVal) {
    let type = '';
    if (/^rgb\(/.test(bgVal)) {
        //如果是rgb开头，200-249，250-255，0-199
        type = '^[rR][gG][Bb][(]([\\s]*(2[0-4][0-9]|25[0-5]|[01]?[0-9][0-9]?)[\\s]*,){2}[\\s]*(2[0-4]\\d|25[0-5]|[01]?\\d\\d?)[\\s]*[)]{1}$';
    } else if (/^rgba\(/.test(bgVal)) {
        //如果是rgba开头，判断0-255:200-249，250-255，0-199 判断0-1：0 1 1.0 0.0-0.9
        type = '^[rR][gG][Bb][Aa][(]([\\s]*(2[0-4][0-9]|25[0-5]|[01]?[0-9][0-9]?)[\\s]*,){3}[\\s]*(1|1.0|0|0.[0-9])[\\s]*[)]{1}$';
    } else if (/^#/.test(bgVal)) {
        //六位或者三位
        type = '^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$';
    } else if (/^hsl\(/.test(bgVal)) {
        //判断0-360 判断0-100%(0可以没有百分号)
        type = '^[hH][Ss][Ll][(]([\\s]*(2[0-9][0-9]|360|3[0-5][0-9]|[01]?[0-9][0-9]?)[\\s]*,)([\\s]*((100|[0-9][0-9]?)%|0)[\\s]*,)([\\s]*((100|[0-9][0-9]?)%|0)[\\s]*)[)]$';
    } else if (/^hsla\(/.test(bgVal)) {
        type = '^[hH][Ss][Ll][Aa][(]([\\s]*(2[0-9][0-9]|360|3[0-5][0-9]|[01]?[0-9][0-9]?)[\\s]*,)([\\s]*((100|[0-9][0-9]?)%|0)[\\s]*,){2}([\\s]*(1|1.0|0|0.[0-9])[\\s]*)[)]$';
    }
    if (!type || bgVal.match(new RegExp(type)) == null) {
        return false;
    } else {
        return true;
    }
}

// 按钮形状单选
$(document).on('click', '.button-radio .button-radio-item', function () {
    $(this).closest('.button-radio').find('.button-radio-item.is-active').removeClass('is-active');
    $(this).addClass('is-active');
});
