/*
 * @Descripttion :
 * @Author       : liuzhifang
 * @Date         : 2022-08-11 11:01:01
 * @LastEditors  : liuzhifang
 * @LastEditTime : 2022-08-11 16:28:56
 */

/**
 * 参数编辑点击
 * @param event
 */
function clickParamEdit(obj){
    $(".param-box").toggle();
}

/**
 * 拼接产品参数数据
 * @param attrParamId
 */
function initAttrParams(attrParamId){
    //产品id
    let id = $("input[name='id']").val();
    if(id != undefined){//编辑
        foxui.dialog({
            title: '参数替换',
            content: `您确定要替换所有预设参数吗`,
            cancelText: '取消',
            confirmText: '确定',
            type: 'danger',
            confirm: function (callback) {
                getAttrParams(attrParamId);
                callback();
            },
            cancel: function () {},
        });
    }else{
        getAttrParams(attrParamId);
    }
}

/**
 * @description: 绑定编辑参数组
 * @param {*}
 * @return {*}
 * @Date: 2022-01-14 16:50:22
 */
function paramGroupEdit(event){
    event.stopPropagation();//阻止冒泡
    const id = $(event.target).closest('.foxui-drag-item').attr('data-id');
    const title = $(event.target).closest('.foxui-drag-content').find('.parts-name').text();
    modifyParamGroup(id, title);
}

// 获取数据后整体更新参数表格（异步获取数据） 注：typeId：11、默认输入； 12、手动输入； 13、下拉选择
// addParamInit([
//     { id: 1, title: '品牌', typeId: 11, value: '黔狐' },
//     { id: 2, title: '规格', typeId: 11, value: '件' },
//     { id: 3, title: '产地', typeId: 12, value: '' },
//     { id: 4, title: '颜色', typeId: 11, value: '黑色' },
//     { id: 5, title: '保质期', typeId: 11, value: '90天' },
//     { id: 6, title: '厚薄', typeId: 11, value: '超薄型' },
//     { id: 7, title: '生产企业', typeId: 11, value: '贵州黔狐科技股份有限公司' },
//     {
//         id: 8,
//         title: '产品颜色',
//         typeId: 13,
//         value: '黑色',
//         valueId: 1,
//         selectList: [
//             { id: 1, text: '黑色' },
//             { id: 2, text: '红色' },
//         ],
//     },
// ]);

/**
 * @description: 参数表格初始化 或 整体更新
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-14 14:48:16
 */
function addParamInit(dataList) {
    $('#paramTable .foxui-table-tbody').empty().append(_addParamHtml(dataList));
}

/**
 * @description: 绑定删除参数 -> 右侧
 * @param {*}
 * @return {*}
 * @Date: 2022-01-14 15:26:42
 */
$(document).on('click', '#paramTable button.delete', function () {
    const $item = $(this).closest('.foxui-table-tr'),
        id = $item.attr('data-id'),
        title = $item.find('.param-title input').val();

    foxui.dialog({
        title: '删除',
        content: `您确定要删除 ${title} 参数吗`,
        confirmText: '删除',
        cancelText: '取消',
        buttonType: 'danger',
        confirm: function (callback) {
            callback();
            // 添加异步请求
            $item.slideUp('fast', function () {
                $(this).remove();
            });
            foxui.message({
                text: '删除成功',
                type: 'success',
            });
        },
    });
});

/**
 * @description: 新增参数 -> 右侧
 * @param {*}
 * @return {*}
 * @Date: 2022-01-14 15:18:53
 */
$(document).on('click', '#addParam button', function () {
    const $tbody = $('#paramTable .foxui-table-tbody');
    $tbody.append(_addParamHtml([{}]));
    $tbody.find('.foxui-table-tr:last-child').css('display', 'none').slideDown('fast');
});

/**
 * @description: 绑定新增参数组 -> 左侧
 * @param {*}
 * @return {*}
 * @Date: 2022-01-14 16:37:36
 */
$(document).on('click', '#addParamGroup', function () {
    modifyParamGroup();
});

/**
 * @description: 绑定编辑参数组 -> 左侧
 * @param {*}
 * @return {*}
 * @Date: 2022-01-14 16:50:22
 */
$(document).on('click', '#paramGroupTable .edit', function () {
    const id = $(this).closest('.foxui-drag-item').attr('data-id');
    const title = $(this).closest('.foxui-drag-content').find('.parts-name').text();
    modifyParamGroup(id, title);
});

