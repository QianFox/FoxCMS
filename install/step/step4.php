<!DOCTYPE html>
<html lang="cn">
<?php include "head.html"?>
<body>
<div class="install-container step4-container">
    <?php include "header.html"?>
    <main>
        <div class="step-content">
            <div class="step-head">
                <div class="progress-text">
                    <div class="step active">
                        <h3>许可协议</h3>
                        <div class="circle"></div>
                    </div>
                    <div class="step active">
                        <h3>环境检测</h3>
                        <div class="circle"></div>
                    </div>
                    <div class="step active">
                        <h3>参数配置</h3>
                        <div class="circle"></div>
                    </div>
                    <div class="step">
                        <h3>安装完成</h3>
                        <div class="circle"></div>
                    </div>
                </div>
                <div>
                    <div class="foxui-progress foxui-progress-type__line" data-foxid="foxid3985">
                        <div class="foxui-progress-line">
                            <div class="foxui-progress-line__outer" style="height:12px;">
                                <div class="foxui-progress-line__inner foxui-progress-line__striped foxui-progress-line__animated" style="width:66.66%; ">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-main step-main-multiple">
                <div class="panel">
                    <div class="panel-head">安装选项</div>
                    <div class="panel-main">
                        <div class="slide">
                            <div class="form-item">
                                <div class="foxui-input-group data-method">
                                    <label>安装方式：</label>
                                    <div class="foxui-radio-group">
                                        <div class="foxui-radio is-checked">
                                            <span class="foxui-radio-input">
                                                <i class="foxui-radio-icon"></i>
                                                <input type="radio" value="1" checked="checked" />
                                            </span>
                                            <span class="foxui-radio-label">体验数据</span>
                                        </div>
                                        <div class="foxui-radio">
                                            <span class="foxui-radio-input">
                                                <i class="foxui-radio-icon"></i>
                                                <input type="radio" value="2" checked="checked" />
                                            </span>
                                            <span class="foxui-radio-label">纯净安装</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 默认模板 -->
                            <div class="form-item foxui-margin-top-20" id="defaultTemplate">
                                <?php echo $templateInfo?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel foxui-margin-top-20">
                    <div class="panel-head">数据库选项</div>
                    <div class="panel-main">
                        <div class="slide">
                            <div class="form-item">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">数据库主机：</label>
                                    <input class="foxui-size-small" placeholder="请输入内容" value="127.0.0.1" id="dbhost" onblur="blurInfo('dbhost','host-info','数据库主机地址为空')"/>
                                    <div class="host-info">
                                        <span class="info">
                                            数据库主机地址，与程序同服务器一般为127.0.0.1
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">数据库端口：</label>
                                    <input class="foxui-size-small" placeholder="输入你的数据库端口" value="3306" id="dbport" onblur="blurInfo('dbport','port-info','数据库端口为空')"/>
                                    <div class="port-info">

                                    </div>
                                </div>
                            </div>
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">数据库用户：</label>
                                    <input class="foxui-size-small" placeholder="输入你的数据库用户名" value="root" id="dbuser" onblur="blurInfo('dbuser','user-info','数据库用户为空')"/>
                                    <div class="user-info">

                                    </div>
                                </div>
                            </div>
                            <!-- 连接正常 -->
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group pwd">
                                    <label class="foxui-required">数据库密码：</label>
                                    <input class="foxui-size-small" placeholder="输入正确的数据库密码" value="" type="password" onblur="checkPwd()" id="dbpwd"/>
                                    <div class="pwd-info">
                                    </div>
                                </div>
                            </div>
                            <!-- 自动创建 -->
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">数据库名称：</label>
                                    <input class="foxui-size-small" placeholder="输入正确的数据库名称" value="<?php echo $dbname;?>" id="dbname" onblur="checkDb()"/>
                                    <div class="dbname-info">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel web-panel foxui-margin-top-20">
                    <div class="panel-head">网站设置</div>
                    <div class="panel-main">
                        <div class="slide">
                            <div class="form-item">
                                <div class="foxui-input-group">
                                    <label>网站名称：</label>
                                    <input class="foxui-size-small" placeholder="" value="我的网站" id="my_website"/>
                                </div>
                            </div>
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label>管理邮箱：</label>
                                    <input class="foxui-size-small" placeholder="" value="admin@FoxCMS.cn" id="email"/>
                                </div>
                            </div>
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">网站域名：</label>
                                    <input class="foxui-size-small" placeholder="" value="<?php echo $serverURLInfo['domain'];?>" id="domain"/>
                                    <input type="hidden" class="foxui-size-small" placeholder="" value="<?php echo $serverURLInfo['url_prefix'];?>" id="url_prefix"/>
                                </div>
                            </div>
                            <div class="line foxui-margin-top-28 foxui-margin-bottom-28"></div>
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">管理员账号：</label>
                                    <input class="foxui-size-small" placeholder="" value="admin" id="username"/>
                                </div>
                            </div>
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">管理员密码：</label>
                                    <input class="foxui-size-small" type="password" placeholder="请设置后台管理员密码" value="" id="password" onblur="isPwd()"/>
                                    <div id="password-info">
                                        <span class="info">
                                            密码必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-item foxui-margin-top-20">
                                <div class="foxui-input-group">
                                    <label class="foxui-required">确认密码：</label>
                                    <input class="foxui-size-small" type="password" placeholder="请牢记后台管理员密码" value="" id="confirm_password"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-foot">
                <button class="foxui-size-medium foxui-shape-round is-long prev">
                    <i class="foxui-icon-xiangzuo-o"></i>
                    <span>后退</span>
                </button>
                <button class="foxui-solid-primary foxui-shape-round foxui-size-medium is-long next">
                    <span>继续</span>
                    <i class="foxui-icon-xiangyou-o"></i>
                </button>
            </div>
        </div>
    </main>
    <?php include "footer.html"?>
