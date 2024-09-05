/*
 * @Descripttion : FOXCMS是一款高效的PHP多端跨平台内容管理系统
 * @Author       : FoxCMS Team
 * @Date         : 2022-12-23 00:41:39
 * @version      : V1.08
 * @copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2022-12-24 11:30:22
 */

// let bannerSelectList = [
//     { id: 1, name: '首页' },
//     { id: 2, name: '我们' },
//     { id: 3, name: '案例' },
// ];

// 切换组件
$(document).on('click', '.page-container .component', function () {
    let $this = $(this),
        isActive = $this.is('.is-active');

    if (!isActive) {
        $('.page-container .component.is-active').removeClass('is-active');
        $this.addClass('is-active');
        $('.component-pannel').hide();
        $(`#${$this.attr('data-target')}Component`).show();
    }
});

// 添加 banner 图片
$(document).on('click', '#addBannerBtn', function () {
    $('#bannerContainer').append(_bannerItemHtml());
    $('#bannerContainer').find('.list-item:last-child').slideDown();
});

// 添加 iconnav 按钮
$(document).on('click', '#addIconnavBtn', function () {
    $('#iconnavContainer').append(_iconnavItemHtml());
    $('#iconnavContainer').find('.list-item:last-child').slideDown();
});

// 添加 bannernav 图片
$(document).on('click', '#addBannernavBtn', function () {
    $('#bannernavContainer').append(_bannernavItemHtml());
    $('#bannernavContainer').find('.list-item:last-child').slideDown();
});

// 删除按钮
// $(document).on('click', '.list-item .delete-btn', function () {
//     foxui.dialog({
//         title: '确认',
//         content: '您确定要做删除吗',
//         confirmText: '删除',
//         cancelText: '取消',
//         buttonType: 'danger',
//         confirm: callback => {
//             $(this)
//                 .closest('.list-item')
//                 .slideUp(function () {
//                     $(this).remove();
//                 });
//             callback();
//         },
//     });
// });

// 添加 banner 图片 html
function _bannerItemHtml() {
    return [
        '<li class="list-item foxui-drag-item bg" style="display:none">',
        '<div class="display-flex">',
        '<div class="foxui-drag-handle"><span>&#9776;</span></div>',
        '<div class="section-main-item foxui-margin-left-4">',
        '<div class="foxui-input-group">',
        '<div class="inline-box">',
        '<div class="foxui-images">',
        '<div class="foxui-images-card">',
        '<ul class="foxui-images-list">',
        '<div class="foxui-images-handle">',
        '<div class="foxui-images-handle-inner"><i class="foxui-icon-jiahao-o"></i></div>',
        '</div>',
        '</ul>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="input-box foxui-margin-left-40 foxui-margin-top-4">',
        '<div class="foxui-select foxcms-link">',
        '<div class="foxui-select-handle foxui-select-icon">',
        '<input class="foxui-select-input foxui-size-small" placeholder="请选跳转链接" value=""/>',
        '</div>',
        '<p class="input-box-info foxui-margin-left-0 foxui-margin-top-12">建议图片宽度750px，高度跟随第一张图片的高度变化</p>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="delete-btn"><button class="foxui-solid-danger foxui-size-small" onclick="slideDelete(event)">删除</button></div>',
        '</li>',
    ].join('');
}
// function _bannerItemHtml() {
//     return [
//         '<li class="list-item foxui-drag-item bg" style="display:none">',
//         '<div class="display-flex">',
//         '<div class="foxui-drag-handle"><span>&#9776;</span></div>',
//         '<div class="section-main-item foxui-margin-left-4">',
//         '<div class="foxui-input-group">',
//         '<div class="inline-box">',
//         '<div class="foxui-images">',
//         '<div class="foxui-images-card">',
//         '<ul class="foxui-images-list">',
//         '<div class="foxui-images-handle">',
//         '<div class="foxui-images-handle-inner"><i class="foxui-icon-plus"></i></div>',
//         '</div>',
//         '</ul>',
//         '</div>',
//         '</div>',
//         '</div>',
//         '<div class="input-box foxui-margin-left-40 foxui-margin-top-4">',
//         '<div class="foxui-select">',
//         '<div class="foxui-select-handle foxui-select-icon">',
//         '<input class="foxui-select-input foxui-size-small" readonly="readonly" placeholder="请选跳转链接栏目" value=""/>',
//         '<i class="foxui-icon-close-circle"></i>',
//         '</div>',
//         '<div class="foxui-select-menu">',
//         '<ul class="foxui-select-slide">',
//         `${_selectItemHtml(bannerSelectList)}`,
//         '</ul>',
//         '</div>',
//         '</div>',
//         '<p class="input-box-info foxui-margin-left-0 foxui-margin-top-12">建议图片宽度750px，高度跟随第一张图片的高度变化</p>',
//         '</div>',
//         '</div>',
//         '</div>',
//         '</div>',
//         '<div class="delete-btn"><button class="foxui-solid-danger foxui-size-small" onclick="slideDelete(event)">删除</button></div>',
//         '</li>',
//     ].join('');
// }

