//ajax封装
function ajaxR(url, type, data, otherParam, successCallback, errorCallback) {
    let {dataType="json", async=true, isHandoverLang=1} = otherParam;
    $.ajax({
        url: url,
        type: type,
        data: data,
        dataType: dataType, // 假设我们处理的数据类型为JSON
        async:async,
        beforeSend: function(xhr) {
            // 在这里可以处理请求前的逻辑，例如显示加载动画
            if(isHandoverLang == 1){//切换语言保存
                let isAllowed = handoverLang();
                if(!isAllowed){
                    // 不发送请求，中止
                    foxui.message({
                        type: 'danger',
                        text: "语言切换失败,防止数据混乱,请刷新页面后再操作！"
                    })
                    xhr.abort(); // 或者返回 false;
                }
            }
        },
        success: function(response) {
            // 请求成功后的处理逻辑
            console.log('请求成功，处理数据...');
            if (successCallback && typeof successCallback === 'function') {
                successCallback(response);
            }
        },
        error: function(xhr, status, error) {
            // 请求失败的处理逻辑
            console.log('请求失败，处理错误...');
            if (errorCallback && typeof errorCallback === 'function') {
                errorCallback(xhr, status, error);
            }else{
                foxui.message({
                    type: 'warning',
                    text: "操作失败"
                })
            }
        },
        complete: function() {
            // 请求完成后的处理逻辑，无论成功或失败都会执行
            console.log('请求完成的处理...');
        }
    });
}

//当前显示语言
function handoverLang() {
    let showLang = $(".lang-select button").attr('data-lang');
    let isChange = false;
    if ($(".lang-select button").hasClass('display-none')) {
        isChange = true;
    } else {
        $.ajax({
            type: "post",
            url: HANDOVER_LANG_PATH,
            dataType: "json",
            data: {"lang":showLang},
            async:false,
            success: function (res) {
                if(res.code == 1){
                    isChange = true;
                }
            }, error: function (res) {
            }
        });
    }
    return isChange;
}

//语言切换
function langHandover(obj) {
    let lang = $(obj).attr('data-lang');
    foxui.loading();
    $.ajax({
        type: "post",
        url: HANDOVER_LANG_PATH,
        dataType: "json",
        data: {"lang":lang},
        success: function (res) {
            foxui.closeLoading();
            if(res.code == 1){
                let langName = "";
                if(res.data){
                    langName = res.data.name;
                }
                foxui.message({
                    type: 'success',
                    text: "当前语言已切换成“"+langName+"”"
                })
                window.location.href= ADMIN_PATH+"/Index";
            }else{
                foxui.message({
                    type: 'danger',
                    text:res.msg
                })
            }
        }, error: function (res) {
            foxui.closeLoading();
        }
    });
}


