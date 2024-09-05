<!DOCTYPE html>
<html lang="cn">
    <?php include "head.html"?>
    <body>
        <div class="install-container step3-container">
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
                            <div class="step">
                                <h3>参数配置</h3>
                                <div class="circle"></div>
                            </div>
                            <div class="step">
                                <h3>安装完成</h3>
                                <div class="circle"></div>
                            </div>
                        </div>
                        <div>
                            <div class="foxui-progress foxui-progress-type__line" data-foxid="foxid8799">
                                <div class="foxui-progress-line">
                                    <div class="foxui-progress-line__outer" style="height:12px;">
                                        <div class="foxui-progress-line__inner foxui-progress-line__striped foxui-progress-line__animated" style="width:33.33%;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="step-main step-main-multiple">
                        <div class="panel">
                            <div class="panel-head">服务器信息</div>
                            <div class="panel-main foxui-padding-0">
                                <div class="slide">
                                    <div class="foxui-table server-info-table foxui-table-border-bottom">
                                        <ul class="foxui-table-thead foxui-margin-left-0">
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-th foxui-padding-left-28">参数</div>
                                                <div class="foxui-table-th">值</div>
                                                <div class="foxui-table-th">说明</div>
                                            </li>
                                        </ul>
                                        <ul class="foxui-table-tbody foxui-margin-left-0">
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-td foxui-padding-left-28">服务器域名</div>
                                                <div class="foxui-table-td"><?php echo $serverURLInfo['domain'];?></div>
                                                <div class="foxui-table-td"></div>
                                            </li>
                                            <li class="foxui-table-tr <?php echo $bg_warning;?>">
                                                <div class="foxui-table-td foxui-padding-left-28">服务器操作系统</div>
                                                <div class="foxui-table-td"><?php echo $serverInfo['server_os'];?></div>
                                                <div class="foxui-table-td"><?php echo $bg_text;?></div>
                                            </li>
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-td foxui-padding-left-28">Web服务器环境</div>
                                                <div class="foxui-table-td"><?php echo $serverInfo['web_server_environment'];?></div>
                                                <div class="foxui-table-td"></div>
                                            </li>
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-td foxui-padding-left-28">PHP版本</div>
                                                <div class="foxui-table-td"><?php echo $serverInfo['php_version'];?></div>
                                                <div class="foxui-table-td"></div>
                                            </li>
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-td foxui-padding-left-28">系统安装目录</div>
                                                <div class="foxui-table-td"><?php echo $serverInfo['system_dir'];?></div>
                                                <div class="foxui-table-td"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel foxui-margin-top-24">
                            <div class="panel-head justify-between">
                                <span>服务器环境检测</span>
                                <span class="info">服务器环境要求必须满足下列所有条件，否则本系统或部份功能将无法使用</span>
                            </div>
                            <div class="panel-main foxui-padding-0">
                                <div class="slide">
                                    <div class="foxui-table server-check-table foxui-table-border-bottom">
                                        <ul class="foxui-table-thead foxui-margin-left-0">
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-th foxui-padding-left-28">选项</div>
                                                <div class="foxui-table-th">要求</div>
                                                <div class="foxui-table-th">状态</div>
                                                <div class="foxui-table-th">说明及帮助</div>
                                            </li>
                                        </ul>
                                        <ul class="foxui-table-tbody foxui-margin-left-0">
                                            <?php foreach ($envInfo as $item): ?>
                                                <li class="foxui-table-tr">
                                                    <div class="foxui-table-td foxui-padding-left-28"><?= $item['name'] ?></div>
                                                    <div class="foxui-table-td"><?= $item['ask'] ?></div>
                                                    <div class="foxui-table-td" data-name="<?= $item['flag']?>" data-status="<?= $item['status']?>">
                                                        <i class="<?= $item['status'] ? 'foxui-icon-zhengque-f' : 'foxui-icon-cuowu-f' ?>"></i>
                                                    </div>
                                                    <div class="foxui-table-td"><?= $item['remark']?></div>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel foxui-margin-top-24">
                            <div class="panel-head justify-between">
                                <span>目录检测</span>
                                <span class="info">服务器环境要求必须满足下列所有条件，否则本系统或部份功能将无法使用</span>
                            </div>
                            <div class="panel-main foxui-padding-0">
                                <div class="slide">
                                    <div class="foxui-table folder-check-table foxui-table-border-bottom">
                                        <ul class="foxui-table-thead foxui-margin-left-0">
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-th foxui-padding-left-28">选项</div>
                                                <div class="foxui-table-th">读取权限</div>
                                                <div class="foxui-table-th">写入权限</div>
                                            </li>
                                        </ul>
                                        <ul class="foxui-table-tbody foxui-margin-left-0">
                                            <?php foreach ($dirInfo as $item): ?>
                                            <li class="foxui-table-tr">
                                                <div class="foxui-table-td foxui-padding-left-28"><?= $item['dir'] ?></div>
                                                <div class="foxui-table-td">
                                                    <i class="<?= $item['read_status'] ? 'foxui-icon-zhengque-f' : 'foxui-icon-cuowu-f' ?>"></i>
                                                </div>
                                                <div class="foxui-table-td">
                                                    <i class="<?= $item['write_status'] ? 'foxui-icon-zhengque-f' : 'foxui-icon-cuowu-f' ?>"></i>
                                                </div>
                                            </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="step-foot">
                        <button class="foxui-size-medium is-long prev">
                            <i class="foxui-icon-xiangzuo-o"></i>
                            <span>后退</span>
                        </button>
                        <button class="foxui-solid-primary foxui-size-medium is-long next">
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
    <script>
        $('button.prev').click(function () {
            location.href = '?step=2';
        });

        $('button.next').click(function () {
            let status = $('div[data-name="php"]').attr("data-status");
            if(status == 1){
                location.href = '?step=4';
            }else{
                foxui.message({
                    type:"danger",
                    text:"php版本太低，请先调整PHP版本大于等于7.2+的版本"
                });
                return;
            }
        });
    </script>
</html>