/**
 * @description: 绑定删除参数组
 * @param {*}
 * @return {*}
 * @Date: 2022-01-14 16:24:35
 */
$(document).on('click', '#paramGroupTable button.delete', function () {
    const $item = $(this).closest('.foxui-drag-item');
    const id = $item.attr('data-id');
    const title = $item.find('.parts-name').text();
    foxui.dialog({
        title: '删除',
        content: `您确定要删除 ${title} 所有参数吗`,
        cancelText: '取消',
        confirmText: '删除',
        buttonType: 'danger',
        confirm: function (callback) {
            callback();
            // 添加异步请求
            $item.slideUp('fast', function () {
                $(this).remove();
            });
            foxui.message({
                text: '删除成功',
                type: 'success',
            });
        },
        cancel: function () {},
    });
});

/**
 * @description: 添加参数 html -> 右侧
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-14 14:40:18
 */
function _addParamHtml(dataList) {
    dataList = dataList || [];
    let htmlArr = [];
    dataList.forEach(item => {
        htmlArr.push(`
            <li class="foxui-table-tr foxui-drag-item" data-id="${item.id || ''}">
                <div class="foxui-table-td">
                    <span class="foxui-drag-handle foxui-icon-liebiao-o"></span>
                </div>
                <div class="foxui-table-td param-title">
                    <input class="foxui-size-small" value="${item.title || ''}" />
                </div>
                <div class="foxui-table-td param-val">
                    ${_judgeTypeHtml(item)}
                </div>
                <div class="foxui-table-td">
                    <button class="foxui-text-primary foxui-size-small delete">删除</button>
                </div>
            </li>
        `);
    });
    return htmlArr.join('');
}

/**
 * @description: 根据判断typeId，生成相应的表单
 * @param {*} item
 * @return {*}
 * @Date: 2022-02-09 16:20:49
 */
function _judgeTypeHtml(item) {
    const { typeId, value, valueId, selectList } = item;
    let html = '';
    if (typeId === 13) {
        let itemHtmlArr = [];
        selectList.forEach(select => {
            itemHtmlArr.push(`
                <li class="foxui-select-item" data-id="${select.id}">${select.text}</li>
            `);
        });
        html = `
            <div class="foxui-select">
                <div class="foxui-select-handle foxui-select-icon">
                    <input class="foxui-select-input foxui-size-small" readonly="readonly" data-id="${valueId || ''}" value="${value || ''}" />
                </div>
                <div class="foxui-select-menu">
                    <ul class="foxui-select-slide">
                        ${itemHtmlArr.join('')}
                    </ul>
                </div>
            </div>
        `;
    } else {
        html = `<input class="foxui-size-small" value="${value || ''}" />`;
    }
    return html;
}

/**
 * @description: 新增/编辑参数预设弹框 html  ->  弹框
 * @param {*} title
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-14 17:07:15
 */
