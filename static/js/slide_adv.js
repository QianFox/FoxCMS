
// 添加广告位
$(document).on('click', '#addAdvertGroupBtn', function () {
    $('.foxui-collapse').append(advertGroupHtml({}));
});

// 添加字段
$(document).on('click', '.add-btn', function () {
    let columnId = $('input[name="columnId"]').val();
    let id = $(this).attr('id');
    window.location.href = `addField?columnId=${columnId}&type=1&id=${id}`;
});

// 添加广告
$(document).on('click', '.add-advert-btn', function () {
    let $handle = $(this).siblings('.handle-box').find('.foxui-collapse-handle'),
        isActive = $handle.is('.is-active');

    if (!isActive) $handle.click();
    $(this).closest('.foxui-collapse-item').find('.adverts-list-container').append(advertHtml({}));
});

// 标签调用
$(document).on('click', '.call-btn', function () {
    let id = $(this).attr('id');
    let jscript = $(this).attr('j-script');

    // 异步获取数据(生成html);
    let flag = `{fox:adv pid='${id}'}
    <a href='[$ad.link]'><img src='[$ad.img_url]' alt='[$ad.title]' /></a>
{/fox:adv}`;

    let jscriptStr = '<script type=&quot;text/javascript&quot; src=&quot;' + jscript + '&quot;></script>';
    let dataObj = {
        flag: flag,
        jscript: jscriptStr,
    };
    foxui.dialog({
        title: '标签调用',
        content: _callHtml(dataObj),
        confirmText: '确认',
        buttonSize: 'small',
        buttonAlign: 'center',
        width: '710px',
        longButton: true,
        border: true,
        confirm: function (callback) {
            callback();
        },
    });
});

// 广告位 HTML
function advertGroupHtml({ gId, gName, isShow, title, linkUrl, imgUrl, imgId }) {
    return [
        '<li class="foxui-collapse-item">',
        '<div class="foxui-collapse-head">',
        `<div class="collapse-column">${gId || ''}</div>`,
        '<div class="collapse-column foxui-display-flex">',
        '<div class="handle-box">',
        '<i class="foxui-collapse-handle foxui-icon-kaishi-f foxui-collapse-icon"></i>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-suffix">',
        `<input class="foxui-size-small" maxlength="30" placeholder="请输入广告位名称" value="${gName || ''}" name="name"/>`,
        '<i class="foxui-suffix-icon foxui-suffix-count">0/30</i>',
        '</div>',
        '</div>',
        '<button class="foxui-text-primary add-advert-btn foxui-margin-left-16">',
        '<i class="foxui-icon-jiahao-o"></i>',
        '<span>添加广告</span>',
        '</button>',
        '</div>',
        '<div class="collapse-column">',
        `<div class="foxui-switch ${isShow ? 'is-checked' : ''} adv_status">`,
        `<input type="checkbox" ${isShow ? 'checked="checked"' : ''} value="" class="foxui-switch-input" />`,
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '</div>',
        '<div class="collapse-column foxui-display-flex foxui-align-items-center">',
        '<button class="foxui-size-mini foxui-color-primary set-btn font-size-14 is-disabled">',
        '<i class="foxui-icon-shezhi-o"></i>',
        '<span>设置</span>',
        '</button>',
        '<button class="foxui-size-mini foxui-color-primary delete-btn font-size-14" del-type="1">',
        '<i class="foxui-icon-shanchu-o"></i>',
        '<span>删除</span>',
        '</button>',
        '<button class="foxui-size-mini foxui-color-primary call-btn font-size-14">',
        '<i class="foxui-icon-kejian-o"></i>',
        '<span>调用</span>',
        '</button>',
        '</div>',
        '</div>',
        '<div class="foxui-collapse-content foxui-padding-left-40 foxui-padding-right-40 foxui-margin-top-24">',
        '<div class="foxui-row foxui-gutter-8 adverts-list-container">',
        `${advertHtml({ title, linkUrl, imgUrl, imgId })}`,
        '</div>',
        '</div>',
        '</li>',
    ].join('');
}

