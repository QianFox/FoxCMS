<?php
//zsl
namespace app\email\controller;

use app\common\controller\AdminApplyBase;
use PHPMailer\PHPMailer\PHPMailer;
use think\facade\Db;
use think\facade\View;

class PluginMailConfig extends AdminApplyBase
{

    public function index()
    {
        $pmcArr = Db::name('plugin_mail_config')->select();
        if(sizeof($pmcArr) > 0){
            $pluginMailConfig  = $pmcArr[0];
            View::assign("pluginMailConfig", $pluginMailConfig);
        }
        return view('index');
    }

    public function save(){
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if(empty($param['id'])){
                unset($param['id']);
                $r = Db::name('plugin_mail_config')->save($param);
            }else{
                $id = $param['id'];
                $pmc = Db::name('plugin_mail_config')->find($id);
                $updateData = [];
                if($pmc['smtp_url'] != $param['smtp_url']){
                    $updateData['smtp_url'] = $param['smtp_url'];
                }
                if($pmc['smtp_port'] != $param['smtp_port']){
                    $updateData['smtp_port'] = $param['smtp_port'];
                }
                if($pmc['send_account'] != $param['send_account']){
                    $updateData['send_account'] = $param['send_account'];
                }
                if($pmc['auth_code'] != $param['auth_code']){
                    $updateData['auth_code'] = $param['auth_code'];
                }
                if($pmc['test_account'] != $param['test_account']){
                    $updateData['test_account'] = $param['test_account'];
                }
                if(sizeof($updateData) <= 0){
                    $this->error("没有更新数据");
                }
                $r = Db::name('plugin_mail_config')
                    ->where('id', $id)
                    ->update($updateData);
            }
            if($r){
                $this->success("操作成功");
            }else{
                $this->error("操作失败");
            }
        }
        $this->error("操作失败");
    }

    public function sendMail(){
        $param = $this->request->param();
        $title = "网站消息通知";
        $to = $param['test_account'];
        $content = "
<!DOCTYPE html>
<html>
<head>
    <title>邮件通知</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; }
        h1 { color: #333; }
        p { color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>尊敬的FoxCMS用户</h1>
        <p>这是一封来自您网站的通知邮件。</p>
        <p style='font-weight: bold'>您网站收到新的表单信息，请登录网后后台“应用-自定义表单”中查看。</p>
        <p>如果您有任何疑问，请随时联系我们。</p>
        <p>感谢您的支持！</p>
        <hr />
        <p>此致<br/>黔狐团队</p>
    </div>
</body>
</html>
";
        //实例化PHPMailer核心类
        $mail = new PHPMailer();
        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug = 0;
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        //smtp需要鉴权 这个必须是true
        $mail->SMTPAuth = true;
        //链接qq域名邮箱的服务器地址
        $mail->Host = trim($param['smtp_url']);
        //设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
        $mail->Port = $param['smtp_port'];
        //设置smtp的helo消息头 这个可有可无 内容任意
        // $mail->Helo = 'Hello smtp.qq.com Server';
        //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
        $mail->Hostname = '';
        //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        $mail->CharSet = 'UTF-8';
        //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = 'FoxCMS网站消息';
        //smtp登录的账号 这里填入字符串格式的qq号即可
        $mail->Username = trim($param['send_account']);
        //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
        $mail->Password = trim($param['auth_code']);
        //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
        $mail->From = trim($param['send_account']);
        //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);
        //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        $mail->addAddress($to,'');
        //添加多个收件人 则多次调用方法即可
        // $mail->addAddress('xxx@163.com','');
        //添加该邮件的主题
        $mail->Subject = $title;
        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = $content;
        //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
        // $mail->addAttachment('./d.jpg','mm.jpg');
        //同样该方法可以多次调用 上传多个附件
        // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
        try {
            $result = $mail->send();
            if (!$result) {
                $msg = $mail->ErrorInfo;
                if (stristr($msg, 'smtp connect() failed')) {
                    if (465 == $mail->Port) {
                        $msg = '请检查配置填写是否正确或更改PHP版本后再重试。';
                    } else {
                        $msg = '请检查SMTP端口填写是否正确，一般默认是465端口，其次25端口，具体请参看各STMP服务商的设置说明。';
                    }
                }
                return array('code'=>0 , 'msg'=>'发送失败:'.$msg);
            } else {
                return array('code'=>1 , 'msg'=>'发送成功');
            }
        } catch (\Exception $e) {
            return array('code'=>0 , 'msg'=>'发送失败: '.$e->errorMessage());
        }
    }
}