//获取栏目或者模型的自定义属性值
function getField(obj, url) {
    let saveData = {};
    //拉取栏目属性
    $.ajax({
        type: 'get',
        url: url,
        dataType: 'json',
        async: false,
        success: function (res) {
            if (res.code == 1 && res.data) {
                let data = res.data;
                data.forEach((item, index) => {
                    let cn = '.' + item.name;
                    let itemC = $(obj).find(cn);
                    if (item.dtype == 'radio') {
                        //单选
                        let rinput = $(itemC).find('.foxui-radio.is-checked').find('input');
                        saveData[item.name] = $(rinput).val()||"";
                    } else if (item.dtype == 'checkbox') {
                        //多选项
                        let dxx = '';
                        let fcs = $(itemC).find('.foxui-checkbox.is-checked');
                        $.each(fcs, function (key, value) {
                            //遍历多选值
                            let ri = $(value).find('input');
                            dxx += $(ri).val() + ',';
                        });
                        if (dxx.length > 0) {
                            dxx = dxx.substr(0, dxx.length - 1);
                        }
                        saveData[item.name] = dxx;
                    } else if (item.dtype == 'switch') {
                        //开关
                        saveData[item.name] = $(itemC).prop('checked')?1:0;
                    } else if (item.dtype == 'htmltext') {
                        //HTML文本
                        saveData[item.name] = getRTtContent(item.name);
                    } else if (item.dtype == 'select') {
                        //下拉框
                        saveData[item.name] = $(itemC).attr('data-id')||"";
                    } else if (item.dtype == 'datetime') {
                        //日期和时间
                        saveData[item.name] = $(itemC).val()||"";
                    } else if (item.dtype == 'int') {
                        //整数类型
                        saveData[item.name] = $(itemC).val()||"";
                    } else if (item.dtype == 'text') {
                        //单行文本
                        saveData[item.name] = $(itemC).val()||"";
                    } else if (item.dtype == 'multitext') {
                        //多行文本
                        saveData[item.name] = $(itemC).val()||"";
                    } else if (item.dtype == 'color') {
                        //颜色
                        saveData[item.name] = $(itemC).val()||"";
                    } else if (item.dtype == 'pic') {
                        //图片文件
                        let imgIds = '';
                        let imgs = $(itemC).find('img');
                        $.each(imgs, function (key, value) {
                            //遍历多选值
                            // let imgId = $(value).attr("data-id");
                            let imgId = $(value).attr('src');
                            imgIds += imgId + ',';
                        });
                        if (imgIds.length > 0) {
                            imgIds = imgIds.substr(0, imgIds.length - 1);
                        }
                        saveData[item.name] = imgIds||"";
                    } else if (item.dtype == 'pics') {
                        //图片文件
                        let imgSrcs = '';
                        let imgs = $(itemC).find('img');
                        $.each(imgs, function (key, value) {
                            //遍历多选值
                            let imgSrc = $(value).attr('src');
                            imgSrcs += imgSrc + ',';
                        });
                        if (imgSrcs.length > 0) {
                            imgSrcs = imgSrcs.substr(0, imgSrcs.length - 1);
                        }
                        saveData[item.name] = imgSrcs||"";
                    }else if (item.dtype == 'media') {//媒体
                        //媒体文件
                        let lis = $(itemC).find('li');
                        let videos = [];
                        $.each(lis, function (key, li) {
                            let videoId = $(li).find("img").attr('data-id');
                            let url = $(li).find("img").attr('data-url');
                            let pic = $(li).find("img").attr('src');
                            let videoTitle = $(li).find("input").val();
                            videos.push({"id":videoId, "title": videoTitle, "url":url, pic});
                        });
                        saveData[item.name] = JSON.stringify(videos);
                    }
                    else if (item.dtype == 'decimal') {
                        //金额类型
                        saveData[item.name] = $(itemC).val()||"";
                    } else if (item.dtype == 'float') {
                        //小数类型
                        saveData[item.name] = $(itemC).val()||"";
                    }else{
                        //普通的input标签，varchar字段获取
                        saveData[item.name] = $(itemC).val()||"";
                    }
                });
            }
        },
        error: function (res) {
            console.log('字段属性。。。。');
        },
    });
    return saveData;
}

/**
 * 根据字典类型获取对应字典数据(dict_data查询)
 * @param dictType 字典类型
 * @param dictType $resultField 返回的字段 如 dict_label,dict_value
 */
function getColumModels() {
    let dictDatas = [];
    $.ajax({
        type: 'get',
        // url: ADMIN_PATH + '/Column/getColumModels',
        url: GETCOLUMMODELS_PATH,
        dataType: 'json',
        async: false,
        success: function (res) {
            if (res.code == 1 && res.data) {
                dictDatas = res.data;
            }
        },
        error: function (res) {
            console.log('查询字典失败。。。。');
        },
    });
    return dictDatas;
}

