/*
 * @Descripttion : 栏目列表
 * @Author       : liuzhifang
 * @Date         : 2022-06-09 14:23:33
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2024-08-21 22:29:28
 */

let selectList =  window.parent.getColumModels();

// 全部折叠
$('#allCollapseBtn').click(function () {
    let $container = $('#collapseContainer'),
        $this = $(this),
        isActive = $this.is('.is-active');

    if (isActive) {
        let $openHandle = $container.find('.foxui-collapse-handle.is-active');
        $openHandle.click();
        $this.removeClass('is-active');
    } else {
        let $foldHandle = $container.find('.foxui-collapse-handle:not(.is-active)');
        $foldHandle.click();
        $this.addClass('is-active');
    }
});

// 根据折叠转换全部折叠按钮状态
$(document).on('click', '.foxui-collapse-handle', function () {
    let count = $('.foxui-collapse-handle').length,
        openLen = $('.foxui-collapse-handle.is-active').length;

    if (count === openLen) {
        $('#allCollapseBtn').addClass('is-active');
    } else {
        $('#allCollapseBtn').removeClass('is-active');
    }
});

// 添加一级栏目
$('#addFirstColumnBtn').click(function () {
    let $container = $('#collapseContainer');
    $container.append(_itemHtml({ level: 1, selectList }));
    $container.children('.foxui-collapse-item:last-child').slideDown('fast');
});

// 添加二级栏目
$(document).on('click', '.column-head-level-1 .add-btn', function () {


    let $this = $(this),
        $container = $this.closest('.foxui-collapse-item').children('.foxui-collapse-content').children('.foxui-collapse'),
        $handle = $this.closest('.foxui-collapse-head').find('.foxui-collapse-handle'),
        isActive = $handle.is('.is-active');
    if (!isActive) $handle.click();
    $container.append(_itemHtml({ level: 2, selectList }));
    $container.children('.foxui-collapse-item:last-child').slideDown('fast');
});

// 添加三级栏目
$(document).on('click', '.column-head-level-2 .add-btn', function () {
    let $this = $(this),
        $container = $this.closest('.foxui-collapse-item').children('.foxui-collapse-content').children('.foxui-collapse'),
        $handle = $this.closest('.foxui-collapse-head').find('.foxui-collapse-handle'),
        isActive = $handle.is('.is-active');
    if (!isActive) $handle.click();
    $container.append(_itemHtml({ level: 3, selectList }));
    $container.children('.foxui-collapse-item:last-child').slideDown('fast');
});

// 删除
// $(document).on('click', '.foxui-collapse .delete-btn', function () {
//     let $this = $(this),
//         $item = $this.closest('.foxui-collapse-item'),
//         len = $item.find('.foxui-collapse-content .foxui-collapse-item').length;
//     if (len > 0) {
//         foxui.message({
//             text: '此栏目包含子栏目，不可删除！',
//             type: 'danger',
//         });
//     }
//     $($item).slideUp('fast', function () {
//         $(this).remove();
//     });
// });

// 设置
$(document).on('click', '.foxui-collapse .set-btn', function () {
    // let id = $(this).closest('.foxui-collapse-head').children('.column-id').text();
    // window.location.href = `column_set.html?columnId=${id}`;
});

function _itemHtml({ id, level, selectList }) {
    let classList = ['column-item-level-1', 'column-item-level-2', 'column-item-level-3'];
    return [
        `<li class="foxui-collapse-item foxui-drag-item ${classList[level - 1]}" style="display:none">`,
        `${_headHtml({ id, level, selectList })}`,
        '<div class="foxui-collapse-content">',
        '<ul class="foxui-collapse foxui-drag-container"></ul>',
        '</div>',
        '</li>',
    ].join('');
}

