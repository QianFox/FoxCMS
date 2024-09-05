/*
 * @Descripttion : FOXCMS是一款高效的PHP多端跨平台内容管理系统
 * @Author       : FoxCMS Team
 * @Date         : 2022-12-21 14:45:08
 * @version      : V1.08
 * @copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023-01-03 15:00:28
 */
// let list = [
//     {
//         groupName: '线块图标',
//         iconList: [
//             { name: '分类', icon: 'icon-fenlei1' },
//             { name: '分类', icon: 'icon-fenlei2' },
//             { name: '视频', icon: 'icon-shipin1' },
//             { name: '视频', icon: 'icon-shipin2' },
//             { name: '等级', icon: 'icon-dengji2' },
//             { name: '等级', icon: 'icon-dengji1' },
//             { name: '等级', icon: 'icon-dengji3' },
//             { name: '等级', icon: 'icon-dengji4' },
//             { name: '联系', icon: 'icon-lianxi1' },
//             { name: '联系', icon: 'icon-lianxi2' },
//             { name: '首页', icon: 'icon-shouye1' },
//             { name: '首页', icon: 'icon-shouye2' },
//             { name: '位置', icon: 'icon-weizhi1' },
//             { name: '位置', icon: 'icon-weizhi2' },
//             { name: '团队', icon: 'icon-tuandui1' },
//             { name: '团队', icon: 'icon-tuandui2' },
//             { name: '钻石', icon: 'icon-zuanshi1' },
//             { name: '钻石', icon: 'icon-zuanshi2' },
//             { name: '新闻', icon: 'icon-xinwen1' },
//             { name: '新闻', icon: 'icon-xinwen2' },
//             { name: '收藏', icon: 'icon-shoucang1' },
//             { name: '收藏', icon: 'icon-shoucang2' },
//             { name: '文章', icon: 'icon-wenzhang2' },
//             { name: '文章', icon: 'icon-wenzhang1' },
//         ],
//     },
//     {
//         groupName: '线型图标',
//         iconList: [
//             { name: '电话', icon: 'icon-dianhua' },
//             { name: '返回', icon: 'icon-fanhui' },
//             { name: '箭头', icon: 'icon-jiantou' },
//             { name: '浏览', icon: 'icon-liulan' },
//             { name: '电话2', icon: 'icon-dianhua2' },
//             { name: '客服', icon: 'icon-kefu' },
//             { name: '搜索', icon: 'icon-sousuo' },
//             { name: '时间', icon: 'icon-shijian' },
//             { name: '头条', icon: 'icon-toutiao' },
//         ],
//     },
// ];
// foxui.iconsel.init(list);

$(document).on('click', '.input-color-group .foxui-input-button', function () {
    let $this = $(this),
        $item = $this.closest('.foxui-display-flex'),
        color = $this.siblings('.default-color').val();
    _modifyColor($item, color);
});

$(document).on('blur', '.input-color-group .color-show-text', function () {
    let $this = $(this),
        $item = $this.closest('.foxui-display-flex'),
        color = $this.val();
    if (!_CheckIsColor(color)) {
        color = $this.siblings('.default-color').val();
    }
    _modifyColor($item, color);
});

$(document).on('click', '.input-color-group .foxui-color .confirm', function () {
    let $this = $(this),
        $item = $this.closest('.foxui-display-flex'),
        color = $this.closest('.foxui-color-bottom__btns').find('.foxui-color-botton__input input').val();
    _modifyColor($item, color);
});

foxui.iconsel.$on(({ target, iconClass }) => {
    let id = $(target).closest('.foxui-iconsel').attr('id');
    if (id === 'tabbarIcon1') {
        $('#tabbar').find('.tabbar-item i').eq(0).attr('class', iconClass);
    } else if (id === 'tabbarIcon2') {
        $('#tabbar').find('.tabbar-item i').eq(1).attr('class', iconClass);
    } else if (id === 'tabbarIcon3') {
        $('#tabbar').find('.tabbar-item i').eq(2).attr('class', iconClass);
    } else if (id === 'tabbarIcon4') {
        $('#tabbar').find('.tabbar-item i').eq(3).attr('class', iconClass);
    }
});

$(document).on('input', '#tabbarText1', function () {
    $('#tabbar').find('.tabbar-item span').eq(0).text($(this).val());
});
$(document).on('input', '#tabbarText2', function () {
    $('#tabbar').find('.tabbar-item span').eq(1).text($(this).val());
});
$(document).on('input', '#tabbarText3', function () {
    $('#tabbar').find('.tabbar-item span').eq(2).text($(this).val());
});
$(document).on('input', '#tabbarText4', function () {
    $('#tabbar').find('.tabbar-item span').eq(3).text($(this).val());
});

// 修改颜色
function _modifyColor($item, color) {
    // 左边颜色修改
    let id = $item.attr('id');
    $item.find('.foxui-color-show').css('background-color', color);
    $item.find('.foxui-color-input').val(color);
    $item.find('.color-show-text').val(color);
    // 调整左边显示效果
    if (id === 'tabbarLineColor') {
        $('#tabbar').css('border-color', color);
    } else if (id === 'tabbarBgColor') {
        $('#tabbar').css('background-color', color);
    } else if (id === 'tabbarIconDefaultColor') {
        $('#tabbar').find('.tabbar-item:not(.acitve) i').css('color', color);
    } else if (id === 'tabbarIconActiveColor') {
        $('#tabbar').find('.tabbar-item.acitve i').css('color', color);
    } else if (id === 'tabbartextDefaultColor') {
        $('#tabbar').find('.tabbar-item:not(.acitve) span').css('color', color);
    } else if (id === 'tabbartextActiveColor') {
        $('#tabbar').find('.tabbar-item.acitve span').css('color', color);
    }
}