function dialogContent() {
    return `
            <div class="input-box" style="padding: 0 40px;">
                <div class="foxui-input-group" style="margin-bottom: 20px">
                    <div class="foxui-align-right" style="width: 110px;">
                        <label class="foxui-required" >原密码：</label>
                    </div>
                    <div class="foxui-input-suffix">
                        <input name="opassword" type="password" placeholder="请输入原密码" value="" />
                        <i class="foxui-suffix-icon foxui-suffix-count"></i>
                    </div>
                </div>
                <div class="foxui-input-group" style="margin-bottom: 20px">
                    <div class="foxui-align-right" style="width: 110px;">
                        <label class="foxui-required">新密码：</label>
                    </div>
                    <div class="foxui-input-suffix">
                        <input type="password" name="password" placeholder="请输入密码" value=""/>
                        <i class="foxui-icon-xianshi-o foxui-suffix-icon foxui-suffix-password"></i>
                    </div>
                </div>
                <div class="foxui-input-group" style="margin-bottom: 20px">
                    <div class="foxui-align-right" style="width: 110px;">
                        <label class="foxui-required">确认新密码：</label>
                    </div>
                    <div class="foxui-input-suffix">
                        <input type="password" name="apassword" placeholder="再次请输入密码" value=""/>
                        <i class="foxui-icon-xianshi-o foxui-suffix-icon foxui-suffix-password"></i>
                    </div>
                </div>
            </div>
        `;
}

//密码校验
function isPwd(password){
    let re =/^(?![a-zA-Z]+$)(?![A-Z0-9]+$)(?![A-Z\W_]+$)(?![a-z0-9]+$)(?![a-z\W_]+$)(?![0-9\W_]+$)[a-zA-Z0-9\W_]{8,}$/;
    let result =  re.test(password);
    if(!result) {
        return false;
    }else {
        return true;
    }
};

//修改密码
function modifyPasword() {
    foxui.dialog({
        title: '修改密码',
        content: dialogContent(),
        confirmText: '确定',
        cancelText: '取消',
        width: '620px',
        confirm: function (callback) {
            let opassword = $('.input-box input[name="opassword"]').val();
            let password = $('.input-box input[name="password"]').val();
            let apassword = $('.input-box input[name="apassword"]').val();
            if(opassword == undefined || opassword == ""){
                foxui.message({
                    type:'warning',
                    text:'原密码不能为空'
                })
                return;
            }
            if(password == undefined || password == ""){
                foxui.message({
                    type:'warning',
                    text:'新密码不能为空'
                })
                return;
            }
            if(!isPwd(password)){
                foxui.message({
                    type:'warning',
                    text:'密码必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位'
                })
                return;
            }
            if(password != apassword){
                foxui.message({
                    type:'warning',
                    text:'两次输入密码不相同'
                })
                return;
            }

            let datas ={opassword, password, apassword};
            $.ajax({
                type: "post",
                // url: ADMIN_PATH + '/admin/updatePassword',
                url: ADMINUPDATEPASSWORD_PATH,
                dataType: "json",
                data: datas,
                success: function (res) {
                    if (res.code == 1) {
                        foxui.message({
                            type:'success',
                            text:res.msg
                        })
                        window.location.href = document.referrer;//返回并且刷新
                    } else {
                        foxui.message({
                            type:'warning',
                            text:res.msg
                        })
                    }
                }, error: function (res) {
                    foxui.message({
                        type:'warning',
                        text:res.msg
                    })
                }
            });
            callback();
        },
        cancel: function () {
            console.log('取消');
        },
    });
}

//清除缓存
function clearCache() {

    foxui.loading();
    $.ajax({
        type: "post",
        // url: ADMIN_PATH + '/Login/clearCache',
        url: LOGINCLEARCACHE_PATH,
        dataType: "json",
        success: function (res) {
            foxui.closeLoading();
            if (res.code == 1) {
                foxui.message({
                    type:'success',
                    text:res.msg
                })
            } else {
                foxui.message({
                    type:'warning',
                    text: res.msg
                })
            }
        }, error: function (res) {
            foxui.closeLoading();
            foxui.message({
                type:'warning',
                text: res.msg
            })
        }
    });
}



$('.dropdown')
    .find('ul')
    .children('li')
    .on('click', function () {
        let user = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : '';
        let operationId = $(this).attr('data-id');
        if (operationId == 1) {
            //个人中心
            // window.location.href = ADMIN_PATH + `/Admin/edit?id=${user.id}&type=1&columnId=8`;
            window.location.href = ADMINEDIT_PATH + `?id=${user.id}&type=1&columnId=8`;

        } else if (operationId == 2) {
            //修改密码
            modifyPasword();
        } else if (operationId == 3) {
            //更新缓存
            clearCache();
        } else if (operationId == 5) {
            // 退出系统
            logout()
        }
    });

