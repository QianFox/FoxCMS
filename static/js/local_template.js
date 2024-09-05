/*
 * @Descripttion :
 * @Author       : liuzhifang
 * @Date         : 2022-08-10 14:35:18
 * @LastEditors  : liuzhifang
 * @LastEditTime : 2022-08-10 15:57:14
 */

// 已安装
// const webInstalledDataList = [
//     // type 1、只有电脑端； 2、只有移动端； 3、有电脑端和移动端； 4、自适应
//     { id: 1, title: '广告公司网站源码传媒公司', serial: 'FOX0001', src: 'https://file.liuzhifang.com/web/web01.jpg', isUsed: 1, type: '3' },
// ];

// 未安装
// const webUninstalledDataList = [
//     { id: 1, title: '广告公司网站源码传媒公司', author: '黔狐掌柜', src: 'https://file.liuzhifang.com/web/web05.jpg', type: '4' },
// ];

//初始化模板查询
function templateInit(){
    let tabContent = $(".foxui-tabs-header .foxui-tabs-item.is-active").html();
    let runStatus = 2;
    let keyword = $('input[name="keyword"]').val();
    if(tabContent.indexOf("已安装模板") != -1){
        runStatus = 1;
    }
    let datas = {
        "keyword": $.trim(keyword),
        "bcid": bcid,
        runStatus
    };
    initTemplateList(datas, runStatus);//查询
}


/**
 * 初始化表格
 */
function initTemplateList(param, runStatus) {
    let bcid = $("input[name='bcid']").val();
    param.bcid = bcid;
    param.runStatus = runStatus;
    let data = getTemplate(param);
    if(runStatus == 1){//已安装
        initInstalledTemplateList(data); // 已安装模板
    }else if(runStatus == 2){//未安装
        initUninstalledTemplateList(data); // 未安装模板
    }

};

//获取模型数据
function getTemplate(datas){
    let data= null;
    $.ajax({
        type: "post",
        url: ADMIN_PATH + '/LocalTemplate/index',
        dataType: "json",
        data: datas,
        async:false,
        success: function(res) {
            if (res.code == 1 && res.data) {
                data = res.data;
            }
        },
        error: function(res) {
            console.log("查询模板失败。。。")
        }
    });
    return data;
}


/**
 * @description: 已安装模板初始化
 * @param {*}
 * @return {*}
 * @Date: 2022-01-18 14:58:47
 */
function initInstalledTemplateList(data) {

    let pageSize = data.per_page,
        total = data.total,
        currentPage = data.current_page;
    let dataList = data.data;
    // 初始化列表数据
    _appendToInstalledList(dataList);
    // 初始化分页
    foxui.pagination(
        {
            el: '#installedPagination',
            currentPage: currentPage,
            total: total,
            onchange: function ({ currentPage, pageSize, total }, callback) {
                // 获取页码相关数据，添加异步请求
                callback({ total, pageSize, currentPage });
                let param = {currentPage, pageSize};
                initTemplateList(param,1);//已安装
            },
        },
        {
            type: 'plain',
            pageSize: pageSize,
            isShowJump: true,
            isShowTotal: true,
            isShowSize: true,
        }
    );
}

/**
 * @description: 未安装模板初始化
 * @param {*} data
 * @return {*}
 * @Date: 2022-01-18 16:16:53
 */
function initUninstalledTemplateList(data) {
    let pageSize = data.per_page,
        total = data.total,
        currentPage = data.current_page;
    let dataList = data.data;
    $("#unCount").html(total);//未安装的模板
    // 初始化列表数据
    _appendToUninstalledList(dataList);
    // 初始化分页
    foxui.pagination(
        {
            el: '#uninstalledPagination',
            currentPage: currentPage,
            total: total,
            onchange: function ({ currentPage, pageSize, total }, callback) {
                callback({ total, pageSize, currentPage });
                let param = {currentPage, pageSize};
                initTemplateList(param,2);//已安装
            },
        },
        {
            type: 'plain',
            pageSize: pageSize,
            isShowJump: true,
            isShowTotal: true,
            isShowSize: true,
        }
    );
}

/**
 * @description: 已安装模板->使用模板
 * @param {*}
 * @return {*}
 * @Date: 2022-01-18 16:37:43
 */
$(document).on('click', '.template-item .use', function () {
    let $this = $(this),
        $curItem = $this.closest('.template-item'),
        $actItem = $this.closest('.foxui-row').find('.template-item').filter('.is-active');

    foxui.dialog({
        title: '<i class="foxui-icon-info-solid" style="color:#ffcc00; font-size:20px; margin-right: 6px;"></i><span>确认提示</span>',
        content: '<span style="margin-left: 26px;">启用此模板作为前端网站模板，是否继续？</span>',
        confirmText: '确定',
        cancelText: '取消',
        confirm: function (callback) {
            const id = $this.closest('.template-item').attr('data-id');
            callback();
            foxui.loading();

            // 异步提交数据
            console.log(id);
            setTimeout(() => {
                $actItem.removeClass('is-active');
                $curItem.addClass('is-active');
                foxui.closeLoading();
            }, 1500);
        },
    });
});

/**
 * @description: 已安装模板->设置模板
 * @param {*}
 * @return {*}
 * @Date: 2022-01-18 16:43:45
 */
$(document).on('click', '.template-item .set', function () {
    const id = $(this).closest('.template-item').attr('data-id');
    window.location.href = `local_template_set.html?id=${id}`;
});

/**
 * @description: 已安装模板->导出模板
 * @param {*}
 * @return {*}
 * @Date: 2022-08-10 15:08:34
 */
