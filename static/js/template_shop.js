/*
 * @Descripttion : 模板-模板市场
 * @Versions     : 0.1
 * @Author       : foxcms team
 * @Date         : 2022-01-24 21:03:59
 * @LastEditors  : liuzhifang
 * @LastEditTime : 2022-01-26 10:54:50
 */

/**
 * @description: 行业切换
 * @param {*} function
 * @return {*}
 * @Date: 2022-01-24 21:21:47
 */
$('.operation-box .left .tab-btn').click(function () {
    const isActive = $(this).is('.is-active');
    if (!isActive) {
        $(this).closest('.left').find('.tab-btn').removeClass('is-active');
        $(this).addClass('is-active');
    }
});

/**
 * @description: 标准模板分页
 * @param {*}
 * @return {*}
 * @Date: 2022-01-24 21:54:33
 */
fox.pagination({
    el: $('#standardPagination'),
    total: 17,
    currentPage: 5,
    type: 'special',
    isShowJump: true,
    isShowTotal: true,
    isShowSize: true,
    pageChange: function (res, callback) {
        // 获取页码相关数据，添加异步请求
        const { total, pageSize, currentPage } = res;
        // 成功获取数据后，把相关参数回传
        fox.loading({
            text: '加载中...',
            background: 'rgba(255, 255, 255, 0.9)',
        });
        // 摸拟异步获取数据
        setTimeout(() => {
            callback({ total, pageSize, currentPage });
            fox.loadingClose();
        }, 1000);
    },
});

/**
 * @description: 自适应模板分页
 * @param {*}
 * @return {*}
 * @Date: 2022-01-24 21:54:53
 */
fox.pagination({
    el: $('#adaptionPagination'),
    total: 13,
    currentPage: 5,
    type: 'special',
    isShowJump: true,
    isShowTotal: true,
    isShowSize: true,
    pageChange: function (res, callback) {
        // 获取页码相关数据，添加异步请求
        const { total, pageSize, currentPage } = res;
        // 成功获取数据后，把相关参数回传
        fox.loading({
            text: '加载中...',
            background: 'rgba(255, 255, 255, 0.9)',
        });
        // 摸拟异步获取数据
        setTimeout(() => {
            callback({ total, pageSize, currentPage });
            fox.loadingClose();
        }, 1000);
    },
});

/**
 * @description: 更多行业
 * @param {*} function
 * @return {*}
 * @Date: 2022-01-26 10:23:49
 */
$('.operation-box .left button.more').click(function () {
    fox.dialog({
        title: '请选择行业',
        content: _dialogMoreHtml(),
        width: '760px',
        border: true,
    });
});

/**
 * @description: 未找到行业
 * @param {*} function
 * @return {*}
 * @Date: 2022-01-26 10:24:03
 */
$('.operation-box .right .not-found-btn').click(function () {
    fox.dialog({
        title: '末找到行业',
        content: _notFoundHtml(),
        cancelText: '取消',
        confirmText: '确定',
        buttonSize: 'small',
        buttonAlign: 'center',
        width: '626px',
        buttonWidth: '136px',
        border: true,
        confirm: function () {
            // 摸拟异步提交
            let loadingEl = fox.loading({
                el: 'body',
                text: '加载中...',
                background: 'rgba(255, 255, 255, 0.9)',
            });
            setTimeout(() => {
                fox.loadingClose(loadingEl);
                fox.message({
                    message: '提交成功',
                    type: 'success',
                });
            }, 3000);
        },
        cancel: function () {},
    });
});

/**
 * @description: 更多行业 html
 * @param {*}
 * @return {*}
 * @Date: 2022-01-26 10:24:13
 */
function _dialogMoreHtml() {
    return `
        <div>
            <div class="operation-box" style="padding-bottom: 12px">
                <div class="fox-form-group">
                    <input class="fox-size-small" placeholder="搜索行业" required value="" />
                    <button class="fox-size-small fox-solid-primary">搜索</button>
                </div>
            </div>
            <div class="fox-table">
                <ul class="fox-table-tbody">
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>广告</strong>
                            <strong>文件</strong>
                            <strong>设计服务</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>广告</span>
                            <span>文化传媒</span>
                            <span>印刷包装</span>
                            <span>展览设计</span>
                            <span>园林设计</span>
                            <span>工艺雕塑</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>学校</strong>
                            <strong>教育</strong>
                            <strong>培训机构</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>五金</strong>
                            <strong>设备</strong>
                            <strong>工业制品</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>门窗</strong>
                            <strong>卫浴</strong>
                            <strong>灯光照明</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>IT</strong>
                            <strong>软件</strong>
                            <strong>互联网</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>化工</strong>
                            <strong>原材料</strong>
                            <strong>环保</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>建筑建材</strong>
                            <strong>能源</strong>
                            <strong>科技</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>运输</strong>
                            <strong>房产</strong>
                            <strong>物业管理</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>金融</strong>
                            <strong>投资</strong>
                            <strong>理财保险</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>学校</span>
                            <span>考试课程</span>
                            <span>技能培训</span>
                            <span>企业培训</span>
                            <span>幼教早教</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>工商</strong>
                            <strong>法律</strong>
                            <strong>知识产权</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>工商服务</span>
                            <span>人力资源</span>
                            <span>法律服务</span>
                            <span>知识产权</span>
                        </div>
                    </li>
                    <li class="fox-table-tr">
                        <div class="fox-table-td">
                            <strong>休闲</strong>
                            <strong>娱乐</strong>
                            <strong>生活服务</strong>
                        </div>
                        <div class="fox-table-td">
                            <span>美发美甲</span>
                            <span>美容护肤</span>
                            <span>生活服务</span>
                            <span>月子会所</span>
                            <span>维修服务</span>
                            <span>安保服务</span>
                            <span>母婴服务</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    `;
}

/**
 * @description: 末找到行业 html
 * @param {*}
 * @return {*}
 * @Date: 2022-01-26 10:47:01
 */
function _notFoundHtml() {
    return `
        <div class="main">
            <div class="item">
                <div class="fox-form-group">
                    <span class="column">
                        <label>我需要的行业：</label>
                    </span>
                    <div class="input-box">
                        <input class="fox-size-small" placeholder="" required value="" />
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="fox-form-group">
                    <span class="column">
                        <label>参考网站地址：</label>
                    </span>
                    <div class="fox-form-prepend input-box">
                        <div class="fox-prepend-inner">
                            <div class="fox-select">
                                <div class="fox-select-header">
                                    <input class="fox-select-input fox-size-small" readonly="readonly" placeholder="" value="http://" />
                                    <i class="foxfont icon-jiantouxiangxia fox-select-icon"></i>
                                </div>
                                <ul class="fox-select-menu">
                                    <li class="fox-select-item" data-id="1">http://</li>
                                    <li class="fox-select-item" data-id="2">https://</li>
                                </ul>
                            </div>
                        </div>
                        <input class="fox-size-small" placeholder="" required value="" />
                    </div>
                </div>
            </div>
        </div>
    `;
}