$(".foxcms-logo").on('click', function () {
    // window.location.href = ADMIN_PATH;
})

$(".foxcms-logo").hover(function () {
    // this.style.cursor = 'pointer';
})
$(".item.icon").hover(function () {
    this.style.cursor = 'pointer';
})
//跳转到前段index
$(".index").on('click', function () {
    window.open(SERVER_URL);
})


//图片集默认值控制开始
//初始化
let dtype = $('.dtype .foxui-radio.is-checked input').val();
disabledDefault(dtype);
$('.dtype .foxui-radio').on("click", function () {
    let dtype = $(this).find("input").val();
    disabledDefault(dtype);
})

//禁用启用默认值
function disabledDefault(dtype){
    if((dtype == "pics") || (dtype=="media")){
        $('textarea[name="dfvalue"]').attr("disabled", "disabled");
        $('textarea[name="dfvalue"]').val("");
    }else{
        $('textarea[name="dfvalue"]').removeAttr("disabled");
    }
}
//图片集默认值控制结束

//判断是否已英文字母开头
function letterBegin(str){
    return /^[a-z]/.test(str);
}

//时间间隔
function myrefresh() {
    window.location.reload();
}

function logout() {
    localStorage.clear();
    // window.location.href= ADMIN_PATH + "/Login/logout";
    window.location.href= LOGOUT_PATH;
}


//进度条
function dialogSiteContent() {
    return `
            <div class="input-box" style="padding: 0 40px;">
                <div id="progress"></div>
                <div style="text-align: center" class="foxui-margin-top-8">
                    <p>成功生成栏目：<span id="columnId"></span></p>
                    <p class="display-none" id="errTip">失败生成栏目：<span id="errColumnId" style="color: red"></span></p>
                </div>
            </div>
        `;
}

let progressObj = null;
//循环生成静态html
function dialogSite(params) {
    $.ajax({
        type: "post",
        // url: ADMIN_PATH + "/Seo/allSite",
        url: SEOALLSITE_PATH,
        dataType: "json",
        data: params,
        success: function (res) {
            if(res.code == 1){
                if(res.data == ""){
                    foxui.message({
                        type: 'success',
                        text: res.msg
                    })
                    progressObj.update(100);
                }else{
                    dialogSite(res.data);
                    let maxUpdate = (res.data.index/res.data.buildCount)*100;
                    if(maxUpdate >= 100){
                        maxUpdate = 99;
                    }
                    let updateIndex = Math.round(maxUpdate);
                    progressObj.update(updateIndex);
                    $("#columnId").html(res.data.columnId)
                }
            }else{
                foxui.message({
                    type: 'warning',
                    text: res.msg
                })
                $("#errTip").removeClass("display-none");
                $("#errColumnId").html(res.msg);
            }
        }, error: function (res) {
            foxui.message({
                type: 'warning',
                text: "操作失败"
            })
        }
    });
}


/**
 * 生成
 * @param params
 */
function allSite(params){
    foxui.dialog({
        title: '生成静态文件',
        content: dialogSiteContent(),
        width: '620px',
    });
    progressObj = foxui.progress({
        el: '#progress',
        percent: params.index,
        strokeWidth: 16,
        textInside: true,
    });
    dialogSite(params);
}

/**
 * 单独静态生成
 * @param params
 */
function addDataBuildDetail(params){
    $.ajax({
        type: "post",
        url: SEOADDDATABUILDDETAIL_PATH,
        dataType: "json",
        data: params,
        async:false,
        success: function (res) {

        }, error: function (res) {
        }
    });
}

/**
 *
 * 单独静态栏目生成
 */
function singleAllSite(params){
    $.ajax({
        type: "post",
        url: SINGLEALLSITE_PATH,
        dataType: "json",
        data: params,
        async:false,
        success: function (res) {
            if(res.code == 1){
                if(res.data == ""){
                }else{
                    singleAllSite(res.data);
                }
            }

        }, error: function (res) {
        }
    });
}
