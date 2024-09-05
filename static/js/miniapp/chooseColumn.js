/*
 * @Descripttion :
 * @Author       : liuzhifang
 * @Date         : 2022-12-29 21:10:06
 * @LastEditors  : liuzhifang
 * @LastEditTime : 2023-01-01 19:53:36
 */
// let columnGroupList = [
//     {
//         id: 1,
//         name: '栏目',
//     },
//     {
//         id: 7,
//         name: '文章',
//         child: [
//             { id: 4, name: '案例' },
//             { id: 5, name: '资讯' },
//         ],
//     },
// ];

// let columnList = [
//     { id: 1, name: '我们' },
//     { id: 2, name: '案例' },
//     { id: 3, name: '资讯' },
//     { id: 4, name: '联系' },
//     { id: 5, name: '首页' },
//     { id: 6, name: '文章列表' },
//     { id: 7, name: '文章详情' },
//     { id: 8, name: '案例列表' },
//     { id: 9, name: '案例详情' },
//     { id: 10, name: '新闻' },
// ];
//
// let articleList = [
//     { id: 1, name: '文章列表与内容详情页设计' },
//     { id: 2, name: '文章列表与内容详情页设计' },
//     { id: 3, name: '文章列表与内容详情页设计' },
//     { id: 4, name: '文章列表与内容详情页设计' },
//     { id: 5, name: '文章列表与内容详情页设计' },
//     { id: 6, name: '文章列表与内容详情页设计' },
//     { id: 7, name: '文章列表与内容详情页设计' },
//     { id: 8, name: '文章列表与内容详情页设计' },
//     { id: 9, name: '文章列表与内容详情页设计' },
//     { id: 10, name: '文章列表与内容详情页设计' },
//     { id: 11, name: '文章列表与内容详情页设计' },
//     { id: 12, name: '文章列表与内容详情页设计' },
// ];

// $(document).on('click', '.foxcms-link .foxui-select-handle', function () {
//     let inputObj = $(this).find('input');
//   _chooseColumn('column', columnGroupList, columnList, inputObj);
// });

function _chooseColumn(type, groupList, dataList, obj=null) {
    // type: 1、文章(article); 2、栏目(column)
    foxui.dialog({
        title: '链接地址',
        content: _contentHtml(type, groupList, dataList),
        confirmText: '确定',
        cancelText: '取消',
        width: '1000px',
        marginTop: '10vh',
        buttonSize: 'small',
        buttonAlign: 'center',
        longButton: true,
        border: true,
        className: 'choose-column-dialog',
        confirm: function (callback) {
            let isActives = $('.choose-column-dialog  .column-item.is-active'),
                len = isActives.length;
            if (len > 0) {
                // 获取到选中项
                let id = isActives.attr('data-id'),
                    name = isActives.text();
                if(obj != null){
                    $(obj).val(name);
                    $(obj).attr('data-id', id);
                }
                callback();
            } else {
                foxui.message({
                    text: '必须先选择',
                    type: 'danger',
                });
            }
        },
    });
}

// $(document).on('click', '.choose-column-dialog .foxui-menu-item', function () {
//     let id = $(this).attr('data-id');
//     let name = $(this).text();
//     // 切换栏目
//     console.log(id, name);
//     // 测试使用(切换组)
//     $('.choose-column-dialog .right-part').empty().append(_rightPartHtml('article', articleList));
// });

$(document).on('click', '.choose-column-dialog  .column-item', function () {
    $('.choose-column-dialog  .column-item.is-active').removeClass('is-active');
    $(this).addClass('is-active');
});

function _contentHtml(type, groupList, dataList) {
    return [
        '<div class="foxui-row foxui-gutter-0">',
        '<div class="foxui-col-xs-5 foxui-col-sm-5 foxui-border-right">',
        '<div class="left-part">',
        `${_leftPartHtml(groupList)}`,
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-19 foxui-col-sm-19">',
        '<div class="right-part">',
        `${_rightPartHtml(type, dataList)}`,
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

function _leftPartHtml(groupList) {
    let arrhtml = [];
    groupList.forEach((item, idx) => {
        let html = '',
            itemHtml = '';
        if (item.child && item.child.length > 0) {
            item.child.forEach(column => {
                itemHtml += `<li class="foxui-menu-item${idx === 0 ? ' is-active' : ''}" data-id="${column.id}">${column.name}</li>`;
            });
            html = [
                '<li class="foxui-menu-submenu">',
                `<div class="foxui-menu-handle foxui-menu-icon">${item.name}</div>`,
                '<div class="foxui-menu-menu">',
                '<ul class="foxui-menu-slide">',
                `${itemHtml}`,
                '</ul>',
                '</div>',
                '</li>',
            ].join('');
        } else {
            html = [`<li class="foxui-menu-item${idx === 0 ? ' is-active' : ''}" data-id="${item.id}">${item.name}</li>`].join('');
        }
        arrhtml.push(html);
    });
    return ['<ul class="foxui-menu foxui-type-vertical">', `${arrhtml.join('')}`, '</ul>'].join('');
}

function _rightPartHtml(type, dataList) {
    if (type === 'column') {
        return _columnHtml(dataList);
    } else if (type === 'article') {
        return _articleHtml(dataList);
    }
}

function _columnHtml(dataList) {
    let htmlArr = [];
    dataList.forEach(item => {
        htmlArr.push('<div class="foxui-col-xs-4 foxui-col-sm-4">', `<div class="column-item column foxui-ellipsis" data-id="${item.id}">${item.name}</div>`, '</div>');
    });
    return '<div class="foxui-row foxui-gutter-4">' + htmlArr.join('') + '</div>';
}

function _articleHtml(dataList) {
    let htmlArr = [];
    dataList.forEach(item => {
        htmlArr.push('<div class="foxui-col-xs-12 foxui-col-sm-12">', `<div class="column-item article foxui-ellipsis" data-id="${item.id}">${item.name}</div>`, '</div>');
    });
    return '<div class="foxui-row foxui-gutter-4">' + htmlArr.join('') + '</div>';
}
