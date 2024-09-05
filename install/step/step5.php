<!DOCTYPE html>
<html lang="cn">
    <?php include "head.html"?>
    <body>
        <div class="install-container step5-container limit-height">
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
                            <div class="step active">
                                <h3>安装完成</h3>
                                <div class="circle"></div>
                            </div>
                        </div>
                        <div>
                            <div class="foxui-progress foxui-progress-type__line" data-foxid="foxid1073">
                                <div class="foxui-progress-line">
                                    <div class="foxui-progress-line__outer" style="height:12px;">
                                        <div class="foxui-progress-line__inner foxui-progress-line__striped foxui-progress-line__animated" style="width:100%; ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="step-main step-main-single">
                        <div class="success-block">
                            <div class="icon">
                                <i class="foxui-icon-zhengque-f color-success"></i>
                            </div>
                            <h1>安装完成!</h1>
                            <p>恭喜您，已成功安装黔狐内容管理系统</p>
                            <p class="foxui-margin-top-12">请牢记您的后台入口</p>
                            <div class="foxui-input-group margin-top-46"
                                 style="background-color: #f4f4f5;
                                 justify-content: center;
                                 border-color: #e9e9eb;
                                 color: #909399;
                                 border-radius: 20px;
                                 font-size: 16px;
                                 padding: 12px 10px";

                            >
                                <i class="foxui-icon-anquan-o"></i>
                                <span style="margin-left: 14px;cursor: pointer;" onclick="copyPath(event)">
                                    <?php echo $adminPath;?>
                                </span>
                            </div>
                            <div class="btn-list">
                                <button class="foxui-size-medium foxui-shape-round is-long" name="index">
                                    <i class="foxui-icon-shouye-o"></i>
                                    <span>预览首页</span>
                                </button>
                                <button class="foxui-solid-primary foxui-size-medium foxui-shape-round is-long" name="admin">
                                    <i class="foxui-icon-xitong-o"></i>
                                    <span>进入后台</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include "footer.html"?>
        </div>
    </body>
    <script src="/static/js/jquery-3.6.0.min.js"></script>
    <script src="/static/js/foxui-1.32.min.js"></script>
    <script src="<?php echo INP;?>"></script>
    <script>
        //首页
        $('button[name="index"]').click(function () {
            location.href = "/index.php";
        })
        //后台
        $('button[name="admin"]').click(function () {
            location.href = "/"+"<?php echo $adminPathNew;?>"+".php";
        })
        function copyPath(event) {
            const text = event.target.innerText,
                el = document.createElement('input');
            el.value = text;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.opacity = 0;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            foxui.message({
                text: "后台地址已复制",
                type: 'warning'
            })
        }
        $.ajax({
            url: "/index.php/"+"<?php echo $adminPathNew;?>"+"/login/renameInstall",
            type: 'get',
            success: function (data) {}
        });
    </script>
</html>