// 广告 HTML
function advertHtml({ title, linkUrl, imgUrl, imgId }) {
    return [
        '<div class="foxui-col-xs-8 foxui-col-sm-8 foxui-col-xxl-6 foxui-animate-fadeInDown advertising">',
        '<div class="adverts-item">',
        `${imagesHtml(imgUrl, imgId)}`,
        '<div class="adverts-text-box">',
        '<div class="foxui-input-group foxui-margin-top-8">',
        '<label>标题：</label>',
        '<div class="foxui-input-suffix">',
        `<input class="foxui-size-small" maxlength="30" placeholder="请输入标题" value="${title || ''}" name="title"/>`,
        '<i class="foxui-suffix-icon foxui-suffix-count">0/30</i>',
        '</div>',
        '</div>',
        '<div class="foxui-input-group foxui-margin-top-12">',
        '<label>链接：</label>',
        '<div class="foxui-input-suffix">',
        `<input class="foxui-size-small" placeholder="请输入跳转地址" value="${linkUrl || ''}" name="link"/>`,
        '</div>',
        '</div>',
        '<div class="foxui-input-group foxui-margin-top-12 foxui-padding-bottom-8 foxui-padding-top-8 foxui-justify-content-end">',
        '<button class="foxui-size-mini foxui-color-primary set-btn font-size-14 display-none">',
        '<i class="foxui-icon-shezhi-o"></i>',
        '<span>设置</span>',
        '</button>',
        '<button class="foxui-size-mini foxui-color-primary delete-btn font-size-14" del-type="2">',
        '<i class="foxui-icon-shanchu-o"></i>',
        '<span>删除</span>',
        '</button>',
        '</div>',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

// 图片 THML
function imagesHtml(imgUrl, imgId) {
    let htmlArr = [];
    if (imgUrl) {
        htmlArr = [
            '<div class="foxui-images">',
            '<div class="foxui-images-card">',
            '<ul class="foxui-images-list ">',
            '<li class="foxui-images-item foxui-animate-fadeInDown">',
            '<div class="content">',
            `<img data-id="${imgId}" src="${imgUrl}">`,
            '<span class="replace">替换</span>',
            '<i class="foxui-icon-cuowu-f delete"></i>',
            '</div>',
            '</li>',
            '<div class="foxui-images-handle" style="display: none;">',
            '<div class="foxui-images-handle-inner">',
            '<i class="foxui-icon-jiahao-o"></i>',
            '<span class="text">添加图片</span>',
            '</div>',
            '</div>',
            '</ul>',
            '</div>',
            '</div>',
        ];
    } else {
        htmlArr = [
            '<div class="foxui-images">',
            '<div class="foxui-images-card">',
            '<ul class="foxui-images-list">',
            '<div class="foxui-images-handle">',
            '<div class="foxui-images-handle-inner">',
            '<i class="foxui-icon-jiahao-o"></i>',
            '<span class="text">添加图片</span>',
            '</div>',
            '</div>',
            '</ul>',
            '</div>',
            '</div>',
        ];
    }
    return htmlArr.join('');
}

// 标签调用 html
function _callHtml(dataObj) {
    return [
        '<div class="edit-model-call-dialog">',

        '<div class="foxui-input-group foxui-vertical">',
        '<label>标签adv调用：</label>',

        `<div class="foxui-textarea">`,
        ` <textarea autocomplete="off" rows="2" style="min-height: 110px;">${dataObj.flag}</textarea>`,
        `</div>`,
        '</div>',
        '<div class="foxui-input-group foxui-vertical margin-top-32">',
        '<label>JavaScript：</label>',
        `<input class="foxui-size-small"  placeholder="" value="${dataObj.jscript}" />`,
        '</div>',
        '<div class="section-top-info margin-top-32">',
        '<p>请将相应标签复制并粘贴到对应模板文件中！</p>',
        '</div>',
        '</div>',
    ].join('');
}