</div>
</body>
<script src="/static/js/jquery-3.6.0.min.js"></script>
<script src="/static/js/foxui-1.32.min.js"></script>
<script src="js/common.install.js"></script>
<script>
    // checkPwd();//检查数据密码
    let isConn=checkDb();//检查数据库
    let time = 0,
        timer = null;

    // 上一步
    $('button.prev').click(function () {
        location.href = '?step=3';
    });

    // 下一步
    $('button.next').click(function () {

        let dbhost = $("#dbhost").val();
        let dbport = $("#dbport").val();
        let dbuser = $("#dbuser").val();
        let dbpwd = $("#dbpwd").val();
        let dbname = $("#dbname").val();
        let my_website = $("#my_website").val();
        let email = $("#email").val();
        let domain = $("#domain").val();
        let username = $("#username").val();
        let password = $("#password").val();
        let confirm_password = $("#confirm_password").val();
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
        if(isConn){
            return;
        }
        if(dbname == ""){
            blurInfo("dbname", "dbname-info", "数据库名称为空");
            return;
        }
        if(domain == ""){
            foxui.message({
                text: "网站域名为空",
                type: 'danger'
            })
            return;
        }
        if(username == ""){
            foxui.message({
                text: "管理员账号为空",
                type: 'danger'
            })
            return;
        }
        if(!isPwd()){
            return;
        }
        if(confirm_password != password){
            foxui.message({
                text: "确认密码不一致",
                type: 'danger'
            })
            return;
        }

        foxui.loading({
            el: 'body',
            type: 'block',
            background: 'black',
            text: '正在安装',
            info: '安装中，请稍后...',
            iconType: 'spinner',
            iconColor: '#000000',
            iconSize: 42,
        });
        timer = setInterval(() => {
            time++;
            _calTime();
        }, 1000);

        $.ajax({
            url: 'installdb.php',
            data: {
                check:"install_struct",
                dbhost,
                dbport,
                dbuser,
                dbpwd,
                dbname,
                my_website,
                email,
                domain,
                username,
                password,
                'url_prefix':$('#url_prefix').val()
            },
            type: 'post',
            dataType: 'html',
            success: function (data) {
                if(data == "pwd"){
                    let text = "密码必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位！";
                    foxui.message({
                        type:"danger",
                        text: text
                    });
                    onSuccess()
                }else if(data == "true"){
                    $.ajax({
                        url: 'installdb.php',
                        data: {
                            check:"install_data",
                            dbhost,
                            dbport,
                            dbuser,
                            dbpwd,
                            dbname,
                            my_website,
                            email,
                            domain,
                            username,
                            password,
                            'url_prefix':$('#url_prefix').val(),
                            'data_method':$('.data-method .foxui-radio.is-checked input').val()
                        },
                        type: 'post',
                        dataType: 'html',
                        success: function (data) {
                            let text = "";
                            let type = "danger";
                            if(data == "conn"){
                                text = "数据库连接失败！";
                            }else if (data == "create"){
                                text = "数据库创建失败！";
                            }else if (data == "false"){
                                text = "基础数据安装出错！";
                            }else if (data == "true"){
                                type = "success";
                                text = "安装成功";
                                location.href = '?step=5';
                            }else{
                                text = "基础数据安装出错！";
                            }
                            foxui.closeLoading();
                            onSuccess();
                            foxui.message({
                                type:type,
                                text:text
                            });
                        }
                    });
                }else{
                    foxui.closeLoading();
                    foxui.message({
                        type:"danger",
                        text:"数据库表安装出错！"
                    });
                    onSuccess()
                }
            }
        });

    });

    // 安装成功后跳转
    function onSuccess() {
        time = 0;
        foxui.closeLoading();
        clearInterval(timer);
        timer = null;
    }

    // 计时
    function _calTime() {
        $('.foxui-loading.is-fullscreen .foxui-loading-inner.block p').text(`安装中，请稍后(${time}s)`);
    }
</script>
<script>
    //密码校验
    function isPwd(){
        let text= $("#password").val();
        let re =/^(?![a-zA-Z]+$)(?![A-Z0-9]+$)(?![A-Z\W_]+$)(?![a-z0-9]+$)(?![a-z\W_]+$)(?![0-9\W_]+$)[a-zA-Z0-9\W_]{8,}$/;
        let result =  re.test(text);
        if(!result) {
            $("#password-info").html("<span class='info color-danger'>密码必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位</span>");
            return false;
        }else {
            $("#password-info").html("");
            return true;
        }
    };
</script>
</html>
