/*
 * @Descripttion : FOXCMS是一款高效的PHP多端跨平台内容管理系统
 * @Author       : FoxCMS Team
 * @Date         : 2023-01-14 15:23:23
 * @version      : V1.08
 * @copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2024-02-20 14:25:14
 */

new WOW().init();

// 分享至QQ、微信、新浪
/**
 * @description: 分享至QQ空间
 * @param {*} url             分享地址(默认为当前页)
 * @param {*} title           分享标题(可选)
 * @param {*} showcount       是否显示分享总数 --> 显示：'1'； 不显示(默认)：'0'
 * @param {*} desc            分享理由
 * @param {*} summary         分享描述(可选)
 * @param {*} pic             分享图片(可选)
 * @param {*} flash           视频地址(可选)
 * @param {*} site            分享来源 (可选)
 * @return {*}
 * @Date: 2022-05-09 17:27:14
 */
function shareQQ(url, title, showcount, desc, summary, pic, flash, site) {
    var param = {
        url: url || window.location.href,
        title: title || '',
        showcount: showcount || '1',
        desc: desc || '',
        summary: summary || '',
        pics: pic || '',
        flash: flash || '',
        site: site || '',
    };
    var temp = [];
    for (var i in param) {
        temp.push(i + '=' + encodeURIComponent(param[i] || ''));
    }
    var targetUrl = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?' + temp.join('&'); // 分享至QQ空间
    // var targetUrl = 'http://connect.qq.com/widget/shareqq/iframe_index.html?' + temp.join('&'); // 分享至QQ
    window.open(targetUrl, 'qq', 'height=430, width=400');
}

/**
 * @description: 分享至微信(二维码)
 * @param {*} url
 * @return {*}
 * @Date: 2022-05-12 22:53:27
 */
function shareWeixin(url) {
    url = url || window.location.href;
    let encodePath = encodeURIComponent(url),
        targetUrl = '//api.qianfox.com/api/qrcode?text=' + encodePath;
    // window.open(targetUrl, 'weixin', 'height=320, width=320');
    foxui.dialog({
        title: '微信',
        content: `<div class="foxui-display-flex foxui-justify-content-center"><img width="180" src="${targetUrl}"/></div>`,
        width: '340px',
    });
}

/**
 * @description: 分享至新浪微博
 * @param {*} url              分享地址(默认为当前页)
 * @param {*} title            分享标题(可选，默认为所在页面的title)
 * @param {*} type
 * @param {*} pic              分享图片的路径(可选)
 * @param {*} count            是否显示分享数 --> 显示：'1'； 不显示(默认)：'0'
 * @param {*} appkey           您申请的应用appkey,显示分享来源(可选)
 * @param {*} ralateUid        关联用户的UID，分享微博会@该用户(可选)
 * @param {*} rnd              分享时间
 * @return {*}
 * @Date: 2022-05-12 21:42:07
 */
function shareSina(url, title, type, pic, count, appkey, ralateUid, rnd) {
    var param = {
        url: url || window.location.href,
        title: title || '',
        type: type || '3',
        pic: pic || '',
        count: count || '1',
        appkey: appkey || '',
        ralateUid: ralateUid || '',
        rnd: rnd || new Date().valueOf(),
    };
    var temp = [];
    for (var p in param) {
        temp.push(p + '=' + encodeURIComponent(param[p] || ''));
    }
    var targetUrl = 'http://service.weibo.com/share/share.php?' + temp.join('&');
    window.open(targetUrl, 'sinaweibo', 'height=430, width=400');
}