function _headHtml({ id, level, selectList }) {
    let is_thumb = $('input[name="is_thumb"]').val();
    if(is_thumb == 1){
        return [
            `<div class="foxui-collapse-head foxui-drag-content column-head-level-${level}">`,
            '<div class="drag-handle">',
            '<i class="foxui-drag-handle foxui-icon-liebiao-o"></i>',
            '</div>',
            `<div class="column-id">${id || ''}</div>`,
            `${_collapseHandleHtml(level)}`,
            `${_columnHtml(level)}`,
            `${_picHtml()}`,
            `${_modelHtml(selectList)}`,
            `${_stateHtml()}`,
            `<div class="model foxui-align-center column-sid">-</div>`,
            `${_handleHtml()}`,
            '</div>',
        ].join('');
    }else{
        return [
            `<div class="foxui-collapse-head foxui-drag-content column-head-level-${level}">`,
            '<div class="drag-handle">',
            '<i class="foxui-drag-handle foxui-icon-liebiao-o"></i>',
            '</div>',
            `<div class="column-id">${id || ''}</div>`,
            `${_collapseHandleHtml(level)}`,
            `${_columnHtml(level)}`,
            `${_modelHtml(selectList)}`,
            `${_stateHtml()}`,
            `<div class="model foxui-align-center column-sid">-</div>>`,
            `${_handleHtml()}`,
            '</div>',
        ].join('');
    }
}

function _collapseHandleHtml(level) {
    let html = '';
    if (level < 3) {
        html = ['<div>', '<i class="foxui-collapse-handle foxui-icon-kaishi-f foxui-collapse-icon"></i>', '</div>'].join('');
    }
    return html;
}

function _columnHtml(level) {
    let levelArr = ['一级', '二级', '三级'],
        html = '';
    let nowLevel = $('input[name="level"]').val();
    if (level < 3 && nowLevel > level) {
        html = [
            '<div class="column">',
            `<div class="level">${levelArr[level - 1]}</div>`,
            '<div class="foxui-input-group column-title">',
            '<div class="foxui-input-suffix">',
            '<input class="foxui-size-mini" placeholder="请输入栏目名称" value="" />',
            '</div>',
            '</div>',

            '<button class="foxui-text-primary add-btn">',
            '<i class="foxui-icon-jiahao-o"></i>',
            `<span>添加${levelArr[level]}栏目</span>`,
            '</button>',

            '</div>',
        ].join('');

    } else {
        html = [
            '<div class="column">',
            `<div class="level">${levelArr[level - 1]}</div>`,
            '<div class="foxui-input-group column-title">',
            '<div class="foxui-input-suffix">',
            '<input class="foxui-size-mini" placeholder="请输入栏目名称" value="" />',
            '</div>',
            '</div>',
            '</div>',
        ].join('');
    }
    return html;
}

function _picHtml(imgList) {
    return [
        '<div class="pic">',
        '<div class="foxui-images foxui-images-small">',
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
        '</div>',
    ].join('');
}

function _modelHtml(selectList) {
    let htmlArr = [];
    let firstItem = {"id":"", "title":"","sid":""};
    if(selectList.length > 0){
        firstItem = selectList[0];
    }
    selectList.forEach(item => {
        htmlArr.push(`<li class="foxui-select-item async" data-id="${item.id}">${item.title}</li>`);
    });
    return [
        '<div class="foxui-select model">',
        '<div class="foxui-select-handle foxui-select-icon">',
        '<input class="foxui-select-input foxui-size-mini" readonly="readonly" placeholder="选择模型" value="'+firstItem.title+'" data-id="'+firstItem.id+'"/>',
        '<i class="foxui-icon-close-circle"></i>',
        '</div>',
        '<div class="foxui-select-menu">',
        '<ul class="foxui-select-slide">',
        `${htmlArr.join('')}`,
        '</ul>',
        '</div>',
        '</div>',
    ].join('');
}

function _stateHtml() {
    return [
        '<div class="state">',
        '<div class="foxui-switch is-checked">',
        '<input type="checkbox" checked="checked" value="" class="foxui-switch-input" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '</div>',
    ].join('');
}

function _handleHtml() {
    return [
        '<div class="handle display-flex">',
        '<button class="foxui-size-mini color-primary set-btn font-size-14 is-disabled">',
        '<i class="foxui-icon-shezhi-o"></i>',
        '<span>设置</span>',
        '</button>',
        '<button class="foxui-size-mini color-primary delete-btn font-size-14" onclick="deleteCollapseItem(event)">',
        '<i class="foxui-icon-shanchu-o"></i>',
        '<span>删除</span>',
        '</button>',
        '<button class="foxui-size-mini color-primary content-btn font-size-14 is-disabled">',
        '<i class="foxui-icon-shuji-o"></i>',
        '<span>内容</span>',
        '</button>',
        '<button class="foxui-size-mini color-primary view-btn font-size-14 is-disabled">',
        '<i class="foxui-icon-kejian-o"></i>',
        '<span>浏览</span>',
        '</button>',
        '</div>',
    ].join('');
}