function _modifyParamGroupHtml(title, dataList) {
    return `
        <div class="param-preset-box">
            <div class="section-main-item margin-top-0">
                <div class="foxui-input-group">
                    <div class="input-label">
                        <label class="foxui-required">预设名称：</label>
                    </div>
                    <div class="input-box">
                        <div class="foxui-input-suffix">
                            <input maxlength="10" class="foxui-size-small" placeholder="" required value="${title || ''}" name="attrName"/>
                            <i class="foxui-suffix-icon foxui-suffix-count">0/10</i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-main-item">
                <div class="foxui-input-group foxui-align-items-start">
                    <div class="input-label">
                        <label>参数列表：</label>
                    </div>
                    <div class="block-box foxui-border">
                        <div class="foxui-table foxui-table-border-bottom">
                            <ul class="foxui-table-thead">
                                <li class="foxui-table-tr">
                                    <div class="foxui-table-th"></div>
                                    <div class="foxui-table-th">参数名称</div>
                                    <div class="foxui-table-th">参数类型</div>
                                    <div class="foxui-table-th">参数值</div>
                                    <div class="foxui-table-th">操作</div>
                                </li>
                            </ul>
                            <ul class="foxui-table-tbody foxui-drag foxui-drag-container">
                                ${_modifyParamGroupTrHtml(dataList)}
                            </ul>
                        </div>
                        <div class="add-param">
                            <button class="foxui-text-primary" onclick="_handleAddParamPop(event)">
                                <i class="foxui-icon-jiahao-o"></i>
                                <span>添加参数</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * @description: 新增/编辑参数预设弹框tr的 html
 * @param {*} dataList
 * @return {*}
 * @Date: 2022-01-14 22:52:25
 */
function _modifyParamGroupTrHtml(dataList) {
    dataList = dataList || [];
    let htmlArr = [];
    dataList.forEach(item => {
        htmlArr.push(`
            <li class="foxui-table-tr foxui-drag-item" data-id="${item.id}">
                <div class="foxui-table-td">
                    <span class="foxui-drag-handle foxui-icon-liebiao-o"></span>
                </div>
                <div class="foxui-table-td param-title">
                    <input name="paramName" class="foxui-size-small" value="${item.title || ''}" />
                </div>
                <div class="foxui-table-td">
                    <div class="foxui-select">
                        <div class="foxui-select-handle foxui-select-icon">
                            <input name="type" class="foxui-select-input foxui-size-small" readonly="readonly" data-id="${item.typeId || ''}" value="${item.type || ''}" />
                        </div>
                        <div class="foxui-select-menu">
                            <ul class="foxui-select-slide">
                                <li class="foxui-select-item" data-id="11" onclick="_handleTypeChange(event)">默认输入</li>
                                <li class="foxui-select-item" data-id="12" onclick="_handleTypeChange(event)">手动输入</li>
                                <li class="foxui-select-item" data-id="13" onclick="_handleTypeChange(event)">下拉选择</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="foxui-table-td">
                    ${_paramValueBySelectHtml(item.type, item.value)}
                </div>
                <div class="foxui-table-td">
                    <button class="foxui-text-primary foxui-size-small delete" onclick="_handleDeleteParamPop(event)">删除</button>
                </div>
            </li>
        `);
    });
    return htmlArr.join('');
}

/**
 * @description: 通过 type 生成参数预设参数值 html
 * @param {*} type
 * @param {*} value
 * @return {*}
 * @Date: 2022-01-14 23:22:34
 */
function _paramValueBySelectHtml(type, value) {
    let html = '<p>/</p>';
    if (type === '默认输入') {
        html = `<input name="dfvalue"  class="foxui-size-small" placeholder="请输入默认参数值" value="${value || ''}" />`;
    } else if (type === '手动输入') {
        html = '<p>/</p>';
    } else if (type === '下拉选择') {
        html = `<textarea name="dfvalue"  autocomplete="off" rows="2" maxlength="120" placeholder="一行一个可选值">${value || ''}</textarea>`;
    }
    return html;
}

/**
 * @description: 参数预设弹框新增字段
 * @param {*} event
 * @return {*}
 * @Date: 2022-01-15 00:23:25
 */
function _handleAddParamPop(event) {
    const target = event.target;
    const html = _modifyParamGroupTrHtml([{}]);
    const $mainItem = $(target).closest('.section-main-item');
    $mainItem.find('.foxui-table .foxui-table-tbody').append(html);
    $mainItem.find('.foxui-table .foxui-table-tbody .foxui-table-tr:last-child').css('display', 'none').slideDown('fast');
}

/**
 * @description: 参数预设弹框删除字段
 * @param {*} event
 * @return {*}
 * @Date: 2022-01-15 00:18:29
 */
function _handleDeleteParamPop(event) {
    const target = event.target;
    const $item = $(target).closest('.foxui-table-tr');
    foxui.dialog({
        title: '删除',
        content: `您确定要删除吗`,
        cancelText: '取消',
        confirmText: '删除',
        buttonType: 'danger',
        confirm: function (callback) {
            callback();
            $item.slideUp('fast', function () {
                $(this).remove();
            });
            foxui.message({
                text: '删除成功',
                type: 'success',
            });
        },
        cancel: function () {},
    });
}

/**
 * @description: 绑定监听type，改变更新html
 * @param {*}
 * @return {*}
 * @Date: 2022-01-14 23:44:42
 */
function _handleTypeChange(event) {
    const target = event.target;
    const type = $(target).text();
    const html = _paramValueBySelectHtml(type);
    $(target).closest('.foxui-table-td').next('.foxui-table-td').empty().append(html);
}
