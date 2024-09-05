/**
 * 失去焦点信息判断
 * @param className
 * @param desc
 */
function blurInfo(id,className, desc, infoclass) {
    let val = $("#" + id).val();
    if(infoclass == undefined){
        infoclass = "color-danger"
    }
    if(val == ""){
        $("." + className).empty().append(`<span class="info ${infoclass}">
                                                    ${desc}
                                                </span>`);
    }
}


/**
 * 监测密码
 */
function checkPwd() {
    let dbhost = $("#dbhost").val();
    let dbport = $("#dbport").val();
    let dbuser = $("#dbuser").val();
    let dbpwd = $("#dbpwd").val();
    if(dbhost == ""){
        blurInfo("dbhost", "host-info", "数据库主机为空");
        return;
    }
    if(dbport == ""){
        blurInfo("dbport", "port-info", "数据库端口为空");
        return;
    }
    if(dbuser == ""){
        blurInfo("dbuser", "user-info", "数据库用户为空");
        return;
    }
    if(dbpwd == ""){
        blurInfo("dbpwd", "pwd-info", "数据库密码为空");
        return;
    }
    $.ajax({
        url: 'installdb.php',
        data: {
            check:"conn",
            dbhost,
            dbport,
            dbuser,
            dbpwd,
        },
        type: 'post',
        dataType: 'html',
        success: function (data) {
            if (data === 'true') {
                $(".pwd-info").empty().append(`<span class="info color-success">
                                                    <i class="foxui-icon-zhengque-f"></i>
                                                    数据库连接正常
                                                </span>`);
            } else {
                $(".pwd-info").empty().append(` <span class="info color-danger">
                                                <i class="foxui-icon-cuowu-f"></i>
                                                数据库连接失败！
                                            </span>`);
            }
        }
    });
}

/**
 * 监测数据库
 */
function checkDb() {
    let dbhost = $("#dbhost").val();
    let dbport = $("#dbport").val();
    let dbuser = $("#dbuser").val();
    let dbpwd = $("#dbpwd").val();
    let dbname = $("#dbname").val();
    if(dbhost == ""){
        blurInfo("dbhost", "host-info", "数据库主机为空");
        return;
    }
    if(dbport == ""){
        blurInfo("dbport", "port-info", "数据库端口为空");
        return;
    }
    if(dbuser == ""){
        blurInfo("dbuser", "user-info", "数据库用户为空");
        return;
    }
    if(dbpwd == ""){
        blurInfo("dbpwd", "pwd-info", "数据库密码为空");
        return;
    }
    if(dbname == ""){
        blurInfo("dbname", "dbname-info", "数据库名称为空");
        return;
    }
   let  isConn = false;
    $.ajax({
        url: 'installdb.php',
        data: {
            check:"db",
            dbhost,
            dbport,
            dbuser,
            dbpwd,
            dbname
        },
        type: 'post',
        dataType: 'html',
         async:false,
        success: function (data) {
            let text = "";
            let color = "";
            let i = "";
            if(data === 'conn'){
                $(".dbname-info").empty().append(`<span class="info color-danger">
                                                <i class="foxui-icon-cuowu-f"></i>
                                                  请先保证数据库连接正常!
                                            </span>`);
            }else if (data === 'true') {
                let cu = checkCu();
                if(cu === 'true'){
                    color = "color-success";
                    text = "数据库不存在,系统将自动创建!"
                    i = "foxui-icon-zhengque-f";
		    isConn=true;
                }else{
                    color = "color-danger";
                    text = " 数据库不存在,用户没有权限自动创建数据库!"
                    i = "foxui-icon-cuowu-f";
                }
                $(".dbname-info").empty().append(`<span class="info ${color}">
                                                    <i class="${i}"></i>
                                                    ${text}
                                                </span>`);
            }else {
                color = "color-primary";
                text = "数据库已经存在，系统将覆盖数据库！"
                i = "foxui-icon-tishi-f";
                $(".dbname-info").empty().append(`<span class="info ${color}">
                                                <i class="${i}"></i>
                                                 ${text}
                                            </span>`);
                 isConn=true;

            }
        }
    });
}

/**
 * 检查用户权限
 */
function checkCu() {
    let dbhost = $("#dbhost").val();
    let dbport = $("#dbport").val();
    let dbuser = $("#dbuser").val();
    let dbpwd = $("#dbpwd").val();
    if(dbhost == ""){
        blurInfo("dbhost", "host-info", "数据库主机为空");
        return;
    }
    if(dbport == ""){
        blurInfo("dbport", "port-info", "数据库端口为空");
        return;
    }
    if(dbuser == ""){
        blurInfo("dbuser", "user-info", "数据库用户为空");
        return;
    }
    if(dbpwd == ""){
        blurInfo("dbpwd", "pwd-info", "数据库密码为空");
        return;
    }
    let du = false;
    $.ajax({
        url: 'installdb.php',
        data: {
            check:"cu",
            dbhost,
            dbport,
            dbuser,
            dbpwd
        },
        type: 'post',
        dataType: 'html',
        async: false,
        success: function (data) {
            du = data;
        }
    });
    return du;
}