$(document).on('click', '.template-item .export', function () {
    alert('导出模板');
});

/**
 * @description: 已安装模板->卸载模板
 * @param {*} event
 * @return {*}
 * @Date: 2022-01-20 09:43:46
 */
$(document).on('click', '.template-item .uninstall', function () {
    const id = $(this).closest('.template-item').attr('data-id');
    window.location.href = `uninstall_template.html?id=${id}`;
});

/**
 * @description: 未安装模板->安装模板
 * @param {*} event
 * @return {*}
 * @Date: 2022-01-19 19:49:32
 */
$(document).on('click', '.template-item .install', function () {
    const id = $(this).closest('.template-item').attr('data-id');
    window.location.href = `install_template.html?id=${id}`;
});

/**
 * @description: 未安装模板->删除模板
 * @param {*} event
 * @return {*}
 * @Date: 2022-01-19 19:51:50
 */
$(document).on('click', '.template-item .delete', function () {
    const $this = $(this),
        id = $this.closest('.template-item').attr('data-id');
    foxui.dialog({
        title: '删除',
        content: '您确定要删除此模板吗',
        confirmText: '删除',
        cancelText: '取消',
        buttonType: 'danger',
        confirm: function (callback) {
            callback();
            // 添加异步请求，删除数据
            console.log(id);
            $this.closest('.foxui-col-sm-4').remove();
            foxui.message({
                text: '删除成功',
                type: 'success',
            });
        },
    });
});

/* 子方法
 ---------------------------------------------------------------------------------------------------- */
/**
 * @description: 更新已安装模板列表
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-18 14:55:04
 */
function _appendToInstalledList(dataList) {
    const html = _listInstalledHtml(dataList);
    $('.foxui-tabs .foxui-tabs-pane .installed').empty().append(html);
}

/**
 * @description: 更新未安装模板列表
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-18 16:14:59
 */
function _appendToUninstalledList(dataList) {
    const html = _listUninstalledHtml(dataList);
    $('.foxui-tabs .foxui-tabs-pane .uninstalled').empty().append(html);
}

/**
 * @description: 生成已安装模板列表 html
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-18 14:55:23
 */
function _listInstalledHtml(dataList) {
    let htmlArr = [];
    dataList.forEach(item => {
        htmlArr.push(
            [
                '<div class="foxui-col-xs-4 foxui-col-sm-4">',
                `<div class="template-item ${item.run_status === 1 ? 'is-active' : ''}" data-id="${item.id}">`,
                '<div class="template-main">',
                `<img src="${item.thumb_url || ''}" />`,
                '</div>',
                '<div class="template-footer">',
                '<div class="serial-number">',
                '<div class="left trapezoid">',
                `<span class="serial">${item.code || ''}</span>`,
                '</div>',
                '<div class="right">',
                `${_typeHtml(item.type)}`,
                '</div>',
                '</div>',
                `<div class="title">${item.title || ''}</div>`,
                '</div>',
                '<div class="template-mask">',
                '<div class="center-btns">',
                '<button class="foxui-size-medium foxui-solid-primary use">启用模板</button>',
                '</div>',
                '<div class="btn-list">',
                '<button class="foxui-solid-info foxui-size-mini foxui-solid-primary set">',
                '<i class="foxui-icon-shezhi-o"></i>',
                '</button>',
                '<button class="foxui-solid-info foxui-size-mini foxui-solid-primary export">',
                '<i class="foxui-icon-shangchuan-o"></i>',
                '</button>',
                '<button class="foxui-solid-info foxui-size-mini foxui-solid-primary uninstall">',
                '<i class="foxui-icon-shanchu-o"></i>',
                '</button>',
                '</div>',
                '<i class="foxui-icon-mark-top-center mark inuse">启用</i>',
                '</div>',
                '</div>',
                '</div>',
            ].join('')
        );
    });
    return htmlArr.join('');
}

/**
 * @description: 生成未安装模板列表 html
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-18 16:14:24
 */
function _listUninstalledHtml(dataList) {
    let htmlArr = [];
    dataList.forEach(item => {
        htmlArr.push(
            [
                '<div class="foxui-col-xs-4 foxui-col-sm-4">',
                `<div class="template-item" data-id="${item.id}">`,
                '<div class="template-main">',
                `<img src="${item.thumb_url || ''}" />`,
                '</div>',
                '<div class="template-footer">',
                '<div class="serial-number">',
                '<div class="left">',
                `<span class="author">作者：${item.author || ''}</span>`,
                '</div>',
                '<div class="right">',
                `${_typeHtml(item.type)}`,
                '</div>',
                '</div>',
                `<div class="title">${item.title || ''}</div>`,
                '</div>',
                '<div class="template-mask">',
                '<div class="center-btns">',
                '<button class="foxui-size-medium foxui-solid-primary install"">安装模板</button>',
                '<button class="foxui-size-medium foxui-solid-danger delete">删除模板</button>',
                '</div>',
                '</div>',
                '</div>',
                '</div>',
            ].join('')
        );
    });
    return htmlArr.join('');
}

/**
 * @description: 模板类型 html
 * @param {*} type
 * @return {*}
 * @Date: 2022-01-18 15:14:49
 */
function _typeHtml(type) {
    switch (type) {
        case '1':
            return `<i class="foxui-icon-pc"></i>`;
        case '2':
            return `<i class="foxui-icon-phone"></i>`;
        case '3':
            return `<i class="foxui-icon-pc"></i><i class="foxui-icon-phone"></i>`;
        case '4':
            return `<i class="foxui-icon-pc-phone"></i>`;
        default:
            return '';
    }
}