// 添加 iconnav 按钮 html
function _iconnavItemHtml() {
    return [
        '<li class="list-item foxui-drag-item bg" style="display:none">',
        '<div class="display-flex">',
        '<div class="foxui-drag-handle"><span>&#9776;</span></div>',
        '<div class="section-main-item foxui-margin-left-20">',
        '<div class="foxui-input-group foxui-align-items-start">',
        '<div class="input-label foxui-margin-top-12"><label>按钮图片：</label></div>',
        '<div class="inline-box">',
        '<div class="foxui-images foxui-images-small">',
        '<div class="foxui-images-card">',
        '<ul class="foxui-images-list">',
        '<div class="foxui-images-handle">',
        '<div class="foxui-images-handle-inner"><i class="foxui-icon-plus"></i><span class="text">添加图片</span></div>',
        '</div>',
        '</ul>',
        '</div>',
        '</div>',
        '<p class="input-box-info foxui-margin-left-0 foxui-margin-top-4">建议图片尺寸为200x200，比例为1:1</p>',
        '</div>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="input-label"><label>按钮文字：</label></div>',
        '<div class="input-box foxui-margin-top-4">',
        '<div class="foxui-input-suffix">',
        '<input class="foxui-size-small" maxlength="10" placeholder="请输入导航名称" value="首页" />',
        '<i class="foxui-suffix-icon foxui-suffix-count">0/10</i>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="input-label"><label>跳转链接：</label></div>',
        '<div class="input-box foxui-margin-top-4">',
        '<div class="foxui-select foxcms-link">',
        '<div class="foxui-select-handle foxui-select-icon">',
        '<input class="foxui-select-input foxui-size-small" readonly="readonly" placeholder="请选择跳转链接栏目" value=""/>',
        '<i class="foxui-icon-close-circle"></i>',
        '</div>',
        '<div class="foxui-select-menu">',
        '<ul class="foxui-select-slide">',
        `${_selectItemHtml(bannerSelectList)}`,
        '</ul>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="delete-btn"><button class="foxui-solid-danger foxui-size-small" onclick="navgroupDelete(event)">删除</button></div>',
        '</li>',
    ].join('');
}

// 添加 bannernav 图片 html
function _bannernavItemHtml() {
    return [
        '<li class="list-item foxui-drag-item bg" style="display:none">',
        '<div class="display-flex">',
        '<div class="foxui-drag-handle"><span>&#9776;</span></div>',
        '<div class="section-main-item foxui-margin-left-4">',
        '<div class="foxui-input-group">',
        '<div class="inline-box">',
        '<div class="foxui-images">',
        '<div class="foxui-images-card">',
        '<ul class="foxui-images-list">',
        '<div class="foxui-images-handle">',
        '<div class="foxui-images-handle-inner"><i class="foxui-icon-plus"></i></div>',
        '</div>',
        '</ul>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="input-box foxui-margin-left-40 foxui-margin-top-4">',
        '<div class="foxui-select">',
        '<div class="foxui-input-suffix">',
        '<input class="foxui-size-small" maxlength="10" placeholder="请输入名称" value="" />',
        '<i class="foxui-suffix-icon foxui-suffix-count">0/10</i>',
        '</div>',
        '</div>',
        '<div class="foxui-select foxui-margin-top-12 foxcms-link">',
        '<div class="foxui-select-handle foxui-select-icon">',
        '<input class="foxui-select-input foxui-size-small" readonly="readonly" placeholder="请选择跳转链接栏目" value=""/>',
        '<i class="foxui-icon-close-circle"></i>',
        '</div>',
        '<div class="foxui-select-menu">',
        '<ul class="foxui-select-slide">',
        `${_selectItemHtml(bannerSelectList)}`,
        '</ul>',
        '</div>',
        '</div>',
        '<p class="input-box-info foxui-margin-left-0 foxui-margin-top-12">建议图片宽度750px，高度跟随第一张图片的高度变化</p>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
        '<div class="delete-btn"><button class="foxui-solid-danger foxui-size-small" onclick="picwindowDelete(event)">删除</button></div>',
        '</li>',
    ].join('');
}

// banner 图片 select html
function _selectItemHtml(dataList) {
    let htmlArr = [];
    dataList.forEach(item => {
        htmlArr.push(`<li class="foxui-select-item" data-id="${item.id}">${item.name}</li>`);
    });
    return htmlArr.join('');
}
